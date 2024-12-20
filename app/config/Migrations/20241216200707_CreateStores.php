<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateStores extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('stores');
        $table->addColumn('name', 'string', ['limit' => 200, 'null' => false])->addIndex(['name'], ['unique' => true])->create();
    }
}
