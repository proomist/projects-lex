<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DocumentRepository;
use Exception;

class DocumentService
{
    private DocumentRepository $documentRepository;
    private string $uploadDir;
    private int $maxFileSize;
    private array $allowedExtensions;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;

        // Proje kökünü baz al (src/Service/ → 2 seviye yukarı = proje kökü)
        $projectRoot = dirname(__DIR__, 2);
        $envDir = $_ENV['UPLOAD_DIR'] ?? 'storage/uploads';

        // Eğer mutlak yol verilmişse direkt kullan, göreliyse proje köküne göre çözümle
        $this->uploadDir = str_starts_with($envDir, '/') ? $envDir : ($projectRoot . '/' . $envDir);

        $this->maxFileSize = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760); // 10MB default
        $allowedStr = $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,tif,tiff,udf';
        $this->allowedExtensions = array_map('trim', explode(',', $allowedStr));
    }

    public function uploadDocument(\Psr\Http\Message\UploadedFileInterface $file, array $meta, int $userId, string $ipAddress): int
    {
        // Validate file
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Dosya boyutu sunucu limitini aşıyor.',
                UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu form limitini aşıyor.',
                UPLOAD_ERR_PARTIAL => 'Dosya yalnızca kısmen yüklendi.',
                UPLOAD_ERR_NO_FILE => 'Dosya seçilmedi.',
            ];
            throw new Exception($errorMessages[$file->getError()] ?? 'Dosya yükleme hatası.', 400);
        }

        // Size check
        if ($file->getSize() > $this->maxFileSize) {
            $maxMB = round($this->maxFileSize / 1048576, 1);
            throw new Exception("Dosya boyutu maksimum {$maxMB} MB olabilir.", 400);
        }

        // Extension check
        $originalName = $file->getClientFilename();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception("Bu dosya türüne izin verilmiyor (.{$extension}). İzin verilen türler: " . implode(', ', $this->allowedExtensions), 400);
        }

        // MIME type check
        $mimeType = $file->getClientMediaType();
        $tmpPath = $file->getStream()->getMetadata('uri');
        if ($tmpPath && is_file($tmpPath) && is_readable($tmpPath) && strpos($tmpPath, 'php://') === false) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            if ($detectedMime = $finfo->file($tmpPath)) {
                $mimeType = $detectedMime;
            }
        }
        $allowedMimes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'udf' => 'application/zip', // UYAP dökümanları genelde zip formatında sıkıştırılmış XML'dir
        ];

        if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
            // Some edge cases: allow application/octet-stream for docx/xlsx/udf
            if (!in_array($mimeType, ['application/octet-stream', 'application/zip', 'application/vnd.uyap.document', 'text/xml', 'application/xml'])) {
                throw new Exception("Dosya içeriği uzantısıyla uyuşmuyor. Beklenen: {$allowedMimes[$extension]}, Gelen: $mimeType", 400);
            }
        }

        // Create upload directory
        $uploadPath = rtrim($this->uploadDir, '/');
        if (!is_dir($uploadPath)) {
            if (!@mkdir($uploadPath, 0755, true)) {
                throw new Exception('Yükleme dizini oluşturulamadı.', 500);
            }
        }

        // Generate UUID-based filename to prevent directory traversal
        $storedFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $uploadPath . '/' . $storedFilename;

        // Move file
        try {
            $file->moveTo($targetPath);
        } catch (\Throwable $e) {
            throw new Exception('Dosya kaydedilemedi.', 500);
        }

        // Store in database
        $docData = [
            'client_id' => !empty($meta['client_id']) ? (int)$meta['client_id'] : null,
            'case_id' => !empty($meta['case_id']) ? (int)$meta['case_id'] : null,
            'document_type' => $meta['document_type'],
            'title' => $meta['title'],
            'original_filename' => $originalName,
            'stored_filename' => $storedFilename,
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'uploaded_by' => $userId,
            'notes' => $meta['notes'] ?? null,
        ];

        return $this->documentRepository->create($docData);
    }

    public function getDocuments(?int $clientId, ?int $caseId, int $page, int $limit): array
    {
        return $this->documentRepository->findAll($clientId, $caseId, $page, $limit);
    }

    public function getDocumentById(int $id): array
    {
        $doc = $this->documentRepository->findById($id);
        if (!$doc) {
            throw new Exception('Evrak bulunamadı.', 404);
        }
        return $doc;
    }

    public function getDocumentFilePath(int $id): array
    {
        $doc = $this->getDocumentById($id);
        $uploadPath = rtrim($this->uploadDir, '/');
        $filePath = $uploadPath . '/' . $doc['stored_filename'];

        // Prevent directory traversal
        $realPath = realpath($filePath);
        $realUploadDir = realpath($uploadPath);
        if (!$realPath || !$realUploadDir || strpos($realPath, $realUploadDir) !== 0) {
            throw new Exception('Dosya bulunamadı veya erişim engellendi.', 404);
        }

        if (!file_exists($realPath)) {
            throw new Exception('Dosya fiziksel olarak bulunamadı.', 404);
        }

        return [
            'path' => $realPath,
            'original_filename' => $doc['original_filename'],
            'mime_type' => $doc['mime_type'],
            'file_size' => $doc['file_size'],
        ];
    }

    public function deleteDocument(int $id, int $userId, string $ipAddress): void
    {
        // Verify document exists
        $this->getDocumentById($id);
        $this->documentRepository->softDelete($id);
    }
}
