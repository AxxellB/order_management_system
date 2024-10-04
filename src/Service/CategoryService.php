<?php

namespace App\Service;

use App\Repository\CategoryRepository;

class CategoryService
{


    private CategoryRepository $categoryRepository;


    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    public function getAll(): array
    {
        return $this->categoryRepository->fondAllNonDeletedCategories();
    }
}