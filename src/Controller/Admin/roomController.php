<?php

namespace App\Controller\Admin;

use App\Controller\APIController;
use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class roomController extends AbstractController
{
    #[Route('/room/create', name: 'app_admin_room_create')]
    public function createRequest (Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();

            $this->addFlash('success', 'La salle a été créée avec succès.');

            return $this->redirectToRoute('app_admin_room');
        }

        return $this->render('admin/Room/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/room', name: 'app_admin_room')]
    public function list(Request $request, UserRepository $userRepository, APIController $apiController,EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        $rooms = $entityManager->getRepository(Room::class)->findAll();
        return $this->render('admin/Room/list.html.twig', [
            'rooms' => $rooms,
            'user' => $user,
        ]);
    }

    #[Route('/room/{id}', name: 'app_admin_room_edit')]
    public function editRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $room = $entityManager->getRepository(Room::class)->find($id);
        if (!$room) {
            throw $this->createNotFoundException('La salle n\'existe pas.');
        }

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash('success', 'La salle a été modifiée avec succès.');

            return $this->redirectToRoute('app_admin_room');
        }

        return $this->render('admin/Room/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'room' => $room,
        ]);
    }

    #[Route('/room/delete/{id}', name: 'app_admin_room_delete')]
    public function deleteRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $room = $entityManager->getRepository(Room::class)->find($id);
        if (!$room) {
            throw $this->createNotFoundException('La salle n\'existe pas.');
        }

        $entityManager->remove($room);
        $entityManager->flush();

        $this->addFlash('success', 'La séance a été supprimée avec succès.');

        return $this->redirectToRoute('app_admin_room');
    }
}
