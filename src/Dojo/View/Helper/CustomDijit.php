<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CustomDijit.php 23953 2011-05-03 05:47:39Z ralph $
 */

namespace Dojo\View\Helper;

use Dojo\View\Exception\InvalidArgumentException;

/**
 * Arbitrary dijit support
 *
 * @uses       Zend_Dojo_View_Helper_DijitContainer
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class CustomDijit extends DijitContainer
{
    /**
     * Default dojoType; set the value when extending
     * @var string
     */
    protected $_defaultDojoType;

    /**
     * Render a custom dijit
     *
     * Requires that either the {@link $_defaultDojotype} property is set, or
     * that you pass a value to the "dojoType" key of the $params argument.
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params
     * @param  array $attribs
     * @return string|CustomDijit
     */
    public function customDijit($id = null, $value = null, array $params = array(), array $attribs = array())
    {
        if (null === $id) {
            return $this;
        }

        $this->_validateDojoType($params);

        if (array_key_exists('rootNode', $params)) {
            $this->setRootNode($params['rootNode']);
            unset($params['rootNode']);
        }

        return $this->_createLayoutContainer($id, $value, $params, $attribs);
    }

    /**
     * Validate the dojo data type.
     * @param $params
     * @throws \Dojo\View\Exception\InvalidArgumentException
     */
    private function _validateDojoType($params)
    {
        if (!array_key_exists('dojoType', $params) && !array_key_exists('data-dojo-type')
            && (null === $this->_defaultDojoType)
        ) {
            throw new InvalidArgumentException('No dojoType specified; cannot create dijit');
        } elseif (array_key_exists('dojoType', $params)) {
            $this->_dijit  = $params['dojoType'];
            $this->_module = $params['dojoType'];
            unset($params['dojoType']);
        } elseif (array_key_exists('data-dojo-type', $params)) {
            $this->_dijit  = $params['data-dojo-type'];
            $this->_module = $params['data-dojo-type'];
            unset($params['data-dojo-type']);
        } else {
            $this->_dijit  = $this->_defaultDojoType;
            $this->_module = $this->_defaultDojoType;
        }
    }

    /**
     * Begin capturing content.
     *
     * Requires that either the {@link $_defaultDojotype} property is set, or
     * that you pass a value to the "dojoType" key of the $params argument.
     *
     * @param  string $id
     * @param  array $params
     * @param  array $attribs
     * @return void
     */
    public function captureStart($id, array $params = array(), array $attribs = array())
    {
        $this->_validateDojoType($params);

        return parent::captureStart($id, $params, $attribs);
    }
}
