<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Cash = 'Cash';
    case Card = 'Card';
    case BankTransfer = 'BankTransfer';
    case Stripe = 'Stripe';
}
