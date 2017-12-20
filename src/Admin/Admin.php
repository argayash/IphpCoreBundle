<?php

namespace Argayash\CoreBundle\Admin;

use Argayash\CoreBundle\Admin\Traits\EntityInformationBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;

class Admin extends AbstractAdmin
{
    use EntityInformationBlock;
}
