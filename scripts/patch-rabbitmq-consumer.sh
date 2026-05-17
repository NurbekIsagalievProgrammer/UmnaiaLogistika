#!/bin/sh
set -e
FILE="vendor/vladimir-yuldashev/laravel-queue-rabbitmq/src/Consumer.php"
if [ -f "$FILE" ] && grep -q 'protected $currentJob' "$FILE"; then
  sed -i '/protected $currentJob;/d' "$FILE"
fi
