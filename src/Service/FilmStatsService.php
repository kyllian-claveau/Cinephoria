<?php
// src/Service/FilmStatsService.php
namespace App\Service;

use App\Document\FilmStats;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class FilmStatsService
{
    public function __construct(
        private LoggerInterface $logger
    )
    {
    }


    public function updateStatsForReservation($reservation): void
    {
        try {
            $session = $reservation->getSession();
            $film = $session->getFilm();
            $today = new \DateTime();
            $today->setTime(0, 0);

// Log de la tentative de mise à jour des statistiques
            $this->logger->info('Tentative de mise à jour des statistiques pour le film', [
                'filmId' => $film->getId(),
                'title' => $film->getTitle(),
                'date' => $today->format('Y-m-d')
            ]);

// Connexion à MongoDB
            $uri = $_ENV['MONGODB_DSN'];
            $apiVersion = new ServerApi(ServerApi::V1);
            $client = new Client($uri, [], ['serverApi' => $apiVersion]);
            $collection = $client->selectDatabase($_ENV['MONGODB_DB'])->selectCollection($_ENV['MONGODB_COLLECTION']);

// Recherche ou création du document pour les statistiques
            $date = new UTCDateTime($today);
            $stats = $collection->findOne([
                'filmId' => $film->getId(),
                'date' => $date
            ]);

            if (!$stats) {
// Création d'un nouveau document si non trouvé
                $stats = [
                    'filmId' => $film->getId(),
                    'filmTitle' => $film->getTitle(),
                    'date' => $date,
                    'reservationsCount' => 0,
                    'totalRevenue' => 0.0
                ];
            }

// Mise à jour des statistiques
            $stats['reservationsCount'] += 1;
            $stats['totalRevenue'] += $reservation->getTotalPrice();

// Log avant l'insertion
            $this->logger->info('Mise à jour des statistiques', [
                'filmId' => $film->getId(),
                'reservationsCount' => $stats['reservationsCount'],
                'revenue' => $stats['totalRevenue']
            ]);

// Insertion ou mise à jour dans la base MongoDB
            $collection->updateOne(
                ['filmId' => $film->getId(), 'date' => $date],
                ['$set' => $stats],
                ['upsert' => true] // Créer le document s'il n'existe pas
            );

            $this->logger->info('Statistiques mises à jour avec succès');
        } catch (\Exception $e) {
            $this->logger->error('Échec de la mise à jour des statistiques: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Relancer l'exception pour un traitement éventuel dans le contrôleur
        }
    }

    /**
     * Récupère les statistiques hebdomadaires depuis MongoDB
     * @return array
     */
    public function getWeeklyStats(): array
    {
        try {
            // Connexion à MongoDB
            $uri = $_ENV['MONGODB_DSN'];
            $apiVersion = new ServerApi(ServerApi::V1);
            $client = new Client($uri, [], ['serverApi' => $apiVersion]);
            $collection = $client->selectDatabase($_ENV['MONGODB_DB'])->selectCollection($_ENV['MONGODB_COLLECTION']);

            $date = new UTCDateTime(new \DateTime('7 days ago'));

            // Récupération des statistiques des 7 derniers jours
            $cursor = $collection->aggregate([
                [
                    '$match' => [
                        'date' => ['$gte' => $date]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$filmTitle',
                        'count' => ['$sum' => '$reservationsCount'],
                        'revenue' => ['$sum' => '$totalRevenue']
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ]
            ]);

            // Traitement des données
            $processedStats = [];
            $films = [];
            $dates = [];

            foreach ($cursor as $document) {
                $filmTitle = $document['_id'];
                $processedStats[] = [
                    'film' => $filmTitle,
                    'count' => $document['count'],
                    'revenue' => $document['revenue']
                ];
                $films[] = $filmTitle;
            }

            // Récupération des films et des dates
            $dates[] = (new \DateTime('7 days ago'))->format('Y-m-d');

            return [
                'stats' => $processedStats,
                'films' => $films,
                'dates' => $dates
            ];
        } catch (\Exception $e) {
            $this->logger->error('Échec de la récupération des statistiques hebdomadaires: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

}
