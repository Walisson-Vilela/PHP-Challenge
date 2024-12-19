<?php

/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder) {
        $builder->setExtensions(['json']);

        // Rotas para Stores
        $builder->connect('/stores', ['controller' => 'Stores', 'action' => 'index', 'method' => 'GET']);
        $builder->connect('/stores/view/:id', ['controller' => 'Stores', 'action' => 'view', 'method' => 'GET'])->setPass(['id']);
        $builder->connect('/stores/add', ['controller' => 'Stores', 'action' => 'add', 'method' => 'POST']);
        $builder->connect('/stores/edit/:id', ['controller' => 'Stores', 'action' => 'edit', 'method' => ['PATCH', 'PUT']])->setPass(['id']);
        $builder->connect('/stores/delete/:id', ['controller' => 'Stores', 'action' => 'delete', 'method' => 'DELETE'])->setPass(['id']);

        $builder->fallbacks(DashedRoute::class);
    });
};
