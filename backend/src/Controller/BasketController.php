<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Product;
use App\Service\BasketService;
use App\Repository\BasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/',name: 'basket_view', methods: ['GET'])]
    public function viewBasket(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $basket = $this->basketService->getOrCreateBasket($user);

        return new JsonResponse([
            'basket' => $this->formatBasket($basket),
        ], Response::HTTP_OK);
    }

    #[Route('/add/{id}', name: 'basket_add_product', methods: ['POST'])]
    public function addProduct(Request $request, Product $product): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $quantity = $request->request->get('quantity', 1);
        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->addProductToBasket($basket, $product, $quantity);

        return new JsonResponse([
            'message' => 'Product added to basket',
            'basket' => $this->formatBasket($basket),
        ], Response::HTTP_CREATED);
    }

    #[Route('/edit/{id}', name: 'basket_edit_product', methods: ['PUT'])]
    public function edit(Request $request, Product $product): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $quantity = $request->request->get('quantity', 1);
        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->updateProductQuantity($basket, $product, $quantity);

        return new JsonResponse([
            'message' => 'Product quantity edited from basket',
            'basket' => $this->formatBasket($basket),
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'basket_remove_product', methods: ['DELETE'])]
    public function removeProduct(Product $product): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->removeProductFromBasket($basket, $product);

        return new JsonResponse([
            'message' => 'Product removed from basket',
            'basket' => $this->formatBasket($basket),
        ], Response::HTTP_OK);
    }

    #[Route('/clear/{id}', name: 'basket_clear', methods: ['POST'])]
    public function clearBasket(int $id, BasketService $basketService): JsonResponse
    {
        $basket = $this->basketRepository->find($id);

        if (!$basket) {
            throw $this->createNotFoundException('Basket not found');
        }

        $basketService->clearBasket($basket);

        return new JsonResponse([
            'message' => 'Basket cleared',
        ], Response::HTTP_OK);
    }

    private function formatBasket(Basket $basket): array
    {
        $formatted = [];
        foreach ($basket->getBasketProducts() as $basketProduct) {
            $formatted[] = [
                'product' => [
                    'id' => $basketProduct->getProduct()->getId(),
                    'name' => $basketProduct->getProduct()->getName(),
                    'price' => $basketProduct->getProduct()->getPrice(),
                ],
                'quantity' => $basketProduct->getQuantity(),
            ];
        }
        return $formatted;
    }

}
