<?php

namespace App\Repository;

use App\Document\FilmStats;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class FilmStatsRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        parent::__construct($dm, $dm->getUnitOfWork(), $dm->getClassMetadata(FilmStats::class));
    }

    public function getWeeklyStats(): array
    {
        $startDate = new \DateTime('-7 days');
        $startDate->setTime(0, 0);

        return $this->createQueryBuilder()
            ->field('date')->gte($startDate)
            ->sort('date', 'ASC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findOrCreateDaily(string $filmId, string $title, \DateTime $date)
    {
        $stats = $this->findOneBy([
            'filmId' => $filmId,
            'date' => $date
        ]);

        if (!$stats) {
            $stats = new FilmStats();
            $stats->setFilmId($filmId);
            $stats->setFilmTitle($title);
            $stats->setDate($date);
        }

        return $stats;
    }
}