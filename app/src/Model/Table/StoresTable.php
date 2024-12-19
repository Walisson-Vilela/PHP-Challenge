<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use Cake\ORM\Rule\IsUnique;

class StoresTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('stores');
        $this->setPrimaryKey('id');
        $this->hasOne('Addresses', [
            'foreignKey' => 'foreign_id',
            'conditions' => ['Addresses.foreign_table' => 'Stores'],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 200)
            ->notEmptyString('name', 'Name is required')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table', 'message' => 'Nome em uso']);

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name'], 'Nome em uso'));
        return $rules;
    }
}
