<?php

namespace App\Enum;

enum OrderStatus: string {
    case NEW = 'new';
    case PROCESSING = 'processing';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
}