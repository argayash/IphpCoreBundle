<?php
/**
 * Created by Vitiko
 * Date: 25.01.12
 * Time: 15:29.
 */

namespace Iphp\CoreBundle\Module;

use Iphp\CoreBundle\Model\RubricInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Module in web site rubric.
 */
abstract class Module
{
    /**
     * @var string Module Name
     */
    protected $name;

    /**
     * Access to external resources via ModuleManage.
     *
     * @var \Argayash\CoreBundle\Module\ModuleManager
     */
    protected $moduleManager;

    /**
     * Allow multiples module instances in rubrics.
     *
     * @var bool
     */
    protected $allowMultiple = false;

    /**
     * Module route collection.
     *
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $routeCollection = null;

    /**
     * Rubric where module placed.
     *
     * @var \Argayash\CoreBundle\Model\Rubric
     */
    protected $rubric = null;

    private $routeResources = [];

    abstract protected function registerRoutes();

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function buildRouteCollection()
    {
        if ($this->routeCollection) {
            return;
        }

        $this->routeCollection = new RouteCollection();
        $this->registerRoutes();
    }

    public function setManager(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;

        return $this;
    }

    public function setRubric(RubricInterface $rubric)
    {
        $this->rubric = $rubric;

        return $this;
    }

    protected function importRoutes($resource, $type = null)
    {
        $this->routeResources[$resource] = $type;
    }

    protected function addRoute(
        $name, $pattern,
        array $defaults = [],
        array $requirements = [],
        array $options = []
    ) {
        $route = new Route($pattern, $defaults, $requirements, $options);
        $this->routeCollection->add($this->prepareRouteName($name), $route);

        return $this;
    }

    /**
     * Return route name. If route can be multiples - it prefixes with rubricFullPathCode
     * rubric: /some/path/, route : index , route name will be some_path_index.
     *
     * @param $name
     *
     * @return string
     */
    protected function prepareRouteName($name)
    {
        return $this->allowMultiple && $this->rubric ?
          $this->moduleManager->getEntityRouter()->routeNameForRubricAction($this->rubric, $name) : $name;
    }

    public function getRoutes()
    {
        $this->buildRouteCollection();

        return $this->routeCollection;
    }

    /**
     * @return array
     */
    public function getRouteResources()
    {
        return $this->routeResources;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Allow multiple module instances.
     *
     * @return bool
     */
    public function isAllowMultiple()
    {
        return $this->allowMultiple ? true : false;
    }

    public function getAdminExtension()
    {
    }
}
