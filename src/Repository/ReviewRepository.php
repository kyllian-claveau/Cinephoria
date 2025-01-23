<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getAverageRatingForFilm($filmId): ?float
    {
        // Récupère toutes les reviews validées pour un film donné
        $qb = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)') // Calcule la moyenne des évaluations
            ->where('r.film = :filmId') // Filtre par film
            ->andWhere('r.validated = :validated') // Filtre pour les avis validés
            ->setParameter('filmId', $filmId)
            ->setParameter('validated', true); // Prendre seulement les avis validés

        // Exécute la requête
        $result = $qb->getQuery()->getSingleScalarResult();

        // Si aucune évaluation validée n'est trouvée, retourne null
        return $result ? round($result, 2) : null;
    }
}
