<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    private $jwtManager;
    private $mailer;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        MailerInterface          $mailer
    )
    {
        $this->jwtManager = $jwtManager;
        $this->mailer = $mailer;
    }

    #[Route(path:'/login', name: 'app_login')]
    public function login(){
        $loginForm = $this->createForm(LoginType::class);
        return $this->render('auth/login.html.twig', [
            'loginForm' => $loginForm->createView()
        ]);
    }

    #[Route(path: '/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(Request $request, APIController $APIController, UserRepository $userRepository, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // Utilisation de la méthode pour obtenir l'utilisateur à partir du token
        $user = $APIController->getUserFromToken($request, $userRepository);

        if ($user instanceof User) {
            $token = $this->jwtManager->create($user);
            return new JsonResponse([
                'message' => 'Authentification réussie',
                'token' => $token
            ], JsonResponse::HTTP_OK);

        }

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error instanceof AuthenticationException) {
            return new JsonResponse([
                'message' => 'Identifiants invalides',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'message' => 'Erreur lors de la connexion',
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route(path: '/api/change-password', name: 'api_change_password', methods: ['POST'])]
    public function changePassword(
        Request                     $request,
        UserRepository              $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager,
        APIController $apiController,
    ): JsonResponse
    {

        // Décoder le token pour récupérer l'utilisateur
        $user = $apiController->getUserFromToken($request, $userRepository);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Récupérer le nouveau mot de passe depuis la requête
        $data = json_decode($request->getContent(), true);
        $newPassword = $data['newPassword'] ?? null;

        if (!$newPassword) {
            return new JsonResponse(['message' => 'Mot de passe manquant'], 400);
        }

        // Hachage du nouveau mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);

        // Mettre à jour le mot de passe de l'utilisateur
        $user->setPassword($hashedPassword);
        $user->setIsTemporaryPassword(false);  // Désactive l'indicateur de mot de passe temporaire

        // Sauvegarder les modifications
        $entityManager->flush();

        return new JsonResponse(['message' => 'Mot de passe changé avec succès.']);
    }


    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): Response
    {
        $response = $this->redirectToRoute('app_index');
        $response->headers->clearCookie('authToken');
        return $response;
    }

    #[Route(path: '/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        UserRepository              $userRepository,
        APIController               $apiController,
        Request                     $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager,
        MailerInterface             $mailer
    ): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);

        if ($user instanceof User && $user->getId()) {
            return $this->redirectToRoute('app_index');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setIsActive(false); // Account will remain inactive until confirmed

            // Generate a confirmation token
            $confirmationToken = bin2hex(random_bytes(32));
            $user->setConfirmationToken($confirmationToken);

            // Save the user
            $entityManager->persist($user);
            $entityManager->flush();

            // Send confirmation email
            $confirmationUrl = $this->generateUrl(
                'app_confirm_account',
                ['token' => $confirmationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('no-reply@votresite.com')
                ->to($user->getEmail())
                ->subject('Confirmez votre inscription')
                ->html($this->renderView('confirmation.html.twig', [
                    'user' => $user,
                    'confirmationUrl' => $confirmationUrl,
                ]));

            $mailer->send($email);

            $this->addFlash('success', 'Un e-mail de confirmation a été envoyé à votre adresse.');

            return new JsonResponse(['message' => 'Veuillez activez votre compte']);
        }

        return $this->render('register.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route(path: '/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request                     $request,
        UserRepository              $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager
    ): JsonResponse
    {
        // Récupération et validation de l'email
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['message' => 'Email manquant'], 400);
        }

        // Validation de l'email (utilisation de la fonction filter_var)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Email invalide'], 400);
        }

        // Vérification de l'existence de l'utilisateur
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Génération d'un mot de passe temporaire
        $temporaryPassword = bin2hex(random_bytes(5)); // 10 caractères de mot de passe temporaire

        // Hachage du mot de passe temporaire
        $encodedPassword = $passwordHasher->hashPassword($user, $temporaryPassword);

        // Mise à jour du mot de passe de l'utilisateur
        $user->setPassword($encodedPassword);
        $user->setIsTemporaryPassword(true);
        $entityManager->flush();

        // Envoi de l'email avec le mot de passe temporaire
        $emailMessage = (new Email())
            ->from('noreply@votresite.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation du mot de passe')
            ->html("<p>Voici votre mot de passe temporaire : <strong>$temporaryPassword</strong></p>");

        try {
            $this->mailer->send($emailMessage);
            return new JsonResponse(['message' => 'Un mot de passe temporaire a été envoyé à votre email.']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de l\'envoi de l\'email.'], 500);
        }
    }

    #[Route('/confirm/{token}', name: 'app_confirm_account', methods: ['GET'])]
    public function confirmAccount(
        string                 $token,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token invalide ou utilisateur introuvable.');
            return $this->redirectToRoute('app_register');
        }

        $user->setConfirmationToken(null); // Remove the token
        $user->setIsActive(true); // Activate the account
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.');

        return new JsonResponse(['message' => 'Compte activé !']);
    }
}