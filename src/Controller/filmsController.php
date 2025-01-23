<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Review;
use App\Form\FilmFilterType;
use App\Repository\CinemaRepository;
use App\Repository\FilmRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

class filmsController extends AbstractController
{
    #[Route("/films", name: "app_films")]
    public function index(Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur à partir du token
        $user = $apiController->getUserFromToken($request, $userRepository);
        $cinemaId = $request->query->get('cinema');
        $genreId = $request->query->get('genre');

        // Si les paramètres sont définis et sont des nombres valides, on les transforme en int
        $cinemaId = $cinemaId ? intval($cinemaId) : null;
        $genreId = $genreId ? intval($genreId) : null;

        // Appel de la méthode findFilmsByFilters pour obtenir les films filtrés
        $filmsFilter = $entityManager->getRepository(Film::class)
            ->findFilmsByFilters($cinemaId, $genreId);

        // Récupération des autres informations nécessaires
        $films = $entityManager->getRepository(Film::class)->findAll();
        $cinemas = $entityManager->getRepository(Cinema::class)->findAll();
        $genres = $entityManager->getRepository(Genre::class)->findAll();

        $filmsWithRatings = [];
        foreach ($films as $film) {
            $averageRating = $entityManager->getRepository(Review::class)->getAverageRatingForFilm($film->getId());
            $film->averageRating = $averageRating;
        }

        return $this->render('films.html.twig', [
            'user' => $user,
            'films' => $films,
            'filmsFilter' => $filmsFilter,
            'genres' => $genres,
            'filmsWithRatings' => $filmsWithRatings,
            'cinemas' => $cinemas,
        ]);
    }

    #[Route("/film/{id}", name: "app_film_show")]
    public function show(int $id, Request $request, FilmRepository $filmRepository, APIController $apiController, UserRepository $userRepository, SessionRepository $sessionRepository, CinemaRepository $cinemaRepository): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        // Récupérer le film par son ID
        $film = $filmRepository->find($id);
        if (!$film) {
            throw $this->createNotFoundException('Film non trouvé');
        }

        // Récupérer toutes les séances pour ce film
        $sessions = $sessionRepository->findByFilm($film);

        // Récupérer tous les cinémas associés à ces séances
        $cinemas = [];
        foreach ($sessions as $session) {
            $cinema = $session->getCinema();
            if (!in_array($cinema, $cinemas)) {
                $cinemas[] = $cinema;
            }
        }

        return $this->render('show_film.html.twig', [
            'film' => $film,
            'sessions' => $sessions,
            'cinemas' => $cinemas,
            'user' => $user,
        ]);
    }


}