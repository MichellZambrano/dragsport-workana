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
 * Modelo Musicplaylist
 */
class Musicplaylist extends Models implements IModels {
    use DBModel;

    /**
     * Conexión a Spotify
     * 
     * @var object
     */

    private $spotify;

    /**
     * Obtiene los datos de spotify de un usuario
     * 
     * @param string $code : Código para crear el token
     * 
     * @return Datos del usuario de spotify | redirección
     */
    private function getMe($code){
    	global $config;
    	# HAcemos try catch por si hay un error
    	try {
    		# Api de spotify
    		$api = new \SpotifyWebAPI\SpotifyWebAPI();
    		# Asignnamos el access Token
    		$this->spotify->requestAccessToken($code);
    		# Asiganmos el token a la api
    		$api->setAccessToken($this->spotify->getAccessToken());
    		# Información del usuario
    		return $api->me();
    	} catch (\Exception $e) {
    		Helper\Functions::redir($config['build']['url'] . 'musicplaylist');
    		exit;
    	}
    }
    /**
     * Registra al usuario de spotify
     * 
     * @param string $id_social Id del usuario de spotify
     * 
     * @return void
     */
    private function registerSpotify($id_social){
    	# Insertamos los datos
    	$this->db->insert('social', array(
    		'id_user' => $this->id_user,
    		'id_social' => $id_social,
    		'is_logged' => 'on',
    		'created_at' => time()
    	));
    }
    /**
     * Logea a un usuario de spotify
     * 
     * @return void
     */
    public function loginSpotify(){
    	global $http, $config;

    	# Validar si existe el código
    	if ($code = $http->query->get('code')) {

    		# Obtenemos al usuario
    		$user = $this->getMe($code);
    		# Validamos que el usuario exista en la db
    		$id_social = $user->id;
    		$u = $this->db->select('*', 'social', null, "id_social = '$id_social' AND id_user = '$this->id_user'", 1);

    		# En caso de no existir lo registramos
    		if (false == $u) {
    			$this->registerSpotify($id_social);	

    		}else{
    			# Logeamos al usuario
    			$this->db->update('social', array(
    				'is_logged' => 'on'
    			), "id_social = '$id_social'", 1);
    		}

    		
    	}

    	# Devolvemos al playlist
    	Helper\Functions::redir($config['build']['url'] . 'musicplaylist');
    }
    /**
     * Obtiene la url para inicio de sesión
     * 
     * @return string con la url
     */
    public function getUrl(){
    	return $this->spotify->getAuthorizeUrl([
    		'scope' => [
    			'user-read-email',
                'playlist-read-private',
                'playlist-read-collaborative'
    		]
    	]);
    }
    /**
     * Devuelve los datos de un usuario logead ode Spotify
     * @return type
     */
    public function getUser(){
        global $config;
    	$social = $this->db->select('*', 'social', null, "id_user = '$this->id_user' AND is_logged = 'on'", 1);
    	
    	# Si no existe devolvemos false
    	if (false == $social) {
    		return false;
    	}

    	# Api de spotify
    	$api = new \SpotifyWebAPI\SpotifyWebAPI();


    	# Creamos un token con las credenciales
    	$this->spotify->requestCredentialsToken();
    	# Asignamos el token
    	$api->setAccessToken($this->spotify->getAccessToken());

        # PlayList de spotify
        $playlists = $api->getUserPlaylists('kwyv1e2e8fzg8bs87suk7pyx1', [
            'limit' => 10
        ]);
        
        $user_playlist = array();

        # Iteramos cada elemento de la playlist
        foreach ($playlists->items as $playlist) {
            # playlist 
            $pl = json_decode( json_encode($playlist) , true );
            # Tracks
            $pl['data_tracks'] = json_decode( json_encode( $api->getPlaylistTracks($playlist->id) ), true );

            $user_playlist[] = $pl;
       
        }
        
        # Datos del usuario
        $spotify_user = json_decode( json_encode($api->getUser($social[0]['id_social'])),true );


        # Agregamos la playlist
        $spotify_user['pl'] = $user_playlist;
        
  
    	# Devolvemos los datos del usuario
    	return $spotify_user;
    }
    /**
     * Deslogea a un usuario de spotify
     * 
     * @return void
     */
    public function logout(){
        global $config;
        $this->db->update('social', array(
            'is_logged' => 'off'
        ), "id_user = '$this->id_user'", 1);

        Helper\Functions::redir($config['build']['url'] . 'musicplaylist');
    }

    /**
     * __construct()
    */
    public function __construct(IRouter $router = null) {
        parent::__construct($router);
        global $config;
		$this->startDBConexion();
		# Objecto de spotify
		$this->spotify = new \SpotifyWebAPI\Session(
		    $config['spotify']['client_id'],
		    $config['spotify']['client_secret'],
		    $config['spotify']['redirect_url']
		);

    }
}