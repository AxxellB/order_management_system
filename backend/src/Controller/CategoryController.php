<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories')]
final class CategoryController extends AbstractController
{

    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }


    #[Route('/', name: 'categories_list', methods: ['GET'])]
    public function listCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $this->categoryService->getAll();

        return $this->render('category/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'category_products', methods: ['GET'])]
    public function listProductsByCategory(int $id, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $products = $category->getProducts();

        return $this->render('category/category_product.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }


    #[Route('/view',name: 'category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $this->categoryService->getAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {

            if ($category->getProducts()->isEmpty()) {
                $category->setDeletedAt(new \DateTimeImmutable());
                $entityManager->flush();

                $this->addFlash('success', 'Category has been deleted.');
            } else {
                $this->addFlash('error', 'The category contains a product and cannot be deleted.');
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
    }

}
