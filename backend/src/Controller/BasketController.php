<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Product;
use App\Service\BasketService;
use App\Repository\BasketRepository;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api')]
final class BasketController extends AbstractController
{
    private BasketService $basketService;
    private BasketRepository $basketRepository;

    public function __construct(BasketService $basketService, BasketRepository $basketRepository)
    {
        $this->basketService = $basketService;
        $this->basketRepository = $basketRepository;
    }

    // API
    #[Route('/basket',name: 'api_basket_view', methods: ['GET'])]
    public function apiViewBasket(): JsonResponse
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

    #[Route('/basket', name: 'api_basket_add_product', methods: ['POST'])]
    public function apiAddProduct(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $basket = $this->basketService->getOrCreateBasket($user);

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'];
        $productId = $data['productId'];


        $this->basketService->addProductToBasket($basket, $productId, $quantity);

        return new JsonResponse([
            'message' => 'Product added to basket',
            'basket' => $this->formatBasket($basket),
        ], Response::HTTP_CREATED);
    }

    #[Route('/basket/{id}', name: 'api_basket_edit_product', methods: ['PUT'])]
    public function apiEdit(Request $request, Product $product): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 1;

        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->updateProductQuantity($basket, $product, $quantity);

        return new JsonResponse([
            'message' => 'Product quantity edited from basket',
            'basket' => $this->formatBasket($basket),
        ], Response::HTTP_OK);
    }

    #[Route('/basket/{id}', name: 'api_basket_remove_product', methods: ['DELETE'])]
    public function apiRemoveProduct(Product $product): JsonResponse
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

    #[Route('/basket', name: 'api_basket_clear', methods: ['DELETE'])]
    public function apiClearBasket(BasketService $basketService): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $basket = $user->getBasket();

        if (!$basket) {
            return new JsonResponse(['error' => 'Basket not found'], Response::HTTP_NOT_FOUND);
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
    /*
    // TWIG TEMPLATES
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

        try {
            $this->basketService->addProductToBasket($basket, $product, $quantity);
            $this->addFlash('success', 'Product added to basket');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('homepage');
        }

        return $this->redirectToRoute('homepage');
    }

    #[Route('/remove/{id}', name: 'basket_remove_product', methods: ['POST'])]
    public function removeProduct(Product $product): Response
    {
        $user = $this->getUser();
        $basket = $this->basketService->getOrCreateBasket($user);

        $this->basketService->removeProductFromBasket($basket, $product);
        $this->addFlash('success', 'Product removed from basket');

        return $this->redirectToRoute('basket_view');
    }

    #[Route('/edit/{id}', name: 'basket_edit_product', methods: ['POST'])]
    public function edit(Request $request, Product $product): Response
    {
        $newQuantity = $request->request->get('quantity');
        $user = $this->getUser();
        $basket = $this->basketService->getOrCreateBasket($user);

        try {
            $this->basketService->updateProductQuantity($basket, $product, $newQuantity);

            $this->addFlash('success', 'Product quantity edited from basket');
        } catch(\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

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

        $this->addFlash('success', 'Basket successfully cleared');

        return $this->redirectToRoute('basket_view', [
            'id' => $id,
        ]);
    }
    */
}
