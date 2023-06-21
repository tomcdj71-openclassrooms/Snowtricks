<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use App\Service\TokenService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private TranslatorInterface $translator,
        private TokenService $tokenService
    ) {
        $this->translator = $translator;
        $this->userService = $userService;
        $this->tokenService = $tokenService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator): ?Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $appSecret = $this->getParameter('app.secret');

            if (is_string($plainPassword) && is_string($appSecret)) {
                $this->userService->registerUser($user, $plainPassword, $appSecret);
                $this->addFlash('success', $this->translator->trans('Your account has been created. Please check your email for a verification link.'));

                return $userAuthenticator->authenticateUser($user, $authenticator, $request);
            }

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
    public function verifyUserEmail(string $token, UserRepository $userRepository): Response
    {
        // Get the payload from the token
        $payload = $this->tokenService->getPayload($token);

        // Check if user exists and not already verified
        $user = $userRepository->find($payload['id']);
        if (!$user instanceof User) {
            $this->addFlash('error', $this->translator->trans('The user does not exist. Please register again.'));

            return $this->redirectToRoute('app_register');
        }
        if ($user->getIsVerified()) {
            $this->addFlash('info', $this->translator->trans('The user is already verified. Please login.'));

            return $this->redirectToRoute('app_login');
        }
        $appSecret = $this->getParameter('app.secret');
        if (is_string($appSecret)) {
            $this->userService->verifyUserEmail($user, $token, $appSecret);
            $this->addFlash('success', $this->translator->trans('Your email address has been verified. You can now login.'));

            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_login');
    }
}
