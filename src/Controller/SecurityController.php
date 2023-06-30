<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(private TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        throw new \LogicException($this->translator->trans('This method can be blank - it will be intercepted by the logout key on your firewall.'));
    }

    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function forgottenPassword(Request $request, SecurityService $securityService): Response
    {
        if ($this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $this->ensureIsString(
                $form->get('username')->getData(),
                $this->translator->trans('Username must be a string.')
            );
            $securityService->handleForgotPasswordRequest($username);
            $this->addFlash('success', $this->translator->trans('An email has been sent with the password reset link.'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPass(string $token, Request $request, SecurityService $securityService): Response
    {
        if ($this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->ensureIsString(
                $form->get('plainPassword')->getData(),
                $this->translator->trans('Password must be a string.')
            );
            $securityService->handleResetPassword($token, $password);
            $this->addFlash('success', $this->translator->trans('Password successfully changed.'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * This function is here toi avoid code duplication.
     * It checks if a value passed by the user is a string.
     */
    private function ensureIsString(mixed $value, string $errorMessage): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException($errorMessage);
        }

        return $value;
    }
}
