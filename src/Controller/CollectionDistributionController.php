<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CollectionDistributionService;
use App\Repository\FeeAgreementRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class CollectionDistributionController
{
    private CollectionDistributionService $service;
    private FeeAgreementRepository $feeAgreementRepo;
    private \App\Repository\CollectionDistributionRepository $collectionRepo;
    private \App\Repository\CaseExpenseRepository $expenseRepo;

    public function __construct(
        CollectionDistributionService $service,
        FeeAgreementRepository $feeAgreementRepo,
        \App\Repository\CollectionDistributionRepository $collectionRepo,
        \App\Repository\CaseExpenseRepository $expenseRepo
    ) {
        $this->service = $service;
        $this->feeAgreementRepo = $feeAgreementRepo;
        $this->collectionRepo = $collectionRepo;
        $this->expenseRepo = $expenseRepo;
    }

    public function getDetails(Request $request, Response $response, array $args): Response
    {
        $caseId = (int)$args['case_id'];

        $collections = $this->collectionRepo->getCollectionsByCaseId($caseId);
        $distributions = $this->collectionRepo->getDistributionsByCaseId($caseId);
        $expenses = $this->expenseRepo->getByCaseId($caseId);
        $agreement = $this->feeAgreementRepo->findByCaseId($caseId);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => [
                'collections' => $collections,
                'distributions' => $distributions,
                'expenses' => $expenses,
                'agreement' => $agreement
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function createCollection(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        
        $v = new Validator($data);
        $v->rule('required', ['case_id', 'source', 'gross_amount', 'net_amount', 'collection_date']);
        $v->rule('numeric', ['gross_amount', 'deductions', 'net_amount']);

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Eksik veya hatalı bilgi.',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $id = $this->service->createCollection($data);
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Tahsilat başarıyla havuza alındı.',
                'data' => ['id' => $id]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Tahsilat eklenirken hata: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function createDistribution(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        
        $v = new Validator($data);
        $v->rule('required', ['collection_id', 'distribution_date', 'client_id', 'case_id']);
        $v->rule('numeric', ['opposing_attorney_fee', 'expense_refund', 'client_attorney_fee', 'client_net_payment']);

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $clientId = (int)$data['client_id'];
            $caseId = (int)$data['case_id'];

            $id = $this->service->createDistribution($data, $clientId, $caseId);
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Tahsilat başarıyla dağıtıldı ve hesaba işlendi.',
                'data' => ['id' => $id]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Dağıtım yapılırken hata: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function saveFeeAgreement(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        
        try {
            // Update exist or Create new
            if (!empty($data['id'])) {
                $this->feeAgreementRepo->update((int)$data['id'], $data);
            } else {
                $this->feeAgreementRepo->create($data);
            }

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Sözleşme bilgisi kaydedildi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Kaydedilirken hata oluştu: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function deleteCollection(Request $request, Response $response, array $args): Response
    {
        $collectionId = (int)$args['id'];
        
        try {
            $deleted = $this->service->deleteCollection($collectionId);
            if ($deleted) {
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Tahsilat havuzdan başarıyla silindi.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Tahsilat bulunamadı veya dağıtımı yapıldığı için silinemez.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Silinirken hata oluştu: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
