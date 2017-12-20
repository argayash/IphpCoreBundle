<?php

namespace Argayash\CoreBundle\Twig;

use Argayash\CoreBundle\Manager\RubricManager;
use Argayash\CoreBundle\Routing\EntityRouter;
use Symfony\Component\Security\Core\SecurityContextInterface;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment
     */
    protected $twigEnviroment;

    /**
     * @var \Argayash\CoreBundle\Manager\RubricManager
     */
    protected $rubricManager;

    /**
     * @var \Argayash\CoreBundle\Routing\EntityRouter;
     */
    protected $entityRouter;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param \Twig_Environment        $twigEnviroment
     * @param RubricManager            $rubricManager
     * @param EntityRouter             $entityRouter
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        \Twig_Environment $twigEnviroment,
        RubricManager $rubricManager,
        EntityRouter $entityRouter,
        SecurityContextInterface $securityContext
    ) {
        $this->twigEnviroment = $twigEnviroment;
        $this->rubricManager = $rubricManager;
        $this->entityRouter = $entityRouter;
        $this->securityContext = $securityContext;

        $twigEnviroment->addGlobal('iphp', new TemplateHelper($rubricManager));
    }

    public function getFunctions()
    {
        return [
            'iphp_block_by_name' => new \Twig_SimpleFunction('iphp_block_by_name', [$this, 'getBlockByName']),
            //Camel case forever
            'entity_path' => new \Twig_SimpleFunction('entity_path', [$this, 'getEntityPath']),
            'entity_action' => new \Twig_SimpleFunction('entity_action', [$this, 'getEntityActionPath']),
            'inline_edit' => new \Twig_SimpleFunction('inline_edit', [$this, 'getInlineEditStr'], ['is_safe' => ['html']]),
            //For BC
            'entitypath' => new \Twig_SimpleFunction('entitypath', [$this, 'getEntityPath']),
            'entityaction' => new \Twig_SimpleFunction('entityaction', [$this, 'getEntityActionPath']),
            'inlineedit' => new \Twig_SimpleFunction('inlineedit', [$this, 'getInlineEditStr'], ['is_safe' => ['html']]),
            'rpath' => new \Twig_SimpleFunction('rpath', [$this, 'getRubricPath']),
            'path_exists' => new \Twig_SimpleFunction('path_exists', [$this, 'pathExists']),
        ];
    }

    public function getRubricPath($rubric)
    {
        return $this->rubricManager->generatePath($rubric);
    }

    public function pathExists($name)
    {
        return (null === $this->entityRouter->getRouter()->getRouteCollection()->get($name)) ? false : true;
    }

    public function getBlockByName($blockName, $rubric = null)
    {
        $blocks = $this->rubricManager->getBlockRepository()->findBy(
            ['title' => $blockName, 'enabled' => 1]);
        if ($rubric === null) {
            $rubric = $this->rubricManager->getRubricFromRequest();
        }

        $commonBlock = null;
        foreach ($blocks as $block) {
            if ($rubric && $block->getRubric() && $block->getRubric()->getId() === $rubric->getId()) {
                return $block;
            }
            if (!$block->getRubric() && !$commonBlock) {
                $commonBlock = $block;
            }
        }

        if ($commonBlock) {
            return $commonBlock;
        }

        return null;
    }

    public function getEntityPath($entity, $arg1 = null, $arg2 = null, $arg3 = null)
    {
        $path = $this->entityRouter->entitySitePath($entity, $arg1, $arg2, $arg3);

        if (mb_substr($path, 0, mb_strlen($this->rubricManager->getBaseUrl())) !== $this->rubricManager->getBaseUrl()) {
            $path = $this->rubricManager->getBaseUrl() . $path;
        }

        return $path;
    }

    /**
     * @param mixed  $entityName
     * @param string $action
     * @param array  $params
     *
     * @return string
     */
    public function getEntityActionPath($entityName, $action = 'index', array $params = [])
    {
        return $this->entityRouter->generateEntityActionPath($entityName, $action, $params);
    }

    public function getInlineEditStr($entity)
    {
        return $this->securityContext->isGranted(['ROLE_ADMIN' /*,'ROLE_SUPER_ADMIN'*/]) ?
            '<a href="#" onClick="return inlineEdit (\'' . addslashes(get_class($entity)) . '\',' . $entity->getId() .
                ')">edit</a>' : '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'iphpp';
    }
}
