<?php

namespace Argayash\CoreBundle\Entity;

/*
 * @author Vitiko <vitiko@mail.ru>
 */
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Form;
use Traversable;

class FormQueryBuilderMapper
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $qb;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $defaultMapping;

    /**
     * @var array
     */
    protected $mappingOptions = [];

    public function __construct(Form $form, QueryBuilder $qb)
    {
        $this->form = $form;
        $this->qb = $qb;

        $this->defaultMapping = $this->getDefaultMapping();
    }

    public static function create(Form $form, QueryBuilder $qb)
    {
        return new static ($form, $qb);
    }

    protected function getDefaultMapping()
    {
        return [
            'search' => function ($qb, $value) {
                if ($value) {
                    $qb->search($value);
                }
            },
        ];
    }

    public function add($formField, $mapping = null, $default = null, $options = [])
    {
        $this->mapping[$formField] = $mapping;
        $this->mappingDefault[$formField] = $default;
        $this->mappingOptions[$formField] = $options;

        return $this;
    }

    public function addAll()
    {
        foreach ($this->form->all() as $fieldName => $field) {
            if (!in_array($field->getConfig()->getType()->getName(), ['submit', 'button'], true)) {
                $this->add($fieldName);
            }
        }

        return $this;
    }

    public function getDefaultValue($formField)
    {
        if (is_callable($this->mappingDefault[$formField])) {
            $default = $this->mappingDefault[$formField];

            return $default();
        }

        return $this->mappingDefault[$formField];
    }

    public function getValue($formField)
    {
        $value = $this->form[$formField]->getData();
        if (!$value) {
            $value = $this->getDefaultValue($formField);
        }

        return $value;
    }

    public function process($returnWithValues = true)
    {
        $processed = [];
        foreach (array_keys($this->mapping) as $formField) {
            $value = $this->processField($formField);
            if ($value || !$returnWithValues) {
                $processed[$formField] = $value;
            }
        }

        return $processed;
    }

    protected function setQbCondition($formField, $value)
    {
        $mapping = $this->mapping[$formField];
        if (null === $mapping && isset($this->defaultMapping[$formField])) {
            $mapping = $this->defaultMapping[$formField];
        }

        $this->processMapping($formField, $value, $mapping);
    }

    protected function processMapping($formField, $value, $mapping)
    {
        if (is_callable($mapping)) {
            $mapping($this->qb, $value);
        } elseif ($value) {
            if (method_exists($this->qb, 'where' . ucfirst($formField))) {
                $this->qb->{'where' . ucfirst($formField)}($value);
            } else {
                $multi = is_array($value) || $value instanceof Traversable;
                $holder = 'map_' . $formField;

                if (!$mapping && $this->qb instanceof BaseEntityQueryBuilder) {
                    $mapping = $this->qb->getCurrentAlias() . '.' . $formField;
                }

                $this->qb->andWhere($mapping . ' ' . ($multi ? 'IN (:' . $holder . ')' : ' = :' . $holder))
                ->setParameter($holder, $value);
            }
        }
    }

    public function processField($formField)
    {
        $value = $this->getValue($formField);
        $this->setQbCondition($formField, $value);

        return $value;
    }
}
