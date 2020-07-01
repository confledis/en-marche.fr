<?php

namespace App\Controller\EnMarche\InstitutionalEvents;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use App\Entity\MyTeam\DelegatedAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-partage/{delegated_access_uuid}/evenements-institutionnels", name="app_referent_institutional_events_delegated_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_INSTITUTIONAL_EVENTS', request)")
 */
class DelegatedReferentInstitutionalEventsController extends ReferentInstitutionalEventsController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function redirectToInstitutionalEventsRoute(Request $request, string $action)
    {
        $delegatedAccess = $this->getDelegatedAccess($request);

        return $this->redirectToRoute(sprintf('app_%s_institutional_events_delegated_%s', $this->getSpaceType(), $action), [DelegatedAccess::ATTRIBUTE_KEY => $delegatedAccess->getUuid()]);
    }
}
