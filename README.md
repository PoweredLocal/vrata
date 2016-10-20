# vrata
[![Build Status](https://travis-ci.org/PoweredLocal/vrata.svg)](https://travis-ci.org/PoweredLocal/vrata)
[![Latest Stable Version](https://poser.pugx.org/poweredlocal/vrata/v/stable)](https://packagist.org/packages/poweredlocal/vrata)
[![Code Climate](https://codeclimate.com/github/PoweredLocal/vrata/badges/gpa.svg)](https://codeclimate.com/github/PoweredLocal/vrata)
[![Test Coverage](https://codeclimate.com/github/PoweredLocal/vrata/badges/coverage.svg)](https://codeclimate.com/github/PoweredLocal/vrata/coverage)
[![Total Downloads](https://poser.pugx.org/poweredlocal/vrata/downloads)](https://packagist.org/packages/poweredlocal/vrata)
[![License](https://poser.pugx.org/poweredlocal/vrata/license)](https://packagist.org/packages/poweredlocal/vrata)

[![Docker Hub](http://dockeri.co/image/pwred/vrata)](https://hub.docker.com/r/pwred/vrata/)

API gateway implemented in PHP and Lumen

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

### Lumen variables

#### CACHE_DRIVER

It's recommended to set this to 'memcached' or another shared cache supported by Lumen if you are running multiple instances of API gateway. API rate limitting relies on cache.

#### DB_DATABASE, DB_HOST, DB_PASSWORD, DB_USERNAME

Standard Lumen variables for your database credentials. Use if you keep users in database 

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
- Sync and async outgoing requests 
- DNS service discovery *

* work in progress

## Installation

You can either do a git clone or use composer (Packagist):

```bash
$ composer create-project poweredlocal/vrata
```

## Copyright

PoweredLocal 2016. Made in Melbourne
