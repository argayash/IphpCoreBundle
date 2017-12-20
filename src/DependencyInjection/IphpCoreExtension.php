<?php

namespace Argayash\CoreBundle\DependencyInjection;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
//use Symfony\Component\DependencyInjection\Definition;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IphpCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('twig.xml');
        $loader->load('services.xml');

        $loader->load('admin.xml');
        $loader->load('front.xml');
        $loader->load('block.xml');
        $this->registerDoctrineMapping($config);

        $this->setContainerParameters($container, $config);
    }

    protected function setContainerParameters(ContainerBuilder $container, array $config)
    {
        if (!$container->has('iphp.web_dir') || !$container->getParameter('iphp.web_dir')) {
            $container->setParameter('iphp.web_dir',
                str_replace('\\', '/', realpath($container->getParameter('kernel.root_dir') . '/../web/')));
        }

        $container->setParameter('iphp.user.class', $config['class']['user']);
        $container->setParameter('iphp.usergroup.class', $config['class']['usergroup']);
    }

    /**
     * @param array $config
     */
    public function registerDoctrineMapping(array $config)
    {
        // print 'Extends!';

        if (!class_exists($config['class']['rubric'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['rubric'], 'mapOneToMany', [
            'fieldName' => 'children',
            'targetEntity' => $config['class']['rubric'],
            'cascade' => [
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ],
            'mappedBy' => 'parent',
            'orphanRemoval' => false,
            'orderBy' => [
                'left' => 'ASC',
            ],
        ]);

        //ИНДЕКСЫ
        $collector->addIndex($config['class']['rubric'], 'lftrgt', ['lft', 'rgt']);
        $collector->addIndex($config['class']['rubric'], 'lvl', ['lvl']);
        $collector->addIndex($config['class']['rubric'], 'created_at', ['created_at']);
        $collector->addIndex($config['class']['rubric'], 'updated_at', ['updated_at']);
        $collector->addIndex($config['class']['rubric'], 'full_path', ['full_path']);
        /*
        $collector->addAssociation($config['class']['rubric'], 'mapManyToOne', array(
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['rubric'],
            'cascade' => array(
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                ),
            ),
            'orphanRemoval' => false,
        ));




        <many-to-one field="parent" target-entity="Application\Argayash\CoreBundle\Entity\Rubric">
                    <join-column name="parent_id" referenced-column-name="id" on-delete="SET NULL"/>
                    <gedmo:tree-parent/>
                </many-to-one>

      */

        $collector->addAssociation($config['class']['rubric'], 'mapOneToMany', [
            'fieldName' => 'blocks',
            'targetEntity' => $config['class']['block'],
            'cascade' => [
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ],
            'mappedBy' => 'rubric',
            'orphanRemoval' => false,
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        $collector->addAssociation($config['class']['block'], 'mapOneToMany', [
            'fieldName' => 'children',
            'targetEntity' => $config['class']['block'],
            'cascade' => [
                'remove',
                'persist',
            ],
            'mappedBy' => 'parent',
            'orphanRemoval' => true,
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', [
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['block'],
            'cascade' => [],
            'mappedBy' => null,
            'inversedBy' => 'children',
            'joinColumns' => [
                [
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', [
            'fieldName' => 'rubric',
            'targetEntity' => $config['class']['rubric'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => 'blocks',
            'joinColumns' => [
                [
                    'name' => 'rubric_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        if ($config['class']['user'] && class_exists($config['class']['user'])) {
            $collector->addAssociation($config['class']['rubric'], 'mapManyToOne', [
                'fieldName' => 'createdBy',
                'targetEntity' => $config['class']['user'],
                'cascade' => [
                    'persist',
                ],
                'mappedBy' => null,
                'inversedBy' => null,
                'joinColumns' => [
                    [
                        'name' => 'createdby_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                    ],
                ],
                'orphanRemoval' => false,
            ]);

            $collector->addAssociation($config['class']['rubric'], 'mapManyToOne', [
                'fieldName' => 'updatedBy',
                'targetEntity' => $config['class']['user'],
                'cascade' => [
                    'persist',
                ],
                'mappedBy' => null,
                'inversedBy' => null,
                'joinColumns' => [
                    [
                        'name' => 'updatedby_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                    ],
                ],
                'orphanRemoval' => false,
            ]);
        }
    }
}
