<?php

namespace App\Entity;

use App\Repository\WarrantyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: WarrantyRepository::class)]
#[ApiResource]
class Warranty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\ManyToOne(inversedBy: 'warranties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipment $equipment_id = null;

    #[ORM\ManyToOne(inversedBy: 'warranties')]
    private ?Manufacturer $manufacturer_id = null;

    #[ORM\OneToMany(mappedBy: 'warranty_id', targetEntity: Document::class)]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getEquipmentId(): ?Equipment
    {
        return $this->equipment_id;
    }

    public function setEquipmentId(?Equipment $equipment_id): static
    {
        $this->equipment_id = $equipment_id;

        return $this;
    }

    public function getManufacturerId(): ?Manufacturer
    {
        return $this->manufacturer_id;
    }

    public function setManufacturerId(?Manufacturer $manufacturer_id): static
    {
        $this->manufacturer_id = $manufacturer_id;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setWarrantyId($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getWarrantyId() === $this) {
                $document->setWarrantyId(null);
            }
        }

        return $this;
    }
}
