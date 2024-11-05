<?php

namespace App\Controller;

use App\Repository\DiscountCodeRepository;
use App\Service\DiscountCodeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DiscountCodeController extends AbstractController
{
    private DiscountCodeService $discountCodeService;
    private DiscountCodeRepository $discountCodeRepository;

    public function __construct(DiscountCodeService $discountCodeService, DiscountCodeRepository $discountCodeRepository)
    {
        $this->discountCodeService = $discountCodeService;
        $this->discountCodeRepository = $discountCodeRepository;
    }

    #[Route('/api/discount-codes', name: 'createDiscount', methods: ['POST'])]
    public function createDiscount(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['message' => 'Invalid JSON data.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $discountCodeResponse = $this->discountCodeService->generateCode($data);
            return new JsonResponse(['message' => $discountCodeResponse['message']], $discountCodeResponse['code']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/validate-discount-code', name: 'validateDiscount', methods: ['POST'])]
    public function validateDiscount(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if(!isset($data['discountCode'])) {
            return new JsonResponse(['message' => 'Please provide a discount code'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $discountCodeResponse = $this->discountCodeService->validateCode($data['discountCode']);
            if (is_array($discountCodeResponse)) {
                return new JsonResponse(['message' => $discountCodeResponse['message']], $discountCodeResponse['code']);
            }

            return new JsonResponse([
                'discountCode' => $discountCodeResponse->getCouponCode(),
                'percentOff' => $discountCodeResponse->getPercentOff(),
                'expirationDate' => $discountCodeResponse->getExpirationDate()->format('Y-m-d')
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while validating the discount code.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/discount-code/{id}', name: 'deleteDiscount', methods: ['DELETE'])]
    public function deleteDiscount(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $discountCode = $this->discountCodeRepository->find($id);

        if (!$discountCode) {
            return new JsonResponse(['message' => 'Discount code not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->discountCodeService->deleteCode($discountCode);
            return new JsonResponse(['message' => 'Discount code deleted successfully.'], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while creating the discount code.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/discount-code/{id}', name: 'editDiscount', methods: ['PUT'])]
    public function editDiscount(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $discountCode = $this->discountCodeRepository->find($id);

        if (!$discountCode) {
            return new JsonResponse(['message' => 'Discount code not found'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        try {
            $discountCodeResponse = $this->discountCodeService->editCode($discountCode, $data);
            return new JsonResponse(['message' => $discountCodeResponse['message']], $discountCodeResponse['code']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while creating the discount code.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
