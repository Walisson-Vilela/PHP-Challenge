<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Event\EventInterface;

class StoresController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->RequestHandler->renderAs($this, 'json');
        $this->response = $this->response->withType('application/json');
        $this->loadModel('Addresses');
    }

    public function fetchAddressDetails($postal_code)
    {
        $client = new \Cake\Http\Client();
        $response = $client->get('https://republicavirtual.com.br/web_cep.php', ['cep' => $postal_code, 'formato' => 'json']);
        if ($response->isOk() && $response->getJson()) {
            return $response->getJson();
        } else {
            $response = $client->get('https://viacep.com.br/ws/' . $postal_code . '/json/');
            if ($response->isOk() && $response->getJson()) {
                return $response->getJson();
            }
        }
        throw new BadRequestException('CEP não encontrado');
    }

    public function index()
    {
        $stores = $this->Stores->find('all', [
            'contain' => ['Addresses']
        ])->toArray();
        $this->set(compact('stores'));
        $this->viewBuilder()->setOption('serialize', ['stores']);
    }

    public function view($id = null)
    {
        try {
            $store = $this->Stores->get($id, [
                'contain' => ['Addresses']
            ]);
            $this->set(compact('store'));
            $this->viewBuilder()->setOption('serialize', ['store']);
        } catch (\Exception $e) {
            throw new NotFoundException(__('Store not found'));
        }
    }

    public function add()
    {
        $store = $this->Stores->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Verificar e completar os detalhes do endereço
            $addressDetails = $this->fetchAddressDetails($data['address']['postal_code']);
            $data['address'] = array_merge($data['address'], $addressDetails);

            $store = $this->Stores->patchEntity($store, $data, ['associated' => ['Addresses']]);
            if ($this->Stores->save($store)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
            $this->set([
                'message' => $message,
                'store' => $store,
            ]);
            $this->viewBuilder()->setOption('serialize', ['message', 'store']);
        }
    }

    public function edit($id = null)
    {
        $store = $this->Stores->get($id, [
            'contain' => ['Addresses']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            // Apagar o endereço antigo
            $this->Addresses->deleteAll(['foreign_id' => $id, 'foreign_table' => 'Stores']);

            // Verificar e completar os detalhes do endereço
            $data = $this->request->getData();
            $addressDetails = $this->fetchAddressDetails($data['address']['postal_code']);
            $data['address'] = array_merge($data['address'], $addressDetails);

            // Adicionar um novo endereço e atualizar a loja
            $store = $this->Stores->patchEntity($store, $data, ['associated' => ['Addresses']]);
            if ($this->Stores->save($store)) {
                $message = 'Updated';
            } else {
                $message = 'Error';
            }
            $this->set([
                'message' => $message,
                'store' => $store,
            ]);
            $this->viewBuilder()->setOption('serialize', ['message', 'store']);
        }
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $store = $this->Stores->get($id, ['contain' => ['Addresses']]);
        $message = 'Deleted';
        if ($this->Stores->delete($store)) {
            $this->Addresses->deleteAll(['foreign_id' => $id, 'foreign_table' => 'Stores']);
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
        ]);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
