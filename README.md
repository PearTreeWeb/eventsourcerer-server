# EventSourcerer

> ⚠️ **Beta Notice:** EventSourcerer is currently in beta. We're actively tracking bugs and making improvements — feedback is very welcome!

A self-hosted event sourcing server. For more information, visit [eventsourcerer.com](https://eventsourcerer.com).

---

## Setup with Docker (recommended)

Docker is the easiest way to get up and running.

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose installed
- `make` available on your system

### First-time setup

Run the following command to set up the full environment, generate SSL certificates, and trust the local CA:

```bash
make setup
```

This will:
1. Create the required Docker network
2. Start the PHP container and install Composer dependencies
3. Generate SSL certificates for local HTTPS (via Traefik)
4. Load default projections and templates
5. Trust the local root CA on your system (requires `sudo`)
6. Start all services and run database migrations

Once complete, open your browser at: **https://eventsourcerer.docker.localhost**

### Starting and stopping

```bash
# Start all services
make up

# Stop all services
make down

# Open a shell inside the PHP container
make in
```

---

## Setup without Docker

If you prefer to run EventSourcerer without Docker, ensure you have PHP, Composer, and a PostgreSQL database configured, then run:

```bash
composer install
bin/console d:m:m
bin/console app:socket-server:start
```

---

## Contact

Questions or issues? Reach us at [contact@peartreeweb.com](mailto:contact@peartreeweb.com).
