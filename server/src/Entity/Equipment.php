<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{    
    /**
    * @Groups({"equipment"})
    */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    /**
     * @Groups({"equipment"})
     */
    #[ORM\Column(length: 150)]
    private ?string $name = null;
    /**
     * @Groups({"equipment"})
     */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $brand = null;
    /**
     * @Groups({"equipment"})
     */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $model = null;
    /**
     * @Groups({"equipment"})
     */
    #[ORM\Column(length: 200, unique: true)]
    private string $serial_code;
    /**
    * @Groups({"equipment"})
    */
    #[ORM\Column(type: 'boolean')]
    private ?bool $is_active = true;

    /**
     * @Groups({"equipment"})
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $purchase_date = null;


    #[ORM\ManyToOne(inversedBy: 'equipments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // #[ORM\OneToMany(mappedBy: 'equipment', targetEntity: Warranty::class)]
    // private Collection $warranties;

    public function __construct()
    {
        // $this->warranties = new ArrayCollection();
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

    public function setBrand(?string $brand): static
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

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFormattedPurchaseDate(): ?string
    {
        $purchaseDate = $this->getPurchaseDate();
        
        if ($purchaseDate instanceof \DateTimeInterface) {
            return $purchaseDate->format('Y-m-d');
        }
        
        return null;
    }

    // /**
    //  * @return Collection<int, Warranty>
    //  */
    // public function getWarranties(): Collection
    // {
    //     return $this->warranties;
    // }

    // public function addWarranty(Warranty $warranty): static
    // {
    //     if (!$this->warranties->contains($warranty)) {
    //         $this->warranties->add($warranty);
    //         $warranty->setEquipment($this);
    //     }

    //     return $this;
    // }

    // public function removeWarranty(Warranty $warranty): static
    // {
    //     if ($this->warranties->removeElement($warranty)) {
    //         // set the owning side to null (unless already changed)
    //         if ($warranty->getEquipment() === $this) {
    //             $warranty->setEquipment(null);
    //         }
    //     }

    //     return $this;
    // }
}
