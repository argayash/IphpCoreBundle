<?php

namespace Argayash\CoreBundle\Module;

/**
 * Модуль индекс сайта.
 */
class SiteIndexModule extends Module
{
    public function __construct()
    {
        $this->setName('Website main page');
        $this->allowMultiple = false;
    }

    protected function registerRoutes()
    {
        $this->addRoute('site_index', '/', ['_controller' => 'IphpCoreBundle:Rubric:indexSite']);
    }
}
