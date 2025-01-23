<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, UserRepository $userRepository, APIController $apiController): Response
    {
        $user = $apiController->getUserFromToken($request, $userRepository);
        // Créez le formulaire de contact
        $form = $this->createForm(ContactType::class);

        // Traitez la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez les données du formulaire
            $data = $form->getData();

            // Créez l'email
            $email = (new Email())
                ->from('no-reply@cinephoria.com')  // Email générique
                ->to('kyllian.claveau@gmail.com')  // L'email de réception des demandes
                ->subject('Demande de contact - ' . $data['title'])
                ->html(
                    $this->renderView('emails/contact.html.twig', [
                        'username' => $data['username'] ?? 'Anonyme',
                        'title' => $data['title'],
                        'description' => $data['description']
                    ])
                );

            // Envoyer l'email
            $mailer->send($email);

            // Afficher un message de succès
            $this->addFlash('success', 'Votre demande a été envoyée avec succès !');

            // Rediriger après l'envoi
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
