<?php

use App\Enums\OrderStatus;

return [
    OrderStatus::PENDING          => 'В ожидании',
    OrderStatus::ACCEPT           => 'Принят',
    OrderStatus::PREPARING        => 'Готовится',
    OrderStatus::PREPARED         => 'Готов',
    OrderStatus::OUT_FOR_DELIVERY => 'В доставке',
    OrderStatus::DELIVERED        => 'Доставлен',
    OrderStatus::CANCELED         => 'Отменен',
    OrderStatus::REJECTED         => 'Отклонен',
    OrderStatus::RETURNED         => 'Возвращен',


];

