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
 * Doctrine_Ticket_1436_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1436_TestCase extends Doctrine_UnitTestCase
{
    public function prepareTables()
    {
        parent::prepareTables();
    }
    
    public function prepareData()
    {
        $user = new User();
        $user->name = 'John';
        $user->save();
        
        # Create existing groups
        $group = new Group();
        $group->name = 'Group One';
        $group->save();
        $this->group_one = $group['id'];
        
        $group = new Group();
        $group->name = 'Group Two';
        $group->save();
        $this->group_two = $group['id'];
        
        $group = new Group();
        $group->name = 'Group Three';
        $group->save();
        $this->group_three = $group['id'];
    }
    
    public function testSynchronizeAddMNLinks()
    {
        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $userArray = array(
            'Group' => array(
                '_identifiers' => array(
                    $this->group_one,
                    $this->group_two,
                    $this->group_three
                    )
            ));

        $user->synchronizeWithArray($userArray);
        $this->assertEqual($user->Group->count(), 3);
        $user->save();
        $user->free();

        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $this->assertEqual($user->Group->count(), 3);
        $this->assertEqual($user->Group[0]->name, 'Group One');
        $this->assertEqual($user->Group[1]->name, 'Group Two');
        $this->assertEqual($user->Group[2]->name, 'Group Three');
    }

    public function testSynchronizeRemoveMNLinks()
    {
        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $userArray = array(
            'Group' => array(
                '_identifiers' => array(
                    $this->group_three
                    )
            ));

        $user->synchronizeWithArray($userArray);
        $this->assertEqual($user->Group->count(), 1);
        $user->save();
        $user->free();

        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $this->assertEqual($user->Group->count(), 1);
        $this->assertEqual($user->Group[0]->name, 'Group Three');
    }

    public function testSynchronizeRemoveAllLinks()
    {
        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $userArray = array(
            'Group' => array(
                '_identifiers' => array()
            ));

        $user->synchronizeWithArray($userArray);
        $this->assertEqual($user->Group->count(), 0);
        $user->save();
        $user->free();

        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $this->assertEqual($user->Group->count(), 0);
    }

    public function testSynchronizeDoesNotPersistUntilSave()
    {
        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $userArray = array(
            'Group' => array(
                '_identifiers' => array(
                    $this->group_three
                    )
            ));

        $user->synchronizeWithArray($userArray);
        $this->assertEqual($user->Group->count(), 1);
        $user->free();

        $user = Doctrine_Query::create()->from('User u, u.Group g')->fetchOne();
        $this->assertEqual($user->Group->count(), 0);
    }
}