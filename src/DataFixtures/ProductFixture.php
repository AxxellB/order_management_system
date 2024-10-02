<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $electronics = $this->getReference('category-electronics');
        $homeGarden = $this->getReference('category-home-garden');
        $fashion = $this->getReference('category-fashion');
        $sportsOutdoors = $this->getReference('category-sports-outdoors');
        $beautyHealth = $this->getReference('category-beauty-health');


        $product1 = new Product();
        $product1->setName('4K Ultra HD Smart TV')
            ->setPrice('599.99')
            ->setDescription('A 55-inch 4K Ultra HD Smart TV with HDR support.')
            ->setStockQuantity(50)
            ->addCategory($electronics);
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setName('Wireless Bluetooth Earbuds')
            ->setPrice('99.99')
            ->setDescription('True wireless earbuds with noise cancellation.')
            ->setStockQuantity(150)
            ->addCategory($electronics);
        $manager->persist($product2);

        $product3 = new Product();
        $product3->setName('Cordless Vacuum Cleaner')
            ->setPrice('199.99')
            ->setDescription('Lightweight cordless vacuum cleaner with powerful suction.')
            ->setStockQuantity(75)
            ->addCategory($homeGarden);
        $manager->persist($product3);

        $product4 = new Product();
        $product4->setName('Indoor Plant Set')
            ->setPrice('29.99')
            ->setDescription('Set of 3 indoor plants to brighten your home.')
            ->setStockQuantity(200)
            ->addCategory($homeGarden);
        $manager->persist($product4);

        $product5 = new Product();
        $product5->setName('Leather Handbag')
            ->setPrice('249.99')
            ->setDescription('Stylish leather handbag with multiple compartments.')
            ->setStockQuantity(30)
            ->addCategory($fashion);
        $manager->persist($product5);

        $product6 = new Product();
        $product6->setName('Men\'s Running Shoes')
            ->setPrice('79.99')
            ->setDescription('Lightweight and durable running shoes for men.')
            ->setStockQuantity(100)
            ->addCategory($fashion);
        $manager->persist($product6);

        $product7 = new Product();
        $product7->setName('Mountain Bike')
            ->setPrice('499.99')
            ->setDescription('26-inch mountain bike with dual suspension.')
            ->setStockQuantity(20)
            ->addCategory($sportsOutdoors);
        $manager->persist($product7);

        $product8 = new Product();
        $product8->setName('Trekking Backpack')
            ->setPrice('89.99')
            ->setDescription('50L water-resistant trekking backpack with multiple pockets.')
            ->setStockQuantity(120)
            ->addCategory($sportsOutdoors);
        $manager->persist($product8);

        $product9 = new Product();
        $product9->setName('Organic Skincare Set')
            ->setPrice('59.99')
            ->setDescription('A set of organic skincare products for healthy glowing skin.')
            ->setStockQuantity(80)
            ->addCategory($beautyHealth);
        $manager->persist($product9);

        $product10 = new Product();
        $product10->setName('Electric Toothbrush')
            ->setPrice('49.99')
            ->setDescription('Rechargeable electric toothbrush with multiple brushing modes.')
            ->setStockQuantity(100)
            ->addCategory($beautyHealth);
        $manager->persist($product10);

        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [
            CategoryFixture::class,
        ];
    }
}
