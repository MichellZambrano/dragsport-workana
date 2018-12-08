<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\models;

use app\models as Model;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Models\Traits\DBModel;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo ApiCurl
 */
class ApiCurl extends Models implements IModels {
    
	/**
	 * Realiza petición tipo POST con curl a una url
	 * 
	 * @param string $url : Url 
	 * @param array $data  : Datos a enviar
	 * 
	 * @return array resultado
	 */
	public function curl_post_query(string $url, array $data){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$data = json_decode(curl_exec($curl),true);
		curl_close($curl);
		return $data;
	}

	/**
	 * Realiza una petición con solo los headers con curl a una url
	 * 
	 * @param string $url : Url 
	 * @param array $headers : Headers
	 * 
	 * @return array resultado
	 */
	public function curl_header_query(string $url, array $headers){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$data = json_decode(curl_exec($curl),true);
		curl_close($curl);
		return $data;
	}

    /**
     * __construct()
    */
    public function __construct(IRouter $router = null) {
        parent::__construct($router);
    }
}