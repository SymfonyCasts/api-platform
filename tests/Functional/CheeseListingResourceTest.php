<?php

namespace App\Tests\Functional;

use App\ApiPlatform\Test\ApiTestCase;
use App\Entity\User;

class CheeseListingResourceTest extends ApiTestCase
{
    public function testCreateCheeseListing()
    {
        $client = self::createClient();
        $client->request('POST', '/api/cheeses', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);
        $this->assertResponseStatusCodeSame(401);

        $user = new User();
        $user->setEmail('cheeseplease@example.com');
        $user->setUsername('cheeseplease');
        $user->setPassword('$argon2id$v=19$m=65536,t=6,p=1$AIC3IESQ64NgHfpVQZqviw$1c7M56xyiaQFBjlUBc7T0s53/PzZCjV56lbHnhOUXx8');

        $em = self::$container->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $client->request('POST', '/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'cheeseplease@example.com',
                'password' => 'foo'
            ],
        ]);
        $this->assertResponseStatusCodeSame(204);
    }
}
