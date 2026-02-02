# Async File Conversion

This project provides an API for simulating file format conversions. It supports file types (`CSV`, `JSON`, `XLSX`, `ODS`) and converts them to `JSON` or `XML`. The conversion process is simulated with a delay.

## Architecture Overview

**User** → **API Controller** → **Validator**  → **File Conversion Creator** (Store File, Create Outbox for Tracking, Dispatch File Conversion) → **Message Bus** (Dispatch Conversion Message) → **File Conversion Consumer** (Process Conversion) → **File Conversion Processor** (Simulate Conversion)  → **User** (Check Conversion Status) → **Message Consumer** (Retry Failed Dispatches, if needed)


### Key Components:
- **Controller (`Controller/FileConversionsController`)**: Exposes endpoints for initiating conversions (`POST /file-conversions`) and checking conversion status (`GET /file-conversions/{id}`).
- **Creator (`Service/FileConversionCreator`)**: Handles file upload and stores conversion details.
- **Processor (`Service/FileConversionProcessor`)**: Simulates the conversion process.
- **Message Handler (`FileConversionMessageHandler`)**: Processes file conversions asynchronously.
- **Consumer (`Cunsomer/OutboxMessageConsumer`)**: Retries failed message dispatches.
---

## Setup & Execution

### Prerequisites:
- Docker
- Composer

### 1. Clone the Repository:
```bash
https://github.com/zlabdaoui/async-file-conversion.git
cd async-file-conversion
```

### 2. Start Docker Containers:
```bash
docker compose up --build -d
```
### 3. Install Dependencies:
```bash
docker-compose exec app composer install
```

### 4. Run Migrations:
```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```
Check with Adminer `http://localhost:8080/adminer.php`
- **System**: PostgreSQL
- **Server**: postgres
- **Username**: postgres
- **Password**: `postgres`
- **Database**: file_conversion_db

### 5. API Access:
- **API URL**: `http://localhost:8080`
- **OpenAPI Specification**: The project includes an OpenAPI specification file `docs/openapi.yaml`
- **Postman Collection**: Import the `docs/postman/Async File Conversion Flow Testing.postman_collection.json` file into Postman for easy testing.

## Asynchronous Processing
The file conversion process is handled asynchronously using Symfony Messenger. Once a file is uploaded, a message is dispatched for processing. The processing is done in the background by the `FileConversionProcessor` service.

### Key Steps:
1. **Message Dispatch**: When a file conversion is initiated via the `POST /file-conversions` endpoint, a message is dispatched containing the file conversion id.
2. **Message Consumption**: The message is consumed by the `MessageHandler/FileConversionMessageHandler` which triggers the actual conversion processing.
3. **Processing Logic**: The `Service/FileConversionProcessor` service marks the conversion as `PROCESSING`, simulates the conversion process (via `sleep(3)`), and then marks the conversion as `COMPLETED`. If an error occurs, the status is updated to `FAILED`.
4. **Outbox Handling**: If message dispatch fails when the file conversion is created, it is retried by the `OutboxMessageConsumer`
5. **Retry**: Failed file conversion messages are retried up to **5 times** with exponential backoff
6. **Failure Handling**: After max failed retries, messages are moved to the **failed transport**.

To process the queued messages and simulate file conversions:
```bash
docker-compose exec app php bin/console messenger:consume async -vv
```
To retry failed messages:
```bash
docker-compose exec app php bin/console messenger:consume failed -vv
```
To run the outbox consumer:
```bash
  docker-compose exec app php bin/console app:consume-outbox -vv
```

## Run Tests
```bash
docker-compose exec app php bin/phpunit
```
