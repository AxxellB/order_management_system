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

        $product11 = new Product();
        $product11->setName('Smartphone with 5G')
            ->setPrice('699.99')
            ->setDescription('5G-enabled smartphone with high-resolution camera.')
            ->setStockQuantity(60)
            ->addCategory($electronics);
        $manager->persist($product11);

        $product12 = new Product();
        $product12->setName('Gaming Console')
            ->setPrice('399.99')
            ->setDescription('Next-gen gaming console with ultra-fast loading times.')
            ->setStockQuantity(35)
            ->addCategory($electronics);
        $manager->persist($product12);

        $product13 = new Product();
        $product13->setName('Espresso Machine')
            ->setPrice('249.99')
            ->setDescription('Premium espresso machine for café-style coffee at home.')
            ->setStockQuantity(45)
            ->addCategory($homeGarden);
        $manager->persist($product13);

        $product14 = new Product();
        $product14->setName('Air Purifier')
            ->setPrice('129.99')
            ->setDescription('Air purifier with HEPA filter for a healthier home.')
            ->setStockQuantity(55)
            ->addCategory($homeGarden);
        $manager->persist($product14);

        $product15 = new Product();
        $product15->setName('Men\'s Winter Coat')
            ->setPrice('179.99')
            ->setDescription('Warm and stylish winter coat for men.')
            ->setStockQuantity(40)
            ->addCategory($fashion);
        $manager->persist($product15);

        $product16 = new Product();
        $product16->setName('Designer Sunglasses')
            ->setPrice('149.99')
            ->setDescription('Stylish sunglasses with UV protection.')
            ->setStockQuantity(70)
            ->addCategory($fashion);
        $manager->persist($product16);

        $product17 = new Product();
        $product17->setName('Yoga Mat')
            ->setPrice('24.99')
            ->setDescription('Non-slip yoga mat with extra cushioning.')
            ->setStockQuantity(150)
            ->addCategory($sportsOutdoors);
        $manager->persist($product17);

        $product18 = new Product();
        $product18->setName('Camping Tent')
            ->setPrice('129.99')
            ->setDescription('Water-resistant 4-person camping tent.')
            ->setStockQuantity(35)
            ->addCategory($sportsOutdoors);
        $manager->persist($product18);

        $product19 = new Product();
        $product19->setName('Hair Dryer')
            ->setPrice('59.99')
            ->setDescription('Professional hair dryer with multiple heat settings.')
            ->setStockQuantity(95)
            ->addCategory($beautyHealth);
        $manager->persist($product19);

        $product20 = new Product();
        $product20->setName('Anti-Aging Cream')
            ->setPrice('79.99')
            ->setDescription('Anti-aging cream for smoother, younger-looking skin.')
            ->setStockQuantity(85)
            ->addCategory($beautyHealth);
        $manager->persist($product20);


        $product21 = new Product();
        $product21->setName('Smartwatch')
            ->setPrice('199.99')
            ->setDescription('Smartwatch with fitness tracking and heart rate monitor.')
            ->setStockQuantity(80)
            ->addCategory($electronics);
        $manager->persist($product21);

        $product22 = new Product();
        $product22->setName('Air Fryer')
            ->setPrice('89.99')
            ->setDescription('Compact air fryer for quick and healthy meals.')
            ->setStockQuantity(90)
            ->addCategory($homeGarden);
        $manager->persist($product22);

        $product23 = new Product();
        $product23->setName('Leather Wallet')
            ->setPrice('49.99')
            ->setDescription('Slim leather wallet with multiple card slots.')
            ->setStockQuantity(120)
            ->addCategory($fashion);
        $manager->persist($product23);

        $product24 = new Product();
        $product24->setName('Cycling Helmet')
            ->setPrice('59.99')
            ->setDescription('Lightweight cycling helmet with enhanced ventilation.')
            ->setStockQuantity(60)
            ->addCategory($sportsOutdoors);
        $manager->persist($product24);

        $product25 = new Product();
        $product25->setName('Resistance Bands Set')
            ->setPrice('19.99')
            ->setDescription('Set of resistance bands for strength training.')
            ->setStockQuantity(150)
            ->addCategory($sportsOutdoors);
        $manager->persist($product25);

        $product26 = new Product();
        $product26->setName('Facial Roller')
            ->setPrice('14.99')
            ->setDescription('Jade facial roller for skin care and relaxation.')
            ->setStockQuantity(100)
            ->addCategory($beautyHealth);
        $manager->persist($product26);

        $product27 = new Product();
        $product27->setName('Electric Kettle')
            ->setPrice('39.99')
            ->setDescription('Electric kettle with rapid boiling feature.')
            ->setStockQuantity(85)
            ->addCategory($homeGarden);
        $manager->persist($product27);

        $product28 = new Product();
        $product28->setName('Waterproof Smart Speaker')
            ->setPrice('69.99')
            ->setDescription('Portable waterproof smart speaker with voice control.')
            ->setStockQuantity(75)
            ->addCategory($electronics);
        $manager->persist($product28);

        $product29 = new Product();
        $product29->setName('Noise-Cancelling Headphones')
            ->setPrice('149.99')
            ->setDescription('Over-ear headphones with active noise cancellation.')
            ->setStockQuantity(65)
            ->addCategory($electronics);
        $manager->persist($product29);

        $product30 = new Product();
        $product30->setName('Hiking Boots')
            ->setPrice('129.99')
            ->setDescription('Durable and comfortable hiking boots.')
            ->setStockQuantity(45)
            ->addCategory($sportsOutdoors);
        $manager->persist($product30);


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixture::class,
        ];
    }
}
