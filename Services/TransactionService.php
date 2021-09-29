<?php

class TransactionService
{
    private $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function checkPendingTransactionsByCourse($courseId)
    {
        return Transaction::where([
                ['user_id', '=', $this->userId],
                ['paymentStatus', '=', 'pending'],
            ])
            ->leftJoin('transactionsHistory as tH', 'tH.idTransaction', '=', 'transactions.id')
            ->where('tH.idCourse', $courseId)
            ->select(['tH.idCourse as idcourse', 'transactions.user_id as user_id'])
            ->exists();
    }
}
