<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\InvoiceDocumentUploadType;
use App\Storage\InvoiceDocumentStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\InvoiceDocument;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Clock\ClockInterface;
use App\Repository\InvoiceDocumentRepository;

final class InvoiceDocumentController extends AbstractController
{
    public function __construct(
        private readonly InvoiceDocumentStorage $documentStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly ClockInterface $clock,
    ) {
    }

    #[Route('/invoice-documents', name: 'invoice_document_index')]
    public function index(InvoiceDocumentRepository $repository): Response
    {
        $documents = $repository->findBy(
            [],
            ['uploadedAt' => 'DESC'],
        );

        return $this->render('invoice_document/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    #[Route('/invoice-documents/upload', name: 'invoice_document_upload')]
    public function upload(Request $request): Response
    {
        $form = $this->createForm(InvoiceDocumentUploadType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('document')->getData();

            $originalFilename = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getMimeType();

            if ($mimeType === null) {
                throw new \RuntimeException('Could not determine the uploaded file MIME type.');
            }

            $storagePath = $this->documentStorage->store($uploadedFile);

            try {
                $document = new InvoiceDocument();
                $document->setOriginalFilename($originalFilename);
                $document->setMimeType($mimeType);
                $document->setStoragePath($storagePath);
                $document->setUploadedAt($this->clock->now());

                $this->entityManager->persist($document);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                try {
                    $this->documentStorage->delete($storagePath);
                } catch (\Throwable $cleanupException) {
                    $this->logger->error(
                        'Failed to delete invoice document after database persistence failure.',
                        [
                            'storagePath' => $storagePath,
                            'exception' => $cleanupException,
                        ],
                    );
                }

                throw $exception;
            }

            $this->addFlash(
                'success',
                'Invoice document uploaded successfully.',
            );

            return $this->redirectToRoute('invoice_document_upload');
        }

        return $this->render('invoice_document/upload.html.twig', [
            'form' => $form,
        ]);
    }
}
