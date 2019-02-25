<?php

namespace App\DataFixtures;

use App\Entity\CheeseListing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $cheeseListing = new CheeseListing();
        $cheeseListing->setTitle('Cheese Title');
        $cheeseListing->setDescription('Cheese Description');
        $cheeseListing->setIsStinky(false);

        $manager->persist($cheeseListing);

        $manager->flush();
    }
}
