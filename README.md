# vrata
[![Build Status](https://travis-ci.org/PoweredLocal/vrata.svg)](https://travis-ci.org/PoweredLocal/vrata)
[![Latest Stable Version](https://poser.pugx.org/poweredlocal/vrata/v/stable)](https://packagist.org/packages/poweredlocal/vrata)
[![Code Climate](https://codeclimate.com/github/PoweredLocal/vrata/badges/gpa.svg)](https://codeclimate.com/github/PoweredLocal/vrata)
[![Test Coverage](https://codeclimate.com/github/PoweredLocal/vrata/badges/coverage.svg)](https://codeclimate.com/github/PoweredLocal/vrata/coverage)
[![Total Downloads](https://poser.pugx.org/poweredlocal/vrata/downloads)](https://packagist.org/packages/poweredlocal/vrata)
[![License](https://poser.pugx.org/poweredlocal/vrata/license)](https://packagist.org/packages/poweredlocal/vrata)

[![Docker Hub](http://dockeri.co/image/pwred/vrata)](https://hub.docker.com/r/pwred/vrata/)

API gateway implemented in PHP and Lumen. Currently only supports JSON format.

## Preface

API gateway is an important component of microservices architectural pattern – it's a layer that sits in front of all your services. [Read more](http://microservices.io/patterns/apigateway.html)

## Overview

Vrata (Russian for 'gates') is a simple API gateway implemented in PHP7 with Lumen framework

## Requirements and dependencies

- PHP >= 7.0
- Lumen 5.3
- Guzzle 6
- Laravel Passport (with [Lumen Passport](https://github.com/dusterio/lumen-passport))

## Running as a Docker container

Ideally, you want to run this as a stateless Docker container configured entirely by environment variables. Therefore, you don't even need to deploy 
this code anywhere yourself - just use our [public Docker Hub image](https://hub.docker.com/r/pwred/vrata).

Deploying it is as easy as:

```bash
$ docker run -d -e GATEWAY_SERVICES=... -e GATEWAY_GLOBAL=... -e GATEWAY_ROUTES=... pwred/vrata
```

Where environment variables are JSON encoded settings (see configuration options below).

## Configuration via environment variables

Ideally you won't need to touch any code at all. You could just snap the latest Docker image, set environment variables and done. API gateway is not a place to hold any business logic, API gateway is a smart proxy that can discover microservices, query them and process their responses with minimal adjustments.

### Terminology and structure

Internal structure of a typical API gateway - microservices setup is as follows:

![API gateway - structure](https://www.mysenko.com/images/vrata-internal_map.jpg)

Since API gateway doesn't have any state really it scales horizontally very well.

### Lumen variables

#### CACHE_DRIVER

It's recommended to set this to 'memcached' or another shared cache supported by Lumen if you are running multiple instances of API gateway. API rate limitting relies on cache.

#### DB_DATABASE, DB_HOST, DB_PASSWORD, DB_USERNAME, DB_CONNECTION

Standard Lumen variables for your database credentials. Use if you keep users in database.
See Laravel/Lumen documentation for the list of supported databases.

#### APP_KEY

Lumen application key 

### Gateway variables

#### GATEWAY_SERVICES

JSON array of microservices behind the API gateway

#### GATEWAY_ROUTES

JSON array of extra routes including any aggregate routes

#### GATEWAY_GLOBAL

JSON object with global settings

## Features

- Built-in OAuth2 server to handle authentication for all incoming requests
- Aggregate queries (combine output from 2+ APIs)
- Output restructuring 
- Aggregate Swagger documentation (combine Swagger docs from underlying services) *
- Automatic mount of routes based on Swagger JSON
- Sync and async outgoing requests 
- DNS service discovery 

## Installation

You can either do a git clone or use composer (Packagist):

```bash
$ composer create-project poweredlocal/vrata
```

## Performance

Performance is one of the key indicators of an API gateway and that's why we chose Lumen – bootstrap only takes ~25ms on a basic machine.

See an example of an aggregate request. First let's do separate requests to underlying microservices:

```bash
$ time curl http://service1.local/devices/5
{"id":5,"network_id":2,...}
real    0m0.025s

$ time curl http://service1.local/networks/2
{"id":2,...}
real    0m0.025s

$ time curl http://service2.local/visits/2
[{"id":1,...},{...}]
real    0m0.041s
```

So that's 91ms of real OS time – including all the web-server-related overhead. Let's now make a single aggregate request to the API gateway
which behind the scenes will make the same 3 requests:

```bash
$ time curl http://gateway.local/devices/5/details
{"data":{"device":{...},"network":{"settings":{...},"visits":[]}}}
real    0m0.056s
```

And it's just 56ms for all 3 requests! Second and third requests were executed in parallel (in async mode). 

This is pretty decent, we think!

## Copyright

PoweredLocal 2016. Made in Melbourne
