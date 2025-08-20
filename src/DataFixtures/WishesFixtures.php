<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WishesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('en_US');

        $categories = [];
        $categoryData = ['Travel & Adventure','Sport', 'Entertainment', 'Human Relations', 'Other'];

        foreach ($categoryData as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        for ($i = 0; $i < 100; $i++) {
            $wish = new Wish();
            $wish->setTitle($faker->realText(15))
                ->setDescription($faker->realText(150))
                ->setAuthor($faker->name())
                ->setCategory($faker->randomElement($categories))
                ->setDateCreated($faker->dateTimeBetween('-3 months', 'now'))
                ->setDateUpdated($faker->optional()->dateTimeBetween('-3 months', 'now'))
                ->setIsPublished($faker->boolean());

            $manager->persist($wish);
        }
        $manager->flush();
    }
}
