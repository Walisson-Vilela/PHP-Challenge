<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Store extends Entity
{
    protected $_accessible = [
        'name' => true,
        'address' => true,
    ];

    protected $_hidden = [
        'id'
    ];
}
