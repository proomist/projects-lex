<?php

declare(strict_types=1);

namespace App\Controller\Web;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardWebController
{
    private Twig $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        // Token kontrolü yapılacak (İlerleyen aşamalarda Cookie üzerinden JWT okuma)
        // Şimdilik sadece render ediyoruz
        return $this->view->render($response, 'pages/dashboard.twig', [
            'page_title' => 'Dashboard'
        ]);
    }
}
