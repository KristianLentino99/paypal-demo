<?php


namespace App\util\classes;


interface PaypalOrderStatus
{

    const ORDER_STATUS_APPROVED = 'APPROVED';
    const ORDER_STATUS_CREATED = 'CREATED';
    const ORDER_STATUS_SAVED = 'SAVED';
    const ORDER_STATUS_VOIDED = 'VOIDED';
    const ORDER_STATUS_COMPLETED = 'COMPLETED';
    const ORDER_STATUS_PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';

}
