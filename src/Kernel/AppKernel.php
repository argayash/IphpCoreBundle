<?php
/**
 * @author Vitiko <vitiko@mail.ru>
 */

namespace Iphp\CoreBundle\Kernel;

use Symfony\Component\HttpKernel\Kernel;

abstract class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array_merge(RegisterBundles::register($this), $this->addBundles());

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles = array_merge($bundles, $this->baseDevTestBundles(), $this->addDevTestBundles());
        }

        return $bundles;
    }

    public function baseDevTestBundles()
    {
        return [
        new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
        new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle(),
        new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(), ];
    }

    public function addDevTestBundles()
    {
        return [];
    }

    public function addBundles()
    {
        return [];
    }
}
