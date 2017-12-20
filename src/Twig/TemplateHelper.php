<?php

namespace Iphp\CoreBundle\Twig;

use Iphp\CoreBundle\Manager\RubricManager;

class TemplateHelper
{
    /**
     * @var \Argayash\CoreBundle\Manager\RubricManager
     */
    protected $rubricManager;

    public function __construct(RubricManager $rubricManager)
    {
        //Напрямую инжектировать в сервис request нельзя т.к. он может быть неактивным

        $this->rubricManager = $rubricManager;
    }

    public function getRubric()
    {
        // return 'Rubrica '.$this->request->get ('_rubric');

        return $this->rubricManager->getRubricFromRequest();
    }

    public function getRubricRepo()
    {
        return $this->rubricManager->getRepository();
    }
}
