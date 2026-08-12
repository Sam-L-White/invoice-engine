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
        yield 'PDF' => ['sample.pdf', 'application/pdf', 'pdf'];
        yield 'JPEG' => ['sample.jpg', 'image/jpeg', 'jpg'];
        yield 'PNG' => ['sample.png', 'image/png', 'png'];
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

    public function testIndexPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/invoice-documents');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('invoice_document_index');
        self::assertSelectorTextContains('h1', 'Invoice Documents');
    }

    public function testInvoiceDocumentIndexDisplaysDocumentsNewestFirst(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $olderDocument = new InvoiceDocument();
        $olderDocument->setOriginalFilename('older.pdf');
        $olderDocument->setMimeType('application/pdf');
        $olderDocument->setStoragePath('invoice-documents/older.pdf');
        $olderDocument->setUploadedAt(
            new \DateTimeImmutable('2026-08-10 10:00:00'),
        );

        $newerDocument = new InvoiceDocument();
        $newerDocument->setOriginalFilename('newer.pdf');
        $newerDocument->setMimeType('application/pdf');
        $newerDocument->setStoragePath('invoice-documents/newer.pdf');
        $newerDocument->setUploadedAt(
            new \DateTimeImmutable('2026-08-11 10:00:00'),
        );

        $entityManager->persist($olderDocument);
        $entityManager->persist($newerDocument);
        $entityManager->flush();

        $crawler = $client->request('GET', '/invoice-documents');

        self::assertResponseIsSuccessful();

        $rows = $crawler->filter('tbody tr');

        self::assertCount(2, $rows);
        self::assertStringContainsString('newer.pdf', $rows->eq(0)->text());
        self::assertStringContainsString('older.pdf', $rows->eq(1)->text());
    }

    #[DataProvider('supportedDocumentProvider')]
    public function testValidDocumentUploadRedirectsSuccessfully(
        string $filename,
        string $_expectedMimeType,
        string $_expectedExtension,
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
        string $expectedExtension,
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
        self::assertStringEndsWith(
            '.'.$expectedExtension,
            $document->getStoragePath(),
        );
    }

    #[DataProvider('supportedDocumentProvider')]
    public function testValidDocumentUploadStoresFile(
        string $filename,
        string $_expectedMimeType,
        string $_expectedExtension,
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

    public function testUnsupportedDocumentUploadIsRejected(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/sample.txt',
        );

        $client->submit($form);

        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains(
            'form',
            'Please upload a valid PDF, JPEG or PNG document.',
        );
    }

    public function testUnsupportedDocumentUploadDoesNotPersistInvoiceDocument(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/sample.txt',
        );

        $client->submit($form);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $document = $entityManager
            ->getRepository(InvoiceDocument::class)
            ->findOneBy([
                'originalFilename' => 'sample.txt',
            ]);

        self::assertNull($document);
    }

    public function testUnsupportedDocumentUploadDoesNotStoreFile(): void
    {
        $client = static::createClient();

        /** @var FilesystemOperator $storage */
        $storage = static::getContainer()->get('private.storage');

        $before = $storage->listContents('', true)->toArray();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/sample.txt',
        );

        $client->submit($form);

        $after = $storage->listContents('', true)->toArray();

        self::assertSame($before, $after);
    }

    public function testUploadedDocumentHasUploadedAt(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/sample.pdf',
        );

        $client->submit($form);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $document = $entityManager
            ->getRepository(InvoiceDocument::class)
            ->findOneBy([
                'originalFilename' => 'sample.pdf',
            ]);

        self::assertNotNull($document);
        self::assertNotNull($document->getUploadedAt());
    }
}
