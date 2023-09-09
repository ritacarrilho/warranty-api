<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    /**
    * @Groups({"document"})
    */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    /**
    * @Groups({"document"})
    */
    #[ORM\Column(length: 100, nullable: false)]
    private ?string $name = null;
    /**
    * @Groups({"document"})
    */
    #[ORM\Column(length: 200, unique: true)]
    private ?string $path = null;
    /**
    * @Groups({"document"})
    */
    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warranty $warranty = null;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getWarranty(): Warranty
    {
        return $this->warranty;
    }

    public function setWarranty(Warranty $warranty): static
    {
        $this->warranty = $warranty;

        return $this;
    }
}
