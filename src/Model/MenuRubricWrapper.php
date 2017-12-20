<?php

namespace Argayash\CoreBundle\Model;

use Iphp\TreeBundle\Model\TreeNodeWrapper;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class MenuRubricWrapper extends TreeNodeWrapper
{
    protected $active;

    public function isActive()
    {
        return $this->active;
    }

    public function setIsActive($active)
    {
        $this->active = $active;
    }
}
