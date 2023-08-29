<?php

namespace App\Entity;

use App\Repository\WarrantyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: WarrantyRepository::class)]
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

    #[ORM\ManyToOne(inversedBy: 'warranties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipment $equipment = null;

    #[ORM\ManyToOne(inversedBy: 'warranties')]
    private ?Manufacturer $manufacturer = null;

    // #[ORM\OneToMany(mappedBy: 'warranty', targetEntity: Document::class)]
    // private Collection $documents;

    public function __construct()
    {
        // $this->documents = new ArrayCollection();
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

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    // /**
    //  * @return Collection<int, Document>
    //  */
    // public function getDocuments(): Collection
    // {
    //     return $this->documents;
    // }

    // public function addDocument(Document $document): static
    // {
    //     if (!$this->documents->contains($document)) {
    //         $this->documents->add($document);
    //         $document->setWarranty($this);
    //     }

    //     return $this;
    // }

    // public function removeDocument(Document $document): static
    // {
    //     if ($this->documents->removeElement($document)) {
    //         // set the owning side to null (unless already changed)
    //         if ($document->getWarranty() === $this) {
    //             $document->setWarranty(null);
    //         }
    //     }

    //     return $this;
    // }
}
