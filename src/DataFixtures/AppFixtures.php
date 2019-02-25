<?php

namespace App\DataFixtures;

use App\Entity\CheeseListing;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('Cheese Seller');
        $manager->persist($user);

        $cheeseListing = new CheeseListing();
        $cheeseListing->setUser($user);
        $cheeseListing->setTitle('Cheese Title');
        $cheeseListing->setDescription('Cheese Description');
        $cheeseListing->setIsStinky(false);

        $manager->persist($cheeseListing);

        $manager->flush();
    }
}
