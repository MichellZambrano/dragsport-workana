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
 * Controlador fanpage/
*/
class fanpageController extends Controllers implements IControllers {

    public function __construct(IRouter $router) {
        parent::__construct($router,array(
        	'users_logged' => true
        ));

        $m = $router->getMethod();


        switch ($this->method) {
        	case 'about':
        		$this->template->display('fanpage/about', array(
        			'm' => $m
        		));
        	break;
        	case 'photos':
        		$this->template->display('fanpage/photos', array(
        			'm' => $m
        		));
        	break;
        	case 'videos':
        		$this->template->display('fanpage/videos', array(
        			'm' => $m
        		));
        	break;
        	case 'statistics':
        		$this->template->display('fanpage/statistics', array(
        			'm' => $m
        		));
        	break;
        	case 'events':
        		$this->template->display('fanpage/events', array(
        			'm' => $m
        		));
        	break;

        	default:
        		$this->template->display('fanpage/fanpage', array(
        			'm' => $m
        		));
        	break;
        }
       
    }
}