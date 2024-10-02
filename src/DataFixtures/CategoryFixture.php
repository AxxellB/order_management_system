<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $electronics = new Category();
        $electronics->setName('Electronics');
        $manager->persist($electronics);
        $this->addReference('category-electronics', $electronics);

        $homeGarden = new Category();
        $homeGarden->setName('Home & Garden');
        $manager->persist($homeGarden);
        $this->addReference('category-home-garden', $homeGarden);

        $fashion = new Category();
        $fashion->setName('Fashion & Accessories');
        $manager->persist($fashion);
        $this->addReference('category-fashion', $fashion);

        $sportsOutdoors = new Category();
        $sportsOutdoors->setName('Sports & Outdoors');
        $manager->persist($sportsOutdoors);
        $this->addReference('category-sports-outdoors', $sportsOutdoors);

        $beautyHealth = new Category();
        $beautyHealth->setName('Beauty & Health');
        $manager->persist($beautyHealth);
        $this->addReference('category-beauty-health', $beautyHealth);

        $manager->flush();
    }
}
