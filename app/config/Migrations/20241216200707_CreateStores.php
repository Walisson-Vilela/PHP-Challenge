<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateStores extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('stores');
        $table->addColumn('name', 'string', ['limit' => 200, 'null' => false])
              ->addPrimaryKey('id')
              ->create();
    }
}
