<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DocumentService;
use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class DocumentController
{
    private DocumentService $documentService;
    private LookupService $lookupService;

    public function __construct(DocumentService $documentService, LookupService $lookupService)
    {
        $this->documentService = $documentService;
        $this->lookupService = $lookupService;
    }

    public function upload(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $uploadedFiles = $request->getUploadedFiles();
        $data = (array)$request->getParsedBody();

        // Check file exists
        if (!isset($uploadedFiles['file'])) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Dosya seçilmedi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $file = $uploadedFiles['file'];

        // Required fields
        if (empty($data['title']) || empty($data['document_type'])) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Başlık ve evrak türü zorunludur.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validate document_type against lookup
        $validTypes = $this->lookupService->getValuesForValidation('document_types');
        if (!in_array($data['document_type'], $validTypes)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Geçersiz evrak türü.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';

            $docId = $this->documentService->uploadDocument($file, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Evrak başarıyla yüklendi.',
                'data' => ['id' => $docId]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();
            error_log('Document Upload Error: ' . (string)$e);

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

        try {
            $result = $this->documentService->getDocuments($clientId, $caseId, $page, $limit);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Evraklar listelenirken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function download(Request $request, Response $response, array $args): Response
    {
        $docId = (int)$args['id'];

        try {
            $fileInfo = $this->documentService->getDocumentFilePath($docId);

            $stream = fopen($fileInfo['path'], 'rb');
            $body = new \Slim\Psr7\Stream($stream);

            // Dosya adını sanitize et (header injection koruması)
            $safeFilename = preg_replace('/["\r\n\x00-\x1f]/', '_', $fileInfo['original_filename']);
            $encodedFilename = rawurlencode($fileInfo['original_filename']);

            return $response
                ->withHeader('Content-Type', $fileInfo['mime_type'])
                ->withHeader('Content-Disposition', "attachment; filename=\"{$safeFilename}\"; filename*=UTF-8''{$encodedFilename}")
                ->withHeader('Content-Length', (string)$fileInfo['file_size'])
                ->withBody($body);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $docId = (int)$args['id'];

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->documentService->deleteDocument($docId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Evrak başarıyla silindi.'
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
}
