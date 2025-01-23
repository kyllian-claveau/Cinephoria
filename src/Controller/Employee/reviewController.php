<?php

namespace App\Controller\Employee;

use App\Controller\APIController;
use App\Entity\Film;
use App\Entity\Review;
use App\Entity\User;
use App\Form\FilmType;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employee')]
class reviewController extends AbstractController
{
    #[Route('/reviews/to-validate', name: 'app_employee_reviews')]
    public function listReviewsToValidate(EntityManagerInterface $entityManager, APIController $apiController, Request $request, UserRepository $userRepository): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_index');
        }
        // Récupérer tous les avis non validés
        $reviews = $entityManager->getRepository(Review::class)->findBy(['validated' => false]);

        return $this->render('employee/Review/to_validate.html.twig', [
            'reviews' => $reviews,
            'user' => $user
        ]);
    }

    #[Route('/reviews/approve/{id}', name: 'app_employee_review_approve')]
    public function approve(int $id, EntityManagerInterface $entityManager, APIController $apiController, Request $request, UserRepository $userRepository): RedirectResponse
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_index');
        }

        $review = $entityManager->getRepository(Review::class)->find($id);

        if (!$review) {
            $this->addFlash('error', 'Avis introuvable');
            return $this->redirectToRoute('app_employee_reviews');
        }

        $review->setValidated(true);
        $entityManager->flush();

        $this->addFlash('success', 'Avis validé avec succès.');
        return $this->redirectToRoute('app_employee_reviews');
    }

    #[Route('/reviews/reject/{id}', name: 'app_employee_review_reject')]
    public function reject(int $id, EntityManagerInterface $entityManager, APIController $apiController, Request $request, UserRepository $userRepository): RedirectResponse
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_index');
        }

        $review = $entityManager->getRepository(Review::class)->find($id);

        if (!$review) {
            $this->addFlash('error', 'Avis introuvable');
            return $this->redirectToRoute('app_employee_reviews');
        }
        $review->setValidated(false);
        $entityManager->flush();

        $this->addFlash('success', 'Avis rejeté avec succès.');
        return $this->redirectToRoute('app_employee_reviews');
    }

}
