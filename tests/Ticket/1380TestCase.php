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
 * Doctrine_Ticket_1380_TestCase
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_1380_TestCase extends Doctrine_UnitTestCase 
{
    public function testCalculatedColumnsHydratedInToFirstEncounteredComponent()
    {
        $q = Doctrine_Query::create()
            ->select('u.*, p.*, a.*, COUNT(a.id) + COUNT(p.id) AS test_calculated_column')
            ->from('User u')
            ->leftJoin('u.Phonenumber p')
            ->leftJoin('u.Album a')
            ->limit(1);
        $user = $q->fetchOne();
        $this->assertTrue(isset($user['Album'][0]['test_calculated_column']));
    }

    public function testCalculatedColumnHydratedInToRootAlso()
    {
        $q = Doctrine_Query::create()
            ->select('u.*, p.*, a.*, COUNT(a.id) + COUNT(p.id) AS test_calculated_column')
            ->from('User u')
            ->leftJoin('u.Phonenumber p')
            ->leftJoin('u.Album a')
            ->limit(1);
        $user = $q->fetchOne();
        $this->assertTrue(isset($user['test_calculated_column']));
    }
}