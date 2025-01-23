<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ApiResource(
    operations: [
        'get' => new Get(),
        'post' => new Post(),
        'delete' => new Delete(),
        'patch' => new Patch(),
    ]
)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $rating; // La note de la séance

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Film::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Film $film;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Reservation', inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private $reservation;

    #[ORM\Column(type: 'boolean')]
    private $validated = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et Setter pour rating
    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    // Getter et Setter pour description
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // Getter et Setter pour user
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getReservation(): Reservation
    {
        return $this->reservation;
    }

    public function setReservation(Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }

    // Getter et Setter pour session
    public function getFilm(): Film
    {
        return $this->film;
    }

    public function setFilm(Film $film): self
    {
        $this->film = $film;
        return $this;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;
        return $this;
    }
}