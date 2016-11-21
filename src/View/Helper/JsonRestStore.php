<?php

namespace Dojo\View\Helper;

/**
 * Creates a declariative JsonRestStore.
 *
 * @author Dustin Thomson <dthomson@51systems.com>
 */
class JsonRestStore extends CustomDijit
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dojox/data/JsonRestStore';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dojox/data/JsonRestStore';

    /**
     * Creates a new jsonrestStore instance
     *
     * @param string $id
     * @param string $url Endpoint URL for the store
     * @param array $params
     * @param array $attribs
     * @return string
     */
    public function __invoke($id, $url, array $params = array(), array $attribs = array())
    {
        if(!isset($params['target'])){
            $params['target'] = $url;
        }

        if(!isset($params['jsId'])){
            $params['jsId'] = $id;
        }

        return $this->_createLayoutContainer($id, null, $params, $attribs);
    }
}