<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dustin
 * Date: 23/06/13
 * Time: 5:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Dojo\Controller;


use Dojo\ArraySerializerInterface;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

/**
 * Class AbstractJsonRestController
 * @package Dojo\Controller
 *
 */
abstract class AbstractJsonRestController extends AbstractRestfulController
{

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
     * @return ArraySerializerInterface
     */
    abstract protected function getEntity($id);

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
        
        return new JsonModel($entity->toArray());
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
        
        $entity->fromArray($data);

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setStatusCode(Response::STATUS_CODE_200);

        return new JsonModel($entity->toArray());
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
}