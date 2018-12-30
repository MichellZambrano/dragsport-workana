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
use Abraham\TwitterOAuth\TwitterOAuth;
/**
 * Modelo Users
 */
class Users extends Models implements IModels {
    use DBModel;

    /**
     * Máximos intentos de inincio de sesión de un usuario
     *
     * @var int
     */
    const MAX_ATTEMPTS = 5;

    /**
     * Tiempo entre máximos intentos en segundos
     *
     * @var int
     */
    const MAX_ATTEMPTS_TIME = 120; # (dos minutos)

    /**
     * Log de intentos recientes con la forma 'email' => (int) intentos
     *
     * @var array
     */
    private $recentAttempts = array();

    /**
     * Hace un set() a la sesión login_user_recentAttempts con el valor actualizado.
     *
     * @return void
    */
    private function updateSessionAttempts() {
        global $session;

        $session->set('login_user_recentAttempts', $this->recentAttempts);
    }

    /**
     * Revisa si las contraseñas son iguales
     *
     * @param string $pass : Contraseña sin encriptar
     * @param string $pass_repeat : Contraseña repetida sin encriptar
     *
     * @throws ModelsException cuando las contraseñas no coinciden
     */
    private function checkPassMatch(string $pass, string $pass_repeat) {
        if ($pass != $pass_repeat) {
            throw new ModelsException('Las contraseñas no coinciden.');
        }
    }

    /**
     * Verifica el email introducido, tanto el formato como su existencia en el sistema
     *
     * @param string $email: Email del usuario
     *
     * @throws ModelsException en caso de que no tenga formato válido o ya exista
     */
    private function checkEmail(string $email) {
        # Formato de email
        if (!Helper\Strings::is_email($email)) {
            throw new ModelsException('The email does not have a valid format.');
        }

        # Existencia de email
        $email = $this->db->scape($email);
        $query = $this->db->select('id_user', 'users', null, "email='$email'", 1);
        if (false !== $query) {
            throw new ModelsException('The email entered already exists.');
        }
    }
    /**
     * Verifica una fecha que seas válida y que tenga un formato válido.
     * 
     * @param string $date : Fecha
     * 
     * @throws ModelsException en caso de que no tenga formato válido n no sea válida
     */
    private function checkDate($date){
        # Verificar formato
        if (!preg_match('/^([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/', $date)) {
            throw new ModelsException('The format of the date must be DD/MM/YYYY.');
        }

        # Convertimos en array
        $date = explode('/', $date);
        # Validamos fecha
        if (!checkdate($date[1], $date[0], $date[2])) {
            throw new ModelsException('The selected date is invalid.');
        }
    } 

    /**
     * Restaura los intentos de un usuario al iniciar sesión
     *
     * @param string $email: Email del usuario a restaurar
     *
     * @throws ModelsException cuando hay un error de lógica utilizando este método
     * @return void
     */
    private function restoreAttempts(string $email) {       
        if (array_key_exists($email, $this->recentAttempts)) {
            $this->recentAttempts[$email]['attempts'] = 0;
            $this->recentAttempts[$email]['time'] = null;
            $this->updateSessionAttempts();
        } else {
            throw new ModelsException('Logical error');
        }
    }

    /**
     * Genera la sesión con el id del usuario que ha iniciado
     *
     * @param array $user_data: Arreglo con información de la base de datos, del usuario
     *
     * @return void
     */
    private function generateSession(array $user_data) {
        global $session, $cookie, $config;
        
        # Generar un session hash
        $cookie->set('session_hash', md5(time()), $config['sessions']['user_cookie']['lifetime']);
        
        # Generar la sesión del usuario
        $session->set($cookie->get('session_hash') . '__user_id',(int) $user_data['id_user']);

        # Generar data encriptada para prolongar la sesión
        if($config['sessions']['user_cookie']['enable']) {
            # Generar id encriptado
            $encrypt = Helper\Strings::ocrend_encode($user_data['id_user'], $config['sessions']['user_cookie']['key_encrypt']);

            # Generar cookies para prolongar la vida de la sesión
            $cookie->set('appsalt', Helper\Strings::hash($encrypt), $config['sessions']['user_cookie']['lifetime']);
            $cookie->set('appencrypt', $encrypt, $config['sessions']['user_cookie']['lifetime']);
        }
    }

    /**
     * Verifica en la base de datos, el email y contraseña ingresados por el usuario
     *
     * @param string $email: Email del usuario que intenta el login
     * @param string $pass: Contraseña sin encriptar del usuario que intenta el login
     *
     * @return bool true: Cuando el inicio de sesión es correcto 
     *              false: Cuando el inicio de sesión no es correcto
     */
    private function authentication(string $email,string $pass) : bool {
        $email = $this->db->scape($email);
        $query = $this->db->select('id_user,pass','users',null, "email='$email'",1);
        
        # Incio de sesión con éxito
        if(false !== $query && Helper\Strings::chash($query[0]['pass'],$pass)) {

            # Restaurar intentos
            $this->restoreAttempts($email);

            # Generar la sesión
            $this->generateSession($query[0]);
            return true;
        }

        return false;
    }

    /**
     * Establece los intentos recientes desde la variable de sesión acumulativa
     *
     * @return void
     */
    private function setDefaultAttempts() {
        global $session;

        if (null != $session->get('login_user_recentAttempts')) {
            $this->recentAttempts = $session->get('login_user_recentAttempts');
        }
    }
    
    /**
     * Establece el intento del usuario actual o incrementa su cantidad si ya existe
     *
     * @param string $email: Email del usuario
     *
     * @return void
     */
    private function setNewAttempt(string $email) {
        if (!array_key_exists($email, $this->recentAttempts)) {
            $this->recentAttempts[$email] = array(
                'attempts' => 0, # Intentos
                'time' => null # Tiempo 
            );
        } 

        $this->recentAttempts[$email]['attempts']++;
        $this->updateSessionAttempts();
    }

    /**
     * Controla la cantidad de intentos permitidos máximos por usuario, si llega al límite,
     * el usuario podrá seguir intentando en self::MAX_ATTEMPTS_TIME segundos.
     *
     * @param string $email: Email del usuario
     *
     * @throws ModelsException cuando ya ha excedido self::MAX_ATTEMPTS
     * @return void
     */
    private function maximumAttempts(string $email) {
        if ($this->recentAttempts[$email]['attempts'] >= self::MAX_ATTEMPTS) {
            
            # Colocar timestamp para recuperar más adelante la posibilidad de acceso
            if (null == $this->recentAttempts[$email]['time']) {
                $this->recentAttempts[$email]['time'] = time() + self::MAX_ATTEMPTS_TIME;
            }
            
            if (time() < $this->recentAttempts[$email]['time']) {
                # Setear sesión
                $this->updateSessionAttempts();
                # Lanzar excepción
                throw new ModelsException('You have already exceeded the limit of attempts to log in.');
            } else {
                $this->restoreAttempts($email);
            }
        }
    }   

    /**
     * Obtiene datos de un usuario según su id en la base de datos
     *    
     * @param int $id: Id del usuario a obtener
     * @param string $select : Por defecto es *, se usa para obtener sólo los parámetros necesarios 
     *
     * @return false|array con información del usuario
     */   
    public function getUserById(int $id, string $select = '*') {
        return $this->db->select($select,'users',null,"id_user='$id'",1);
    }
    
    /**
     * Obtiene a todos los usuarios
     *    
     * @param string $select : Por defecto es *, se usa para obtener sólo los parámetros necesarios 
     *
     * @return false|array con información de los usuarios
     */  
    public function getUsers(string $select = '*') {
        return $this->db->select($select, 'users');
    }

    /**
     * Obtiene datos del usuario conectado actualmente
     *
     * @param string $select : Por defecto es *, se usa para obtener sólo los parámetros necesarios
     *
     * @throws ModelsException si el usuario no está logeado
     * @return array con datos del usuario conectado
     */
    public function getOwnerUser(string $select = '*') : array {
        if(null !== $this->id_user) {    
               
            $user = $this->db->select($select,'users',null, "id_user='$this->id_user'",1);

            # Si se borra al usuario desde la base de datos y sigue con la sesión activa
            if(false === $user) {
                $this->logout();
            }

            return $user[0];
        } 
           
        throw new \RuntimeException('The user is not logged in.');
    }
    /**
     * Trae el id de un usuario registrado con alguna red social
     * 
     * @param $social_id : Id del user de facebook
     * 
     * @return array|false
     */
    private function getSocialUserById($social_id, $login_with){
        $social_id = $this->db->scape($social_id);
        return $this->db->select('id_user', 'users', null, "social_id = '$social_id' AND login_with = '$login_with'", 1);
    }
    /**
     * Validamos a un usuario en un método de inicio se sesión
     * 
     * @param array $user : Datos del usuario enviados
     * @param string $login_with : Tipo de logeo
     * 
     * @return void
     */
    private function checkUser($user, string $login_with){
        # Id del usuario
        $id_user = $this->getSocialUserById($user['id'], 'fb');
        
        # Validar existencia del usuario
        if (false == $id_user) {
            $id_user = $this->registerUser($user, $login_with);
        }

        # Iniciar sesión
        $this->generateSession(array(
            'id_user' => $id_user[0]['id_user']
        ));
    }
    /**
     * Registra un usuario con facebook
     * 
     * @param array $user : Datos del usuario
     * 
     * @return id del usuario en forma de matriz $array[0]['id_user']
     */
    private function registerUser($user, string $login_with){
        # Verificar email 
        $this->checkEmail($user['email']);

        # Verificar fecha de nacimiento
        $this->checkDate($user['datetimepicker']);

        # Verificar género
        if (!in_array($user['gender'], ['female', 'male'])) {
             throw new ModelsException('You must choose a gender.');
        }

        # Verificar Deportes
        $this->checkSports($user['favorite_sports']);
        
        
        # Registrar al usuario
        $id_user = $this->db->insert('users', array(
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'pass' => Helper\Strings::hash( uniqid() ),
            'birthdate' => strtotime( str_replace('/', '-', $user['datetimepicker'])),
            'gender' => $user['gender'],
            'login_with' => $login_with,
            'social_id' => $user['id'],
            'image' => $user['picture'],
            'created_at' => time()
        ));

        # Guardamos los deportes
        $this->saveSports($user['favorite_sports'], $id_user);

        # Datos a devolver
        return array(
            array(
                'id_user' => $id_user
            )
        );
    }



    /**
     * Logea a un usuario con facebook
     * 
     * @return array
     */
    public function loginFB() : array{
        try {
            global $http;
            # Usuario de facebook
            $user = $this->getSocialUserById($http->request->get('id'), 'fb');
            # Evaluar si existe fecha de nacimiento y genero
            if (false == $user && Helper\Functions::e($http->request->get('datetimepicker'),$http->request->get('gender'))) {
                 return array('nextStep' => true);
            }
            # En caso de no existir el usuario pero si la fecha y el género
            else if (false == $user) {
                $data = $http->request->all();
                $data['picture'] = 'https://graph.facebook.com/'.$http->request->get('id').'/picture?type=large';
                # Validar usuario
                $this->checkUser($data, 'fb');
            }else{
                # Iniciar sesión
                $this->generateSession(array(
                    'id_user' => $user[0]['id_user']
                ));

            }

            return array('success' => 1, 'message' => 'Connected successfully.');
        } catch (ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }
    }
    /**
     * Obtiene el token de twitch
     * 
     * @return string con el token de twitch
     */
    public function getTCToken() {
        global $http, $config;

        # Obtenemos el código
        $code = $http->query->get('code');

        # Instancia de la api de curl
        $ApiCurl = new ApiCurl;

        # Realizamos la peticion
        $results = $ApiCurl->curl_post_query($config['twitch']['token_url'], array(
            'client_id' => $config['twitch']['client_id'],
            'client_secret' => $config['twitch']['client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $config['twitch']['redirect_url'],
            'code' => $code
        ));

        # En caso de error
        if (array_key_exists('status', $results) && $results['status'] == 400) {
            Helper\Functions::redir($config['build']['url'] . 'start/login');
            exit;
        }
        # Devolvemos el token
        return $results['access_token'];
    }

    /**
     * Si un usuario existe lo logeacon twitch
     * 
     * @param string $token : Token de twitch
     * 
     * @return array
     */
    public function loginTC(string $token){
        global $config;

        # Instancia de la api de curl
        $ApiCurl = new ApiCurl;

        # Realizamos la peticion
        $user = $ApiCurl->curl_header_query($config['twitch']['user_url'], array(
            'Accept: application/vnd.twitchtv.v3+json',
            'Client-ID: ' . $config['twitch']['client_id'],
            'Authorization: OAuth ' . $token
        ));


        # COmprobar usuario
        $u = $this->getSocialUserById($user['_id'], 'tc');

        # En caso de no existir el usuario
        if (false == $u) {
            return array(
                'route' => 'loginTC2',
                'data' => array(
                    '_id' => $user['_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'picture' => $user['logo']
                )
            );
        }

        # Iniciar sesión
        $this->generateSession(array(
            'id_user' => $u[0]['id_user']
        ));
        
        return array('success' => true);
    }

    /**
     * En Caso de no existir el usuario pasa a esta fase donde lo registra y logea
     * 
     * @return array
     */
    public function loginTC2() : array {
        try {
            global $http;

            $data = array(
                'id' => $http->request->get('_id'),
                'first_name' => $http->request->get('name'),
                'last_name' => $http->request->get('last_name'),
                'email' => $http->request->get('email'),
                'datetimepicker' => $http->request->get('datetimepicker'),
                'gender' => $http->request->get('gender'),
                'favorite_sports' => $http->request->get('favorite_sports'),
                'picture' => $http->request->get('picture') 
            );

            # Tdos los campos son requeridos
            if (Helper\Functions::e($data['id'], $data['first_name'], $data['last_name'], $data['email'], $data['datetimepicker'],$data['gender']) || !Helper\Functions::all_full($data['favorite_sports']) ) {
                throw new ModelsException('All fields are required.');
            }


            # Obtenemos el usuario si existe
            $u = $this->getSocialUserById($data['id'], 'tc');

            # Si no existe el usuario
            if (false == $u) {
                $this->checkUser($data, 'tc');
            }
           
           
            return array('success' => 1, 'message' => 'Connected successfully.');
           

        } catch (ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }
    }

    /**
     * Obtiene la url de autenticación de twitter
     * 
     * @return string con la url
     */
    public function TwitterUrl(){
        try {
            global $config, $session;
            # Conexión a Twitter
            $twitter = new TwitterOAuth($config['twitter']['consumer_key'], $config['twitter']['consumer_secret']);
            # Request Token
            $request_token = $twitter->oauth('oauth/request_token', array('oauth_callback' => $config['twitter']['redirect_url']));
            # Guardamos el request
            $session->set('request_token', $request_token);
            # Url de retorno
            return $twitter->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        } catch (\Exception $e) {
            return 'javascript:void(0)';
        }
        
    }

    /**
     * Obtiene un access token en base al anterior y el obtenido veryfier de la url
     * 
     * @param array $token: Tokens anteriores 
     * @param string $oauth_verifier : Veryfier obtenido por get
     * 
     * @return Array con el nuevo access token
     */
    private function getNewAccessToken($token, $oauth_verifier){
        global $config;
        $twitter = new TwitterOAuth($config['twitter']['consumer_key'], $config['twitter']['consumer_secret'], $token['oauth_token'], $token['oauth_token_secret']);
        return $twitter->oauth("oauth/access_token", ["oauth_verifier" => $oauth_verifier]);
    }

    /**
     * Obtiene la información del perfil del usuario 
     * 
     * @param array $access_token: Nuevo access token obtenido
     * 
     * @return objecto con la información del usuario
     */
    private function getUserProfile($access_token){
        global $config;
        # Conexión a twitter
        $twitter = new TwitterOAuth($config['twitter']['consumer_key'], $config['twitter']['consumer_secret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
        # Información del usuario
        return $twitter->get('account/verify_credentials', ['tweet_mode' => 'extended', 'include_entities' => 'true']);
    }
    /**
     * Logea al usuario de twitter si este ya esta registrado en el sistema
     * 
     * @return array con mensaje de exito si está registrado | datos del usario a registrar
     */
    public function TWLogin() {
        try {
            global $config, $session, $http;
            # Obtenemos los tokens
            $request_token = $session->get('request_token');

            # Verificar posibles errores
            if (null != $http->query->get('oauth_token') 
            && $request_token['oauth_token'] !== $http->query->get('oauth_token')) {
                throw new \Exception(true);        
            }

            # Eliminamos la sesión
            $session->remove('request_token');

            # Nuevo token 
            $user =  $this->getUserProfile( $this->getNewAccessToken( $request_token, $http->query->get('oauth_verifier') ) );
         
            # Verificar si esta registrao
            $u = $this->getSocialUserById($user->id, 'tw');

            # Si no esta registrado completados los campos q faltan para registrar
            if (false == $u) {
                $return_data = array(
                    'route' => 'twregister',
                    'data' => array(
                        'id' => $user->id,
                        'name' => $user->name,
                        'picture' => 'https://avatars.io/twitter/'.$user->screen_name.'/original'
                        //'picture' => 'https://twitter.com/'.$user->screen_name.'/profile_image?size=original'
                    )
                );

                if (property_exists($user, 'email')) {
                    $return_data['data']['email'] = $user->email;
                }
                return $return_data;
            }

            # Iniciar sesión
            $this->generateSession(array(
                'id_user' => $u[0]['id_user']
            ));
            
            return array('success' => true);
           
        } catch (\Exception $e) {
            Helper\Functions::redir($config['build']['url'] . 'start/login');
            $session->remove('request_token');
        }
    }
    /**
     * Registra a un usuario de twitter en el sistema de dragsport
     * 
     * @return array
     */
    public function TWRegister() {
         try {
            global $http;

            $data = array(
                'id' => $http->request->get('id'),
                'first_name' => $http->request->get('name'),
                'last_name' => $http->request->get('last_name'),
                'email' => $http->request->get('email'),
                'datetimepicker' => $http->request->get('datetimepicker'),
                'gender' => $http->request->get('gender'),
                'favorite_sports' => $http->request->get('favorite_sports'),
                'picture' => $http->request->get('picture')
            );


            # Tdos los campos son requeridos
            if (Helper\Functions::e($data['id'], $data['first_name'], $data['last_name'], $data['email'], $data['datetimepicker'],$data['gender']) || !Helper\Functions::all_full($data['favorite_sports']) ) {
                throw new ModelsException('All fields are required.');
            }

           
            # Obtenemos el usuario si existe
            $u = $this->getSocialUserById($data['id'], 'tw');

            # Si no existe el usuario
            if (false == $u) {
                $this->checkUser($data, 'tw');
            }
           
           
            return array('success' => 1, 'message' => 'Connected successfully.');
        } catch (ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }
    }
     /**
     * Realiza la acción de login dentro del sistema
     *
     * @return array : Con información de éxito/falla al inicio de sesión.
     */
    public function login() : array {
        try {
            global $http;

            # Definir de nuevo el control de intentos
            $this->setDefaultAttempts();   

            # Obtener los datos $_POST
            $email = strtolower($http->request->get('email'));
            $pass = $http->request->get('pass');


            # Verificar que no están vacíos
            if (Helper\Functions::e($email, $pass)) {
                throw new ModelsException('Incomplete credentials.');
            }
            
            # Añadir intentos
            $this->setNewAttempt($email);
        
            # Verificar intentos 
            $this->maximumAttempts($email);

            # Autentificar
            if ($this->authentication($email, $pass)) {
                return array('success' => 1, 'message' => 'Connected successfully.');
            }
            
            throw new ModelsException('Bad credentials.');

        } catch (ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }        
    }

    /**
     * Verifica los deportes favoritos
     * 
     * @param array $sports : Deportes seleccionados
     * 
     * @return void
     */
    private function checkSports($sports) {
        # Validar que hallan deportes selecionados
        if (null == $sports || !Helper\Functions::all_full($sports)) {
            throw new ModelsException('You must select at least 1 sport.');
        }
        foreach ($sports as $s) {
            # Vericiar que sea numerico 
            if (!is_numeric($s)) {
                throw new ModelsException('Invalid Sport.');
            }

            # Verificar que exista
            if (false == $this->db->select('id_sport', 'sports', null, "id_sport = '$s'", 1)) {
                throw new ModelsException('Invalid Sport.');
            }
        }
    }
    /**
     * Guarda los deportes 
     * 
     * @return void
     */
    private function saveSports(array $sports, int $id_user){
        # Preparamos la consulta
        $prepare = $this->db->prepare("INSERT INTO user_sport VALUES (?,?)");
        # Preparamos los paráemtros
        $prepare->bind_param('ii', $id_u, $id_sport);
        # Recorremos los deportes
        foreach ($sports as $s) {
            # ASignamos los valores
            $id_u = $id_user;
            $id_sport = $s;
            

            # Ejecutamos la consulta
            $prepare->execute();
        }

        # Cerramos la consulta
        $prepare->close();
    }

    /**
     * Realiza la acción de registro dentro del sistema
     *
     * @return array : Con información de éxito/falla al registrar el usuario nuevo.
     */
    public function register() : array {
        try {
            global $http;
            # Obtener los datos $_POST
            $first_name = $http->request->get('first_name');
            $last_name = $http->request->get('last_name');
            $email = $http->request->get('email');
            $pass = $http->request->get('pass');
            $birthdate = $http->request->get('datetimepicker');
            $gender = $http->request->get('gender');
            $tyc = $http->request->get('optionsCheckboxes');
            $sports = $http->request->get('favorite_sports');

            # Verificar que no están vacíos
            if (Helper\Functions::e($first_name, $last_name, $email, $pass, $birthdate, $gender)) {
                throw new ModelsException('All fields are required.');
            }

            # Verificar email 
            $this->checkEmail($email);
            
            # Verificar fecha de nacimiento
            $this->checkDate($birthdate);

            # Verificar género
            if (!in_array($gender, ['female', 'male'])) {
                 throw new ModelsException('You must choose a gender.');
            }

            # Verificar Deportes
            $this->checkSports($sports);

            # Verificar termnos y condiciones
            if (null == $tyc) {
                throw new ModelsException('You must accept the terms and conditions.');
            }

            
         
            # Registrar al usuario
            $id_user = $this->db->insert('users', array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'pass' => Helper\Strings::hash($pass),
                'birthdate' => strtotime( str_replace('/', '-', $birthdate)),
                'gender' => $gender,
                'created_at' => time()
            ));

            # Guardamos los deportes
            $this->saveSports($sports, $id_user);

            # Iniciar sesión
            $this->generateSession(array(
                'id_user' => $id_user
            ));

            return array('success' => 1, 'message' => 'Successfully registered');
        } catch (ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }        
    }
    
    /**
      * Envía un correo electrónico al usuario que quiere recuperar la contraseña, con un token y una nueva contraseña.
      * Si el usuario no visita el enlace, el sistema no cambiará la contraseña.
      *
      * @return array<string,integer|string>
    */  
    public function lostpass() : array {
        try {
            global $http, $config;

            # Obtener datos $_POST
            $email = $http->request->get('email');
            
            # Campo lleno
            if (Helper\Functions::emp($email)) {
                throw new ModelsException('The email is required.');
            }

            # Filtro
            $email = $this->db->scape($email);

            # Obtener información del usuario 
            $user_data = $this->db->select('id_user,CONCAT(first_name, " ", last_name) as name', 'users', null, "email='$email'", 1);

            # Verificar correo en base de datos 
            if (false === $user_data) {
                throw new ModelsException('The email is not registered in the system.');
            }

            # Generar token y contraseña 
            $token = md5(time());
            $pass = uniqid();
            $link = $config['build']['url'] . 'lostpass?token='.$token.'&user='.$user_data[0]['id_user'];

            # Construir mensaje y enviar mensaje
            $HTML = 'hello <b>'. $user_data[0]['name'] .'</b>, you have requested to recover your lost password, if you have not done this action you do not need to do anything.
					<br />
					<br />
					To change your password to <b>'. $pass .'</b>, <a href="'. $link .'" target="_blank">click here</a> or on the recover button.';

            # Enviar el correo electrónico
            $dest = array();
			$dest[$email] = $user_data[0]['name'];
            $email_send = Helper\Emails::send($dest,array(
                # Título del mensaje
                '{{title}}' => 'Recover password from ' . $config['build']['name'],
                # Url de logo
                '{{url_logo}}' => $config['build']['url'],
                # Logo
                '{{logo}}' => $config['mailer']['logo'],
                # Contenido del mensaje
                '{{content}} ' => $HTML,
                # Url del botón
                '{{btn-href}}' => $link,
                # Texto del boton
                '{{btn-name}}' => 'Recover password',
                # Copyright
                '{{copyright}}' => '&copy; '.date('Y') .' <a href="'.$config['build']['url'].'">'.$config['build']['name'].'</a> - All rights reserved.'
              ),0);

            # Verificar si hubo algún problema con el envío del correo
            if(false === $email_send) {
                throw new ModelsException('The email could not be sent.');
            }

            # Actualizar datos 
            $id_user = $user_data[0]['id_user'];
            $this->db->update('users',array(
                'tmp_pass' => Helper\Strings::hash($pass),
                'token' => $token
            ),"id_user='$id_user'",1);

            return array('success' => 1, 'message' => 'A link to your email has been sent.');
        } catch(ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }
    }

    /**
     * Desconecta a un usuario si éste está conectado, y lo devuelve al inicio
     *
     * @return void
     */    
    public function logout() {
        global $session, $cookie;
	    
        $session->remove($cookie->get('session_hash') . '__user_id');
        foreach($cookie->all() as $name => $value) {
            $cookie->remove($name);
        }

        Helper\Functions::redir();
    }

    /**
     * Cambia la contraseña de un usuario en el sistema, luego de que éste haya solicitado cambiarla.
     * Luego retorna al sitio de inicio con la variable GET success=(bool)
     *
     * La URL debe tener la forma URL/lostpass?token=TOKEN&user=ID
     *
     * @return void
     */  
    public function changeTemporalPass() {
        global $config, $http;
        
        # Obtener los datos $_GET 
        $id_user = $http->query->get('user');
        $token = $http->query->get('token');

        $success = false;
        if (!Helper\Functions::emp($token) && is_numeric($id_user) && $id_user >= 1) {
            # Filtros a los datos
            $id_user = $this->db->scape($id_user);
            $token = $this->db->scape($token);
            # Ejecutar el cambio
            $this->db->query("UPDATE users SET pass=tmp_pass, tmp_pass=NULL, token=NULL
            WHERE id_user='$id_user' AND token='$token' LIMIT 1;");
            # Éxito
            $success = true;
        }
        
        # Devolover al sitio de inicio
        Helper\Functions::redir($config['build']['url'] . '?sucess=' . (int) $success);
    }

    /**
     * __construct()
     */
    public function __construct(IRouter $router = null) {
        parent::__construct($router);
		$this->startDBConexion();
    }
}