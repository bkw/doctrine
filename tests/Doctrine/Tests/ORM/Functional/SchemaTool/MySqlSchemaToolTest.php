<?php

namespace Doctrine\Tests\ORM\Functional\SchemaTool;

use Doctrine\ORM\Tools\SchemaTool,
    Doctrine\ORM\Mapping\ClassMetadata;

require_once __DIR__ . '/../../../TestInit.php';

class MySqlSchemaToolTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp() {
        parent::setUp();
        if ($this->_em->getConnection()->getDatabasePlatform()->getName() !== 'mysql') {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of mysql.');
        }
    }
    
    public function testGetCreateSchemaSql()
    {
        $classes = array(
            $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsAddress'),
            $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsUser'),
            $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsPhonenumber'),
        );

        $tool = new SchemaTool($this->_em);
        $sql = $tool->getCreateSchemaSql($classes);
        $this->assertEquals(count($sql), 8);
        $this->assertEquals("CREATE TABLE cms_addresses (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(50) NOT NULL, zip VARCHAR(50) NOT NULL, city VARCHAR(50) NOT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
        $this->assertEquals("CREATE TABLE cms_users_groups (user_id INT DEFAULT NULL, group_id INT DEFAULT NULL, PRIMARY KEY(user_id, group_id)) ENGINE = InnoDB", $sql[1]);
        $this->assertEquals("CREATE TABLE cms_users (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, username VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[2]);
        $this->assertEquals("CREATE TABLE cms_phonenumbers (phonenumber VARCHAR(50) NOT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(phonenumber)) ENGINE = InnoDB", $sql[3]);
        $this->assertEquals("ALTER TABLE cms_addresses ADD FOREIGN KEY (user_id) REFERENCES cms_users(id)", $sql[4]);
        $this->assertEquals("ALTER TABLE cms_users_groups ADD FOREIGN KEY (user_id) REFERENCES cms_users(id)", $sql[5]);
        $this->assertEquals("ALTER TABLE cms_users_groups ADD FOREIGN KEY (group_id) REFERENCES cms_groups(id)", $sql[6]);
        $this->assertEquals("ALTER TABLE cms_phonenumbers ADD FOREIGN KEY (user_id) REFERENCES cms_users(id)", $sql[7]);
    }
    
    public function testGetCreateSchemaSql2()
    {
        $classes = array(
            $this->_em->getClassMetadata('Doctrine\Tests\Models\Generic\DecimalModel')
        );

        $tool = new SchemaTool($this->_em);
        $sql = $tool->getCreateSchemaSql($classes);
        
        $this->assertEquals(1, count($sql));
        $this->assertEquals("CREATE TABLE decimal_model (id INT AUTO_INCREMENT NOT NULL, decimal NUMERIC(2, 5) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
    }
    
    public function testGetCreateSchemaSql3()
    {
        $classes = array(
            $this->_em->getClassMetadata('Doctrine\Tests\Models\Generic\BooleanModel')
        );

        $tool = new SchemaTool($this->_em);
        $sql = $tool->getCreateSchemaSql($classes);
        
        $this->assertEquals(1, count($sql));
        $this->assertEquals("CREATE TABLE boolean_model (id INT AUTO_INCREMENT NOT NULL, boolean DEFAULT 1 NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
    }
    
    public function testGetUpdateSchemaSql()
    {
        $classes = array(
            $this->_em->getClassMetadata(__NAMESPACE__ . '\SchemaToolEntityA')
        );
        
        $tool = new SchemaTool($this->_em);
        
        $tool->createSchema($classes);
        
        // Add field to SchemaToolEntityA
        $classA = $classes[0];
        $classA->mapField(array(
            'fieldName' => 'newField',
            'columnName' => 'new_field',
            'type' => 'string',
            'length' => 50,
            'nullable' => false
        ));
        
        // Introduce SchemaToolEntityB
        $classB = new ClassMetadata(__NAMESPACE__ . '\SchemaToolEntityB');
        $classB->setTableName('schematool_entity_b');
        $classB->mapField(array(
            'fieldName' => 'id',
            'columnName' => 'id',
            'type' => 'integer',
            'nullable' => false,
            'id' => true
        ));
        $classB->mapField(array(
            'fieldName' => 'field',
            'columnName' => 'field',
            'type' => 'string',
            'nullable' => false
        ));
        $classes[] = $classB;
        
        $sql = $tool->getUpdateSchemaSql($classes);
        
        $this->assertEquals(2, count($sql));
        $this->assertEquals("CREATE TABLE schematool_entity_b (id INT NOT NULL, field VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
        $this->assertEquals("ALTER TABLE schematool_entity_a ADD new_field VARCHAR(50) NOT NULL", $sql[1]);
        
    }
}

/** @Entity @Table(name="schematool_entity_a") */
class SchemaToolEntityA {
    /** @Id @Column(type="integer") */
    private $id;
    private $newField;
}

class SchemaToolEntityB {
    private $id;
    private $field;
}

