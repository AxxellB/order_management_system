<?php
namespace App\Tests\Service;

use App\Entity\Address;
use App\Repository\AddressRepository;
use App\Repository\UserRepository;
use App\Service\AddressService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddressServiceTest extends KernelTestCase
{
    private AddressService $addressService;
    private AddressRepository $addressRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->addressService = self::getContainer()->get(AddressService::class);
        $this->addressRepository = self::getContainer()->get(AddressRepository::class);
    }

    public function testCreateAddress(): void
    {
        $user = $this->userRepository->findOneByEmail("admin@gmail.com");

        $data = [
            'line' => 'Test',
            'line2' => 'Test 2',
            'city' => 'City',
            'country' => 'BG',
            'postcode' => '4300',
        ];

        $result = $this->addressService->createAddress($user, $data);

        $this->assertInstanceOf(Address::class, $result);

        $this->assertEquals('Test', $result->getLine());
        $this->assertEquals('Test 2', $result->getLine2());
        $this->assertEquals('City', $result->getCity());
        $this->assertEquals('BG', $result->getCountry());
        $this->assertEquals('4300', $result->getPostcode());
    }

    public function testValidateAddressData(): void
    {
        $data = [
            'line' => 'Test',
            'line2' => '',
            'city' => '',
            'country' => '',
            'postcode' => '',
        ];

        $errors = $this->addressService->validateAddressData($data);

        $this->assertArrayHasKey('city', $errors);
        $this->assertEquals('City is required.', $errors['city']);

        $this->assertArrayHasKey('country', $errors);
        $this->assertEquals('Country is required.', $errors['country']);

        $this->assertArrayHasKey('postcode', $errors);
        $this->assertEquals('Postal code is required.', $errors['postcode']);

        // Optional field should not return an error
        $this->assertArrayNotHasKey('line2', $errors);
    }


    public function testEditAddress(): void
    {
        $user = $this->userRepository->findOneByEmail("admin@gmail.com");
        $address = $user->getAddresses()->first();

        $data = [
            'line' => 'Edited address',
            'line2' => '',
            'city' => 'Test edit',
            'country' => 'Test edit',
            'postcode' => 'test',
        ];

        $result = $this->addressService->updateAddress($data, $address);
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Edited address', $result->getLine());
        $this->assertEquals('', $result->getLine2());
        $this->assertEquals('Test edit', $result->getCity());
        $this->assertEquals('Test edit', $result->getCountry());
        $this->assertEquals('test', $result->getPostcode());
    }

    public function testDeleteAddress(): void
    {
        $user = $this->userRepository->findOneByEmail("admin@gmail.com");

        $data = [
            'line' => 'Test',
            'line2' => 'Test 2',
            'city' => 'City',
            'country' => 'BG',
            'postcode' => '4300',
        ];

        $address = $this->addressService->createAddress($user, $data);
        $this->assertInstanceOf(Address::class, $address);
        $addressId = $address->getId();
        $this->addressService->deleteAddress($address);

        $deletedAddress = $this->addressRepository->find($addressId);
        $this->assertNull($deletedAddress, "The address should be deleted and not found in the repository.");
    }
}
