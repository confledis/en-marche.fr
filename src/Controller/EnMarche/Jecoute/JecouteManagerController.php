<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-jecoute", name="app_jecoute_manager_")
 *
 * @Security("is_granted('ROLE_JECOUTE_MANAGER')")
 */
class JecouteManagerController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::MANAGER_SPACE;
    }

    protected function getLocalSurveys(Adherent $adherent): array
    {
        return $this->localSurveyRepository->findAllByTagsWithStats($this->getSurveyTags($adherent));
    }

    protected function getSurveyTags(Adherent $adherent): array
    {
        return $adherent->getJecouteManagedArea()->getCodes();
    }
}
