<?php

namespace Argayash\CoreBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author     Vitiko <vitiko@mail.ru>
 */
class TextPlainBlockService extends TextBlockService
{
    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', [
            'label' => 'Содержание блока',
            'keys' => [
                ['content', 'textarea', [
                    'attr' => [
                        'width' => '700',
                        'rows' => 30,
                        'style' => 'width: 700px',
                        'class' => 'label_hidden',
                    ],
                    'label' => ' ',
                    'required' => false, ]],
                /*array('link', 'text', array('label' => 'Ссылка заголовка блока', 'required' => false))*/
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Текстовый блок (текстовое редактирование)';
    }
}
