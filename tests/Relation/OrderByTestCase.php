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
 * Doctrine_Relation_OrderBy_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Relation_OrderBy_TestCase extends Doctrine_UnitTestCase 
{
    public function prepareTables()
    {
        $this->tables[] = 'OrderByTest_Article';
        $this->tables[] = 'OrderByTest_Friend';
        $this->tables[] = 'OrderByTest_Group';
        $this->tables[] = 'OrderByTest_User';
        $this->tables[] = 'OrderByTest_UserGroup';
        parent::prepareTables();
    }

    public function testTest()
    {
        $profiler = new Doctrine_Connection_Profiler();

    	$this->conn->addListener($profiler);

        $userTable = Doctrine::getTable('OrderByTest_User');
        $q = $userTable
            ->createQuery('u')
            ->leftJoin('u.Articles a')
            ->leftJoin('u.Groups g')
            ->leftJoin('u.Friends f')
            ->leftJoin('u.ChildrenUsers cu')
            ->leftJoin('u.ParentUser pu');

        $this->assertEqual($q->getSqlQuery(), 'SELECT o.id AS o__id, o.login AS o__login, o.password AS o__password, o.parent_user_id AS o__parent_user_id, o2.id AS o2__id, o2.title AS o2__title, o2.content AS o2__content, o2.user_id AS o2__user_id, o3.id AS o3__id, o3.name AS o3__name, o5.id AS o5__id, o5.login AS o5__login, o5.password AS o5__password, o5.parent_user_id AS o5__parent_user_id, o7.id AS o7__id, o7.login AS o7__login, o7.password AS o7__password, o7.parent_user_id AS o7__parent_user_id, o8.id AS o8__id, o8.login AS o8__login, o8.password AS o8__password, o8.parent_user_id AS o8__parent_user_id FROM order_by_test__user o LEFT JOIN order_by_test__article o2 ON o.id = o2.user_id LEFT JOIN order_by_test__user_group o4 ON (o.id = o4.user_id) LEFT JOIN order_by_test__group o3 ON o3.id = o4.group_id LEFT JOIN order_by_test__friend o6 ON (o.id = o6.user_id1 OR o.id = o6.user_id2) LEFT JOIN order_by_test__user o5 ON (o5.id = o6.user_id2 OR o5.id = o6.user_id1) AND o5.id != o.id LEFT JOIN order_by_test__user o7 ON o.id = o7.parent_user_id LEFT JOIN order_by_test__user o8 ON o.parent_user_id = o8.id ORDER BY o2.title ASC, o3.name ASC, o5.login ASC, o7.login ASC, o8.id ASC');

        $this->assertEqual($userTable->getRelation('Articles')->getRelationDql(1), 'FROM OrderByTest_Article WHERE OrderByTest_Article.user_id IN (?) ORDER BY OrderByTest_Article.title ASC');
        $this->assertEqual($userTable->getRelation('Groups')->getRelationDql(1), 'FROM OrderByTest_Group.OrderByTest_UserGroup WHERE OrderByTest_Group.OrderByTest_UserGroup.user_id IN (?) ORDER BY OrderByTest_Group.name ASC');
        $this->assertEqual($userTable->getRelation('Friends')->getRelationDql(1), 'FROM OrderByTest_User.OrderByTest_Friend WHERE OrderByTest_User.OrderByTest_Friend.user_id1 IN (?) ORDER BY OrderByTest_User.username ASC');
        $this->assertEqual($userTable->getRelation('ParentUser')->getRelationDql(1), 'FROM OrderByTest_User WHERE OrderByTest_User.id IN (?) ORDER BY OrderByTest_User.id ASC');
        $this->assertEqual($userTable->getRelation('ChildrenUsers')->getRelationDql(1), 'FROM OrderByTest_User WHERE OrderByTest_User.parent_user_id IN (?) ORDER BY OrderByTest_User.username ASC');

        $user = new OrderByTest_User();
        $user->username = 'jwage';
        $user->password = 'changeme';
        $user->save();

        $articles = $user->Articles;
        $this->assertEqual($profiler->pop()->getQuery(), 'SELECT o.id AS o__id, o.title AS o__title, o.content AS o__content, o.user_id AS o__user_id FROM order_by_test__article o WHERE (o.user_id IN (?)) ORDER BY o.title ASC');

        $groups = $user->Groups;
        $this->assertEqual($profiler->pop()->getQuery(), 'SELECT o.id AS o__id, o.name AS o__name, o2.user_id AS o2__user_id, o2.group_id AS o2__group_id FROM order_by_test__group o LEFT JOIN order_by_test__user_group o2 ON o.id = o2.group_id WHERE (o2.user_id IN (?)) ORDER BY o.name ASC');

        $friends = $user->Friends;
        $this->assertEqual($profiler->pop()->getQuery(), 'SELECT order_by_test__user.id AS order_by_test__user__id, order_by_test__user.login AS order_by_test__user__login, order_by_test__user.password AS order_by_test__user__password, order_by_test__user.parent_user_id AS order_by_test__user__parent_user_id, order_by_test__friend.user_id1 AS order_by_test__friend__user_id1, order_by_test__friend.user_id2 AS order_by_test__friend__user_id2 FROM order_by_test__user INNER JOIN order_by_test__friend ON order_by_test__user.id = order_by_test__friend.user_id2 OR order_by_test__user.id = order_by_test__friend.user_id1 WHERE order_by_test__user.id IN (SELECT user_id2 FROM order_by_test__friend WHERE user_id1 = ?) OR order_by_test__user.id IN (SELECT user_id1 FROM order_by_test__friend WHERE user_id2 = ?) ORDER BY order_by_test__user.id ASC, order_by_test__user.login ASC');

        $childrenUsers = $user->ChildrenUsers;
        $this->assertEqual($profiler->pop()->getQuery(), 'SELECT o.id AS o__id, o.login AS o__login, o.password AS o__password, o.parent_user_id AS o__parent_user_id FROM order_by_test__user o WHERE (o.parent_user_id IN (?)) ORDER BY o.login ASC');

        $parentUser = $user->ParentUser;
        $this->assertEqual($profiler->pop()->getQuery(), 'SELECT o.id AS o__id, o.login AS o__login, o.password AS o__password, o.parent_user_id AS o__parent_user_id FROM order_by_test__user o WHERE (o.parent_user_id IN (?)) ORDER BY o.login ASC');
    }
}

class OrderByTest_Article extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('content', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             ));
    }

    public function setUp()
    {
        $this->hasOne('OrderByTest_User as User', array(
             'local' => 'user_id',
             'foreign' => 'id'));
    }
}

class OrderByTest_Friend extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('user_id1', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('user_id2', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
    }
}

class OrderByTest_Group extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
    }

    public function setUp()
    {
        $this->hasMany('OrderByTest_User as User', array(
             'refClass' => 'OrderByTest_UserGroup',
             'local' => 'group_id',
             'foreign' => 'user_id'));
    }
}

class OrderByTest_User extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('login AS username', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('password', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('parent_user_id', 'integer');
    }

    public function setUp()
    {
        $this->hasMany('OrderByTest_Article as Articles', array(
             'local' => 'id',
             'foreign' => 'user_id',
             'orderBy' => 'title ASC'));

        $this->hasMany('OrderByTest_Group as Groups', array(
             'refClass' => 'OrderByTest_UserGroup',
             'local' => 'user_id',
             'foreign' => 'group_id',
             'orderBy' => 'name ASC'));

        $this->hasMany('OrderByTest_User as Friends', array(
             'refClass' => 'OrderByTest_Friend',
             'local' => 'user_id1',
             'foreign' => 'user_id2',
             'equal' => true,
             'orderBy' => 'username ASC'));

        $this->hasOne('OrderByTest_User as ParentUser', array(
            'local' => 'parent_user_id',
            'foreign' => 'id',
            'orderBy' => 'id ASC'));

        $this->hasMany('OrderByTest_User as ChildrenUsers', array(
            'local' => 'id',
            'foreign' => 'parent_user_id',
            'orderBy' => 'username ASC'));
    }
}

class OrderByTest_UserGroup extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('group_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
    }
}