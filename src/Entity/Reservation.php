<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Session $session = null;

    #[ORM\Column(type: "json")]
    #[Assert\NotBlank]
    private array $seats = [];

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $totalPrice = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Review', mappedBy: "reservation")]
    private $reviews;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;
        return $this;
    }

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    // Méthode pour ajouter un avis à la réservation
    public function addReview(Review $review): self
    {
        $this->reviews[] = $review;
        return $this;
    }

    public function removeReview(Review $review): self
    {
        $this->reviews->removeElement($review);
        return $this;
    }

    public function getSeats(): array
    {
        return $this->seats;
    }

    public function setSeats(array $seats): self
    {
        $this->seats = $seats;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }
}
