<?php namespace App\Crud\UserProvider;

use App\Entity\User;
use Ewll\CrudBundle\UserProvider\UserProviderInterface;

class ManualUserProvider implements UserProviderInterface
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /** @inheritDoc */
    public function getUser(): User
    {
        return $this->user;
    }
}
