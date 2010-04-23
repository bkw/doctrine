<?php

namespace Doctrine\Tests\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Mapping\ClassMetadataInfo,
    Doctrine\ORM\Mapping\Driver\XmlDriver,
    Doctrine\ORM\Mapping\Driver\YamlDriver;

require_once __DIR__ . '/../../TestInit.php';

abstract class AbstractMappingDriverTest extends \Doctrine\Tests\OrmTestCase
{
    abstract protected function _loadDriver();

    public function testLoadMapping()
    {
        $className = 'Doctrine\Tests\ORM\Mapping\User';
        $mappingDriver = $this->_loadDriver();

        $class = new ClassMetadata($className);
        $mappingDriver->loadMetadataForClass($className, $class);

        return $class;
    }

    /**
     * @depends testLoadMapping
     * @param ClassMetadata $class
     */
    public function testEntityTableNameAndInheritance($class)
    {
        $this->assertEquals('cms_users', $class->getTableName());
        $this->assertEquals(ClassMetadata::INHERITANCE_TYPE_NONE, $class->inheritanceType);

        return $class;
    }

    /**
     * @depends testEntityTableNameAndInheritance
     * @param ClassMetadata $class
     */
    public function testFieldMappings($class)
    {
        $this->assertEquals(3, count($class->fieldMappings));
        $this->assertTrue(isset($class->fieldMappings['id']));
        $this->assertTrue(isset($class->fieldMappings['name']));
        $this->assertTrue(isset($class->fieldMappings['email']));

        $this->assertEquals('string', $class->fieldMappings['name']['type']);
        $this->assertTrue($class->fieldMappings['name']['nullable']);
        $this->assertTrue($class->fieldMappings['name']['unique']);
        $this->assertEquals("user_email", $class->fieldMappings['email']['columnName']);

        return $class;
    }

    /**
     * @depends testFieldMappings
     * @param ClassMetadata $class
     */
    public function testIdentifier($class)
    {
        $this->assertEquals(array('id'), $class->identifier);
        $this->assertEquals(ClassMetadata::GENERATOR_TYPE_AUTO, $class->generatorType, "ID-Generator is not ClassMetadata::GENERATOR_TYPE_AUTO");

        return $class;
    }

    /**
     * @depends testIdentifier
     * @param ClassMetadata $class
     */
    public function testAssocations($class)
    {
        $this->assertEquals(3, count($class->associationMappings));

        return $class;
    }

    /**
     * @depends testAssocations
     * @param ClassMetadata $class
     */
    public function testOwningOneToOneAssocation($class)
    {
        $this->assertTrue($class->associationMappings['address'] instanceof \Doctrine\ORM\Mapping\OneToOneMapping);
        $this->assertTrue(isset($class->associationMappings['address']));
        $this->assertTrue($class->associationMappings['address']->isOwningSide);
        $this->assertEquals('user', $class->associationMappings['address']->inversedBy);
        // Check cascading
        $this->assertTrue($class->associationMappings['address']->isCascadeRemove);
        $this->assertFalse($class->associationMappings['address']->isCascadePersist);
        $this->assertFalse($class->associationMappings['address']->isCascadeRefresh);
        $this->assertFalse($class->associationMappings['address']->isCascadeDetach);
        $this->assertFalse($class->associationMappings['address']->isCascadeMerge);

        return $class;
    }

    /**
     * @depends testOwningOneToOneAssocation
     * @param ClassMetadata $class
     */
    public function testInverseOneToManyAssociation($class)
    {
        $this->assertTrue($class->associationMappings['phonenumbers'] instanceof \Doctrine\ORM\Mapping\OneToManyMapping);
        $this->assertTrue(isset($class->associationMappings['phonenumbers']));
        $this->assertFalse($class->associationMappings['phonenumbers']->isOwningSide);
        $this->assertTrue($class->associationMappings['phonenumbers']->isCascadePersist);
        $this->assertFalse($class->associationMappings['phonenumbers']->isCascadeRemove);
        $this->assertFalse($class->associationMappings['phonenumbers']->isCascadeRefresh);
        $this->assertFalse($class->associationMappings['phonenumbers']->isCascadeDetach);
        $this->assertFalse($class->associationMappings['phonenumbers']->isCascadeMerge);

        // Test Order By
        $this->assertEquals(array('number' => 'ASC'), $class->associationMappings['phonenumbers']->orderBy);

        return $class;
    }

    /**
     * @depends testInverseOneToManyAssociation
     * @param ClassMetadata $class
     */
    public function testManyToManyAssociationWithCascadeAll($class)
    {
        $this->assertTrue($class->associationMappings['groups'] instanceof \Doctrine\ORM\Mapping\ManyToManyMapping);
        $this->assertTrue(isset($class->associationMappings['groups']));
        $this->assertTrue($class->associationMappings['groups']->isOwningSide);
        // Make sure that cascade-all works as expected
        $this->assertTrue($class->associationMappings['groups']->isCascadeRemove);
        $this->assertTrue($class->associationMappings['groups']->isCascadePersist);
        $this->assertTrue($class->associationMappings['groups']->isCascadeRefresh);
        $this->assertTrue($class->associationMappings['groups']->isCascadeDetach);
        $this->assertTrue($class->associationMappings['groups']->isCascadeMerge);

        $this->assertNull($class->associationMappings['groups']->orderBy);

        return $class;
    }

    /**
     * @depends testManyToManyAssociationWithCascadeAll
     * @param ClassMetadata $class
     */
    public function testLifecycleCallbacks($class)
    {
        $this->assertEquals(count($class->lifecycleCallbacks), 2);
        $this->assertEquals($class->lifecycleCallbacks['prePersist'][0], 'doStuffOnPrePersist');
        $this->assertEquals($class->lifecycleCallbacks['postPersist'][0], 'doStuffOnPostPersist');

        return $class;
    }

    /**
     * @depends testManyToManyAssociationWithCascadeAll
     * @param ClassMetadata $class
     */
    public function testLifecycleCallbacksSupportMultipleMethodNames($class)
    {
        $this->assertEquals(count($class->lifecycleCallbacks['prePersist']), 2);
        $this->assertEquals($class->lifecycleCallbacks['prePersist'][1], 'doOtherStuffOnPrePersistToo');

        return $class;
    }

    /**
     * @depends testLifecycleCallbacksSupportMultipleMethodNames
     * @param ClassMetadata $class
     */
    public function testJoinColumnUniqueAndNullable($class)
    {
        // Non-Nullability of Join Column
        $this->assertFalse($class->associationMappings['groups']->joinTable['joinColumns'][0]['nullable']);
        $this->assertFalse($class->associationMappings['groups']->joinTable['joinColumns'][0]['unique']);

        return $class;
    }

    /**
     * @depends testJoinColumnUniqueAndNullable
     * @param ClassMetadata $class
     */
    public function testColumnDefinition($class)
    {
        $this->assertEquals("CHAR(32) NOT NULL", $class->fieldMappings['email']['columnDefinition']);
        $this->assertEquals("INT NULL", $class->associationMappings['groups']->joinTable['inverseJoinColumns'][0]['columnDefinition']);

        return $class;
    }

    /**
     * @depends testColumnDefinition
     * @param ClassMetadata $class
     */
    public function testJoinColumnOnDeleteAndOnUpdate($class)
    {
        $this->assertEquals('CASCADE', $class->associationMappings['address']->joinColumns[0]['onDelete']);
        $this->assertEquals('CASCADE', $class->associationMappings['address']->joinColumns[0]['onUpdate']);

        return $class;
    }
}

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="cms_users")
 */
class User
{
    /** @Id @Column(type="integer") @generatedValue(strategy="AUTO") */
    public $id;

    /**
     * @Column(length=50, nullable=true, unique=true)
     */
    public $name;

    /**
     * @Column(name="user_email", columnDefinition="CHAR(32) NOT NULL")
     */
    public $email;

    /**
     * @OneToOne(targetEntity="Address", cascade={"remove"}, inversedBy="user")
     * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE")
     */
    public $address;

    /**
     * @OneToMany(targetEntity="Phonenumber", mappedBy="user", cascade={"persist"})
     * @OrderBy({"number"="ASC"})
     */
    public $phonenumbers;

    /**
     * @ManyToMany(targetEntity="Group", cascade={"all"})
     * @JoinTable(name="cms_user_groups",
     *    joinColumns={@JoinColumn(name="user_id", referencedColumnName="id", nullable=false, unique=false)},
     *    inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id", columnDefinition="INT NULL")}
     * )
     */
    public $groups;


    /**
     * @PrePersist
     */
    public function doStuffOnPrePersist()
    {
    }

    /**
     * @PrePersist
     */
    public function doOtherStuffOnPrePersistToo() {
    }

    /**
     * @PostPersist
     */
    public function doStuffOnPostPersist()
    {

    }

    public static function loadMetadata(ClassMetadataInfo $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(array(
           'name' => 'cms_users',
          ));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->addLifecycleCallback('doStuffOnPrePersist', 'prePersist');
        $metadata->addLifecycleCallback('doOtherStuffOnPrePersistToo', 'prePersist');
        $metadata->addLifecycleCallback('doStuffOnPostPersist', 'postPersist');
        $metadata->mapField(array(
           'id' => true,
           'fieldName' => 'id',
           'type' => 'integer',
           'columnName' => 'id',
          ));
        $metadata->mapField(array(
           'fieldName' => 'name',
           'type' => 'string',
           'length' => 50,
           'unique' => true,
           'nullable' => true,
           'columnName' => 'name',
          ));
        $metadata->mapField(array(
           'fieldName' => 'email',
           'type' => 'string',
           'columnName' => 'user_email',
           'columnDefinition' => 'CHAR(32) NOT NULL',
          ));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
        $metadata->mapOneToOne(array(
           'fieldName' => 'address',
           'targetEntity' => 'Doctrine\\Tests\\ORM\\Mapping\\Address',
           'cascade' => 
           array(
           0 => 'remove',
           ),
           'mappedBy' => NULL,
           'inversedBy' => 'user',
           'joinColumns' => 
           array(
           0 => 
           array(
            'name' => 'address_id',
            'referencedColumnName' => 'id',
            'onDelete' => 'CASCADE',
            'onUpdate' => 'CASCADE'
           ),
           ),
           'orphanRemoval' => false,
          ));
        $metadata->mapOneToMany(array(
           'fieldName' => 'phonenumbers',
           'targetEntity' => 'Doctrine\\Tests\\ORM\\Mapping\\Phonenumber',
           'cascade' => 
           array(
           1 => 'persist',
           ),
           'mappedBy' => 'user',
           'orphanRemoval' => false,
           'orderBy' => 
           array(
           'number' => 'ASC',
           ),
          ));
        $metadata->mapManyToMany(array(
           'fieldName' => 'groups',
           'targetEntity' => 'Doctrine\\Tests\\ORM\\Mapping\\Group',
           'cascade' => 
           array(
           0 => 'remove',
           1 => 'persist',
           2 => 'refresh',
           3 => 'merge',
           4 => 'detach',
           ),
           'mappedBy' => NULL,
           'joinTable' => 
           array(
           'name' => 'cms_users_groups',
           'joinColumns' => 
           array(
            0 => 
            array(
            'name' => 'user_id',
            'referencedColumnName' => 'id',
            'unique' => false,
            'nullable' => false,
            ),
           ),
           'inverseJoinColumns' => 
           array(
            0 => 
            array(
            'name' => 'group_id',
            'referencedColumnName' => 'id',
            'columnDefinition' => 'INT NULL',
            ),
           ),
           ),
           'orderBy' => NULL,
          ));
    }
}