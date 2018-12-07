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
     * Trae el id de un usuario registrado con facebook
     * 
     * @param $fbId : Id del user de facebook
     * 
     * @return array|false
     */
    private function getFbUserById($fbId){
        $fbId = $this->db->scape($fbId);
        return $this->db->select('id_user', 'users', null, "social_id = '$fbId'", 1);
    }
    /**
     * Registra un usuario con facebook
     * 
     * @param array $user : Datos del usuario
     * 
     * @return id del usuario en forma de matriz $array[0]['id_user']
     */
    private function registerFB($user){
        # Verificar email 
        $this->checkEmail($user['email']);

        # Verificar fecha de nacimiento
        $this->checkDate($user['datetimepicker']);

        # Verificar género
        if (!in_array($user['gender'], ['female', 'male'])) {
             throw new ModelsException('You must choose a gender.');
        }
        
        # Registrar al usuario
        $id_user = $this->db->insert('users', array(
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'pass' => Helper\Strings::hash( uniqid() ),
            'birthdate' => strtotime( str_replace('/', '-', $user['datetimepicker'])),
            'gender' => $user['gender'],
            'social_id' => $user['id'],
            'created_at' => time()
        ));

        # Datos a devolver
        return array(
            array(
                'id_user' => $id_user
            )
        );
    }
    /**
     * Valida si un usuario está registrado o no.
     * si no está registrado lo registra e inicia sesión | inicia sesión
     * 
     * @param array $user : Datos del usuaio
     * 
     * @return void
     */
    private function checkFbUser($user){
        # Datos de la vista
        $id = $user['id'];
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $email = $user['email'];
        $birthdate = $user['datetimepicker'];
        $gender = $user['gender'];

        # Id del usuario
        $id_user = $this->getFbUserById($id);
        
        # Validar existencia del usuario
        if (false == $id_user) {
            $id_user = $this->registerFB($user);
        }

        # Iniciar sesión
        $this->generateSession(array(
            'id_user' => $id_user[0]['id_user']
        ));
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
            $user = $this->getFbUserById($http->request->get('id'));
            # Evaluar si existe fecha de nacimiento y genero
            if (false == $user && Helper\Functions::e($http->request->get('datetimepicker'),$http->request->get('gender'))) {
                 return array('nextStep' => true);
            }
            # En caso de no existir el usuario pero si la fecha y el género
            else if (false == $user) {
                # Validar usuario
                $this->checkFbUser($http->request->all());
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