<?php

namespace App\DataFixtures;

use App\Entity\CheeseListing;
use App\Entity\CheeseType;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $seller = new User();
        $seller->setUsername('stinky_cheese_hater');
        $manager->persist($seller);
        $buyer = new User();
        $buyer->setUsername('david');
        $manager->persist($buyer);
        $cheeseType1 = new CheeseType();
        $cheeseType1->setCategory('Firm');
        $manager->persist($cheeseType1);
        $cheeseType2 = new CheeseType();
        $cheeseType2->setCategory('Fresh');
        $manager->persist($cheeseType2);
        $cheeseType3 = new CheeseType();
        $cheeseType3->setCategory('Blue');
        $manager->persist($cheeseType3);
        $cheeseType4 = new CheeseType();
        $cheeseType4->setCategory('Soft');
        $manager->persist($cheeseType4);
        $cheeseListing = new CheeseListing();
        $cheeseListing->setUser($seller);
        $cheeseListing->setTitle('Gouda Cheese');
        $cheeseListing->setDescription('Imported from Holland');
        $cheeseListing->setIsStinky(false);
        $cheeseListing->setCheeseType($cheeseType1);
        $manager->persist($cheeseListing);
        $cheeseListing2 = new CheeseListing();
        $cheeseListing2->setUser($seller);
        $cheeseListing2->setTitle('Stinky Cheese');
        $cheeseListing2->setDescription('Bah');
        $cheeseListing2->setIsStinky(true);
        $cheeseListing2->setCheeseType($cheeseType3);
        $manager->persist($cheeseListing2);
        $manager->flush();
    }
}
