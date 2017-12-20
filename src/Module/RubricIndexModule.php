<?php

namespace Argayash\CoreBundle\Module;

/**
 * Модуль - список подрубрик текущей рубрики.
 */
class RubricIndexModule extends Module
{
    public function __construct()
    {
        $this->setName('Website section - index of subsections');
        $this->allowMultiple = true;
    }

    protected function registerRoutes()
    {
        $this->addRoute('index', '/', ['_controller' => 'IphpCoreBundle:Rubric:indexSubrubrics']);
    }
}
