<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductController extends AbstractController
{

    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/',name: 'product_index', methods: ['GET'])]
    public function getProducts(Request $request): Response
    {
        $status = $request->query->get('status', 'active');

        if($status === 'deleted'){
            $products = $this->productService->getAllDeleted();
        } else{
            $products = $this->productService->getAllNonDeleted();

        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'status' => $status,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'product_by_id', methods: ['GET'])]
    public function GetProductById(int $id): Response
    {
        $productById = $this->productService->getProductById($id);

        $isDeleted = ($productById->getDeletedAt() !== null);

        return $this->render('product/show.html.twig', [
            'product' => $productById,
            'isDeleted' => $isDeleted,
        ]);
    }

    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'product_delete_restore', methods: ['POST'])]
    public function deleteOrRestore(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {

            $product->setDeletedAt(new \DateTimeImmutable());

            $entityManager->flush();
        }
        else if ($this->isCsrfTokenValid('restore'.$product->getId(), $request->getPayload()->getString('_token'))) {

            $product->setDeletedAt(null);

            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }
}
