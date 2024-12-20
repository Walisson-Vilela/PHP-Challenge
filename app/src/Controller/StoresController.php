<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

class StoresController extends AppController
{
    public function index()
    {
        $this->loadComponent('Paginator');
        $stores = $this->Paginator->paginate($this->Stores->find('all')->contain(['Addresses']));
        $this->set(compact('stores'));
        $this->viewBuilder()->setOption('serialize', ['stores']);
    }

    public function view($id)
    {
        $store = $this->Stores->get($id, ['contain' => ['Addresses']]);
        if (!$store) {
            throw new NotFoundException(__('Store not found'));
        }
        $this->set(compact('store'));
        $this->viewBuilder()->setOption('serialize', ['store']);
    }

    public function add()
    {
        $store = $this->Stores->newEmptyEntity();
        if ($this->request->is('post')) {
            $store = $this->Stores->patchEntity($store, $this->request->getData(), ['associated' => ['Addresses']]);
            if (empty($store->address)) {
                $response = ['message' => 'Erro: O endereco e obrigatorio.', 'errors' => ['address' => 'O endereco e obrigatorio']];
                return $this->response->withType('application/json')->withStatus(400)->withStringBody(json_encode($response));
            }

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
        $store = $this->Stores->get($id, ['contain' => ['Addresses'],]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $store = $this->Stores->patchEntity($store, $this->request->getData(), ['associated' => ['Addresses']]);
            if (!empty($store->address)) {
                $store->address->foreign_table = 'stores';
            }
            if ($this->Stores->save($store)) {
                $response = [
                    'message' => 'Edicao concluida com sucesso',
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'address' => $store->address
                    ]
                ];
                return $this->response->withType('application/json')->withStringBody(json_encode($response));
            } else {
                $response = ['message' => 'A edicao nao foi concluida, tente novamente', 'errors' => $store->getErrors()];
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
