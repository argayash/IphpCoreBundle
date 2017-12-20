<?php

namespace Iphp\CoreBundle\Admin;

use Iphp\CoreBundle\Admin\Traits\EntityInformationBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;

class Admin extends AbstractAdmin
{
    use EntityInformationBlock;
}
