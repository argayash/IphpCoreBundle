<?php

namespace Iphp\CoreBundle\Entity;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

class BaseEntityQueryBuilder extends QueryBuilder
{
    protected $currentAlias;

    protected $entityName;

    public function getDefaultAlias()
    {
        return 'e';
    }

    public function setCurrentAlias($alias = '')
    {
        $this->currentAlias = $alias ? $alias : $this->getDefaultAlias();

        return $this;
    }

    public function getCurrentAlias()
    {
        return $this->currentAlias;
    }

    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function prepareDefaultQuery()
    {
        return $this->select($this->currentAlias)
            ->from($this->entityName, $this->currentAlias);
    }

    /**
     * @return \Argayash\CoreBundle\Entity\BaseEntityQueryBuilder
     */
    public function addParameters($parameters)
    {
        if (is_object($parameters) && method_exists($parameters, 'getParameters')) {
            $parameters = $parameters->getParameters();
        }

        foreach ($parameters as $parameter) {
            $this->setParameter($parameter->getName(), $parameter->getValue());
        }

        return $this;
    }

    /**
     * Поддержка magic методом whereXXX и joinXXX.
     *
     * @param $method
     * @param $arguments
     */
    public function __call($method, $arguments)
    {
        switch (true) {
            case mb_substr($method, 0, 5) === 'where':
                $fieldName = lcfirst(mb_substr($method, 5, mb_strlen($method)));
                $method = 'where';
                break;

            case mb_substr($method, 0, 4) === 'join':
                $fieldName = lcfirst(mb_substr($method, 4, mb_strlen($method)));
                $method = 'join';
                break;

            case mb_substr($method, 0, 8) === 'searchBy':
                $fieldName = lcfirst(mb_substr($method, 8, mb_strlen($method)));
                $method = 'searchBy';
                break;

            case mb_substr($method, 0, 10) === 'searchLeft':
                $fieldName = lcfirst(mb_substr($method, 10, mb_strlen($method)));
                $method = 'searchLeft';
                break;

            default:
                throw new \BadMethodCallException(
                    "Undefined method '$method'. The method name must start with " .
                    'either where , join, searchBy!'
                );
        }

        if (empty($arguments) && $method === 'where') {
            throw new \BadMethodCallException(
                "Method '$method' need arguments field name and field value (for whereXXX)!"
            );
        }

        //$fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));

        if ($method === 'where' /*&& $this->_class->hasField($fieldName) || $this->_class->hasAssociation($fieldName)*/) {
            return (is_array($arguments[0])) ?
                $this->andWhere($this->expr()->in($this->currentAlias . '.' . $fieldName, $arguments[0]))
                :
                (null === $arguments[0] ?
                    $this->andWhere($this->currentAlias . '.' . $fieldName . ' IS NULL') :
                    $this->andWhere($this->currentAlias . '.' . $fieldName . ' = :' . $fieldName)
                        ->setParameter($fieldName, $arguments[0]));
        } elseif ($method === 'join' /*&& $this->_class->hasField($fieldName) || $this->_class->hasAssociation($fieldName)*/) {
            //return $this;
            return $this->innerJoin($this->currentAlias . '.' . $fieldName, $fieldName);
        } elseif ($method === 'searchBy') {
            return $this->searchBy($fieldName, $arguments[0], isset($arguments[1]) ? $arguments[1] : []);
        } elseif ($method === 'searchLeft') {
            return $this->searchLeft($fieldName, $arguments[0]);
        }

        throw new \BadMethodCallException(
            "Method '$method' not found!"
        );
    }

    public function searchLeft($fieldName, $searchStr)
    {
        return $this->andWhere($this->expr()->like($this->currentAlias . '.' . $fieldName, $this->expr()->literal($searchStr . '%')));
    }

    public function searchBy($fieldNames, $searchStr, $options = [])
    {
        return $this->search($searchStr, $fieldNames, $options);
    }

    protected function getSearchFields($params = [])
    {
        $fields = isset($params['fields']) && $params['fields'] ? $params['fields'] : ['id', 'title'];

        $searchFields = [];
        foreach ($fields as $field) {
            $searchFields[] = (mb_strpos($field, '.') === false) ? $this->currentAlias . '.' . $field : $field;
        }

        return $searchFields;
    }

    public function search($searchStr, $fields = [], $options = [])
    {
        if (!$searchStr) {
            return $this;
        }
        if (!is_array($fields) && $fields) {
            $fields = [$fields];
        }

        $words = $this->prepareWords($searchStr, $options);

        $searchExpr = $this->expr()->orx();
        foreach ($this->getSearchFields(['fields' => $fields]) as $field) {
            $wordsSearchExpr = $this->expr()->andx();
            foreach ($words as $word) {
                $wordsSearchExpr->add($this->expr()->like($field, $this->expr()->literal('%' . $word . '%')));
            }
            $searchExpr->add($wordsSearchExpr);
        }
        $this->andWhere($searchExpr);

        return $this;
    }

    protected function prepareWords($searchStr, $options = [])
    {
        $explodeWords = !isset($options['explodeWords']) || !$options['explodeWords'];

        $maxSearchString = 100;
        $maxSearchWords = 5;
        $minWordLength = 2;

        $sText = mb_substr(trim($searchStr), 0, $maxSearchString);
        // замена всех символов кроме букв и цифр на пробелы
        $sText = preg_replace("/[^\w\x7F-\xFF\s_]/", ' ', $sText);
        $sText = preg_replace('/ +/', '  ', $sText);

        if ($explodeWords) {
            $sText = preg_replace("/\s\S{1," . ($minWordLength - 1) . "}\s/", ' ', " $sText ");
            $sText = trim(preg_replace('/ +/', ' ', $sText));

            return array_slice(explode(' ', $sText), 0, $maxSearchWords);
        }

        return [$sText];
    }

    public function mapFromForm(FormInterface $form)
    {
        FormQueryBuilderMapper::create($form, $this)->addAll()->process();

        return $this;
    }

    public function getQueryWithRowNumHint($hintName, $distinct = false)
    {
        $query = $this->getQuery();
        $res = $this->getCountClone($distinct)->getQuery()->getOneOrNullResult();
        $query->setHint($hintName, $res['rownum']);

        return $query;
    }

    public function getCountClone($distinct = false)
    {
        $qbCount = clone $this;
        $qbCount->resetDQLPart('orderBy')
            ->select('COUNT(' . ($distinct ? 'DISTINCT ' : '') . $qbCount->getCurrentAlias() . '.id) as rownum');

        return $qbCount;
    }
}
