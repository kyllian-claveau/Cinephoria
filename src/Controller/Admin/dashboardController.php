<?php

namespace App\Controller\Admin;

use App\Controller\APIController;
use App\Repository\UserRepository;
use App\Service\FilmStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin')]
class dashboardController extends AbstractController
{
    #[Route(path: '/dashboard', name: 'app_admin_dashboard', methods: ["GET"])]
    public function list(FilmStatsService $statsService, Request $request, UserRepository $userRepository, APIController $apiController): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $stats = $statsService->getWeeklyStats();

        return $this->render('admin/dashboard.html.twig', [
            'user' => $user,
            'stats' => $stats
        ]);
    }
}