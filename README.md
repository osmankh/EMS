# EMS

Repository for a simple Expense Management System

## External Dependencies and Prerequisites

* [Docker](https://docs.docker.com/desktop/mac/install/)
* [PHP 8.1 or greater](https://stitcher.io/blog/php-81-upgrade-mac)
  * Postgres and sqlite extensions

## Ports

* APP
  * By default, the port **8000** is used when running the app in development.
* DB
  * DB port is **5432**
  * To access the Database using a DB Tool, run `docker ps` get your container port.

## Project setup

* Clone this repo
* For the first time setup, you can run the `make init` command, and it will prepare the following:
  * Dev database using docker
  * Install composer dependencies
  * Create and migrate the database
  * Seed initial Expense Types (In this implementation I've stored the types in DB instead of Enums just to avoid deployments on enum change)

* Run `make run`
  * To start symfony server

## OpenAPI schema (Swagger)

After completing project setup, run the project using `make run` then access the doc under `/api/doc`

## Running tests

This project contains two tiers of tests

* Unit: unit tests
* Integration: Integration tests; spin up the symfony kernel and run against that service. Tests DB is sqlite in memory database.

You can run tests using `composer test` script.

## Helpful commands

* `make init`: Initiate and set up the project.
* `make run`: Start symfony server
* `composer test`: Run unit and integration tests
* `composer cs`: Run php cs fixer