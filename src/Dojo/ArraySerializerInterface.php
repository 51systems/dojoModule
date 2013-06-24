<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dustin
 * Date: 23/06/13
 * Time: 5:13 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Dojo;


/**
 * Classes implementing this array can be serialized to and from arrays.
 *
 * @package Dojo
 */
interface ArraySerializerInterface
{

    /**
     * Converts an object to its array representation.
     *
     * @return array
     */
    function toArray();

    /**
     * Populates the object with the data in the array.
     * Implements a fluid interface.
     *
     * @param $data
     * @return ArraySerializerInterface
     */
    function fromArray($data);
}