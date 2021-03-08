<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Entity\Token;

class User
{
    const FIELD_NICKNAME_REGEX = '/^[a-z0-9-_\.]+$/i';

    const CONTACT_TYPES = ['Telegram' => 1, 'Skype' => 2,];

    const PARTNER_SELL_ACTIONS = [
        'user.partner-sell-actions.not-notify' => 1,
        'user.partner-sell-actions.notify' => 2,
        'user.partner-sell-actions.auto-offer' => 3,
    ];

    /** @Db\BigIntType */
    public $id;
    /** @Db\IntType */
    public $tariffId;
    /** @Db\VarcharType(length = 64) */
    public $email;
    /** @Db\VarcharType(length = 64) */
    public $pass;
    /** @Db\VarcharType(length = 24) */
    public $nickname;
    /** @Db\TinyIntType */
    public $twofaTypeId;
    /** @Db\CipheredType */
    public $twofaData;
    /** @Db\VarcharType(length = 39) */
    public $ip;
    /** @Db\VarcharType(30) */
    public $timezone = 'Europe/Minsk';
    /** @Db\BoolType */
    public $isEmailConfirmed;
    /** @Db\JsonType */
    public $accessRights = [['id' => UserAccessRule::ID]];
    /** @Db\VarcharType(length = 64) */
    public $apiKey;
    /** @Db\BoolType() */
    public $isApiEnabled = false;
    /** @Db\BoolType() */
    public $isPublicAgent = false;
    /** @Db\TextType() */
    public $contact;
    /** @Db\TinyIntType */
    public $contactTypeId;
    /** @Db\TextType() */
    public $agentInfo = '';
    /** @Db\DecimalType() */
    public $agentRating = 0;
    /** @Db\IntType() */
    public $agentSalesNum = 0;
    /** @Db\IntType() */
    public $agentPartnershipsNum = 0;
    /** @Db\TinyIntType */
    public $partnerSellActionId = 1;
    /** @Db\DecimalType() */
    public $partnerDefaultFee = 0;
    /** @Db\BoolType() */
    public $isDeleted = false;
    /** @Db\TimestampType */
    public $createdTs;

    /** @var Token|null */
    public $token;

    public static function create($email, $pass, $ip, $isEmailConfirmed): self
    {
        $item = new self();
        $item->tariffId = Tariff::ID_SELLER;
        $item->email = $email;
        $item->pass = $pass;
        $item->ip = $ip;
        $item->isEmailConfirmed = $isEmailConfirmed;

        return $item;
    }

    public function hasTwofa()
    {
        return null !== $this->twofaTypeId;
    }

    public function getName()
    {
        return $this->nickname ?? $this->id;
    }

    public function compileJsConfigView()
    {
        return [
            'id' => $this->id,
            'tariffId' => $this->tariffId,
            'email' => $this->email,
            'name' => $this->nickname ?? $this->email,
        ];
    }
}
