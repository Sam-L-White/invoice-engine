<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\InvoiceDocument;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\DataProvider;

final class InvoiceDocumentControllerTest extends WebTestCase
{
    public static function supportedDocumentProvider(): iterable
    {
        yield 'PDF' => ['sample.pdf', 'application/pdf'];
        yield 'JPEG' => ['sample.jpg', 'image/jpeg'];
        yield 'PNG' => ['sample.png', 'image/png'];
    }
    public function testUploadPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/invoice-documents/upload');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('invoice_document_upload');
        self::assertSelectorTextContains('h1', 'Upload invoice');
        self::assertSelectorExists('input[type="file"]');
    }

    #[DataProvider('supportedDocumentProvider')]
    public function testValidDocumentUploadRedirectsSuccessfully(
        string $filename,
        string $_expectedMimeType,
    ): void {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/'.$filename,
        );

        $client->submit($form);

        self::assertResponseRedirects('/invoice-documents/upload');
    }

    #[DataProvider('supportedDocumentProvider')]
    public function testValidDocumentUploadPersistsInvoiceDocument(
        string $filename,
        string $expectedMimeType,
    ): void {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/'.$filename,
        );

        $client->submit($form);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $document = $entityManager
            ->getRepository(InvoiceDocument::class)
            ->findOneBy([
                'originalFilename' => $filename,
            ]);

        self::assertNotNull($document);
        self::assertSame($filename, $document->getOriginalFilename());
        self::assertSame($expectedMimeType, $document->getMimeType());
        self::assertStringStartsWith(
            'invoice-documents/',
            $document->getStoragePath(),
        );
    }

    #[DataProvider('supportedDocumentProvider')]
    public function testValidDocumentUploadStoresFile(
        string $filename,
        string $_expectedMimeType,
    ): void {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/'.$filename,
        );

        $client->submit($form);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $document = $entityManager
            ->getRepository(InvoiceDocument::class)
            ->findOneBy([
                'originalFilename' => $filename,
            ]);

        self::assertNotNull($document);

        /** @var FilesystemOperator $storage */
        $storage = static::getContainer()->get('private.storage');

        self::assertTrue(
            $storage->fileExists($document->getStoragePath()),
        );
    }
}
