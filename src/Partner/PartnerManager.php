<?php namespace App\Partner;

use App\Entity\Partnership;
use App\Entity\Product;
use App\Entity\User;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class PartnerManager
{
    const COOKIE_NAME = 'partner';
    const REFERER_DURATION = 15552000;//6 month in seconds

    private $repositoryProvider;
    private $domain;
    private $requestStack;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        string $domain,
        RequestStack $requestStack
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->domain = $domain;
        $this->requestStack = $requestStack;
    }

    public function setPartner(int $parnerUserId): void
    {
        /** @var User|null $parnerUser */
        $parnerUser = $this->repositoryProvider->get(User::class)->findById($parnerUserId);
        if (null !== $parnerUser) {
            $this->setCookie($parnerUser, self::REFERER_DURATION);
        }
    }

    public function getRequestPartner(): ?User
    {
        $partnerUserId = $this->requestStack->getCurrentRequest()->cookies->getInt(self::COOKIE_NAME);
        if (0 === $partnerUserId) {
            return null;
        }
        /** @var User|null $partner */
        $partner = $this->repositoryProvider->get(User::class)->findById($partnerUserId);

        return $partner;
    }

    private function setCookie(User $parnerUser, $duration): void
    {
        $value = $parnerUser->id;
        SetCookie(self::COOKIE_NAME, $value, time() + $duration, '/', $this->domain, true, true);
    }

    public function calcFee(User $partner, Product $product): string
    {
        $fee = $product->partnershipFee;
        /** @var Partnership|null $partnership */
        $partnership = $this->repositoryProvider->get(Partnership::class)->findOneBy([
            'sellerUserId' => $product->userId,
            'agentUserId' => $partner->id,
            'statusId' => Partnership::STATUS_ID_OK,
            'isDeleted' => 0,
        ]);
        if (null !== $partnership) {
            $fee = bcadd($fee, $partnership->fee, 2);
        }

        if (1 === bccomp($fee, '100', 2)) {
            $fee = '100';
        }

        return $fee;
    }
}
