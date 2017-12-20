<?php

namespace Argayash\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RubricController extends RubricAwareController
{
    /**
     * @Template()
     */
    public function indexSubrubricsAction()
    {
        $rubrics = $this->getCurrentRubric()->getChildren();

        return ['rubrics' => $rubrics];
    }

    /**
     * @Template()
     */
    public function indexSiteAction()
    {
        return [];
    }

    public function redirectAction()
    {
        $rubric = $this->getCurrentRubric();

        if (!$rubric) {
            return new RedirectResponse('/');
        }

        $redirectUrl = $rubric->getRedirectUrl();

        if (!$redirectUrl) {
            foreach ($rubric->getChildren() as $subrubric) {
                if ($subrubric->getStatus()) {
                    $redirectUrl = $subrubric->getFullPath();
                    break;
                }
            }
        }

        if (!$redirectUrl) {
            throw new \Exception('redirect url not setted');
        }

        return new RedirectResponse(
            $redirectUrl[0] === '/' ? $this->getRubricManager()->generatePath($redirectUrl) :
            $rubric->getRedirectUrl());
    }
}
