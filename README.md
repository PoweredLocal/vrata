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

Introductory blog post [in English](https://medium.com/@poweredlocal/developing-a-simple-api-gateway-in-php-and-lumen-f84756cce043#.6yd9uwmat), [in Russian](https://habrahabr.ru/post/315128/)

## Requirements and dependencies

- PHP >= 7.0
- Lumen 5.3
- Guzzle 6
- Laravel Passport (with [Lumen Passport](https://github.com/dusterio/lumen-passport))
- Memcached (for request throttling)

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

#### PRIVATE_KEY

Put your private RSA key in this variable

You can generate the key with OpenSSL:

```bash
$ openssl genrsa -out private.key 4096
```

Replace new line characters with \n:
```bash
awk 1 ORS='\\n' private.key
```

#### PUBLIC_KEY

Put your public RSA key in this variable

Extract public key using OpenSSL:
```bash
$ openssl rsa -in private.key -pubout > public.key
```

Replace new line characters with \n:
```bash
awk 1 ORS='\\n' public.key
```

#### GATEWAY_SERVICES

JSON array of microservices behind the API gateway

#### GATEWAY_ROUTES

JSON array of extra routes including any aggregate routes

#### GATEWAY_GLOBAL

JSON object with global settings

### Logging

Currently only LogEntries is supported out of the box. To send nginx and Lumen logs to LE, simply set two 
environmetn variables:

#### LOGGING_ID

Identification string for this app

#### LOGGING_LOGENTRIES

Your user key with LogEntries

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

## Features

### Basic output mutation

You can do basic JSON output mutation using ```output``` property of an action. Eg.
```php
[
    'service' => 'service1',
    'method' => 'GET',
    'path' => '/pages/{page}',
    'sequence' => 0,
    'output_key' => 'data'
];
```

Response from *service1* will be included in the final output as *data* key. 

```output_key``` can be an array to allow further mutation:
```php
[
    'service' => 'service1',
    'method' => 'GET',
    'path' => '/pages/{page}',
    'sequence' => 0,
    'output_key' => [
        'id' => 'service_id',
        'title' => 'service_title',
        '*' => 'service_more'
    ]
];
```

This will assign contents of *id* property to *garbage_id*, *title* to *service_title*
and the rest of the content will be inside of *service_more* property of the output JSON.

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

## Examples

### Example 1: Single, simple microservice

Let's say we have a very simple setup: API Gateway + one microservice behind it.

First, we need to let the gateway know about this microservice by adding it to GATEWAY_SERVICES environment variable.

```json
{
	"service": []
}
```

Where *service* is the nickname we chose for our microservice. The array is empty because we will rely on default settings.
Our service has a valid Swagger documentation endpoint running on ```api/doc``` URL.

Next, we provide global settings on GATEWAY_GLOBAL environment variable:

```json
{
	"prefix": "/v1",
	"timeout": 3.0,
	"doc_point": "/api/doc",
	"domain": "supercompany.io"
}
```

This tells the gateway that services that don't have explicit URLs provided, will be communicated at
{serviceId}.{domain}, therefore our service will be contacted at service.supercompany.io, request timeout will be 3 seconds,
Swagger documentation will be loaded from ```/api/doc``` and all routes will be prefixed with "v1".

We could however specify service's hostname explicitly using "hostname" key in the GATEWAY_SERVICES array.

Now we can run ```php artisan gateway:parse``` to force Vrata to parse Swagger documentation
provided by this service. All documented routes will be exposed in this API gateway.

If you use our Docker image, this command will be executed every time you start a container.

Now, if your service had a route ```GET http://service.supercompany.io/users```, it will be available as
```GET http://api-gateway.supercompany.io/v1/users``` and all requests will be subject to JSON Web Token check and rate limiting.

Don't forget to set PRIVATE_KEY and PUBLIC_KEY environment variables, they are necessary for authentication to work.

## License

The MIT License (MIT)

Copyright (c) 2017 PoweredLocal

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
