<?php

declare(strict_types=1);

namespace App\Enum;

enum InvoiceDocumentState: string
{
    case Uploaded = 'uploaded';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Uploaded',
        };
    }
}
