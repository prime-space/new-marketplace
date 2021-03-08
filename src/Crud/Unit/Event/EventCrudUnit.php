<?php namespace App\Crud\Unit\Event;

use App\Crud\Unit\Event\CustomAction\MarkAllAsRead;
use App\Crud\Unit\Event\CustomAction\MarkAsRead;
use App\Entity\Event;
use Ewll\CrudBundle\ReadViewCompiler\Transformer\Date;
use Ewll\CrudBundle\Condition\ExpressionCondition;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;
use Ewll\UserBundle\Authenticator\Exception\NotAuthorizedException;
use LogicException;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'event';

    private $translator;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator,
        TranslatorInterface $translator
    ) {
        parent::__construct($repositoryProvider, $authenticator);
        $this->translator = $translator;
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Event::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    /** @inheritDoc */
    public function getAccessConditions(string $action): array
    {
        try {
            $user = $this->authenticator->getUser();
        } catch (NotAuthorizedException $e) {
            throw new LogicException('User must be here');
        }

        return [
            new ExpressionCondition(ExpressionCondition::ACTION_EQUAL, 'userId', $user->id),
        ];
    }

    public function getReadOneFields(): array
    {
        return [];
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'typeId',
            'referenceId',
            'data',
            'isRead',
            'dateCreate' => new Date('createdTs', Date::FORMAT_SHORT_DATE_TIME),
        ];
    }

    public function getCustomActions(): array
    {
        return [MarkAllAsRead::class, MarkAsRead::class];
    }
}
