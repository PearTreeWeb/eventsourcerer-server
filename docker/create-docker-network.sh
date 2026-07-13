#!/usr/bin/env sh

if ! docker network ls --format '{{.Name}}' | grep -q '^traefik_gateway$'; then
    docker network create traefik_gateway
fi