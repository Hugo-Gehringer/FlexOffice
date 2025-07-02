<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank(message: 'Le nom de l\'équipement est obligatoire')]
    #[Assert\Length(
        max: 60,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    /**
     * @var Collection<int, Desk>
     */
    #[ORM\ManyToMany(targetEntity: Desk::class, mappedBy: 'equipments')]
    private Collection $desks;

    public function __construct()
    {
        $this->desks = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Desk>
     */
    public function getDesks(): Collection
    {
        return $this->desks;
    }

    public function addDesk(Desk $desk): static
    {
        if (!$this->desks->contains($desk)) {
            $this->desks->add($desk);
            $desk->addEquipment($this);
        }

        return $this;
    }

    public function removeDesk(Desk $desk): static
    {
        if ($this->desks->removeElement($desk)) {
            $desk->removeEquipment($this);
        }

        return $this;
    }
}
