<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;

class StoresTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('stores');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        // Definir a associação com Endereço
        $this->hasOne('Addresses', [
            'foreignKey' => 'foreign_id',
            'conditions' => ['Addresses.foreign_table' => 'stores'],
            'dependent' => true, // Define que quando uma Loja for excluída, o Address associado também será
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', 'O nome e obrigatorio')
            ->add('name', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'Nome em uso'
            ]);

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name'], 'Name is already in use'));
        return $rules;
    }

    public function beforeSave($entity)
    {
        if ($entity->isNew() || $entity->isDirty('name')) {
            $existing = $this->find()
                ->where(['name' => $entity->name])
                ->first();

            if ($existing) {
                throw new \Exception('Name is already in use');
            }
        }
    }
}
