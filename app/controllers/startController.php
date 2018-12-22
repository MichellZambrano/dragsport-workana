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
use Abraham\TwitterOAuth\TwitterOAuth;
/**
 * Controlador start/
*/
class startController extends Controllers implements IControllers {

    public function __construct(IRouter $router) {
        global $config, $http;
        parent::__construct($router,array(
            'users_not_logged' => true
        ));
   
        $u = new Model\Users;
        # Conexion a twitter
        $twitter= new TwitterOAuth($config['twitter']['client_id'],$config['twitter']['client_secret']);
        # tokens de twitter
        $tokens= $twitter->oauth("oauth/request_token",array("oauth_callback" => $config['twitter']['redirect_url']));
        # URl twitter
        $urlTwitter= $twitter->url("oauth/authorize", ["oauth_token" => $tokens["oauth_token"]]);

    
    
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
                # nueva conexion a twitter
                $ctwitter= new TwitterOAuth($config['twitter']['client_id'],$config['twitter']['client_secret'],$tokens["oauth_token"], $tokens["oauth_token_secret"]);

                $access_token= $ctwitter->oauth('oauth/access_token', array('oauth_verifier'=> $http->query->get('oauth_verifier'),'oauth_token'=>$http->query->get('oauth_token')));

                $conex_tw= new TwitterOAuth($config['twitter']['client_id'], $config['twitter']['client_secret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);

                $user_info= $conex_tw->get('account/verify_credentials');

                dump($user_info);
                exit;
            break;
        	
        	default:
        		$this->template->display('start/start', array(
					'm' => $router->getMethod(),
                    'url' => $urlTwitter,
                    'sports' => (new Model\Sports)->get()
				));

        	break;
        }

        
		
    }
}