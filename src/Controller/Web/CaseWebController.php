<?php

declare(strict_types=1);

namespace App\Controller\Web;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class CaseWebController
{
    private Twig $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'pages/cases.twig', [
            'page_title' => 'Dosyalar ve Davalar',
            'current_route' => 'cases'
        ]);
    }

    public function finance(Request $request, Response $response, array $args): Response
    {
        $caseId = (int)$args['id'];
        return $this->view->render($response, 'pages/case_finance.twig', [
            'page_title' => 'Dosya Mali Akış & Dağıtım',
            'current_route' => 'cases',
            'case_id' => $caseId
        ]);
    }
}
