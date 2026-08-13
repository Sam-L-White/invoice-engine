<?php

declare(strict_types=1);

namespace App\Storage;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class InvoiceDocumentStorage
{
    public function __construct(
        #[Target('private.storage')]
        private readonly FilesystemOperator $storage,
    ) {
    }

    public function store(UploadedFile $file): string
    {
        $extension = $file->guessExtension();

        if ($extension === null) {
            throw new \RuntimeException(
                'Could not determine uploaded file extension.',
            );
        }

        $path = sprintf(
            'invoice-documents/%s.%s',
            bin2hex(random_bytes(16)),
            $extension,
        );

        $stream = fopen($file->getPathname(), 'rb');

        if ($stream === false) {
            throw new \RuntimeException('Failed to open uploaded file for reading.');
        }

        try {
            $this->storage->writeStream($path, $stream);
        } finally {
            fclose($stream);
        }

        return $path;
    }

    public function delete(string $path): void
    {
        $this->storage->delete($path);
    }

    /**
     * @return resource
     */
    public function readStream(string $path)
    {
        return $this->storage->readStream($path);
    }
}
