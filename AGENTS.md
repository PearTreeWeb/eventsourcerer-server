# AGENTS.md

Welcome to the **EventSourcerer Server** codebase. This guide is written for AI agents and developers to quickly understand the project's architecture, development practices, runtime environments, and operational commands.

---

## 1. Project Overview

**EventSourcerer** is a self-hosted event sourcing platform and administration server designed to ingest, sequence, project, and monitor event-driven systems.

Key capabilities:
- **Event Streaming & Ingestion:** Ingests events with sequence integrity and cryptographic verification.
- **Projections:** Real-time state projections built from event streams with customizable mutations and conditions.
- **Real-Time Sockets:** ReactPHP-powered WebSocket/TCP server for low-latency streaming and subscriber catch-up.
- **Web UI & Dashboard:** Built with Symfony UX (Twig Components, Live Components, Turbo, Stimulus) and Tailwind CSS.
- **REST / API Platform:** API Platform 4 endpoints secured with JWT tokens and application secrets.

---

## 2. Technology Stack

- **PHP Version:** PHP 8.5+ (strict typing enforced everywhere)
- **Framework:** Symfony 8.0 with API Platform 4
- **Web Server / Runtime:** [FrankenPHP](https://frankenphp.dev/) (Caddy v2 with Go-based PHP worker runtime and `baldinof/caddy-supervisor`)
- **Database:** PostgreSQL (Postgres 14+ / 16 recommended)
- **ORM / DBAL:** Doctrine ORM, Doctrine DBAL, Doctrine Migrations
- **Queue / Messenger:** Symfony Messenger with `async` and `projections` transports (supports AMQP / RabbitMQ and Redis)
- **Real-Time Networking:** ReactPHP socket server
- **Frontend:** Symfony UX (Twig Components, Live Components, Turbo, Stimulus), Tailwind CSS, AssetMapper / Importmap
- **Testing & Static Analysis:** PHPUnit 13, PHPStan (Level 6 with baseline)

---

## 3. Production Environment: Amazon ECS

> **Important:** In production, the application runs inside **Amazon ECS (Elastic Container Service)**.

### Architecture & Container Setup
- **Dockerfile:** `Dockerfile.ecs` is used for production builds.
  - Multi-stage build:
    1. **Builder stage:** Uses `dunglas/frankenphp:1-builder-php8.5-bookworm` to compile FrankenPHP via `xcaddy` with custom plugins (`caddy-supervisor`, `caddy-cbrotli`).
    2. **Runner stage:** Uses `dunglas/frankenphp:php8.5-bookworm`, installs production PHP extensions (`pdo_pgsql`, `redis`, `amqp`, `intl`, `zip`, `opcache`, `bcmath`, `pcntl`, etc.), copies the application, and runs `composer install --no-dev --optimize-autoloader`.
- **Exposed Ports:**
  - `80` (HTTP)
  - `443` (HTTPS)
  - `1984` (Socket Server)

### ECS Configuration & Entrypoint (`docker/php/entrypoint.sh`)
- In production ECS tasks, individual database environment variables are injected rather than a single `DATABASE_URL`:
  - `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_PORT` (default: 5432), `DB_NAME`, `DB_SERVER_VERSION` (default: 16)
- The container entrypoint automatically URL-encodes credentials and builds `DATABASE_URL`:
  ```bash
  DATABASE_URL="postgresql://${ENC_USER}:${ENC_PASS}@${DB_HOST}:${PORT}/${DB_NAME}?serverVersion=${SERVER_VER}&charset=utf8&sslmode=require"
  ```
- **Automatic Database Migrations:** The entrypoint automatically runs `doctrine:migrations:migrate` on startup unless `SKIP_MIGRATIONS=true` is set.
- **Certificates:** Automatically generates certificates on startup via `php bin/console app:setup:create_certificates` if missing.
- **Production Caddyfile:** Uses `docker/php/Caddyfile.ecs`, streaming logs as JSON directly to `stdout` for AWS CloudWatch log collection.

---

## 4. Local Development Environment

Local development runs via Docker Compose with a Traefik reverse proxy.

### Local URLs & Ports
- **Application Web UI:** `https://eventsourcerer.docker.localhost`
- **Mailpit (Email testing):** `https://mailpit.docker.localhost` (or `localhost:8025`)
- **Traefik Dashboard:** `https://traefik.docker.localhost:8081`
- **PostgreSQL:** Port `5432` (user: `app`, database: `app`, password: `!ChangeMe!`)

### Common Make Commands
```bash
# First-time setup (starts containers, composer install, certs, tailwind, migrations, loads projections)
make setup

# Start containers, run migrations, install dependencies
make up

# Stop containers
make down

# Open an interactive shell inside the PHP container
make in

# Clean environment (teardown volumes and rebuild)
make clean
```

---

## 5. Repository Layout & Architecture

The application adopts Domain-Driven Design (DDD) with CQRS patterns:

```
├── Dockerfile.ecs                 # Production Dockerfile for Amazon ECS
├── Makefile                       # Developer automation commands
├── assets/                        # Frontend assets (Tailwind CSS, Stimulus controllers)
├── config/                        # Symfony configuration files (packages, routes, services)
├── docker/                        # Local and production Docker configs
│   ├── php/
│   │   ├── Caddyfile              # Local development Caddyfile with supervisor
│   │   ├── Caddyfile.ecs          # Production Caddyfile for ECS
│   │   ├── Dockerfile             # Local development Dockerfile
│   │   └── entrypoint.sh          # Container startup script (builds DATABASE_URL, runs migrations)
│   └── traefik.yaml               # Local Traefik reverse proxy configuration
├── docs/                          # Comprehensive user and architecture documentation
├── migrations/                    # Doctrine database migrations
├── src/
│   ├── ApiDto/                    # API Platform DTOs
│   ├── Command/                   # Symfony Console CLI commands
│   ├── Controller/                # HTTP Controllers (Web UI, Live Components, SSE)
│   ├── Domain/                    # Core business logic (DDD / CQRS)
│   │   ├── Application/           # Application registration, checkpoints, secrets
│   │   ├── Author/                # Event authors
│   │   ├── Client/                # Stream recording and worker connections
│   │   ├── Connection/            # Client connections tracking
│   │   ├── Dashboard/             # Metrics and dashboard aggregations
│   │   ├── Event/                 # Event definitions, schema, validation
│   │   ├── Projection/            # State projections, criteria, mutations
│   │   ├── Stream/                # Stream sequence tracking
│   │   ├── User/                  # User accounts, authentication, superuser management
│   │   └── Widget/                # Dashboard widgets
│   ├── Entity/                    # Doctrine ORM Entities
│   ├── Extension/                 # Extensible condition operators, mutations, property types
│   ├── Infrastructure/            # Adapters (SocketServer, CatchupManager, Messaging)
│   ├── Repository/                # Doctrine & PostgreSQL repositories
│   ├── Security/                  # Security voters and authenticators
│   └── Twig/                      # Twig extensions and components
├── templates/                     # Twig templates and UI views
└── tests/                         # PHPUnit test suites
```

---

## 6. Key CLI Commands

Run these inside the container (`make in` or `docker compose exec php <cmd>`):

### Setup & Bootstrap
- **Bootstrap First Superuser (Interactive):**
  ```bash
  php bin/console app:user:register-first
  ```
  *(Interactively prompts for forename, surname, email, password, and optional company name).*
- **Bootstrap First Superuser (ECS Task / Non-interactive):**
  ```bash
  php bin/console app:user:register-first-non-interactive --forename="Jane" --surname="Doe" --email="admin@example.com" [--company="Acme Corp"]
  ```
  *(Non-interactive bootstrap for ECS tasks. Takes CLI options/arguments or `FIRST_USER_*` environment variables, creates the first superuser, generates a one-time link, and emails it so the user can set their password).*
- **Load Default Projections & Templates:**
  ```bash
  php bin/console app:setup:load-projections
  php bin/console app:setup:load-templates
  php bin/console app:setup:load-applications
  ```
- **Generate Local / SSL Certificates:**
  ```bash
  php bin/console app:setup:create_certificates
  ```

### Background Workers & Processes
- **Start Real-time Socket Server:**
  ```bash
  php bin/console app:socket-server:start
  ```
- **Start Stream Catch-up Process:**
  ```bash
  php bin/console app:catchup:start <applicationId> <streamId> <workerId>
  ```
- **Consume Symfony Messenger Queues:**
  ```bash
  php bin/console messenger:consume async --no-interaction
  php bin/console messenger:consume projections --no-interaction
  ```

### Database & Assets
- **Database Migrations:**
  ```bash
  php bin/console doctrine:migrations:migrate --no-interaction
  ```
- **Compile Tailwind CSS:**
  ```bash
  php bin/console tailwind:build
  ```

---

## 7. Guidelines for AI Agents

1. **Strict Typing:**
   - Every PHP file MUST start with `declare(strict_types=1);`.
   - Use explicit parameter and return types, union types, and modern PHP 8.5 features (`readonly` classes, typed constants, constructor promotion).

2. **Domain-Driven Design (DDD) & CQRS:**
   - Business rules and state mutations belong in `src/Domain/`.
   - Separate state-changing actions into **Commands** and **Command Handlers**.
   - Read operations should use **Queries** and **Query Handlers**.
   - Value objects should validate invariants upon instantiation and remain immutable.

3. **Static Analysis & Testing:**
   - Run PHPStan to ensure no regressions:
     ```bash
     vendor/bin/phpstan analyse
     ```
   - Run PHPUnit tests when modifying core logic:
     ```bash
     vendor/bin/phpunit
     ```

4. **Environment Awareness:**
   - When modifying configuration or container scripts, remember that **production runs on Amazon ECS** using `Dockerfile.ecs`, `docker/php/Caddyfile.ecs`, and `docker/php/entrypoint.sh`.
   - Never assume local environment variables (like a direct `DATABASE_URL` string) are static in production; ECS derives `DATABASE_URL` dynamically from separate `DB_*` variables.
