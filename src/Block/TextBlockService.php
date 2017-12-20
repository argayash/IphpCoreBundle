<?php

namespace Iphp\CoreBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\TextBlockService as BaseTextBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author     Vitiko <vitiko@mail.ru>
 */
class TextBlockService extends BaseTextBlockService
{
    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface  $block, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        return $this->renderResponse('IphpCoreBundle:Block:block_core_text.html.twig', [
            'block' => $block->getBlock(),
            'settings' => $settings,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', [
            'label' => 'Содержание блока',
            'keys' => [
                ['content', 'textarea', ['attr' => ['class' => 'label_hidden tinymce', 'data-theme' => 'advanced'], 'label' => ' ', 'required' => false]],
                /*array('link', 'text', array('label' => 'Ссылка заголовка блока', 'required' => false))*/
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Текстовый блок (визуальное редактирование)';
    }

    public function getDefaultSettings()
    {
        return [
            'content' => '',
        ];
    }
}
