# GiftFlow - Gift Card Platform
This is an API developed in Laravel for gift card redemption and webhook notification.

## 🚀 Architectural Decisions
For this challenge, I prioritized Pragmatism and Testability, utilizing the following patterns:

* Action Pattern: The gift card redemption business logic was isolated in the `RedeemGiftCardAction` class, keeping the controller lean and focused on the request/response cycle.
* Repository Pattern: Since the challenge prohibits traditional database usage, I implemented a `FileGiftCardRepository`. This allows the persistence layer to be swapped for SQL or NoSQL by simply changing the binding in the Service Provider, adhering to the Dependency Inversion Principle (DIP).
* Deterministic Idempotency: The `event_id` is generated via HMAC SHA-256 using a combination of code + email. This ensures that network retries return the same identifier without duplicating side effects on the issuer side.
* Webhook Signing: Implemented security via the `X-GiftFlow-Signature` header using HMAC SHA-256 to ensure data integrity and authenticity for the issuer platform.
* Backoff Exponential: The exponential backoff mechanism was implemented in the job (app/Jobs/NotifyGiftCardIssuerRedemptionJob) that sends the notification via webhook, so that in case of failure, each retry will wait a longer time, avoiding overload on the external service and increasing the chance of success in the request.
* Simplicity: I chose not to introduce complex DDD layers to keep the service minimalist and easy to maintain, as per the challenge requirements.
* Testing: I focused on end-to-end testing to quickly cover the main functionalities, but in the future it would be interesting to implement unit and integration tests.

## 🛠️ Tech Stack
* PHP 8.4+
* Laravel 11
* Docker & Docker Compose
* Redis (Used for Queues and Webhook Idempotency)
* PHPUnit

## 🔧 Installation & Setup
Follow the commands below to spin up the environment:

```bash
# Spin up containers
./sail up --build -d

# Copy environment file
cp .env.example .env

# Install dependencies
./sail composer install

# Generate application key
./sail php artisan key:generate

# Seed initial gift card codes (JSON persistence)
./sail php artisan giftflow:seed
```

## ⚙️ Utility Commands

```bash 
# Running the Worker (Queue)
./sail php artisan queue:work
# Running Tests
./sail php artisan test
# Fix the code syntax following Laravel Pint standards
./sail vendor/bin/pint
```

## 📖 API Endpoints

Before sending requests to the endpoints, remember to set the headers as follows:
- Content-Type: application/json
- Accept: application/json

### 1. Redeem Code

POST /api/redeem

Payload:
```json
{ 
    "code": "GFLOW-TEST-0001", 
    "user": { 
        "email": "dev@example.com"
    }
} 
```

### 2. Mock Webhook (Issuer Platform)
Internal endpoint that simulates the issuer platform. Protected by HMAC signature validation middleware.

POST /webhook/issuer-platform

Payload:
```json
{
    "event_id": "da647c03095782709f19f472b1530ad0bd886ec71b58d714eaa59cedbfc4d79d",
    "type": "giftcard.redeemed",
    "data": {
        "code": "GFLOW-TEST-0001",
        "email": "newuser@example.com",
        "creator_id": "creator_123",
        "product_id": "product_abc"
    },
    "sent_at": "2025-12-29T00:00:00Z"
}
```

## 📝 Notes & Trade-offs
* File Persistence: The storage/app/giftcards.json file was used to ensure the status of the cards (available/redeemed) is maintained.
* Webhook Idempotency: The issuer mock utilizes the cache driver to track processed event_ids, ensuring that processing happens only once.
* Webhook performance: When receiving the webhook, I chose not to validate the inputs and use jobs to process the notifications asynchronously. This way, the endpoint responds quickly, and the notification processing is done in the background.
* Docker: I used Laravel Sail, but to avoid installing Composer and PHP locally, I incorporated the Docker files into the project (./docker). This way, it's possible to run the project using only Docker.

## Improvements & Next Steps
* Use DTOs between actions, repositories, and controllers to enhance type safety and clarity.
* Implement API Resources for consistent and structured API responses.
* Implement unit and integrations tests.
* Abstract functions that are repeated throughout the code (e.g., generating event IDs and signing webhooks).
