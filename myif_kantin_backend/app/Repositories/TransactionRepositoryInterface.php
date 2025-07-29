<?php

namespace App\Repositories;

interface TransactionRepositoryInterface
{
    public function createTransaction(array $data);
}