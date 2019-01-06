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
 * Modelo Shopify
 */
class Shopify extends Models implements IModels {
    use DBModel;

    /**
     * Obtiene los productos de la tienda de shopify
     * 
     * @return array
    */ 
    public function get_products() {
        try {
            global $config;
            $api_url = 'https://'.$config['shopify']['api_key'].':'.$config['shopify']['password'].'@'.$config['shopify']['shop'];
            //$products_obj_url = $api_url . '/admin/products.json?limit=250&page=1';
            $products_obj_url = $api_url . '/admin/products.json';
            $products_content = @file_get_contents( $products_obj_url );
            $products_json = json_decode( $products_content, true );
            dump($products_json['products']);
            return $products_json['products'];
        } catch (\Exception $e) {
            return array();
        }
        
    }

    /**
     * __construct()
    */
    public function __construct(IRouter $router = null) {
        parent::__construct($router);
		$this->startDBConexion();
    }
}