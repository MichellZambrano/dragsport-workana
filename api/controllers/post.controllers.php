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
    * streamsocial
    *
    * @return json
*/  
$app->post('/login', function() use($app) {
    $u = new Model\streamsocial;   

    return $app->json($u->create());   
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
    * Inicio de sesión con Twitter
    *
    * @return json
*/  
$app->post('/twregister', function() use($app) {
    $u = new Model\Users;   

    return $app->json($u->TWRegister());   
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

/**
 * Endpoint para profile
 *
 * @return json
*/
$app->post('/profile', function() use($app) {
    $p = new Model\Profile; 

    return $app->json($p->foo());   
});

/**
 * Endpoint para account
 *
 * @return json
*/
$app->post('/account', function() use($app) {
    $a = new Model\Account; 

    return $app->json($a->foo());   
});

/**
 * Endpoint para messages
 *
 * @return json
*/
$app->post('/messages', function() use($app) {
    $m = new Model\Messages; 

    return $app->json($m->foo());   
});
/**
 * Endpoint para requests
 *
 * @return json
*/
$app->post('/requests', function() use($app) {
    $r = new Model\Requests; 

    return $app->json($r->foo());   
});
/**
 * Endpoint para favpages
 *
 * @return json
*/
$app->post('/favpages', function() use($app) {
    $f = new Model\Favpages; 

    return $app->json($f->foo());   
});
/**
 * Endpoint para youtube
 *
 * @return json
*/
$app->post('/youtube', function() use($app) {
    $y = new Model\Youtube; 

    return $app->json($y->foo());   
});
/**
 * Endpoint para shopify
 *
 * @return json
*/
$app->post('/shopify', function() use($app) {
    $s = new Model\Shopify; 

    return $app->json($s->foo());   
});