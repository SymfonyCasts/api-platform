<?php

namespace App\DataFixtures;

use App\Entity\CheeseListing;
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

        $cheeseListing = new CheeseListing();
        $cheeseListing->setUser($seller);
        $cheeseListing->setTitle('Cheese Title');
        $cheeseListing->setDescription('Cheese Description');
        $cheeseListing->setIsStinky(false);
        $manager->persist($cheeseListing);

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
