<?php namespace App\MessageBroker;

class MessageBrokerConfig
{
    const QUEUE_NAME_EXEC_ORDER = 'exec_order';
    const QUEUE_NAME_EXEC_TRANSACTION = 'exec_transaction';
    const QUEUE_NAME_SEND_PAYOUT = 'send_payout';
    const QUEUE_NAME_TELEGRAM_MESSAGE = 'telegram_message';
    const QUEUE_NAME_ADMIN_API_REQUEST = 'admin_api_request';
    const QUEUE_NAME_SALES_UP = 'sales_up';
}
