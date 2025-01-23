<?php

namespace App\Controller\Employee;

use App\Controller\APIController;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path:'/employee')]
class dashboardController extends AbstractController
{
    #[Route(path: '/dashboard', name:'app_employee_dashboard', methods: ["GET"])]
    public function list(Request $request, UserRepository $userRepository, APIController $apiController): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

            return $this->render('employee/dashboard.html.twig', [
                'user' => $user
            ]);
        }
}