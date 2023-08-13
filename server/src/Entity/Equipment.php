<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 150)]
    private ?string $brand = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(length: 200)]
    private ?string $serial_code = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $purchase_date = null;

    #[ORM\ManyToOne(inversedBy: 'equipments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category_id = null;

    #[ORM\OneToMany(mappedBy: 'equipment_id', targetEntity: Warranty::class)]
    private Collection $warranties;

    public function __construct()
    {
        $this->warranties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getSerialCode(): ?string
    {
        return $this->serial_code;
    }

    public function setSerialCode(string $serial_code): static
    {
        $this->serial_code = $serial_code;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchase_date;
    }

    public function setPurchaseDate(?\DateTimeInterface $purchase_date): static
    {
        $this->purchase_date = $purchase_date;

        return $this;
    }

    public function getCategoryId(): ?Category
    {
        return $this->category_id;
    }

    public function setCategoryId(?Category $category_id): static
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * @return Collection<int, Warranty>
     */
    public function getWarranties(): Collection
    {
        return $this->warranties;
    }

    public function addWarranty(Warranty $warranty): static
    {
        if (!$this->warranties->contains($warranty)) {
            $this->warranties->add($warranty);
            $warranty->setEquipmentId($this);
        }

        return $this;
    }

    public function removeWarranty(Warranty $warranty): static
    {
        if ($this->warranties->removeElement($warranty)) {
            // set the owning side to null (unless already changed)
            if ($warranty->getEquipmentId() === $this) {
                $warranty->setEquipmentId(null);
            }
        }

        return $this;
    }
}
