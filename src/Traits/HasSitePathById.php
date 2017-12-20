<?php
/**
 * @author Vitiko <vitiko@mail.ru>
 */

namespace Argayash\CoreBundle\Traits;

use Argayash\CoreBundle\Routing\EntityRouter;

trait HasSitePathById
{
    public function getSitePath(EntityRouter $entityRouter, $action = 'view')
    {
        return $entityRouter->generateEntityActionPath($this, $action, [
            'id' => $this->getId(),
        ]);
    }
}
