<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
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
    #[ORM\Column(length: 100)]
    private ?string $label = null;

    // #[ORM\OneToMany(mappedBy: 'category', targetEntity: Equipment::class, orphanRemoval: true)]
    // private Collection $equipments;

    // public function __construct()
    // {
    //     $this->equipments = new ArrayCollection();
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    // /**
    //  * @return Collection<int, Equipment>
    //  */
    // public function getEquipments(): Collection
    // {
    //     return $this->equipments;
    // }

    // public function addEquipment(Equipment $equipment): static
    // {
    //     if (!$this->equipments->contains($equipment)) {
    //         $this->equipments->add($equipment);
    //         $equipment->setCategory($this);
    //     }

    //     return $this;
    // }

    // public function removeEquipment(Equipment $equipment): static
    // {
    //     if ($this->equipments->removeElement($equipment)) {
    //         // set the owning side to null (unless already changed)
    //         if ($equipment->getCategory() === $this) {
    //             $equipment->setCategory(null);
    //         }
    //     }

    //     return $this;
    // }
}
