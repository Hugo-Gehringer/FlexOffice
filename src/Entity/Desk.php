<?php

namespace App\Entity;

use App\Repository\DeskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeskRepository::class)]
class Desk
{
    const DESK_TYPE_STANDARD = 0;
    const DESK_TYPE_PRIVATE_OFFICE = 1;
    const DESK_TYPE_MEETING_ROOM = 2;
    const DESK_TYPE_CONFERENCE_ROOM = 3;

    const DESK_TYPES = [
        Desk::DESK_TYPE_STANDARD => 'Bureau standard',
        Desk::DESK_TYPE_PRIVATE_OFFICE => 'Bureau privé',
        Desk::DESK_TYPE_MEETING_ROOM => 'Salle de réunion',
        Desk::DESK_TYPE_CONFERENCE_ROOM => ' Salle de conférence',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'desks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Space $space = null;

    #[ORM\Column(length: 60)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $pricePerDay = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?bool $isAvailable = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'desk')]
    private Collection $reservations;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\ManyToMany(targetEntity: Equipment::class, inversedBy: 'desks')]
    private Collection $equipments;



    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->equipments = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): static
    {
        $this->space = $space;

        return $this;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

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

    public function getPricePerDay(): ?float
    {
        return $this->pricePerDay;
    }

    public function setPricePerDay(float $pricePerDay): static
    {
        $this->pricePerDay = $pricePerDay;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setDesk($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getDesk() === $this) {
                $reservation->setDesk(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipments(): Collection
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments->add($equipment);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        $this->equipments->removeElement($equipment);

        return $this;
    }

    /**
     * Get the availability from the parent space
     */
    public function getAvailability(): ?Availability
    {
        return $this->space ? $this->space->getAvailability() : null;
    }
}
