<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace app\controllers;

use app\models as Model;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Controllers\Controllers;
use Ocrend\Kernel\Controllers\IControllers;
use Ocrend\Kernel\Router\IRouter;


/**
 * Controlador musicplaylist/
*/
class musicplaylistController extends Controllers implements IControllers {

    public function __construct(IRouter $router) {
        parent::__construct($router,array(
        	'users_logged' => true
        ));
        global $config;

        $m = new Model\Musicplaylist;

        switch ($this->method) {
        	case 'login':
        		$m->loginSpotify();
        	break;
        	case 'logout':
        		$m->logout();
        	break;
        	default:
        		$this->template->display('musicplaylist/musicplaylist', array(
					'url' => $m->getUrl(),
					'user' => $m->getUser()
				));
        	break;
        }

		
    }
}