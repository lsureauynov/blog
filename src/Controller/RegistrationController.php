<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\EmailService; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class RegistrationController extends AbstractController
{
    private EmailService $emailService;
    private EntityManagerInterface $entityManager;

    public function __construct(EmailService $emailService, EntityManagerInterface $entityManager)
    {
        $this->emailService = $emailService;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            $data= $request->request->all("registration_form");       

            if ($this->isCsrfTokenValid("registration_form",$data['_token'])) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            $email = $form->get('email')->getData();
    
        
            $emailCount = $this->entityManager->getRepository(User::class)->count(['email' => $email]);
    

            if ($emailCount > 0) {
                return $this->redirectToRoute('account_already_exists');
            }

            if ($form->isValid()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
    
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                var_dump($user->getEmail());
                $this->emailService->sendEmail($user->getEmail(), 'd-c700cb43db334983ba7af1c4ed8020f6');


                return $this->redirectToRoute('app_articles_index');
            }
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/account-already-exists', name: 'account_already_exists')]
    public function accountAlreadyExists(): Response
    {
        return $this->render('registration/account_already_exists.html.twig');
    }
}
