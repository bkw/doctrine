<?php

/*
 *  $Id: Query.php 3938 2008-03-06 19:36:50Z romanb $
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
     * @todo [TODO]
     *
     * Doctrine_Hydrator::setTableAliasMap (refactoring in hydrateResultSet)
     * Doctrine_Hydrator::setQueryComponents (API changes)
     * Document Doctrine_Query_Cache
     * Figure out the possibility to remove code in _execute2 (documented as to-do item)
     *
     */


class Doctrine_Query_Cache
{
    protected $_result;
    protected $_queryComponents;
    protected $_tableAliasMap;


    public function __construct($result, $queryComponents, $tableAliasMap)
    {
        $this->_result = $result;
        $this->_queryComponents = $queryComponents;
        $this->_tableAliasMap = $tableAliasMap;
    }


    public static function fromResultSet($query, $result = false)
    {
        $parser = $query->getParser();
        $componentInfo = array();

        foreach ($parser->getQueryComponents() as $alias => $components) {
            if ( ! isset($components['parent'])) {
                $componentInfo[$alias][] = $components['mapper']->getComponentName();
                //$componentInfo[$alias][] = $components['mapper']->getComponentName();
            } else {
                $componentInfo[$alias][] = $components['parent'] . '.' . $components['relation']->getAlias();
            }
            if (isset($components['agg'])) {
                $componentInfo[$alias][] = $components['agg'];
            }
            if (isset($components['map'])) {
                $componentInfo[$alias][] = $components['map'];
            }
        }

        return new self($result, $componentInfo, $parser->getTableAliasMap());
    }


    public static function fromCachedForm($query, $cached = false)
    {
        $cached = unserialize($cached);
        $cachedComponents = $cached[1];

        $queryComponents = array();

        foreach ($cachedComponents as $alias => $components) {
            $e = explode('.', $components[0]);

            if (count($e) === 1) {
                $queryComponents[$alias]['mapper'] = $query->getConnection()->getMapper($e[0]);
                $queryComponents[$alias]['table'] = $queryComponents[$alias]['mapper']->getTable();
            } else {
                $queryComponents[$alias]['parent'] = $e[0];
                $queryComponents[$alias]['relation'] = $queryComponents[$e[0]]['table']->getRelation($e[1]);
                $queryComponents[$alias]['mapper'] = $query->getConnection()->getMapper($queryComponents[$alias]['relation']->getForeignComponentName());
                $queryComponents[$alias]['table'] = $queryComponents[$alias]['mapper']->getTable();
            }

            if (isset($v[1])) {
                $queryComponents[$alias]['agg'] = $components[1];
            }

            if (isset($v[2])) {
                $queryComponents[$alias]['map'] = $components[2];
            }
        }

        return new self($cached[0], $queryComponents, $cached[2]);
    }


    public function toCachedForm()
    {
        return serialize(array(
            $this->getResult(),
            $this->getQueryComponents(),
            $this->getTableAliasMap()
        ));
    }


    public function getResult()
    {
        return $this->_result;
    }


    public function getTableAliasMap()
    {
        return $this->_tableAliasMap;
    }


    public function getQueryComponents()
    {
        return $this->_queryComponents;
    }
}