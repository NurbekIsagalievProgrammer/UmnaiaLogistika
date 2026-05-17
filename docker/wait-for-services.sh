#!/bin/sh
set -e

wait_for() {
  host="$1"
  port="$2"
  name="$3"
  echo "Waiting for $name at $host:$port..."
  until php -r "exit(@fsockopen('$host', $port) ? 0 : 1);"; do
    sleep 1
  done
  echo "$name is up."
}

wait_for "${DB_HOST:-postgres}" "${DB_PORT:-5432}" "PostgreSQL"
wait_for "${RABBITMQ_HOST:-rabbitmq}" "${RABBITMQ_PORT:-5672}" "RabbitMQ"
wait_for "${REDIS_HOST:-redis}" "${REDIS_PORT:-6379}" "Redis"
