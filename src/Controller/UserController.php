<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\EditUserFormType;
use App\Service\UserService;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

#[Route(path: '/user')]
class UserController extends AbstractController
{
    private UserService $userService;
    private EntityManagerInterface $em;

    public function __construct(UserService $userService, EntityManagerInterface $em){
        $this->userService = $userService;
        $this->em = $em;
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

    #[Route(path: '/me', name: 'user_profile')]
    public function profile(Request $request): Response
    {
        $user = $this->getUser();
        $addresses = $user->getAddresses();

        $form = $this->createForm(EditUserFormType::class, $user, [
            'addresses' => $addresses,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->editUser($user, $form);
            return $this->redirectToRoute('user_login');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/deactive', name: 'user_deactivate')]
    public function deactivate(Request $request): Response
    {
        $user = $this->getUser();
        $this->userService->deleteUser($user);
        return $this->redirectToRoute('user_logout');
    }

    #[Route('/me/get-address/{id}', name: 'get_address', methods: ['GET'])]
    public function getAddress(int $id): JsonResponse
    {
        $address = $this->em->getRepository(Address::class)->find($id);

        if(!$address){
            return new JsonResponse(['message' => 'Address not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'line' => $address->getLine(),
            'city' => $address->getCity(),
            'country' => $address->getCountry(),
            'postcode' => $address->getPostcode(),
        ]);
    }

    #[Route(path: '/logout', name: 'user_logout')]
    public function logout(): void
    {
    }
}
