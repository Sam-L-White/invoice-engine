<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    #[ORM\Column(length: 100)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $invoiceDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $netTotal = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $vatTotal = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $grossTotal = null;

    /**
     * @var Collection<int, InvoiceLineItem>
     */
    #[ORM\OneToMany(targetEntity: InvoiceLineItem::class, mappedBy: 'invoice', orphanRemoval: true)]
    private Collection $invoiceLineItems;

    public function __construct()
    {
        $this->invoiceLineItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeImmutable
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeImmutable $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getNetTotal(): ?string
    {
        return $this->netTotal;
    }

    public function setNetTotal(string $netTotal): static
    {
        $this->netTotal = $netTotal;

        return $this;
    }

    public function getVatTotal(): ?string
    {
        return $this->vatTotal;
    }

    public function setVatTotal(string $vatTotal): static
    {
        $this->vatTotal = $vatTotal;

        return $this;
    }

    public function getGrossTotal(): ?string
    {
        return $this->grossTotal;
    }

    public function setGrossTotal(string $grossTotal): static
    {
        $this->grossTotal = $grossTotal;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceLineItem>
     */
    public function getInvoiceLineItems(): Collection
    {
        return $this->invoiceLineItems;
    }

    public function addInvoiceLineItem(InvoiceLineItem $invoiceLineItem): static
    {
        if (!$this->invoiceLineItems->contains($invoiceLineItem)) {
            $this->invoiceLineItems->add($invoiceLineItem);
            $invoiceLineItem->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceLineItem(InvoiceLineItem $invoiceLineItem): static
    {
        if ($this->invoiceLineItems->removeElement($invoiceLineItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceLineItem->getInvoice() === $this) {
                $invoiceLineItem->setInvoice(null);
            }
        }

        return $this;
    }
}
