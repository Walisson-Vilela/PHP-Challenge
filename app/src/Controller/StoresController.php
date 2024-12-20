<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

class StoresController extends AppController
{

    private function applyPostalCodeMask($postalCode)
    {
        if (preg_match('/^[0-9]{8}$/', $postalCode)) {
            return substr($postalCode, 0, 5) . '-' . substr($postalCode, 5, 3);
        }
        return $postalCode;
    }

    public function index()
    {
        $this->loadComponent('Paginator');
        $stores = $this->Paginator->paginate($this->Stores->find('all')->contain(['Addresses']));
        foreach ($stores as $store) {
            if (!empty($store->address)) {
                $store->address->postal_code_masked = $this->applyPostalCodeMask($store->address->postal_code);
            }
        }
        $this->set(compact('stores'));
        $this->viewBuilder()->setOption('serialize', ['stores']);
    }

    public function view($id)
    {
        $store = $this->Stores->get($id, ['contain' => ['Addresses']]);
        if (!$store) {
            throw new NotFoundException(__('Loja nao encontrada'));
        }
        if (!empty($store->address)) {
            $store->address->postal_code_masked = $this->applyPostalCodeMask($store->address->postal_code);
        }
        $this->set(compact('store'));
        $this->viewBuilder()->setOption('serialize', ['store']);
    }


    public function add()
    {
        $store = $this->Stores->newEmptyEntity();
        if ($this->request->is('post')) {
            $store = $this->Stores->patchEntity($store, $this->request->getData(), ['associated' => ['Addresses']]);

            // Verificar se o endereço está presente
            if (empty($store->address)) {
                $response = ['message' => 'Erro: O endereço é obrigatório.', 'errors' => ['address' => 'O endereço é obrigatório']];
                return $this->response->withType('application/json')->withStatus(400)->withStringBody(json_encode($response));
            }

            // Definir 'foreign_table' para o endereço
            $store->address->foreign_table = 'stores';

            if ($this->Stores->save($store)) {
                $response = [
                    'message' => 'Dados salvos com sucesso!',
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'address' => $store->address
                    ]
                ];
                return $this->response->withType('application/json')->withStringBody(json_encode($response));
            } else {
                $response = ['message' => 'Os dados nao foram salvos, tente novamente', 'errors' => $store->getErrors()];
                return $this->response->withType('application/json')->withStatus(400)->withStringBody(json_encode($response));
            }
        }
    }

    public function edit($id = null)
    {
        if (is_null($id)) {
            throw new NotFoundException(__('Invalid store ID'));
        }

        $store = $this->Stores->get($id, [
            'contain' => ['Addresses'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // Excluir o endereço atual se houver
            if (!empty($store->address)) {
                $this->Stores->Addresses->delete($store->address);
            }

            // Criar novo endereço a partir dos dados recebidos
            if (!empty($data['address'])) {
                $data['address']['foreign_table'] = 'stores';
                $data['address']['foreign_id'] = $store->id;
            }

            $store = $this->Stores->patchEntity($store, $data, ['associated' => ['Addresses']]);

            if ($this->Stores->save($store)) {
                $response = [
                    'message' => 'Dados salvos com sucesso!',
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'address' => $store->address
                    ]
                ];
                return $this->response->withType('application/json')->withStringBody(json_encode($response));
            } else {
                $response = [
                    'message' => 'Os dados nao foram salvos, tente novamente',
                    'errors' => $store->getErrors()
                ];
                return $this->response->withType('application/json')->withStatus(400)->withStringBody(json_encode($response));
            }
        }
    }


    public function delete($id)
    {
        $this->request->allowMethod(['delete', 'post']);
        $store = $this->Stores->get($id);
        if (!$store) {
            throw new NotFoundException(__('Store not found'));
        }
        if ($this->Stores->delete($store)) {
            $this->Stores->Addresses->deleteAll([
                'foreign_table' => 'stores',
                'foreign_id' => $store->id
            ]);
            $response = ['message' => __('Dados deletados com sucesso')];
        } else {
            $response = ['message' => __('Os dados nao foram deletados, por favor tente novamente')];
        }
        return $this->response->withType('application/json')->withStringBody(json_encode($response));
    }
}
