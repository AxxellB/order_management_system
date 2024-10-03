<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Product;
use App\Entity\User;
use App\Service\BasketService;
use App\Form\BasketType;
use App\Repository\BasketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/basket')]

final class BasketController extends AbstractController
{
    private BasketService $basketService;
    private BasketRepository $basketRepository;

    public function __construct(BasketService $basketService, BasketRepository $basketRepository)
    {
        $this->basketService = $basketService;
        $this->basketRepository = $basketRepository;
    }

    #[Route('/',name: 'basket_view')]
    public function viewBasket(): Response
    {
        $user = $this->getUser();
        $basket = $this->basketService->getOrCreateBasket($user);

        return $this->render('basket/index.html.twig', [
            'basket' => $basket,
        ]);
    }

    #[Route('/add/{id}', name: 'basket_add_product', methods: ['POST'])]
    public function addProduct(Request $request, Product $product): Response
    {
        $quantity = $request->request->get('quantity', 1);
        $user = $this->getUser();
        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->addProductToBasket($basket, $product, $quantity);

        return $this->redirectToRoute('homepage');
    }

    #[Route('/remove/{id}', name: 'basket_remove_product', methods: ['POST'])]
    public function removeProduct(Product $product): Response
    {
        $user = $this->getUser();
        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->removeProductFromBasket($basket, $product);
        return $this->redirectToRoute('basket_view');
    }

    #[Route('/edit/{id}', name: 'basket_edit_product', methods: ['POST'])]
    public function edit(Request $request, Product $product): Response
    {
        $quantity = $request->request->get('quantity');
        $user = $this->getUser();
        $basket = $this->basketService->getOrCreateBasket($user);

        $basket = $this->basketService->updateProductQuantity($basket, $product, $quantity);

        return $this->redirectToRoute('basket_view');
    }

    #[Route('clear/{id}', name: 'basket_clear', methods: ['POST'])]
    public function clearBasket(int $id, BasketService $basketService): Response
    {
        $basket = $this->basketRepository->find($id);

        if (!$basket) {
            throw $this->createNotFoundException('Basket not found');
        }

        $basketService->clearBasket($basket);

        return $this->redirectToRoute('basket_view', [
            'id' => $id,
        ]);
    }
}
