<?php

declare(strict_types=1);

namespace App\Controller\Web;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class SystemDefinitionWebController
{
    private Twig $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'pages/system-definitions.twig', [
            'page_title'    => 'Sistem Tanımları',
            'current_route' => 'system-definitions',
        ]);
    }
}
