<?php
/**
 * @author Vitiko <vitiko@mail.ru>
 */

namespace Argayash\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Todo: move to entityController trait.
 */
abstract class EntityController extends RubricAwareController
{
    protected $entityName;

    public function __construct()
    {
        $this->entityName = $this->getDefaultEntityName();
    }

    abstract protected function getDefaultEntityName();

    /**
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $searchForm = $this->getSearchForm($request);

        return [
            'searchForm' => $searchForm ? $searchForm->createView() : null,
            'entities' => $this->paginate($this->getIndexQuery($searchForm)),
        ];
    }

    /**
     * Not Using Param converter cos can't use type hinting.
     *
     * @Template()
     */
    public function viewAction($id)
    {
        $entity = $this->getEntityBySlugOrId($id);

        if (!$this->viewCheckStatus($entity)) {
            throw  $this->createNotFoundException();
        }
        return [
            'title' => $entity ? (string) $entity : null,
            'entity' => $entity, ];
    }

    protected function viewCheckStatus($entity)
    {
        return $entity ? true : false;
    }

    protected function getSearchForm(Request $request)
    {
        return null;
    }

    protected function getEntityBySlugOrId($id)
    {
        $field = method_exists($this->getRepository()->getClassName(), 'getSlug') ? 'slug' : 'id';
        $entity = $this->getRepository()->findOneBy([$field => $id]);

        if (!$entity) {
            throw $this->createNotFoundException();
        }
        return $entity;
    }

    protected function getIndexQuery(Form $searchForm = null)
    {
        $qb = $this->getRepository()->createQueryBuilder('e');
        $this->prepareIndexQueryBuilder($qb, $searchForm);

        return  $qb->getQuery();
    }

    protected function prepareIndexQueryBuilder(QueryBuilder $qb, Form $searchForm = null)
    {
    }

    protected function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    protected function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return \Doctrine\Orm\EntityRepository
     */
    protected function getRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository($this->entityName);
    }

    /**
     * @param $alias
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder($alias)
    {
        return $this->getRepository()->createQueryBuilder($alias);
    }
}
