/*
* Status del usaurio de facebook
*/

function statusChangeCallback(response) {
    logoutFB();
    if (response.status === 'connected') {
      testAPI();
    } else {
    	console.log('deslogeado');
    }
}

/*
* Se ejecuta al hacer click en el botón de logease con facebok
*/

function loginFB(){
  	FB.login(function(response) {
  		console.log(response);
  		testAPI();
	}, {scope: 'public_profile'});
}

/*
* Valida el estado de un login
*/

function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
}

/*
* Inicialización de la api
*/

window.fbAsyncInit = function() {
    FB.init({
      appId            : '364400100982586',
      cookie     	   : true,
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v3.2'
    });
    logoutFB();
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


/*
* Obtiene los datos del usaurio
*/

function testAPI() {
	//{fields: 'id,birthday,email,first_name,gender,last_name'},
	FB.api('/me', {locale: 'en_US', fields: 'id,first_name,last_name,email,link,gender,locale,picture'}, function(response) {
	  	//console.log('Successful login for: ' + response.name);
	  	console.log(response);
	});
}

/*
* Deslogea a un usuario
*/

function logoutFB(){
	FB.getLoginStatus(function(response) {
        if (response && response.status === 'connected') {
            FB.logout(function(response) {
                location.reload();
            });
        }
    });
}