<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CollectionDistributionRepository;
use App\Repository\FinancialRepository;
use App\Repository\CaseRepository;
use Exception;

class CollectionDistributionService
{
    private CollectionDistributionRepository $collectionRepo;
    private FinancialRepository $financialRepo;
    private CaseRepository $caseRepo;

    public function __construct(
        CollectionDistributionRepository $collectionRepo,
        FinancialRepository $financialRepo,
        CaseRepository $caseRepo
    ) {
        $this->collectionRepo = $collectionRepo;
        $this->financialRepo = $financialRepo;
        $this->caseRepo = $caseRepo;
    }

    public function createCollection(array $data): int
    {
        // Sadece koleksiyonu havuza alır. Dağıtım yapılana kadar financial_transactions'a işlemez.
        return $this->collectionRepo->createCollection($data);
    }

    public function createDistribution(array $data, int $clientId, int $caseId): int
    {
        $distributionId = $this->collectionRepo->createDistribution($data);

        // Sistemin bakiye ve kâr-zarar mantığını bozmamak için financial_transactions'a yansıt
        $date = $data['distribution_date'];
        
        $opposingFee = (float)($data['opposing_attorney_fee'] ?? 0);
        $clientFee = (float)($data['client_attorney_fee'] ?? 0);
        $clientNet = (float)($data['client_net_payment'] ?? 0);
        $expenseRefund = (float)($data['expense_refund'] ?? 0);

        // 1. Sözleşmesel Vekalet Ücreti (Alacak Tahakkuku)
        if ($clientFee > 0) {
            $this->financialRepo->createTransaction([
                'transaction_date' => $date,
                'transaction_type' => 'Alacak',
                'sub_type' => null,
                'category' => 'Vekalet Ücreti',
                'amount' => $clientFee,
                'currency' => 'TRY',
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $clientFee,
                'client_id' => $clientId,
                'case_id' => $caseId,
                'description' => 'Tahsilat Dağıtımı: Sözleşmesel Vekalet Hakedişi',
                'document_no' => null,
                'due_date' => $date,
                'payment_method' => null,
                'bank_account_info' => null,
                'status' => 'Ödendi' // Anında tahsil edildiği için
            ]);
        }

        // 2. Avukatın Toplam Geliri (Tahsilat - Ücret)
        $totalAttorneyRevenue = $opposingFee + $clientFee;
        if ($totalAttorneyRevenue > 0) {
            $this->financialRepo->createTransaction([
                'transaction_date' => $date,
                'transaction_type' => 'Tahsilat',
                'sub_type' => 'ucret',
                'category' => 'Vekalet Ücreti Tahsilatı',
                'amount' => $totalAttorneyRevenue,
                'currency' => 'TRY',
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $totalAttorneyRevenue,
                'client_id' => $clientId,
                'case_id' => $caseId,
                'description' => 'Tahsilat Dağıtımı: Karşı Vekalet ve/veya Müvekkil Vekalet Ücreti Tahsilatı',
                'document_no' => null,
                'due_date' => null,
                'payment_method' => 'Banka Havalesi/EFT',
                'bank_account_info' => null,
                'status' => 'Ödendi'
            ]);
        }

        // 3. Müvekkile Gidecek Olan ve İade Edilen Masraf (Tahsilat - Emanet)
        $totalClientEmanet = $clientNet + $expenseRefund;
        if ($totalClientEmanet > 0) {
            $this->financialRepo->createTransaction([
                'transaction_date' => $date,
                'transaction_type' => 'Tahsilat',
                'sub_type' => 'emanet',
                'category' => 'Tahsilat İadesi / Aktarım',
                'amount' => $totalClientEmanet,
                'currency' => 'TRY',
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $totalClientEmanet,
                'client_id' => $clientId,
                'case_id' => $caseId,
                'description' => 'Tahsilat Dağıtımı: Müvekkil Anaparası ve Masraf İadesi (Emanete Alındı)',
                'document_no' => null,
                'due_date' => null,
                'payment_method' => 'Banka Havalesi/EFT',
                'bank_account_info' => null,
                'status' => 'Ödendi'
            ]);
        }

        return $distributionId;
    }

    public function deleteCollection(int $collectionId): bool
    {
        return $this->collectionRepo->deleteCollection($collectionId);
    }
}
