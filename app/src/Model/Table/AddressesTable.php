<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Client;

class AddressesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('addresses');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        // Definir a associação com Stores
        $this->belongsTo('Stores', [
            'foreignKey' => 'foreign_id',
            'conditions' => ['Addresses.foreign_table' => 'stores'],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('postal_code', 'O CEP e obrigatorio')
            ->add('postal_code', 'validFormat', [
                'rule' => ['custom', '/^[0-9]{8}$/'],
                'message' => 'O CEP deve constar 8 digitos obrigatorios'
            ])
            ->notEmptyString('street_number', 'O numero e obrigatorio')
            ->allowEmptyString('complement');

        return $validator;
    }

    public function beforeSave($entity)
    {
        if ($entity->isNew() || $entity->isDirty('postal_code')) {
            $address = $this->fetchAddressFromApi($entity->postal_code);
            if ($address) {
                $entity->state = $address['state'];
                $entity->city = $address['city'];
                $entity->sublocality = $address['sublocality'];
                $entity->street = $address['street'];
            } else {
                throw new RecordNotFoundException('CEP não encontrado');
            }
        }
    }

    private function fetchAddressFromApi($postalCode)
    {
        $client = new Client();

        // Primeira tentativa: API República Virtual
        $response = $client->get('http://cep.republicavirtual.com.br/web_cep.php', ['cep' => $postalCode, 'formato' => 'json']);
        if ($response->isOk()) {
            $data = $response->getJson();
            if ($data['resultado'] !== '0') {
                return [
                    'state' => $data['uf'],
                    'city' => $data['cidade'],
                    'sublocality' => $data['bairro'],
                    'street' => $data['logradouro']
                ];
            }
        }

        // Segunda tentativa: API Via CEP
        $response = $client->get('https://viacep.com.br/ws/' . $postalCode . '/json/');
        if ($response->isOk()) {
            $data = $response->getJson();
            if (empty($data['erro'])) {
                return [
                    'state' => $data['uf'],
                    'city' => $data['localidade'],
                    'sublocality' => $data['bairro'],
                    'street' => $data['logradouro']
                ];
            }
        }
        return null;
    }
}
