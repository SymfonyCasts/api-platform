<?php

namespace App\DataFixtures;

use App\Entity\CheeseListing;
use App\Entity\CheeseType;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $seller = new User();
        $seller->setUsername('Cheese Seller');
        $manager->persist($seller);

        $buyer = new User();
        $buyer->setUsername('Cheese Buyer');
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
        $cheeseListing->setTitle('Cheese Title');
        $cheeseListing->setDescription('Cheese Description');
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

        $conversation = new Conversation();
        $conversation->setCheeseListing($cheeseListing);
        $conversation->addUser($seller);
        $conversation->addUser($buyer);
        $manager->persist($conversation);

        $message1 = new Message();
        $message1->setConversation($conversation);
        $message1->setSender($buyer);
        $message1->setRecipient($seller);
        $message1->setContent('Hi, I see you sell cheese, I want to buy some cheese! How much is it?');
        $manager->persist($message1);
        $message2 = new Message();
        $message2->setConversation($conversation);
        $message2->setSender($seller);
        $message2->setRecipient($buyer);
        $message2->setContent('Hi there, 50$ for 1 pound of cheese. My address is:...');
        $manager->persist($message2);
        $message3 = new Message();
        $message3->setConversation($conversation);
        $message3->setSender($buyer);
        $message3->setRecipient($seller);
        $message3->setContent('Wonderful, deal! ...');
        $manager->persist($message3);

        $manager->flush();
    }
}
