<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use App\Service\SendMailService;
use App\Service\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager, TokenService $tokenService, SendMailService $sendMailService): ?Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the user's password
            $plainPassword = $form->get('plainPassword')->getData();
            if (!is_null($plainPassword) && is_string($plainPassword)) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }
            // Persist the user to the database
            $entityManager->persist($user);
            $entityManager->flush();
            // Generate a token for email verification
            $payload = ['email' => $user->getEmail(), 'id' => $user->getId()];
            $validity = 3600;
            $secret = $this->getParameter('app.secret');
            if (is_string($secret)) {
                $token = $tokenService->generate([], $payload, $secret, $validity);
                $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('PT'.$validity.'S'));
                // Send the verification email
                $sendMailService->send(
                    'your-email@domain.com',
                    $user->getEmail() ?? '',
                    'Email Verification',
                    'confirmation_email',
                    [
                        'url' => $this->generateUrl('app_verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                        'expiresAt' => $expiresAt,
                    ]
                );
            }
            $this->addFlash('success', 'Your account has been created. Please check your email for a verification link.');
            // Authenticate the user and redirect to the homepage
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        // Render the registration form
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email/{token}', name: 'app_verify_email')]
    public function verifyUserEmail(string $token, TranslatorInterface $translator, UserRepository $userRepository, EntityManagerInterface $entityManager, TokenService $tokenService): Response
    {
        $secret = $this->getParameter('app.secret');
        if (is_string($secret) && !$tokenService->check($token, $secret)) {
            $this->addFlash('error', $translator->trans('The verification link is invalid or has expired. Please register again.'));

            return $this->redirectToRoute('app_register');
        }
        // Get the payload from the token
        $payload = $tokenService->getPayload($token);
        // Check if user exists and not already verified
        $user = $userRepository->find($payload['id']);
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', $translator->trans('The user does not exist. Please register again.'));

            return $this->redirectToRoute('app_register');
        }
        if ($user->getIsVerified()) {
            $this->addFlash('info', $translator->trans('The user is already verified. Please login.'));

            return $this->redirectToRoute('app_login');
        }
        // Verify and save the user
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success', $translator->trans('Your email address has been verified. You can now login.'));

        return $this->redirectToRoute('app_login');
    }
}
