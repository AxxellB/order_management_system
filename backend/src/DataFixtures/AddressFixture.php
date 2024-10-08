<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AddressFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $address = new Address();
        $address->setLine('Meden Dol 2');
        $address->setCity('Karlovo');
        $address->setCountry('Bulgaria');
        $address->setPostcode('4300');
        $address->setUser($this->getReference('user'));
        $manager->persist($address);
        $this->addReference('address1', $address);

        $address2 = new Address();
        $address2->setLine('Baker Street 15');
        $address2->setCity('London');
        $address2->setCountry('UK');
        $address2->setPostcode('1ZY 4UI');
        $address2->setUser($this->getReference('user2'));
        $manager->persist($address2);
        $this->addReference('address2', $address2);

        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
