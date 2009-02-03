<?php

class Doctrine_Ticket_1876b_TestCase extends Doctrine_UnitTestCase
{
    public function init()
    {
        Doctrine_Manager::connection('mysql://root:password@localhost/doctrine', 'Mysql');
        $this->driverName = 'Mysql';
        parent::init();
        Doctrine_Manager::connection('mysql://root:password@localhost/doctrine', 'Mysql');
        $this->prepareTables();
        $this->prepareData();
    }

    public function run(DoctrineTest_Reporter $reporter = null, $filter = null)
    {
      parent::run($reporter, $filter);
      $this->manager->closeConnection($this->connection);
    }

    public function prepareData() 
    {
    }
    
    public function prepareTables() 
    {
        try {
            $this->conn->exec('DROP TABLE t1876_recipe_ingredient');
            $this->conn->exec('DROP TABLE t1876_recipe');
            $this->conn->exec('DROP TABLE t1876_company');
        } catch(Doctrine_Connection_Exception $e) {
        }
        
        $this->tables = array(
            'T1876_Recipe', 'T1876_Company', 'T1876_RecipeIngredient'
        );
        
        parent::prepareTables();
    }

    public function testDuplicatedParamsInSubQuery()
    {
        $this->connection->setAttribute('use_dql_callbacks', true);

        for ($i = 0; $i < 2; $i++) {
            $company = new T1876_Company();
            $company->name = 'Test Company ' . ($i + 1);
            $company->save($this->connection);
        }
        
        for ($i = 0; $i < 10; $i++) {
            $recipe = new T1876_Recipe();
            
            $recipe->name = 'test ' . $i;
            $recipe->company_id = ($i % 3 == 0) ? 1 : 2;
            $recipe->RecipeIngredients[]->name = 'test';
            
            $recipe->save($this->connection);
            
            if ($i % 2 == 0) {
                $recipe->delete($this->connection);
            }
        }

        try {
            $q = Doctrine_Query::create()
                ->from('T1876_Recipe r')
                ->leftJoin('r.Company c')
                ->leftJoin('r.RecipeIngredients')
                ->addWhere('c.id = ?', 2);
            
            $this->assertEqual(
                $q->getCountQuery(), 
                'SELECT COUNT(*) AS num_results FROM ('
                    . 'SELECT DISTINCT t.id FROM t1876__recipe t '
                    . 'LEFT JOIN t1876__company t2 ON t.company_id = t2.id AND t2.deleted_at IS NULL '
                    . 'LEFT JOIN t1876__recipe_ingredient t3 ON t.id = t3.recipe_id AND t3.deleted_at IS NULL '
                    . 'WHERE t2.id = ? AND (t.deleted_at IS NULL) GROUP BY t.id' 
                . ') AS dctrn_count_query'
            );
            $this->assertEqual($q->count(), 3);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->connection->setAttribute('use_dql_callbacks', false);
    }
}