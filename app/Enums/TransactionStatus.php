<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case NEW        = 'new';
    case PROCESSING = 'processing';
    case CANCELED   = 'canceled';
    case DELIVERED  = 'delivered';
    case RETURNED   = 'returned';
    case COMPLETE   = 'complete';
}
