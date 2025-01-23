<?php

namespace App\Controller\Admin;

use App\Controller\APIController;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class sessionController extends AbstractController
{
    #[Route('/session/create', name: 'app_admin_session_create')]
    public function createRequest (Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $entityManager->flush();

            $this->addFlash('success', 'La séance a été créée avec succès.');

            return $this->redirectToRoute('app_admin_session');
        }

        return $this->render('admin/Session/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/session', name: 'app_admin_session')]
    public function list(Request $request, UserRepository $userRepository, APIController $apiController,EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        $sessions = $entityManager->getRepository(Session::class)->findAll();
        return $this->render('admin/Session/list.html.twig', [
            'sessions' => $sessions,
            'user' => $user,
        ]);
    }

    #[Route('/session/{id}', name: 'app_admin_session_edit')]
    public function editRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $session = $entityManager->getRepository(Session::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('La séance n\'existe pas.');
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash('success', 'La séance a été modifiée avec succès.');

            return $this->redirectToRoute('app_admin_session');
        }

        return $this->render('admin/Session/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'session' => $session,
        ]);
    }

    #[Route('/session/delete/{id}', name: 'app_admin_session_delete')]
    public function deleteRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $session = $entityManager->getRepository(Session::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('La séance n\'existe pas.');
        }

        $entityManager->remove($session);
        $entityManager->flush();

        $this->addFlash('success', 'La séance a été supprimée avec succès.');

        return $this->redirectToRoute('app_admin_session');
    }
}
