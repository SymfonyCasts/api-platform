<?php

namespace App\Tests;

use App\Entity\CheeseListing;
use App\Entity\Message;
use App\Entity\User;
use PHPUnit\Runner\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class CheeseFunctionalTest extends WebTestCase
{
    public function testGetConversationOfUser()
    {
        self::bootKernel();
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = self::$container->get('doctrine')
            ->getRepository(User::class);
        $user = $userRepository->find('1');
        $this->assertNotNull($user);
        if ($user) {
            $client = self::createClient([]);
            $client->request('GET', '/api/users/'.$user->getId().'', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
            $response = $client->getResponse();
            $this->assertTrue($response->isSuccessful());
            $json = json_decode($response->getContent(), true);
            $conversations = $json['conversations'];
            $this->assertTrue(count($conversations) === 1, 'The user does not have 1 conversation');
            $conversationUrl = array_shift($conversations);
            $this->assertTrue($conversationUrl === '/api/conversations/1');
        }
    }

    public function testGetMessagesFromConversation()
    {
        $client = self::createClient([]);
        $client->request('GET', '/api/conversations/1', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);
        $messages = $json['messages'];
        $this->assertTrue(count($messages) === 3, 'The conversation does not have 3 messages');
        if (count($messages) === 3) {
            $messageContent = [];
            foreach ($json['messages'] as $message) {
                $client->request('GET', $message, [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
                $response = $client->getResponse();
                $this->assertTrue($response->isSuccessful());
                $json = json_decode($response->getContent(), true);
                $messageContent[] = $json['content'];
            }
            $this->assertTrue($messageContent[0] === 'Hi, I see you sell cheese, I want to buy some cheese! How much is it?');
            $this->assertTrue($messageContent[1] === 'Hi there, 50$ for 1 pound of cheese. My address is:...');
            $this->assertTrue($messageContent[2] === 'Wonderful, deal! ...');
        }
    }

    public function testPostMessage() {
        self::bootKernel();
        /** @var \App\Repository\MessageRepository $messageRepository */
        $messageRepository = self::$container->get('doctrine')
            ->getRepository(Message::class);
        $message = $messageRepository->find('4');
        $this->assertNull($message);

        $client = self::createClient([]);
        $data = [
            "sender" => "/api/users/1",
            "recipient" => "/api/users/2",
            "content" => "Test the posting a new message",
            "conversation" => "/api/conversations/1"
        ];
        $json_data = json_encode($data);
        $client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'],
            $json_data
        );
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $message = $messageRepository->find('4');
        $this->assertTrue($message->getContent() === 'Test the posting a new message');
    }

    public function testIsStinkyFilter()
    {
        $client = self::createClient([]);
        $client->request('GET', '/api/cheese_listings?isStinky=true', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);

        $this->assertTrue($json['hydra:totalItems'] === 1);
        $this->assertTrue($json['hydra:member'][0]['title'] === 'Stinky Cheese');
    }

    public function testCreatedAtFilter() {
        self::bootKernel();
        /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
        $entityManager = $userRepository = self::$container->get('doctrine')->getManager();
        // Update the createdAt data with a Doctrine query as we do no longer have the set method.
        $query = $entityManager->createQuery('
            UPDATE App\Entity\CheeseListing c
            SET c.createdAt = \'2000-01-01\'
            WHERE c.id = 2
         ');
        $query->execute();
        $client = self::createClient([]);
        $client->request('GET', '/api/cheese_listings?createdAt[before]=2005-03-19', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);

        $this->assertTrue($json['hydra:totalItems'] === 1);
        $this->assertTrue($json['hydra:member'][0]['id'] === 2);

        $client->request('GET', '/api/cheese_listings?createdAt[after]=3000-03-19', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);
        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $json = json_decode($response->getContent(), true);

        $this->assertTrue($json['hydra:totalItems'] === 0);
    }

    public function testCheeseTypePost() {
        $client = self::createClient([]);
        $data = [
            "category" => "stinky cheese",
        ];
        $json_data = json_encode($data);
        $client->request(
            'POST', '/api/cheese_types', [], [], [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json'
            ], $json_data
        );
        $this->assertTrue($client->getResponse()->getStatusCode() === 405);
    }
}
