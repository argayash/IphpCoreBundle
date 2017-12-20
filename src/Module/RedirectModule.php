<?php
/**
 * Created by Vitiko
 * Date: 25.05.12
 * Time: 15:29.
 */

namespace Iphp\CoreBundle\Module;

class RedirectModule extends Module
{
    public function __construct()
    {
        $this->setName('Redirect');
        $this->allowMultiple = true;
    }

    protected function registerRoutes()
    {
        $this->addRoute('redirect', '/', ['_controller' => 'IphpCoreBundle:Rubric:redirect']);
    }
}
