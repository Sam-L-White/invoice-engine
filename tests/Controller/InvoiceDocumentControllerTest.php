<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\InvoiceDocument;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;

final class InvoiceDocumentControllerTest extends WebTestCase
{
    public function testUploadPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/invoice-documents/upload');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('invoice_document_upload');
        self::assertSelectorTextContains('h1', 'Upload invoice');
        self::assertSelectorExists('input[type="file"]');
    }

    public function testValidPdfUploadRedirectsSuccessfully(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/invoice-documents/upload');

        $form = $crawler->selectButton('Upload')->form();

        $form['invoice_document_upload[document]']->upload(
            dirname(__DIR__).'/Resources/sample.pdf',
        );

        $client->submit($form);

        self::assertResponseRedirects('/invoice-documents/upload');
    }

    public function testValidPdfUploadPersistsInvoiceDocument(): void
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
        self::assertSame('sample.pdf', $document->getOriginalFilename());
        self::assertSame('application/pdf', $document->getMimeType());
        self::assertStringStartsWith(
            'invoice-documents/',
            $document->getStoragePath(),
        );
    }

    public function testValidPdfUploadStoresFile(): void
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

        /** @var FilesystemOperator $storage */
        $storage = static::getContainer()->get('private.storage');

        self::assertTrue(
            $storage->fileExists($document->getStoragePath()),
        );
    }
}
