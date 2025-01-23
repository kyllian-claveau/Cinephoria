<?php

namespace App\Controller\Admin;

use App\Controller\APIController;
use App\Entity\Film;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\EmployeeEditType;
use App\Form\FilmType;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class employeeController extends AbstractController
{
    #[Route('/employee/create', name: 'app_admin_employee_create')]
    public function register(Request $request, UserRepository $userRepository, APIController $apiController, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employee = new User();
        $form = $this->createForm(RegisterType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $employee->setPassword(
                $userPasswordHasher->hashPassword(
                    $employee,
                    $form->get('password')->getData()
                )
            );
            $employee->setRoles(['ROLE_EMPLOYEE']);
            $entityManager->persist($employee);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('admin/Employee/create.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
            'user' => $user,
        ]);
    }

    #[Route('/employee', name: 'app_admin_employee')]
    public function list(Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager)
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        $employees = $userRepository->findEmployees();
        return $this->render('admin/Employee/list.html.twig', [
            'employees' => $employees,
            'user' => $user,
        ]);
    }
    #[Route('/employee/{id}', name: 'app_admin_employee_edit')]
    public function editEmployee(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employee = $userRepository->find($id);
        if (!$employee || !in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
            throw $this->createNotFoundException('L\'employé n\'existe pas.');
        }

        $form = $this->createForm(EmployeeEditType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de l\'employé ont été modifiées avec succès.');

            return $this->redirectToRoute('app_admin_employee');
        }

        return $this->render('admin/Employee/edit.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
            'user' => $user,
        ]);
    }

    #[Route('/employee/change-password/{id}', name: 'app_admin_employee_change_password')]
    public function changePassword(int $id, Request $request, UserRepository $userRepository, APIController $apiController, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employee = $userRepository->find($id);
        if (!$employee || !in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
            throw $this->createNotFoundException('L\'employé n\'existe pas.');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();
            $employee->setPassword(
                $userPasswordHasher->hashPassword(
                    $employee,
                    $newPassword
                )
            );

            $entityManager->flush();

            $this->addFlash('success', 'Le mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_employee');
        }

        return $this->render('admin/Employee/change_password.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
            'user' => $user,
        ]);
    }

    #[Route('/employee/delete/{id}', name: 'app_admin_employee_delete')]
    public function deleteRequest(int $id, Request $request, UserRepository $userRepository, APIController $apiController, EntityManagerInterface $entityManager): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employee = $entityManager->getRepository(User::class)->find($id);
        if (!$employee) {
            throw $this->createNotFoundException('L\'employé n\'existe pas.');
        }

        $entityManager->remove($employee);
        $entityManager->flush();

        $this->addFlash('success', 'L\'employé a été supprimé avec succès.');

        return $this->redirectToRoute('app_admin_employee');
    }
}