<?php

declare(strict_types=1);

namespace App\Controller\Web;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class TaskWebController
{
    private Twig $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'pages/tasks.twig', [
            'page_title' => 'İş Listesi',
            'current_route' => 'tasks'
        ]);
    }
}
