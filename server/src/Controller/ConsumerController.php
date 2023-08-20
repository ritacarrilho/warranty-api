<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConsumerController extends AbstractController
{
    #[Route('/consumer', name: 'app_consumer')]
    public function index(): Response
    {
        return $this->render('consumer/index.html.twig', [
            'controller_name' => 'ConsumerController',
        ]);
    }
}
