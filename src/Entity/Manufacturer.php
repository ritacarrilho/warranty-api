<?php

namespace App\Entity;

use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
class Manufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;
    
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $zip_code = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $country = null;

    // #[ORM\OneToMany(mappedBy: 'manufacturer', targetEntity: Warranty::class)]
    // private Collection $warranties;

    public function __construct()
    {
        // $this->warranties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public function setZipCode(?string $zip_code): static
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
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
    //         $warranty->setManufacturer($this);
    //     }

    //     return $this;
    // }

    // public function removeWarranty(Warranty $warranty): static
    // {
    //     if ($this->warranties->removeElement($warranty)) {
    //         // set the owning side to null (unless already changed)
    //         if ($warranty->getManufacturer() === $this) {
    //             $warranty->setManufacturer(null);
    //         }
    //     }

    //     return $this;
    // }
}
