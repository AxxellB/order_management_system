<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/homepage')]
class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findRandomCategories(2);

        foreach ($categories as $category) {
            $categoryProducts = $category->getProducts()->toArray();
            shuffle($categoryProducts);
            $categoryProducts = array_slice($categoryProducts, 0, 5);
            $category->getProducts(new ArrayCollection($categoryProducts));
        }

        return $this->render('homepage.html.twig', [
            'categories' => $categories,
        ]);
    }
}
