<?php

namespace App\Controller\EnMarche;

use App\Controller\CanaryControllerTrait;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DelegatedAccessController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/espace-partage/{uuid}", name="access_delegation_set")
     */
    public function delegatedSpace(DelegatedAccess $delegatedAccess, SessionInterface $session)
    {
        $this->disableInProduction();

        $session->set(DelegatedAccess::ATTRIBUTE_KEY, $delegatedAccess->getUuid()->toString());

        $routes = DelegatedAccessEnum::getFirstRoutesForType($delegatedAccess->getType());

        return $this->redirectToRoute($routes[$delegatedAccess->getAccesses()[0]]);
    }

    /**
     * @Route("/espace-standard/{type}", name="access_delegation_unset")
     */
    public function standardSpace(string $type, SessionInterface $session)
    {
        $session->remove(DelegatedAccess::ATTRIBUTE_KEY);

        return $this->redirectToRoute("app_message_{$type}_list");
    }
}
