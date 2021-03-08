<?php namespace App\Crud\Unit\Tariff;

use App\Entity\Tariff;
use Ewll\CrudBundle\ReadViewCompiler\Transformer as ReadTransformer;
use Ewll\CrudBundle\Unit\ReadMethodInterface;
use Ewll\CrudBundle\Unit\UnitAbstract;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Ewll\UserBundle\AccessRule\UserAccessRule;
use Ewll\UserBundle\Authenticator\Authenticator;

class TariffCrudUnit extends UnitAbstract implements
    ReadMethodInterface
{
    const NAME = 'tariff';

    public function __construct(
        RepositoryProvider $repositoryProvider,
        Authenticator $authenticator
    ) {
        parent::__construct($repositoryProvider, $authenticator);
    }

    public function getUnitName(): string
    {
        return self::NAME;
    }

    public function getEntityClass(): string
    {
        return Tariff::class;
    }

    public function getAccessRuleClassName(): ?string
    {
        return UserAccessRule::class;
    }

    public function getAccessConditions(string $action): array
    {
        return [];
    }

    public function getAllowedSortFields(): array
    {
        return [Tariff::FIELD_PRICE];
    }

    public function getReadListFields(): array
    {
        return [
            'id',
            'name',
            'saleFee',
            'price' => [new ReadTransformer\Money('price', true)],
            'icon',
            'isHidden',
            'holdDays' => function (Tariff $tariff) {
                $dtF = new \DateTime('@0');
                $dtT = new \DateTime("@$tariff->holdSeconds");

                return $dtF->diff($dtT)->format('%a');
            },
        ];
    }
}
