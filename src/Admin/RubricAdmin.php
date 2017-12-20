<?php

namespace Argayash\CoreBundle\Admin;

use Argayash\CoreBundle\Admin\Traits\EntityInformationBlock;
use Argayash\CoreBundle\Model\RubricInterface;
use Iphp\TreeBundle\Admin\TreeAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Model\UserManagerInterface;

class RubricAdmin extends TreeAdmin
{
    use EntityInformationBlock;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var \Argayash\CoreBundle\Manager\RubricManager
     */
    protected $rubricManager;

    public function getListTemplate()
    {
        return 'IphpTreeBundle:CRUD:treeCollapsible.html.twig';
    }

    public function getNewInstance()
    {
        return parent::getNewInstance()->setStatus(true);
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     */
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('status')
            ->add('title')
            ->add('abstract');
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $rubric = $this->getSubject();

        $formMapper->with('Rubric', ['class' => 'col-md-8'])->end();
        $formMapper->with('Attributes', ['class' => 'col-md-4'])->end();
        $this->addInformationBlock($formMapper);

        $formMapper->with('Rubric');

        $formMapper->add('title');

        if (!$rubric->isRoot()) {
            $formMapper->add('parent', 'rubricchoice', ['label' => 'Parent Rubric'])
                ->add('path', 'slug_text', [
                'source_field' => 'title',
                'usesource_title' => $this->trans('Use rubric title'),
            ])
                ->setHelps(['path' => 'Path used for building rubric url']);
        }

        $formMapper->add('abstract')
            ->add('redirectUrl')
            ->add('controllerName', 'modulechoice',
            ['label' => 'Choose module',
                'required' => false,
                'empty_value' => ' ',
            ]
        );

        $formMapper->end();

        $formMapper->with('Attributes');
        $this->addMenuRelatedFields($rubric, $formMapper);

        if ($rubric->getModuleError()) {
            $formMapper->add('moduleError', 'genemu_plain', ['attr' => ['style' => 'color:red']]);
        }

        $formMapper->end();

        if ($this->subject && $this->subject->getId()) {
            $url = $this->configurationPool->getContainer()->get('iphp.core.entity.router')
                ->entitySiteUrl($this->subject);
            $formMapper->setHelps(['status' => '<a target="_blank" href="' . $url . '">' . $url . '</a>']);
        }

        $this->configureModuleFormFields($rubric, $formMapper);
    }

    protected function addMenuRelatedFields(RubricInterface $rubric, FormMapper $formMapper)
    {
        if (!$rubric->isRoot()) {
            $formMapper->add('status', 'checkbox', ['required' => false, 'label' => 'Show in menu']);
        }
    }

    protected function configureModuleFormFields(RubricInterface $rubric, FormMapper $formMapper)
    {
        $module = $this->configurationPool->getContainer()->get('iphp.core.module.manager')
            ->getModuleFromRubric($rubric);

        if ($module) {
            $moduleAdminExtension = $module->getAdminExtension();
            if ($moduleAdminExtension) {
                $moduleAdminExtension->configureFormFields($formMapper);
            }
        }
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('title', null, [
            'template' => 'IphpCoreBundle:Admin:rubric_treelist_field.html.twig', ])

            ->add('fullPath', null, ['width' => '200px',
                 'template' => 'IphpCoreBundle:Admin:path_treelist_field.html.twig', ])

            ->add('controllerName', null, ['width' => '150px',
                'template' => 'IphpCoreBundle:Admin:rubric_controller_field.html.twig', ])
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title');
    }

    protected function configureSideMenu(\Knp\Menu\ItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'], true)) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sidemenu.link_list_blocks'),
            ['uri' => $admin->generateUrl('iphp.core.admin.block.list', ['id' => $id])]
        );
    }

    public function postUpdate($object)
    {
        $this->rubricManager->clearCache();
    }

    public function postPersist($object)
    {
        $this->rubricManager->clearCache();
    }

    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }

    public function getUserManager()
    {
        return $this->userManager;
    }

    /**
     * @param \Argayash\CoreBundle\Manager\RubricManager $rubricManager
     *
     * @return RubricAdmin
     */
    public function setRubricManager($rubricManager)
    {
        $this->rubricManager = $rubricManager;

        return $this;
    }

    /**
     * @return \Argayash\CoreBundle\Manager\RubricManager
     */
    public function getRubricManager()
    {
        return $this->rubricManager;
    }
}
