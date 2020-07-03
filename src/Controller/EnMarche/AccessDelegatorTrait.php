<?php

namespace App\Controller\EnMarche;

use App\Controller\CanaryControllerTrait;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait AccessDelegatorTrait
{
    use CanaryControllerTrait;

    protected function getMainUser(SessionInterface $session): UserInterface
    {
        $user = $this->getUser();

        if (null !== $delegatedAccessUuid = $session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            $this->disableInProduction();

            return $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid)->getDelegator();
        }

        return $user;
    }
}
