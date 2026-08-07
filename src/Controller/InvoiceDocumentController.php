<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\InvoiceDocumentUploadType;
use App\Storage\InvoiceDocumentStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InvoiceDocumentController extends AbstractController
{
    public function __construct(
        private readonly InvoiceDocumentStorage $invoiceDocumentStorage,
    ) {
    }

    #[Route('/invoice-documents/upload', name: 'invoice_document_upload')]
    public function upload(Request $request): Response
    {
        $form = $this->createForm(InvoiceDocumentUploadType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('document')->getData();

            $storagePath = $this->invoiceDocumentStorage->store($uploadedFile);

            dd($storagePath);
        }

        return $this->render('invoice_document/upload.html.twig', [
            'form' => $form,
        ]);
    }
}