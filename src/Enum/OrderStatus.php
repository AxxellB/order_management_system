<?php

namespace App\Enum;

enum OrderStatus: string {
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PROCESSING = 'processing';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case DELIVERED = 'delivered';
    case SHIPPED = 'shipped';
    case RETURNED = 'returned';
}