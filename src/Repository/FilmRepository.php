<?php

namespace App\Repository;

use App\Entity\Film;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Film>
 */
class FilmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Film::class);
    }

    public function findFilmsFromLastWednesday(): array
    {
        $today = new \DateTime();
        $isWednesday = $today->format('l') === 'Wednesday';

        // Si c'est mercredi, prendre le début de la journée jusqu'à maintenant
        if ($isWednesday) {
            $startOfDay = $today->format('Y-m-d 00:00:00');
            $endOfDay = $today->format('Y-m-d H:i:s'); // Heure actuelle

            return $this->createQueryBuilder('f')
                ->where('f.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $startOfDay)
                ->setParameter('end', $endOfDay)
                ->orderBy('f.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        }

        // Sinon, prendre le dernier mercredi
        $lastWednesday = new \DateTime('last Wednesday');
        $nextThursday = (clone $lastWednesday)->modify('+1 day'); // Fin du dernier mercredi

        return $this->createQueryBuilder('f')
            ->where('f.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $lastWednesday->format('Y-m-d 00:00:00'))
            ->setParameter('end', $nextThursday->format('Y-m-d 00:00:00'))
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }



    public function findFilmsByFilters(?int $cinemaId, ?int $genreId): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.cinemas', 'c') // Relier les films aux cinémas
            ->leftJoin('f.genres', 'g')  // Relier les films aux genres
            ->addSelect('c', 'g'); // Ajouter les autres entités pour filtrer

        // Filtrage par cinéma si spécifié
        if ($cinemaId) {
            $qb->andWhere('c.id = :cinemaId')
                ->setParameter('cinemaId', $cinemaId);
        }

        // Filtrage par genre si spécifié
        if ($genreId) {
            $qb->andWhere('g.id = :genreId')
                ->setParameter('genreId', $genreId);
        }

        // Retourner les résultats
        return $qb->getQuery()->getResult();
    }

    public function findFilmsByCinemas(?int $cinemaId): array
    {
        $qb = $this->createQueryBuilder('f')
            ->leftJoin('f.cinemas', 'c') // Relier les films aux cinémas
            ->addSelect('c'); // Ajouter les autres entités pour filtrer

        // Filtrage par cinéma si spécifié
        if ($cinemaId) {
            $qb->andWhere('c.id = :cinemaId')
                ->setParameter('cinemaId', $cinemaId);
        }

        // Retourner les résultats
        return $qb->getQuery()->getResult();
    }

}
