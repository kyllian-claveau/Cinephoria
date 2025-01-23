<?php

namespace App\Controller\Employee;

use App\Controller\APIController;
use App\Entity\Film;
use App\Form\FilmType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employee')]
class filmController extends AbstractController
{
    #[Route('/film/create', name: 'app_employee_film_create')]
    public function createRequest(Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Vérification du jour actuel
        $currentDay = (new \DateTime())->format('l'); // "l" donne le nom du jour en anglais
        if ($currentDay !== 'Wednesday') {
            $this->addFlash('error', 'Les films ne peuvent être créés que le mercredi.');
            return $this->redirectToRoute('app_employee_film'); // Redirige vers une autre page
        }

        $film = new Film();
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($film->getFilmFile() !== null) {
                $filename = uniqid() . '.' . $film->getFilmFile()->guessClientExtension();

                $path = $this->getParameter('kernel.project_dir') . '/public/images/film/' . $filename;
                $content = $film->getFilmFile()->getContent();
                file_put_contents($path, $content);
                $film->setFilmFilename($filename);
                $film->setFilmFile(null);
            }
            $entityManager->persist($film);
            $entityManager->flush();

            $this->addFlash('success', 'Le film a été créé avec succès.');

            return $this->redirectToRoute('app_employee_film');
        }

        return $this->render('employee/Film/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    #[Route('/film', name: 'app_employee_film')]
    public function list(Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager)
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        $films = $entityManager->getRepository(Film::class)->findAll();
        return $this->render('employee/Film/list.html.twig', [
            'films' => $films,
            'user' => $user,
        ]);
    }

    #[Route('/film/{id}', name: 'app_employee_film_edit')]
    public function editRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $film = $entityManager->getRepository(Film::class)->find($id);
        if (!$film) {
            throw $this->createNotFoundException('Le film n\'existe pas.');
        }

        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($film->getFilmFile() !== null) {
                $filename = uniqid() . '.' . $film->getFilmFile()->guessClientExtension();

                $path = $this->getParameter('kernel.project_dir') . '/public/images/film/' . $filename;
                $content = $film->getFilmFile()->getContent();
                file_put_contents($path, $content);
                $film->setFilmFilename($filename);
                $film->setFilmFile(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le film a été modifié avec succès.');

            return $this->redirectToRoute('app_employee_film');
        }

        return $this->render('employee/Film/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'film' => $film,
        ]);
    }

    #[Route('/film/delete/{id}', name: 'app_employee_film_delete')]
    public function deleteRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_EMPLOYEE', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $film = $entityManager->getRepository(Film::class)->find($id);
        if (!$film) {
            throw $this->createNotFoundException('Le film n\'existe pas.');
        }

        if ($film->getFilmFilename()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/images/film/' . $film->getFilmFilename();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $entityManager->remove($film);
        $entityManager->flush();

        $this->addFlash('success', 'Le film a été supprimé avec succès.');

        return $this->redirectToRoute('app_employee_film');
    }
}
