# Notification Service

Микросервис массовых уведомлений (SMS / Email) для тестового задания «Умная логистика».

## Стек

- **PHP 8.3 + Laravel 13**
- **PostgreSQL** — подписчики, уведомления, статусы
- **RabbitMQ** — персистентные очереди (`notifications.critical`, `notifications.default`)
- **Redis** — идемпотентность (`Idempotency-Key`)
- **Docker Compose** — один запуск всего окружения

## Быстрый старт

```bash
cd notification-service
cp .env.example .env   # при необходимости
docker compose up --build -d
```

После старта:

| Сервис | URL |
|--------|-----|
| API | http://localhost:8080 |
| OpenAPI (Swagger) | http://localhost:8080/docs/api |
| RabbitMQ Management | http://localhost:15672 (guest / guest) |

Демо-подписчики создаются сидером: `demo-sms`, `demo-email`.

## Запуск тестов

```bash
# Остановите workers, чтобы интеграционный тест сам забрал сообщение из очереди
docker compose stop worker-critical worker-default

docker compose --profile tools run --rm test

docker compose start worker-critical worker-default
```

## API

### Массовая рассылка

`POST /api/v1/notifications/bulk`

```json
{
  "channel": "sms",
  "message": "Ваш код: 1234",
  "subscriber_ids": ["demo-sms"],
  "priority": "critical"
}
```

Заголовок `Idempotency-Key` — защита от повторной отправки при дубле запроса.

Приоритеты:

- `critical` → очередь `notifications.critical` (транзакционные, без ожидания маркетинга)
- `normal` → очередь `notifications.default`

### История подписчика

`GET /api/v1/subscribers/{external_id}/notifications?status=delivered`

Статусы: `queued`, `sent`, `delivered`, `dropped`.

## Архитектурные решения

### Надёжность (at-least-once)

Сообщения публикуются в RabbitMQ и подтверждаются worker'ом после успешной обработки. При сбое — повтор (`tries=3`, backoff).

### Exactly-once на уровне бизнеса

- `Idempotency-Key` + Redis для bulk-запросов
- Перед отправкой job проверяет терминальный статус уведомления и пропускает повтор

### Приоритизация

Две физические очереди и отдельные worker'ы. Критичные сообщения не делят consumer с маркетинговыми.

### Провайдеры

`SmsProviderMock` / `EmailProviderMock` имитируют шлюз. В тексте сообщения:

- `[[TRANSIENT_FAILURE]]` — временная ошибка (retry)
- `[[PERMANENT_FAILURE]]` — отброс (`dropped`)

## Структура

```
app/
  Enums/           # channel, priority, status
  Http/            # API controllers, requests, resources
  Jobs/            # SendNotificationJob
  Services/        # dispatch, processor, idempotency, provider mocks
```

## Примечание по Laravel 13 + RabbitMQ

Пакет `laravel-queue-rabbitmq` требует патча для Laravel 13 — скрипт `scripts/patch-rabbitmq-consumer.sh` выполняется при `composer install`.
