<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\FinancialService;
use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class FinancialController
{
    private FinancialService $financialService;
    private LookupService $lookupService;

    public function __construct(FinancialService $financialService, LookupService $lookupService)
    {
        $this->financialService = $financialService;
        $this->lookupService = $lookupService;
    }

    public function create(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['transaction_date', 'transaction_type', 'category', 'amount']);
        $v->rule('in', 'transaction_type', ['Alacak', 'Gider', 'Tahsilat']);
        $v->rule('numeric', 'amount');
        if (isset($data['tax_rate']) && $data['tax_rate'] !== '' && $data['tax_rate'] !== null) {
            $v->rule('numeric', 'tax_rate');
        }
        $v->rule('date', 'transaction_date');
        if (isset($data['due_date']) && $data['due_date'] !== '' && $data['due_date'] !== null) {
            $v->rule('date', 'due_date');
        }

        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Bekliyor', 'Vadesi Geldi', 'Kısmi Ödendi', 'Ödendi', 'Gecikti', 'İptal']);
        }
        if (isset($data['payment_method'])) {
            $v->rule('in', 'payment_method', $this->lookupService->getValuesForValidation('payment_methods'));
        }

        // sub_type validasyonu
        if (isset($data['sub_type']) && $data['sub_type'] !== '' && $data['sub_type'] !== null) {
            $v->rule('in', 'sub_type', ['ucret', 'emanet', 'masraf', 'genel']);
        }

        // client_id: Büro gideri (Gider + genel) hariç zorunlu
        $isBuroGideri = (
            isset($data['transaction_type']) && $data['transaction_type'] === 'Gider' &&
            isset($data['sub_type']) && $data['sub_type'] === 'genel'
        );
        if (!$isBuroGideri) {
            $v->rule('required', 'client_id');
            $v->rule('integer', 'client_id');
        }
        if (isset($data['case_id']) && $data['case_id'] !== '' && $data['case_id'] !== null) {
            $v->rule('integer', 'case_id');
        }

        // Cross-validasyon: Tahsilat→sadece ucret/emanet, Gider→sadece masraf/genel
        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Cross-validasyon (Valitron dışı)
        $crossErrors = $this->validateSubTypeCross($data);
        if (!empty($crossErrors)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $crossErrors
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $transactionId = $this->financialService->createTransaction($data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mali kayıt başarıyla oluşturuldu.',
                'data' => ['id' => $transactionId]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 20;

        $clientId = isset($params['client_id']) ? (int)$params['client_id'] : null;
        $caseId = isset($params['case_id']) ? (int)$params['case_id'] : null;
        $type = $params['type'] ?? null;
        $subType = !empty($params['sub_type']) ? $params['sub_type'] : null;
        $dateFrom = !empty($params['date_from']) ? $params['date_from'] : null;
        $dateTo = !empty($params['date_to']) ? $params['date_to'] : null;

        try {
            $result = $this->financialService->getTransactions($page, $limit, $clientId, $caseId, $type, $subType, $dateFrom, $dateTo);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Mali kayıtlar listelenirken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getBalance(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $clientId = isset($params['client_id']) ? (int)$params['client_id'] : null;
        $caseId = isset($params['case_id']) ? (int)$params['case_id'] : null;

        if (!$clientId && !$caseId) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Lütfen client_id veya case_id parametresi gönderin.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $balance = $this->financialService->getBalance((int)$clientId, $caseId);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $balance
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Bakiye hesaplanırken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $transactionId = (int)$args['id'];

        try {
            $transaction = $this->financialService->getTransactionById($transactionId);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $transaction
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $transactionId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['transaction_type'])) {
            $v->rule('in', 'transaction_type', ['Alacak', 'Gider', 'Tahsilat']);
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Bekliyor', 'Vadesi Geldi', 'Kısmi Ödendi', 'Ödendi', 'Gecikti', 'İptal']);
        }
        if (isset($data['payment_method'])) {
            $v->rule('in', 'payment_method', $this->lookupService->getValuesForValidation('payment_methods'));
        }
        if (isset($data['amount'])) {
            $v->rule('numeric', 'amount');
        }
        if (isset($data['tax_rate'])) {
            $v->rule('numeric', 'tax_rate');
        }
        if (isset($data['transaction_date'])) {
            $v->rule('date', 'transaction_date');
        }
        if (isset($data['due_date']) && $data['due_date'] !== '' && $data['due_date'] !== null) {
            $v->rule('date', 'due_date');
        }
        if (isset($data['client_id']) && $data['client_id'] !== '' && $data['client_id'] !== null) {
            $v->rule('integer', 'client_id');
        }
        if (isset($data['case_id']) && $data['case_id'] !== '' && $data['case_id'] !== null) {
            $v->rule('integer', 'case_id');
        }
        if (isset($data['sub_type']) && $data['sub_type'] !== '' && $data['sub_type'] !== null) {
            $v->rule('in', 'sub_type', ['ucret', 'emanet', 'masraf', 'genel']);
        }

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Cross-validasyon
        if (isset($data['transaction_type']) && isset($data['sub_type'])) {
            $crossErrors = $this->validateSubTypeCross($data);
            if (!empty($crossErrors)) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Doğrulama hatası',
                    'errors' => $crossErrors
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->financialService->updateTransaction($transactionId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mali kayıt başarıyla güncellendi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $transactionId = (int)$args['id'];

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->financialService->deleteTransaction($transactionId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mali kayıt başarıyla iptal/arşiv edildi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    /**
     * Cross-validasyon: Tahsilat→sadece ucret/emanet, Gider→sadece masraf/genel
     */
    private function validateSubTypeCross(array $data): array
    {
        $errors = [];
        $type = $data['transaction_type'] ?? null;
        $subType = $data['sub_type'] ?? null;

        if ($type && $subType) {
            if ($type === 'Tahsilat' && !in_array($subType, ['ucret', 'emanet'], true)) {
                $errors['sub_type'] = ['Tahsilat için alt tip yalnızca "ucret" veya "emanet" olabilir.'];
            }
            if ($type === 'Gider' && !in_array($subType, ['masraf', 'genel'], true)) {
                $errors['sub_type'] = ['Gider için alt tip yalnızca "masraf" veya "genel" olabilir.'];
            }
            if ($type === 'Alacak' && $subType !== null) {
                $errors['sub_type'] = ['Alacak için alt tip belirtilemez.'];
            }
        }

        return $errors;
    }
}
