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
 * <http://www.phpdoctrine.com>.
 */

/**
 * Doctrine_Export
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen
 * @license     LGPL
 */
class Doctrine_Export {
    private $conn;
    
    private $dbh;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->dbh  = $conn->getDBH();
    }
    public function export() {
        $parent = new ReflectionClass('Doctrine_Record');
        $conn   = Doctrine_Manager::getInstance()->getCurrentConnection();
        $old    = $conn->getAttribute(Doctrine::ATTR_CREATE_TABLES);

        $conn->setAttribute(Doctrine::ATTR_CREATE_TABLES, true);
        
        foreach(get_declared_classes() as $name) {
            $class = new ReflectionClass($name);

            if($class->isSubclassOf($parent) && ! $class->isAbstract())
                $obj = new $class();
        }
        $conn->setAttribute(Doctrine::ATTR_CREATE_TABLES, $old);
    }
}
