<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Ticket
{
    const FIELD_ID = 'id';
    const FIELD_USER_ID = 'userId';
    const FIELD_LAST_MESSAGE_TS = 'lastMessageTs';

    /** @Db\BigIntType */
    public $id;
    /** @Db\BigIntType */
    public $userId;
    /** @Db\VarcharType(length = 256) */
    public $subject;
    /** @Db\IntType() */
    public $messagesSendingNum = 1;
    /** @Db\IntType() */
    public $messagesNum = 1;
    /** @Db\BoolType */
    public $hasUnreadMessage = false;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $lastMessageTs;
    /** @Db\TimestampType */
    public $createdTs;

//    public function compileView(): array
//    {
//        $view = [
//            'id' => $this->id,
//            'userId' => $this->userId,
//            'subject' => $this->subject,
//            'lastMessageTs' => $this->lastMessageTs->format(VueViewCompiler::TIMEZONEJS_DATE_FORMAT),
//            'hasUnreadMessage' => $this->hasUnreadMessage,
//        ];
//
//        return $view;
//    }
//
//    public function compileAdminApiFinderView(): array
//    {
//        $view = [
//            'id' => $this->id,
//            'type' => 'Ticket',
//            'info' => $this->subject,
//            'date' => $this->createdTs->format(AdminApi::DATE_FORMAT),
//        ];
//
//        return $view;
//    }
}
