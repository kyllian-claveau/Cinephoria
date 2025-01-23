<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "film_stats")]
class FilmStats
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'int')]
    private int $filmId;

    #[MongoDB\Field(type: 'string')]
    private string $filmTitle;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $date;

    #[MongoDB\Field(type: 'int')]
    private int $reservationsCount = 0;

    #[MongoDB\Field(type: 'float')]
    private float $totalRevenue = 0.0;

    // Getters and setters
    public function getId(): string
    {
        return $this->id;
    }

    public function getFilmId(): int
    {
        return $this->filmId;
    }

    public function setFilmId(int $filmId): self
    {
        $this->filmId = $filmId;
        return $this;
    }

    public function getFilmTitle(): string
    {
        return $this->filmTitle;
    }

    public function setFilmTitle(string $filmTitle): self
    {
        $this->filmTitle = $filmTitle;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getReservationsCount(): int
    {
        return $this->reservationsCount;
    }

    public function incrementReservationsCount(): self
    {
        $this->reservationsCount++;
        return $this;
    }

    public function getTotalRevenue(): float
    {
        return $this->totalRevenue;
    }

    public function addRevenue(float $amount): self
    {
        $this->totalRevenue += $amount;
        return $this;
    }
}
