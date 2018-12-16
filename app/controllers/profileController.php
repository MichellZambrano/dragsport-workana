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
 * Controlador profile/
*/
class profileController extends Controllers implements IControllers {

    public function __construct(IRouter $router) {
        parent::__construct($router,array(
        	'users_logged' => true
        ));
        $p = new Model\Profile;
        $m = $router->getMethod();


        switch ($this->method) {
        	case 'about':
        		$this->template->display('profile/about', array(
        			'm' => $m
        		));
        	break;
        	case 'friends':
        		$this->template->display('profile/friends', array(
        			'm' => $m
        		));
        	break;
        	case 'photos':
        		$this->template->display('profile/photos', array(
        			'm' => $m
        		));
        	break;
        	case 'videos':
        		$this->template->display('profile/videos', array(
        			'm' => $m
        		));
        	break;
        	default:
        		$this->template->display('profile/profile', array(
        			'm' => $m
        		));
        	break;
        }
        
    }
}