<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

class indexController extends AbstractController
{
    #[Route("/", name: "app_index")]
    public function index(Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager)
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        $filmsFromLastWednesday =$entityManager->getRepository(Film::class)->findFilmsFromLastWednesday();
        return $this->render('index.html.twig', [
            'user' => $user,
            'films' => $filmsFromLastWednesday,
        ]);
    }
}