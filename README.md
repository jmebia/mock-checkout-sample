# Mock Checkout Simulator - Backend

> by Josiah Maius S. Ebia

## Overview

This project is a lightweight simulation of a crypto checkout backend. It exposes two main API endpoints to:

- Create a mock checkout session (`POST /api/checkout`)
- Receive and validate webhook events (`POST /api/webhook`)

Transactions are stored in a MySQL database running inside Docker.

---

## Features

- Mock payment URL generation on checkout requests
- Webhook endpoint to process simulated payment completion events
- Basic validation of incoming webhook payloads
- Transaction persistence with UUID-based transaction IDs
- Simple logging of webhook events

---

## How to Run

### Prerequisites

- PHP 8.1+ with Composer
- Docker & Docker Compose
- MySQL client (optional, for checking the database)
- API testing tool like Postman

### Setup

1. Install dependencies by running `composer install`
2. Run docker for the mysql database setup: `docker-compose up -d`
3. Run the laravel migrations for the database tables: `php artisan migrate`
4. Run `php artisan serve` to access the api at `127.0.0.1:8000`*(default)*

Make sure the `.env` file has these database connection values:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cryptodb
DB_USERNAME=checkoutuser
DB_PASSWORD=ch3ckpo1nt_t1cket
```


### The APIs

> Dev notes: In the checkout's method, I have a post request inside that calls for a fake coinbase api. It is commented since it will always result in an error. As of taking this exam, I also have no idea what would a coinbase payload look like so I just put an imaginary payment API payload.

#### Checkout

Sample request body for `localhost:8000/api/checkout`:

```
{
    "amount": 100.00,
    "email": "test@example.com"
}
```


#### Webhook

Sample payload for testing `localhost:8000/api/webhook`:

```
{
  "id": "test_123456",
  "type": "checkout.session.completed",
  "created": 1727382347,
  "data": {
    "id": "cs_test_abc123",
    "amount_total": 5000,
    "transaction_id": "efc467cf-4d39-48d5-9f5c-5b82ed62bbc7",
    "currency": "usd",
    "customer_email": "test@example.com",
    "payment_status": "confirmed"
    
  }
}
```

**Webhook validation - fail scenarios:**
- Remove some fields from the webhook's sample payload to test the validation.
- Enter a random `transaction_id` or `customer_email` not related to a checkout transaction.

---

## Important Directories for Checking

| Path    | Description |
| -------- | ------- |
| app/Http/Controllers/CheckoutController.php  | Contains the checkout and webhook methods    |
| routes/api.php | Contains the API routes     |
| database/migrations | Contains the transactions table migration; This also serves as the table's schema structure |



---

## Improvements I would make:
-I'd add token-based authentication to sensitive endpoints
- Validate incoming webhooks using signature headers to prevent spoofed or malicious requests.
- Webhook processing should be handled using a job queue (like Laravel Queues).