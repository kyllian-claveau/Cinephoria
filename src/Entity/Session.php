<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\SessionRepository;
use App\Validator\SessionCinemasValid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ApiResource(
    operations: [
        'get' => new Get(),
        'post' => new Post(),
        'delete' => new Delete(),
        'patch' => new Patch(),
    ]
)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Film::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Film $film = null;

    #[ORM\ManyToOne(targetEntity: Cinema::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    public function __construct()
    {
        $this->cinemas = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    #[ORM\ManyToOne(targetEntity: Room::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $reservedSeats = [];

    #[ORM\Column(type: 'datetime')]
    private $startDate;

    #[ORM\Column(type: 'datetime')]
    private $endDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(Film $film): self
    {
        $this->film = $film;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCinema(): ?Cinema
    {
        return $this->cinema;
    }

    public function setCinema(Cinema $cinema): self
    {
        $this->cinema = $cinema;

        return $this;
    }

    public function addCinema(Cinema $cinema): self
    {
        if (!$this->cinemas->contains($cinema)) {
            $this->cinemas[] = $cinema;
        }
        return $this;
    }

    public function removeCinema(Cinema $cinema): self
    {
        $this->cinemas->removeElement($cinema);
        return $this;
    }

    public function getReservedSeats(): array
    {
        return $this->reservedSeats ?? []; // Assure que ce soit un tableau
    }

    public function setReservedSeats(array $reservedSeats): self
    {
        $this->reservedSeats = $reservedSeats;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isFinished(): bool
    {
        $now = new \DateTime();
        return $this->endDate < $now;
    }

}