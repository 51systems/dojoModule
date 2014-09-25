<?php

namespace Dojo\Controller;


use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\View\Model\JsonModel;
use Zend\Http\Request;
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
    private static $hydratorInstance;

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
     * Returns the hydrator to use for object hydration.
     * @return HydratorInterface
     */
    protected function getHydrator()
    {
        if (!isset(self::$hydratorInstance)) {
            self::$hydratorInstance = new DoctrineObject($this->getEntityManager());
        }

        return self::$hydratorInstance;
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
     * @param ZendPaginator $p
     * @return ZendPaginator
     */
    protected function paginateResults (ZendPaginator $p)
    {
        //handle the pagination
        /** @var Request $request */
        $request = $this->getRequest();
        $range = $request->getHeader('Range');
        if ($range) {
            //We have a range specified
            if (preg_match('/items=(?P<start>[\d]+)-(?P<end>[\d]+)/i', $range->toString(), $regs)) {
                $itemsPerPage = ($regs['end'] - $regs['start']) + 1;
                $currentPage = ($regs['start'] / $itemsPerPage) + 1;

                $p->setItemCountPerPage($itemsPerPage);
                $p->setCurrentPageNumber($currentPage);

                /** @var Response $response */
                $response = $this->getResponse();
                $response->getHeaders()->addHeaderLine('Content-Range', sprintf('items %d-%d/%d',
                    $regs['start'],
                    $regs['end'],
                    $p->getTotalItemCount()
                ));
            }
        }

        return $p;
    }
}