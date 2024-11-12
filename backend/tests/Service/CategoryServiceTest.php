<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryServiceTest extends TestCase
{
    private $categoryRepository;
    private $validator;
    private CategoryService $categoryService;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->categoryService = new CategoryService($this->categoryRepository);
    }

    public function testGetAllNonDeleted(): void
    {
        $expectedCategories = [new Category(), new Category()];
        $this->categoryRepository->method('findAllNonDeleted')->willReturn($expectedCategories);

        $result = $this->categoryService->getAllNonDeleted();

        $this->assertSame($expectedCategories, $result);
    }

    public function testGetAllPaginated(): void
    {
        $page = 2;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $expectedData = [
            'categories' => [new Category()],
            'totalCount' => 15
        ];

        $this->categoryRepository
            ->expects($this->once())
            ->method('findAllNonDeletedCategoriesWithPagination')
            ->with($offset, $limit)
            ->willReturn($expectedData);

        $result = $this->categoryService->getAllPaginated($page, $limit);

        $this->assertSame($expectedData['categories'], $result['data']);
        $this->assertEquals(2, $result['totalPages']);
    }

    public function testGetCategoryById(): void
    {
        $categoryId = 1;
        $expectedCategory = new Category();

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($expectedCategory);

        $result = $this->categoryService->getCategoryById($categoryId);

        $this->assertSame($expectedCategory, $result);
    }

    public function testGetCategoryByIdThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Category not found');

        $this->categoryRepository
            ->method('findById')
            ->willReturn(null);

        $this->categoryService->getCategoryById(1);
    }

    public function testCreateCategoryWithValidData(): void
    {
        $data = ['name' => 'Valid Category'];
        $category = new Category();
        $category->setName($data['name']);

        $this->validator
            ->method('validate')
            ->with($category)
            ->willReturn(new ConstraintViolationList());

        $result = $this->categoryService->createCategory($data, $this->validator);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertSame($data['name'], $result->getName());
    }

    public function testCreateCategoryWithInvalidData(): void
    {
        $data = ['name' => ''];
        $category = new Category();
        $category->setName($data['name']);

        $violation = new ConstraintViolation('Name should not be blank', '', [], '', 'name', '');
        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->method('validate')
            ->with($category)
            ->willReturn($violations);

        $result = $this->categoryService->createCategory($data, $this->validator);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertEquals('Name should not be blank', $result['errors']['name']);
    }

    public function testDeleteCategoryWithProducts(): void
    {
        $category = new Category();
        $category->setProducts(new ArrayCollection([new \stdClass()]));

        $result = $this->categoryService->deleteCategory($category);

        $this->assertIsArray($result);
        $this->assertSame('error', $result['status']);
        $this->assertSame('The category contains products and cannot be deleted.', $result['message']);
    }

    public function testDeleteCategoryWithoutProducts(): void
    {
        $category = new Category();
        $category->setProducts(new ArrayCollection());

        $result = $this->categoryService->deleteCategory($category);

        $this->assertIsArray($result);
        $this->assertSame('success', $result['status']);
        $this->assertSame('Category deleted successfully.', $result['message']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $category->getDeletedAt());
    }

    public function testGetFormErrors(): void
    {
        $form = $this->createMock(FormInterface::class);

        $error1 = new FormError('This value should not be blank.');
        $error1Origin = $this->createMock(FormInterface::class);
        $error1Origin->method('getName')->willReturn('name');
        $error1->setOrigin($error1Origin);

        $error2 = new FormError('This value is too short.');
        $error2Origin = $this->createMock(FormInterface::class);
        $error2Origin->method('getName')->willReturn('description');
        $error2->setOrigin($error2Origin);

        $form->method('getErrors')->with(true)->willReturn([$error1, $error2]);

        $result = $this->categoryService->getFormErrors($form);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertContains('This value should not be blank.', $result['name']);
        $this->assertContains('This value is too short.', $result['description']);
    }
}
