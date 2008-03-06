<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_Access
 *
 * the purpose of Doctrine_Access is to provice array access
 * and property overload interface for subclasses
 *
 * @package     Doctrine
 * @subpackage  Access
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
abstract class Doctrine_Access extends Doctrine_Locator_Injectable implements ArrayAccess
{
    /**
     * setArray
     *
     * @param array $array          an array of key => value pairs
     * @since 1.0
     * @return Doctrine_Access
     */
    public function setArray(array $array)
    {
        foreach ($array as $k=>$v) {
            $this->set($k,$v);
        }

        return $this;
    }

    /**
     * __set        an alias of set()
     *
     * @see set, offsetSet
     * @param $name
     * @param $value
     * @since 1.0
     * @return void
     */
    public function __set($name,$value)
    {
        $this->set($name,$value);
    }

    /**
     * __get -- an alias of get()
     *
     * @see get,  offsetGet
     * @param mixed $name
     * @since 1.0
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * __isset()
     *
     * @param string $name
     * @since 1.0
     * @return boolean          whether or not this object contains $name
     */
    public function __isset($name)
    {
        return $this->contains($name);
    }

    /**
     * __unset()
     *
     * @param string $name
     * @since 1.0
     * @return void
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * Check if an offsetExists. Alias for contains.
     *
     * @param mixed $offset
     * @return boolean          whether or not this object contains $offset
     */
    public function offsetExists($offset)
    {
        return $this->contains($offset);
    }

    /**
     * offsetGet    an alias of get()
     *
     * @see get,  __get
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * sets $offset to $value
     * @see set,  __set
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            return $this->add($value);
        }
        return $this->set($offset, $value);
    }

    /**
     * unset a given offset
     * @see set, offsetSet, __set
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    /**
     * Remove the element with the specified offset
     *
     * @param mixed $offset The offset to remove
     * @return boolean True if removed otherwise false
     */
    public function remove($offset)
    {
        throw new Doctrine_Exception('Remove is not supported for ' . get_class($this));
    }

    /**
     * Return the element with the specified offset
     *
     * @param mixed $offset The offset to return
     * @return mixed The value of the return object 
     */
    public function get($offset)
    {
        throw new Doctrine_Exception('Get is not supported for ' . get_class($this));
    }

    /**
     * Set the offset to the value
     *
     * @param mixed $offset The offset to set
     * @param mixed $value The value to set the offset to
     *
     */
    public function set($offset, $value)
    {
        throw new Doctrine_Exception('Set is not supported for ' . get_class($this));
    }


    /**
     * Check if the specified offset exists 
     * 
     * @param mixed $offset The offset to check
     * @return boolean True if exists otherwise false
     */
    public function contains($offset)
    {
        throw new Doctrine_Exception('Contains is not supported for ' . get_class($this));
    }


    /**
     * Add the value  
     * 
     * @param mixed $value The value to add 
     * @return void
     */
    public function add($value)
    {
        throw new Doctrine_Exception('Add is not supported for ' . get_class($this));
    }
}
