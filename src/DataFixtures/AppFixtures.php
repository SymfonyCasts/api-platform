<?php

namespace App\DataFixtures;

use App\Entity\CheeseListing;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('cheesefan@example.com');
        $user->setUsername('cheesefan');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'cheese'));
        $manager->persist($user);

        $listing1 = new CheeseListing('Mysterious munster');
        $listing1->setDescription('Origin date: unknown. Actual origin... also unknown.');
        $listing1->setPrice(1500);
        $listing1->setOwner($user);
        $listing1->setIsPublished(true);
        $manager->persist($listing1);

        $listing2 = new CheeseListing('Block of cheddar the size of your face!');
        $listing2->setDescription('When I drive it to your house, it will sit in the passenger seat of my car.');
        $listing2->setPrice(5000);
        $listing2->setOwner($user);
        $listing2->setIsPublished(true);
        $manager->persist($listing2);

        $manager->flush();
    }
}
