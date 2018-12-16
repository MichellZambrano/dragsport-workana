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

$app->get('/', function() use($app) {
    return $app->json(array()); 
});

/**
 * 
 * Obtiene los items  el home
 * 
 **/

$app->get('items/get', function() use($app) {
	return $app['twig']->render('home/items');
});

/**
 * 
 * Obtiene los deportes
 * 
 **/

$app->get('sports', function() use($app) {
	$s = new Model\Sports;

	return $app->json($s->get());
});