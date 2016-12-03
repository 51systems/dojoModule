<?php

namespace Dojo\Controller;


use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Http\Response;
use Zend\Hydrator\HydratorPluginManager;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Hydrator\HydratorInterface;
use Zend\View\Model\JsonModel;
use Zend\Http\Request;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator as ZendPaginator;

/**
 * Class AbstractJsonRestController
 * @package Dojo\Controller
 *
 */
abstract class AbstractJsonRestController extends AbstractRestfulController
{
    /**
     * Static hydrator instance.
     * @var HydratorInterface
     */
    private $hydratorInstance;

    /**
     * The hydration manager that will be used by default to
     * construct the {@link $hydratorInstance}
     *
     * @var HydratorPluginManager
     */
    private $hydratorManager;

    /**
     * Returns the Entity manager to be used for database operations.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * Looks up the entity in the database.
     * If the entity does not exist, returns null.
     *
     * @param $id
     * @return mixed
     */
    abstract protected function getEntity($id);

    /**
     * AbstractJsonRestController constructor.
     * @param HydratorPluginManager $hydratorManager manager that will supply the hydrator
     */
    public function __construct(HydratorPluginManager $hydratorManager)
    {
        $this->hydratorManager = $hydratorManager;
    }

    /**
     * Returns the hydrator to use for object hydration.
     * @return HydratorInterface
     */
    protected function getHydrator()
    {
        if (!isset($this->hydratorInstance)) {
            $this->hydratorInstance = $this->hydratorManager->get(DoctrineObject::class);
        }

        return $this->hydratorInstance;
    }

    /**
     * Return single resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function get($id)
    {
        $entity = $this->getEntity($id);

        if ($entity == null) {
            /** @var Response $response */
            $response = $this->getResponse();

            if ($response->getStatusCode() == Response::STATUS_CODE_200)
                $response->setStatusCode(Response::STATUS_CODE_404);

            return $response;
        }
        
        return new JsonModel($this->getHydrator()->extract($entity));
    }

    /**
     * Update an existing resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return mixed
     */
    public function update($id, $data)
    {
        $entity = $this->getEntity($id);

        if ($entity == null) {
            /** @var Response $response */
            $response = $this->getResponse();

            if ($response->getStatusCode() == Response::STATUS_CODE_200)
                $response->setStatusCode(Response::STATUS_CODE_404);

            return $response;
        }

        $this->getHydrator()->hydrate($data, $entity);

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(Response::STATUS_CODE_200);

        return new JsonModel($this->getHydrator()->extract($entity));
    }

    /**
     * Delete an existing resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function delete($id)
    {
        $entity = $this->getEntity($id);

        if ($entity == null) {
            /** @var Response $response */
            $response = $this->getResponse();

            if ($response->getStatusCode() == Response::STATUS_CODE_200)
                $response->setStatusCode(Response::STATUS_CODE_404);

            return $response;
        }

        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(Response::STATUS_CODE_204);

        return $response;
    }

    /**
     * Preforms automatic results pagination that works with the dojox.data.JsonRestStore
     *
     * @param ZendPaginator|Query|QueryBuilder $p
     * @return ZendPaginator
     */
    protected function paginateResults ($p)
    {
        if ($p instanceof QueryBuilder) {
            $p = $p->getQuery();
        }

        if ($p instanceof Query) {
            $p = new ZendPaginator(new PaginatorAdapter(new ORMPaginator($p)));
        }

        if (!($p instanceof ZendPaginator)) {
            throw new \InvalidArgumentException('expected Zend\Paginator\Paginator, got ' . get_class($p));
        }

        //handle the pagination
        /** @var Request $request */
        $request = $this->getRequest();
        $range = $request->getHeader('Range');

        if ($range) {
            //We have a range specified
            if (preg_match('/items=(?P<start>[\d]+)-(?P<end>[\d]+)/i', $range->toString(), $regs)) {
                $itemsPerPage = ($regs['end'] - $regs['start']);
                $currentPage = ceil($regs['start'] / $itemsPerPage);

                $p->setItemCountPerPage($itemsPerPage);
                $p->setCurrentPageNumber($currentPage);
            }
        } else {
            //the range was not specified, put all of them on a single page
            $p->setItemCountPerPage($p->getTotalItemCount());
        }

        $start = $p->getItemCountPerPage() * ($p->getCurrentPageNumber()-1);
        $end = $p->getTotalItemCount() < $p->getItemCountPerPage()?
            $p->getTotalItemCount() :
            $p->getItemCountPerPage() * $p->getCurrentPageNumber();

        /** @var Response $response */
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Range', sprintf('items %d-%d/%d',
            $start,
            $end,
            $p->getTotalItemCount()
        ));


        return $p;
    }
}