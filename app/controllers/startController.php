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
 * Controlador start/
*/
class startController extends Controllers implements IControllers {

    public function __construct(IRouter $router) {
        global $session;
        parent::__construct($router,array(
            'users_not_logged' => true
        ));
   
        $u = new Model\Users;
       
        switch ($this->method) {
        	case 'tc':
        		# Token
        		$token = $u->getTCToken();
        		# Login de usuario
        		$tc_u = $u->loginTC($token);

        		# Template
        		$this->template->display('start/start', array(
					'm' => 'login',
					'tc_u' => $tc_u,
                    'sports' => (new Model\Sports)->get()
				));
        	break;

            case 'tw':

                # Template
                $this->template->display('start/start', array(
                    'm' => 'login',
                    # Logeo en twitter
                    'tc_u' => $u->TWLogin(),
                    'sports' => (new Model\Sports)->get()
                ));
            break;
        	
        	default:
        		$this->template->display('start/start', array(
					'm' => $router->getMethod(),
                    'sports' => (new Model\Sports)->get(),
                    'twurl' => $u->TwitterUrl()
				));

        	break;
        }

        
		
    }
}