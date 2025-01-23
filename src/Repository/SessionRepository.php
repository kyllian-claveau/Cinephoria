<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findByFilm(Film $film): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.cinema', 'c')  // Jointure avec les cinémas
            ->addSelect('c')  // Sélectionner les cinémas associés
            ->where('s.film = :film')
            ->setParameter('film', $film)
            ->orderBy('s.startDate', 'ASC')  // Trier par date de début
            ->getQuery()
            ->getResult();
    }

    public function findSessionsByCinemas(?int $cinemaId): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.film', 'f') // Relier les séances aux films
            ->leftJoin('s.cinema', 'c') // Relier les séances aux cinémas
            ->addSelect('f', 'c'); // Charger les films et cinémas associés

        // Filtrage par cinéma si spécifié
        if ($cinemaId) {
            $qb->andWhere('c.id = :cinemaId')
                ->setParameter('cinemaId', $cinemaId);
        }

        // Retourner les résultats
        return $qb->getQuery()->getResult();
    }

}
