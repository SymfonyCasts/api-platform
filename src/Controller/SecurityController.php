<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login", methods={"POST"})
     */
    public function login()
    {
        return $this->json([
            'user' => $this->getUser() ? $this->getUser()->getId() : null]
        );
    }
}
