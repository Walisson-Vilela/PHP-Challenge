<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Http\Client;

class AddressesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('addresses');
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('postal_code')
            ->maxLength('postal_code', 8)
            ->notEmptyString('postal_code', 'Postal code is required')
            ->add('postal_code', 'valid', [
                'rule' => function($value, $context) {
                    return preg_match('/^[0-9]{8}$/', $value);
                },
                'message' => 'Invalid postal code'
            ])
            ->scalar('street_number')
            ->maxLength('street_number', 200)
            ->notEmptyString('street_number', 'Street number is required');

        return $validator;
    }
}
