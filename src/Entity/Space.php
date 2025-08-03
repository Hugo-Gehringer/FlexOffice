<?php

namespace App\Entity;

use App\Repository\SpaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SpaceRepository::class)]
class Space
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank(message: 'Le nom de l\'espace est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 60,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'spacesHosted')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $host = null;

    #[ORM\ManyToOne(inversedBy: 'spaces', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $address = null;

    /**
     * @var Collection<int, Desk>
     */
    #[ORM\OneToMany(targetEntity: Desk::class, mappedBy: 'space', orphanRemoval: true, cascade: ['remove'])]
    private Collection $desks;

    /**
     * @var Availability|null
     */
    #[ORM\OneToOne(mappedBy: 'space', targetEntity: Availability::class, cascade: ['persist', 'remove'])]
    private ?Availability $availability = null;

    /**
     * @var Collection<int, Favorite>
     */
    #[ORM\OneToMany(targetEntity: Favorite::class, mappedBy: 'space', cascade: ['remove'])]
    private Collection $favoritedBy;

    public function __construct()
    {
        $this->desks = new ArrayCollection();
        $this->favoritedBy = new ArrayCollection();
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

    public function getHost(): ?User
    {
        return $this->host;
    }

    public function setHost(?User $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

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
            $desk->setSpace($this);
        }

        return $this;
    }

    public function removeDesk(Desk $desk): static
    {
        if ($this->desks->removeElement($desk)) {
            // set the owning side to null (unless already changed)
            if ($desk->getSpace() === $this) {
                $desk->setSpace(null);
            }
        }

        return $this;
    }

    public function getAvailability(): ?Availability
    {
        return $this->availability;
    }

    public function setAvailability(?Availability $availability): static
    {
        // unset the owning side of the relation if necessary
        if ($availability === null && $this->availability !== null) {
            $this->availability->setSpace(null);
        }

        // set the owning side of the relation if necessary
        if ($availability !== null && $availability->getSpace() !== $this) {
            $availability->setSpace($this);
        }

        $this->availability = $availability;

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavoritedBy(): Collection
    {
        return $this->favoritedBy;
    }

    public function addFavoritedBy(Favorite $favoritedBy): static
    {
        if (!$this->favoritedBy->contains($favoritedBy)) {
            $this->favoritedBy->add($favoritedBy);
            $favoritedBy->setSpace($this);
        }

        return $this;
    }

    public function removeFavoritedBy(Favorite $favoritedBy): static
    {
        if ($this->favoritedBy->removeElement($favoritedBy)) {
            // set the owning side to null (unless already changed)
            if ($favoritedBy->getSpace() === $this) {
                $favoritedBy->setSpace(null);
            }
        }

        return $this;
    }
}
