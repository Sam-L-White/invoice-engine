# invoice-engine

A small Symfony application for taking uploaded invoice documents through to structured invoice data.

This is a personal learning project, built to get properly familiar with Symfony's conventions and with how the pieces fit together compared to other PHP frameworks.

## Current state

One complete vertical slice works end to end:

- Upload a PDF, JPEG or PNG through a validated form
- Files are stored outside the web root via Flysystem, under a randomised filename
- Uploads are recorded as an `InvoiceDocument` and listed newest-first
- Each document has a detail page which streams the original back inline

The invoice domain model (`Invoice`, `InvoiceLineItem`, `Supplier`) is mapped and migrated, but nothing populates it yet — that arrives with extraction. `InvoiceDocumentState` currently has a single case for the same reason; it will grow into a proper state machine as documents move through the pipeline.

## Next

- Extraction from the uploaded document into `Invoice` and `InvoiceLineItem`, using a small locally-hosted model rather than a cloud API
- Widen `InvoiceDocumentState` to cover the stages of processing
- Move upload orchestration out of the controller and into a dedicated service once there is more than one step to coordinate

## Stack

PHP 8.4, Symfony 8.1, Doctrine ORM, PostgreSQL 16, Flysystem, Twig, PHPUnit.

## Running it

```bash
composer install
docker compose up -d          # PostgreSQL
composer migrate              # migrates both the dev and test databases
symfony serve
```

The application is served at `/invoice-documents`.

## Tests

```bash
php bin/phpunit
```

Functional tests cover the upload flow across all three supported formats, and the rejection path — checking that an unsupported upload persists no record and leaves no file behind.
