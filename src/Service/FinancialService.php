<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\FinancialRepository;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Repository\CaseRepository;
use Exception;

class FinancialService
{
    private FinancialRepository $financialRepository;
    private UserRepository $userRepository;
    private ClientRepository $clientRepository;
    private CaseRepository $caseRepository;

    public function __construct(
        FinancialRepository $financialRepository,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        CaseRepository $caseRepository
    ) {
        $this->financialRepository = $financialRepository;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->caseRepository = $caseRepository;
    }

    public function createTransaction(array $data, int $userId, string $ipAddress): int
    {
        $type = $data['transaction_type'];
        $subType = $data['sub_type'] ?? null;

        // Sub-type varsayılanları ata
        if ($subType === null) {
            if ($type === 'Tahsilat') {
                $subType = 'ucret';
            } elseif ($type === 'Gider') {
                $subType = 'masraf';
            }
        }

        // Büro gideri (Gider + genel) ise: client_id ve case_id null, client doğrulama atla
        $isBuroGideri = ($type === 'Gider' && $subType === 'genel');

        if ($isBuroGideri) {
            // Büro gideri müvekkile bağlanmaz
            $clientId = null;
            $caseId = null;
        } else {
            // Müvekkil zorunlu
            if (empty($data['client_id'])) {
                throw new Exception("Müvekkil seçimi zorunludur.", 400);
            }
            $client = $this->clientRepository->findById((int)$data['client_id']);
            if (!$client) {
                throw new Exception("Müvekkil bulunamadı.", 404);
            }
            $clientId = (int)$client['id'];

            $caseId = null;
            if (!empty($data['case_id'])) {
                $case = $this->caseRepository->findById((int)$data['case_id']);
                if (!$case || (int)$case['client_id'] !== $clientId) {
                    throw new Exception("Dosya bulunamadı veya bu müvekkile ait değil.", 404);
                }
                $caseId = (int)$data['case_id'];
            }
        }

        // Tutar ve vergi hesaplamaları
        $amount = (float)$data['amount'];
        $taxRate = isset($data['tax_rate']) ? (float)$data['tax_rate'] : 0.00;
        $taxAmount = $amount * ($taxRate / 100);
        $totalAmount = $amount + $taxAmount;

        $status = null;
        if ($type === 'Alacak') {
            $status = $data['status'] ?? 'Bekliyor';
        }

        $insertData = [
            'transaction_date' => $data['transaction_date'],
            'transaction_type' => $type,
            'sub_type' => $subType,
            'category' => $data['category'],
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'TRY',
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'client_id' => $clientId,
            'case_id' => $caseId,
            'description' => $data['description'] ?? null,
            'document_no' => $data['document_no'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'bank_account_info' => $data['bank_account_info'] ?? null,
            'status' => $status
        ];

        $transactionId = $this->financialRepository->createTransaction($insertData);

        // FIFO eşleştirme SADECE Tahsilat + sub_type='ucret' için tetiklenir
        // Emanet tahsilat FIFO'ya GİRMEZ
        if ($type === 'Tahsilat' && $subType === 'ucret' && $clientId !== null) {
            if (!isset($data['manual_match']) || $data['manual_match'] === false) {
                $this->applyFifoMatching($clientId, $transactionId, $totalAmount);
            }
        }

        $this->userRepository->logActivity($userId, 'financial_transaction_created', 'Finance', $ipAddress);

        return $transactionId;
    }

    /**
     * Otomatik FIFO Eşleştirme (İlk Giren İlk Çıkar)
     * Sadece Tahsilat(ucret) → Alacak eşleştirmesi yapar.
     */
    private function applyFifoMatching(int $clientId, int $collectionId, float $remainingAmount): void
    {
        $unpaidReceivables = $this->financialRepository->getUnpaidReceivablesByClientId($clientId);

        foreach ($unpaidReceivables as $receivable) {
            if ($remainingAmount <= 0) break;

            $totalAmount = (float)$receivable['total_amount'];
            $totalPaid = (float)($receivable['total_paid'] ?? 0);
            $debtRemaining = $totalAmount - $totalPaid;

            if ($debtRemaining <= 0) continue;

            $matchAmount = min($remainingAmount, $debtRemaining);

            // Eşleşme kaydını yaz
            $this->financialRepository->addTransactionMatch((int)$receivable['id'], $collectionId, $matchAmount);

            // Alacak (Receivable) durumunu güncelle
            $newPaidTotal = $totalPaid + $matchAmount;
            $newStatus = ($newPaidTotal >= $totalAmount) ? 'Ödendi' : 'Kısmi Ödendi';
            $this->financialRepository->updateTransaction((int)$receivable['id'], ['status' => $newStatus]);

            $remainingAmount -= $matchAmount;
        }
    }

    public function getTransactionById(int $id): array
    {
        $transaction = $this->financialRepository->findById($id);
        if (!$transaction) {
            throw new Exception("Mali kayıt bulunamadı.", 404);
        }

        return $transaction;
    }

    public function updateTransaction(int $id, array $data, int $userId, string $ipAddress): void
    {
        $transaction = $this->financialRepository->findById($id);
        if (!$transaction) {
            throw new Exception("Mali kayıt bulunamadı.", 404);
        }

        $updateData = [];
        $fields = [
            'transaction_date', 'transaction_type', 'sub_type', 'category', 'amount', 'currency', 'tax_rate',
            'client_id', 'case_id',
            'description', 'document_no', 'due_date', 'payment_method',
            'bank_account_info', 'status'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // Çözümlenmiş tip ve sub_type
        $resolvedType = $updateData['transaction_type'] ?? $transaction['transaction_type'];
        $resolvedSubType = $updateData['sub_type'] ?? $transaction['sub_type'];
        $isBuroGideri = ($resolvedType === 'Gider' && $resolvedSubType === 'genel');

        if ($isBuroGideri) {
            // Büro gideri → client_id ve case_id null
            $updateData['client_id'] = null;
            $updateData['case_id'] = null;
        } else {
            // Client kontrolü
            $resolvedClientId = isset($updateData['client_id'])
                ? ($updateData['client_id'] !== null && $updateData['client_id'] !== '' ? (int)$updateData['client_id'] : null)
                : (isset($transaction['client_id']) ? (int)$transaction['client_id'] : null);

            if ($resolvedClientId === null) {
                throw new Exception('Büro gideri dışında müvekkil seçimi zorunludur.', 400);
            }

            if (isset($updateData['client_id'])) {
                $client = $this->clientRepository->findById($resolvedClientId);
                if (!$client) {
                    throw new Exception('Müvekkil bulunamadı.', 404);
                }
                $updateData['client_id'] = $resolvedClientId;
            }

            if (array_key_exists('case_id', $updateData)) {
                if ($updateData['case_id'] === '' || $updateData['case_id'] === null) {
                    $updateData['case_id'] = null;
                } else {
                    $caseId = (int)$updateData['case_id'];
                    $case = $this->caseRepository->findById($caseId);
                    if (!$case || (int)$case['client_id'] !== $resolvedClientId) {
                        throw new Exception('Dosya bulunamadı veya seçilen müvekkile ait değil.', 404);
                    }
                    $updateData['case_id'] = $caseId;
                }
            }
        }

        if (isset($updateData['transaction_type'])) {
            if ($updateData['transaction_type'] !== 'Alacak' && !array_key_exists('status', $data)) {
                $updateData['status'] = null;
            }

            if ($updateData['transaction_type'] === 'Alacak' && !array_key_exists('status', $data) && empty($transaction['status'])) {
                $updateData['status'] = 'Bekliyor';
            }
        }

        // Tutar değişirse tax ve total_amount yeniden hesaplanır
        if (isset($data['amount']) || isset($data['tax_rate'])) {
            $amount = isset($data['amount']) ? (float)$data['amount'] : (float)$transaction['amount'];
            $taxRate = isset($data['tax_rate']) ? (float)$data['tax_rate'] : (float)$transaction['tax_rate'];

            $taxAmount = $amount * ($taxRate / 100);
            $totalAmount = $amount + $taxAmount;

            $updateData['amount'] = $amount;
            $updateData['tax_rate'] = $taxRate;
            $updateData['tax_amount'] = $taxAmount;
            $updateData['total_amount'] = $totalAmount;
        }

        if (!empty($updateData)) {
            // FIFO geri alma: sadece sub_type='ucret' Tahsilat'lar için
            $wasFeeCollection = ($transaction['transaction_type'] === 'Tahsilat' && ($transaction['sub_type'] ?? null) === 'ucret');
            $isFeeCollection = ($resolvedType === 'Tahsilat' && $resolvedSubType === 'ucret');
            $amountChanged = isset($updateData['total_amount']) && (float)$updateData['total_amount'] !== (float)$transaction['total_amount'];

            // Eğer ücret tahsilatından başka tipe dönüştüyse veya tutar değiştiyse
            if ($wasFeeCollection && (!$isFeeCollection || $amountChanged)) {
                $this->reverseMatchesForCollection($id);
            }

            $this->financialRepository->updateTransaction($id, $updateData);

            // Yeni tip ücret tahsilatı ve tutar değiştiyse veya yeni ücret tahsilatına dönüştüyse FIFO yeniden uygula
            if ($isFeeCollection && ($amountChanged || !$wasFeeCollection)) {
                $newTotal = (float)($updateData['total_amount'] ?? $transaction['total_amount']);
                $clientIdForFifo = (int)($updateData['client_id'] ?? $transaction['client_id']);
                if ($clientIdForFifo > 0) {
                    $this->applyFifoMatching($clientIdForFifo, $id, $newTotal);
                }
            }

            $this->userRepository->logActivity($userId, 'financial_transaction_updated', 'Finance', $ipAddress);
        }
    }

    public function deleteTransaction(int $id, int $userId, string $ipAddress): void
    {
        $transaction = $this->financialRepository->findById($id);
        if (!$transaction) {
            throw new Exception("Mali kayıt bulunamadı.", 404);
        }

        // FIFO geri alma SADECE sub_type='ucret' Tahsilat'lar için
        if ($transaction['transaction_type'] === 'Tahsilat' && ($transaction['sub_type'] ?? null) === 'ucret') {
            $this->reverseMatchesForCollection($id);
        }

        $this->financialRepository->softDelete($id);
        $this->userRepository->logActivity($userId, 'financial_transaction_deleted', 'Finance', $ipAddress);
    }

    /**
     * Bir tahsilatın tüm eşleşmelerini geri alır ve ilgili alacakların durumunu yeniden hesaplar.
     */
    private function reverseMatchesForCollection(int $collectionId): void
    {
        $matches = $this->financialRepository->getMatchesByCollectionId($collectionId);
        $affectedReceivableIds = array_unique(array_column($matches, 'receivable_id'));

        $this->financialRepository->deleteMatchesByCollectionId($collectionId);

        foreach ($affectedReceivableIds as $receivableId) {
            $this->financialRepository->recalculateReceivableStatus((int)$receivableId);
        }
    }

    public function getBalance(int $clientId, ?int $caseId = null): array
    {
        if ($caseId !== null) {
            return $this->financialRepository->getCaseBalance($caseId);
        }

        return $this->financialRepository->getClientBalance($clientId);
    }

    public function getOfficeSummary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->financialRepository->getOfficeSummary($dateFrom, $dateTo);
    }

    public function getTransactions(int $page = 1, int $limit = 20, ?int $clientId = null, ?int $caseId = null, ?string $type = null, ?string $subType = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $offset = ($page - 1) * $limit;

        $transactions = $this->financialRepository->findAll($limit, $offset, $clientId, $caseId, $type, $subType, $dateFrom, $dateTo);
        $total = $this->financialRepository->countAll($clientId, $caseId, $type, $subType, $dateFrom, $dateTo);

        return [
            'data' => $transactions,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    public function getClientStatement(int $clientId, ?string $dateFrom, ?string $dateTo): array
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new Exception("Müvekkil bulunamadı.", 404);
        }

        // --- İletişim ve Hassas Verileri Çözme İşlemi Başlangıcı ---
        $contactsRaw = $this->clientRepository->getContactsByClientId($clientId);
        foreach ($contactsRaw as $contact) {
            $val = \App\Helper\CryptoHelper::decrypt($contact['contact_value_encrypted']);
            if ($contact['contact_type'] === 'Telefon' && empty($client['phone'])) {
                $client['phone'] = $val;
            }
            if ($contact['contact_type'] === 'E-posta' && empty($client['email'])) {
                $client['email'] = $val;
            }
            if ($contact['contact_type'] === 'Adres') {
                if ($contact['sub_type'] === 'Şehir') $client['city'] = $val;
                elseif ($contact['sub_type'] === 'İlçe') $client['district'] = $val;
                else $client['address'] = $val;
            }
        }
        
        if (!empty($client['national_id_encrypted'])) {
            $client['tc_no'] = \App\Helper\CryptoHelper::decrypt($client['national_id_encrypted']);
        }
        if (!empty($client['tax_number_encrypted'])) {
            $client['tax_no'] = \App\Helper\CryptoHelper::decrypt($client['tax_number_encrypted']);
        }
        // --- İletişim Çözme Sonu ---

        $initialBalances = ['net_balance' => 0, 'fee_debt' => 0, 'trust_balance' => 0, 'total_receivable' => 0, 'total_fee_collection' => 0, 'total_trust_deposit' => 0, 'total_client_expense' => 0];
        if ($dateFrom) {
            $initialBalances = $this->financialRepository->getCarriedForwardBalance($clientId, $dateFrom);
        }

        $transactions = $this->financialRepository->getStatementTransactions($clientId, $dateFrom, $dateTo);

        $runningBalance = $initialBalances['net_balance'];
        
        foreach ($transactions as &$t) {
            $amount = (float)$t['total_amount'];
            $type = $t['transaction_type'];
            $subType = $t['sub_type'];
            
            if ($type === 'Alacak') {
                $runningBalance += $amount;
            } elseif ($type === 'Tahsilat') {
                $runningBalance -= $amount;
            } elseif ($type === 'Gider' && $subType === 'masraf') {
                $runningBalance += $amount;
            }
            
            $t['running_balance'] = $runningBalance;
        }

        return [
            'client' => $client,
            'period' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'balances' => [
                'initial' => $initialBalances['net_balance'],
                'final' => $runningBalance,
                'carried_details' => $initialBalances
            ],
            'transactions' => $transactions
        ];
    }

}
