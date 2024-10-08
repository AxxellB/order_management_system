<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AddressController extends AbstractController
{
    #[Route('/me/addresses', name: 'user_addresses')]
    public function index(): Response
    {
        $user = $this->getUser();

        $addresses = $user->getAddresses();
        return $this->render('address/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }
}
