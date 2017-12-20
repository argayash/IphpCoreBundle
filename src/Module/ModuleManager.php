<?php
/**
 * Created by Vitiko
 * Date: 02.02.12
 * Time: 13:18.
 */

namespace Argayash\CoreBundle\Module;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class ModuleManager extends ContainerAware
{
    protected $modulesPath = 'Module';

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @return \Argayash\CoreBundle\Routing\EntityRouter
     */
    public function getEntityRouter()
    {
        return $this->container->get('iphp.core.entity.router');
    }

    /**
     * @deprecated
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader
     */
    public function getRoutingLoader()
    {
        return $this->container->get('routing.loader');
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected function getKernel()
    {
        return $this->container->get('kernel');
    }

    /**
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->container->get('translator');
    }

    public function modules($byBundle = false, $trans = false)
    {
        $modules = [];
        foreach ($this->getKernel()->getBundles() as $bundle) {
            $bundleModules = $this->bundleModules($bundle);

            if (!$bundleModules) {
                continue;
            }

            if ($trans) {
                $translatedTitles = [];
                foreach ($bundleModules as $module) {
                    $moduleName = $module->getName();
                    if (null === $moduleName) {
                        $moduleName = \Doctrine\Common\Util\ClassUtils::getClass($module);
                    }

                    $translatedTitles[get_class($module)] = $this->getTranslator()->trans($moduleName);
                }

                $bundleModules = $translatedTitles;
            }

            uasort($bundleModules, function ($a, $b) {
                if ((string) $a === (string) $b) {
                    return 0;
                }

                return ((string) $a < (string) $b) ? -1 : 1;
            });

            $bundleTitle = $bundle->getName() . ' Modules';
            if ($trans) {
                $bundleTitle = $this->getTranslator()->trans($bundleTitle);
            }

            if ($byBundle) {
                $modules[$bundleTitle] = $bundleModules;
            } else {
                $modules = array_merge($modules, $bundleModules);
            }
        }

        return $modules;
    }

    /**
     * @deprecated
     *
     * @param $resource
     * @param null $type
     */
    public function loadRoutes($resource, $type = null)
    {
        throw new \RuntimeException('Deprecated!');
    }

    public function bundleModuleDir($bundle)
    {
        return $bundle->getPath() . DIRECTORY_SEPARATOR . $this->modulesPath;
    }

    public function getModuleByControllerName($controllerName)
    {
        return $this->getModuleInstance($controllerName);
    }

    /**
     * @param $bundle
     *
     * @return \Argayash\CoreBundle\Module\Module[]
     */
    public function bundleModules($bundle)
    {
        $dir = $this->bundleModuleDir($bundle);
        if (!file_exists($dir) || !is_dir($dir) || !is_readable($dir)) {
            return [];
        }

        $finder = new Finder();

        $modules = [];
        foreach ($finder->files()->in($dir)->name('/.+Module\.php$/') as $file) {
            $moduleClass = $bundle->getNamespace() . '\\' . $this->modulesPath . '\\' .
                    mb_substr($file->getRealpath(), mb_strlen($dir) + 1, -4);

            if (class_exists($moduleClass)) {
                $classInfo = new \ReflectionClass($moduleClass);
                if (!$classInfo->isAbstract()) {
                    $modules[] = new $moduleClass();
                }
            }
        }

        return $modules;
    }

    /**
     * @param \Application\Argayash\CoreBundle\Entity\Rubric $rubric
     *
     * @return \Argayash\CoreBundle\Module\Module
     */
    public function getModuleFromRubric(\Application\Argayash\CoreBundle\Entity\Rubric $rubric)
    {
        $moduleClassName = $rubric->getControllerName();
        if (!$moduleClassName) {
            return null;
        }

        $module = $this->getModuleInstance($moduleClassName);
        if (!$module) {
            return null;
        }

        return $module->setRubric($rubric);
    }

    /**
     * @param $moduleClassName
     *
     * @return \Argayash\CoreBundle\Module\Module
     */
    public function getModuleInstance($moduleClassName)
    {
        if (!class_exists($moduleClassName, true)) {
            return null;
        }
        /** @var $module \Argayash\CoreBundle\Module\Module */
        $module = new $moduleClassName();
        $module->setManager($this);

        return $module;
    }
}
