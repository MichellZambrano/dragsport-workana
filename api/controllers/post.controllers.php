<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

use app\models as Model;

/**
    * Inicio de sesión
    *
    * @return json
*/  
$app->post('/login', function() use($app) {
    $u = new Model\Users;   

    return $app->json($u->login());   
});

/**
    * Inicio de sesión con facebook
    *
    * @return json
*/  
$app->post('/loginFB', function() use($app) {
    $u = new Model\Users;   

    return $app->json($u->loginFB());   
});


/**
    * Inicio de sesión con Twitch
    *
    * @return json
*/  
$app->post('/loginTC2', function() use($app) {
    $u = new Model\Users;   

    return $app->json($u->loginTC2());   
});

/**
    * Registro de un usuario
    *
    * @return json
*/
$app->post('/register', function() use($app) {
    $u = new Model\Users; 

    return $app->json($u->register());   
});

/**
    * Recuperar contraseña perdida
    *
    * @return json
*/
$app->post('/lostpass', function() use($app) {
    $u = new Model\Users; 

    return $app->json($u->lostpass());   
});
