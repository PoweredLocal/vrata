# vrata
[![Build Status](https://travis-ci.org/PoweredLocal/vrata.svg)](https://travis-ci.org/PoweredLocal/vrata)
[![Code Climate](https://codeclimate.com/github/PoweredLocal/vrata/badges/gpa.svg)](https://codeclimate.com/github/PoweredLocal/vrata)
[![Test Coverage](https://codeclimate.com/github/PoweredLocal/vrata/badges/coverage.svg)](https://codeclimate.com/github/PoweredLocal/vrata/coverage)

API gateway implemented in PHP and Lumen

## Preface

API gateway is an important component of microservices architectural pattern – it's a layer that sits in front of all your services. [Read more](http://microservices.io/patterns/apigateway.html)

## Overview

Vrata (Russian for 'gates') is a simple API gateway implemented in PHP7 with Lumen framework

## Requirements and dependencies

- PHP >= 5.6
- Lumen 5.3
- Guzzle 6
- Laravel Passport

## Features

- Built-in OAuth2 server to handle authentication for all incoming requests
- Aggregate queries (combine output from 2+ APIs) *
- Aggregate Swagger documentation (combine Swagger docs from underlying services) *
- Sync and async outgoing requests *
- DNS service discovery *

* work in progress

## Copyright

PoweredLocal 2016. Made in Melbourne
