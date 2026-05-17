#!/bin/sh
set -e
# Очереди создаются до старта consumer, иначе RabbitMQ отвечает NOT_FOUND.
php artisan rabbitmq:queue-declare notifications.critical rabbitmq 2>/dev/null || true
php artisan rabbitmq:queue-declare notifications.default rabbitmq 2>/dev/null || true
