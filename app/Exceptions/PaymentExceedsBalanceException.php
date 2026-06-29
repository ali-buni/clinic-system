<?php

namespace App\Exceptions;

use Exception;

class PaymentExceedsBalanceException extends Exception
{


    protected float $remainingBalance;

    public function __construct(float $remainingBalance, string $message = "Payment_amount_exceeds_invoice_remaining_balance")
    {
        parent::__construct($message);
        $this->remainingBalance = $remainingBalance;
    }

    // ميثود كرمال نسحب منها الداتا بالكونترولر
    public function getRemainingBalance(): float
    {
        return $this->remainingBalance;
    }
}