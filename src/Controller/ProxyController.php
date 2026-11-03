<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'PROXY_ORIGIN')] private string $origin,
    ) {}

    #[Route('/{path}', name: 'proxy', requirements: ['path' => '.*'], defaults: ['path' => ''])]
    public function __invoke(Request $request): Response
    {

    }
}
