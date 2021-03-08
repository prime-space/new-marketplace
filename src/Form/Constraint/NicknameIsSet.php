<?php namespace App\Form\Constraint;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NicknameIsSet extends Constraint
{
    public $user;
    public $message = 'user.nickname.not-set';

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }
}
