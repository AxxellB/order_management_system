<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Form\RegisterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/user')]
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    #[Route(path: '/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/register', name: 'user_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $addressData = $form->get('addresses')->getData();
            $user->addAddress($addressData);
            $this->userService->createUser($user);

            return $this->redirectToRoute('user_login');
        }
        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'user_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
