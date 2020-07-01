<?php

namespace App\Controller\EnMarche\InstitutionalEvents;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-referent/evenements-institutionnels", name="app_referent_institutional_events_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentInstitutionalEventsController extends AbstractInstitutionalEventsController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }

    protected function getMainUser(Request $request): UserInterface
    {
        return $this->getUser();
    }
}
