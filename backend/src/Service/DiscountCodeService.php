<?php

namespace App\Service;

use App\Entity\DiscountCode;
use App\Repository\DiscountCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DiscountCodeService
{
    private EntityManagerInterface $em;
    private DiscountCodeRepository $discountCodeRepository;

    public function __construct(EntityManagerInterface $em, DiscountCodeRepository $discountCodeRepository)
    {
        $this->em = $em;
        $this->discountCodeRepository = $discountCodeRepository;
    }

    public function generateCode($data)
    {
        if (is_array($data) && isset($data['discountCode'], $data['percentOff'], $data['expirationDate'])) {

            $discountCode = $this->discountCodeRepository->findOneBy(['couponCode' => $data['discountCode']]);
            if($discountCode) {
                return ['message' => 'Discount code already exists', 'code' => Response::HTTP_CONFLICT];
            }

            try {
                $expirationDate = new \DateTimeImmutable($data['expirationDate']);
            } catch (\Exception $e) {
                return ['message' => 'Invalid date format for expiration date', 'code' => Response::HTTP_BAD_REQUEST];
            }

            $newCode = new DiscountCode();
            $newCode->setCouponCode($data['discountCode']);
            $newCode->setPercentOff((float)$data['percentOff']);
            $newCode->setExpirationDate($expirationDate);

            $this->em->persist($newCode);
            $this->em->flush();

            return ['message' => 'Code generated successfully', 'code' => Response::HTTP_OK];
        } else {
            return ['message' => 'Please fill in all fields', 'code' => Response::HTTP_BAD_REQUEST];
        }
    }

    public function validateCode(string $discountCode) : array
    {
        $code = $this->em->getRepository(DiscountCode::class)->findOneBy(['couponCode' => $discountCode]);

        if(!$code){
            return ['message' => 'Invalid code', 'code' => Response::HTTP_BAD_REQUEST];
        }

        $currentDate = new \DateTime();

        if ($currentDate > $code->getExpirationDate()) {
            return ['message' => 'Expired code', 'code' => Response::HTTP_GONE];
        }

        return ['message' => 'Valid code', 'code' => Response::HTTP_OK];
    }

    public function deleteCode(DiscountCode $code)
    {
        $this->em->remove($code);
        $this->em->flush();
    }

    public function editCode(DiscountCode $code, $data)
    {
        if (isset($data['discountCode'], $data['percentOff'], $data['expirationDate'])) {
            try {
                $expirationDate = new \DateTimeImmutable($data['expirationDate']);
            } catch (\Exception $e) {
                return ['message' => 'Invalid date format for expiration date', 'code' => Response::HTTP_BAD_REQUEST];
            }

            $code->setCouponCode($data['discountCode']);
            $code->setPercentOff((float)$data['percentOff']);
            $code->setExpirationDate($expirationDate);
            $this->em->persist($code);
            $this->em->flush();
            return ['message' => 'Code updated successfully', 'code' => Response::HTTP_OK];
        }
        else {
            return ['message' => 'Please fill in all fields', 'code' => Response::HTTP_BAD_REQUEST];
        }
    }
}