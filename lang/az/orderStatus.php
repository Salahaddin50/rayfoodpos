<?php

use App\Enums\OrderStatus;

return [
    OrderStatus::PENDING          => 'Gözləmədə',
    OrderStatus::ACCEPT           => 'Qəbul edildi',
    OrderStatus::PREPARING        => 'Hazırlanır',
    OrderStatus::PREPARED         => 'Hazırdır',
    OrderStatus::OUT_FOR_DELIVERY => 'Çatdırılmaq üçün yoldadır',
    OrderStatus::DELIVERED        => 'Çatdırıldı',
    OrderStatus::CANCELED         => 'Ləğv edildi',
    OrderStatus::REJECTED         => 'Rədd edildi',
    OrderStatus::RETURNED         => 'Geri qaytarıldı',


];

