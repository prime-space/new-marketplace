<?php namespace App\Crud\Unit\TicketMessage;

class TicketMessageDto
{
    public $id;
    public $ticketId;//При создании сюда попадает id внутреннего тикета. При получении тикетов должно быть пустым. Вот такие костыли
    public $message;
    public $answerUserName;
    public $createdTs;
}
