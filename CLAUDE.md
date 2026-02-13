# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Event Relay is a Symfony 7.3 webhook relay service. It receives incoming HTTP requests, stores them as queries, and asynchronously forwards them to configured callback URLs based on rules. Rules define how to transform and relay the request body using Symfony ExpressionLanguage for variable substitution.

## Development Commands

All commands run via Docker. Start with `make up` (builds and starts containers).

| Command | Description |
|---------|-------------|
| `make up` | Build and start Docker containers |
| `make down` | Stop containers |
| `make bash` | Shell into PHP container |
| `make migrate` | Run Doctrine migrations |
| `make migration` | Generate a new migration |
| `make messenger` | Start Symfony Messenger consumer (processes async tasks) |

### Inside the PHP container

- `bin/console` — Symfony console
- `vendor/bin/phpunit` — Run tests (PHPUnit 12, config in `phpunit.dist.xml`)
- `bin/console app:test` — Retry all queries with `error` status

## Infrastructure

- **Runtime**: FrankenPHP (dunglas/frankenphp) on port 15080
- **Database**: MySQL 8 on port 15306 (user: `user`, password: `123123`, database: `app`)
- **Queue**: LocalStack SQS on port 4566 (used as Messenger transport via `MESSENGER_TRANSPORT_DSN`)
- **Async processing**: Symfony Messenger with `doctrine_transaction` middleware, failed transport stored in `doctrine://default?queue_name=failed`

## Architecture

### Request Flow

1. `POST /key/{rule}` (DefaultController::relay) — receives a webhook, persists a `Query` entity, dispatches `TaskMessage` to Messenger
2. `TaskHandler` (async) — loads the Query, resolves Rule variables using ExpressionLanguage against the request body, makes HTTP callback via HttpClient
3. Query status transitions: `waiting` → `done` or `error` (see `QueryStatus` enum)

### Key Domain Concepts

- **Rule** — defines a relay target: callback URL, HTTP method, headers, content type, a query template with variable placeholders, and variable mappings (dot-notation paths into the incoming JSON body)
- **Query** — an incoming request log tied to a Rule, tracking status, attempts, execution time, and errors
- **TaskMessage** — simple message carrying a Query UUID for async dispatch

### Entities use UUID primary keys (Symfony UID) with Doctrine attribute mapping (`src/Entity/`).

## Conventions

- PHP 8.2+, Symfony autowiring and autoconfiguration enabled
- Doctrine ORM with attribute-based mapping
- Messenger bus configured with `allow_no_handlers` default middleware
- `AbstractRepository` base class provides `prepare()` helper for raw SQL; `QueryRepository` extends plain `EntityRepository` (not AbstractRepository)