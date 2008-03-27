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
 * Doctrine_Adapter_Resource
 *
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @package     Doctrine
 * @subpackage  Adapter
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 2702 $
 */
class Doctrine_Adapter_Resource implements Doctrine_Adapter_Interface 
{
    protected $resourceUrl;
    
    public function __construct($resourceUrl)
    {
        $this->resourceUrl = $resourceUrl;
    }
    
    public function prepare($sql)
    {
    
    }
    
    public function query($sql)
    {
    
    }
    
    public function quote($input)
    {

    }

    public function exec($sql)
    {

    }
    
    public function lastInsertId()
    {
    
    }
    
    public function beginTransaction()
    {
    
    }
    
    public function commit()
    {
    
    }

    public function rollBack()
    {
    
    }
    
    public function errorCode()
    {
    
    }

    public function errorInfo()
    {
    
    }
    
    public function getAttribute()
    {
        return 'sqlite';
    }
    
    public function setAttribute()
    {
        
    }
    
    public function sqliteCreateFunction()
    {
        
    }
}