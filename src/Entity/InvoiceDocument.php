<?php

namespace App\Entity;

use App\Repository\InvoiceDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceDocumentRepository::class)]
class InvoiceDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 100)]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255)]
    private ?string $storagePath = null;

    #[ORM\OneToOne(mappedBy: 'document', cascade: ['persist'])]
    private ?Invoice $invoice = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): static
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        if ($invoice !== null && $invoice->getDocument() !== $this) {
            $invoice->setDocument($this);
        }

        $this->invoice = $invoice;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }
}
