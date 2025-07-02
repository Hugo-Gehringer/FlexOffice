<?php

namespace App\Entity;

use App\Repository\AvailabilityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
class Availability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'availability')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Space $space = null;

    #[ORM\Column]
    private ?bool $monday = true;

    #[ORM\Column]
    private ?bool $tuesday = true;

    #[ORM\Column]
    private ?bool $wednesday = true;

    #[ORM\Column]
    private ?bool $thursday = true;

    #[ORM\Column]
    private ?bool $friday = true;

    #[ORM\Column]
    private ?bool $saturday = false;

    #[ORM\Column]
    private ?bool $sunday = false;


    public function __construct()
    {
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



    public function isMonday(): ?bool
    {
        return $this->monday;
    }

    public function setMonday(bool $monday): static
    {
        $this->monday = $monday;

        return $this;
    }

    public function isTuesday(): ?bool
    {
        return $this->tuesday;
    }

    public function setTuesday(bool $tuesday): static
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function isWednesday(): ?bool
    {
        return $this->wednesday;
    }

    public function setWednesday(bool $wednesday): static
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function isThursday(): ?bool
    {
        return $this->thursday;
    }

    public function setThursday(bool $thursday): static
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function isFriday(): ?bool
    {
        return $this->friday;
    }

    public function setFriday(bool $friday): static
    {
        $this->friday = $friday;

        return $this;
    }

    public function isSaturday(): ?bool
    {
        return $this->saturday;
    }

    public function setSaturday(bool $saturday): static
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function isSunday(): ?bool
    {
        return $this->sunday;
    }

    public function setSunday(bool $sunday): static
    {
        $this->sunday = $sunday;

        return $this;
    }

    /**
     * Check if a specific day is available
     */
    public function isDayAvailable(int $dayOfWeek): bool
    {
        return match ($dayOfWeek) {
            1 => $this->monday,
            2 => $this->tuesday,
            3 => $this->wednesday,
            4 => $this->thursday,
            5 => $this->friday,
            6 => $this->saturday,
            7 => $this->sunday,
            default => false,
        };
    }

    /**
     * Get an array of available days
     */
    public function getAvailableDays(): array
    {
        $days = [];
        if ($this->monday) $days[] = 1;
        if ($this->tuesday) $days[] = 2;
        if ($this->wednesday) $days[] = 3;
        if ($this->thursday) $days[] = 4;
        if ($this->friday) $days[] = 5;
        if ($this->saturday) $days[] = 6;
        if ($this->sunday) $days[] = 7;
        return $days;
    }
}
