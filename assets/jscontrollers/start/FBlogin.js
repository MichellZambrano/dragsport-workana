/*
* Datos a enviar
*/
var __data = new FormData();
/*
* Status del usaurio de facebook
*/

function statusChangeCallback(response) {
    logoutFB();
    if (response.status === 'connected') {
      data();
    } 
}

/*
* Se ejecuta al hacer click en el botón de logease con facebok
*/

function loginFB(){
  	FB.login(function(response) {
  		data();
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
* Obtiene los datos del usuario y los guarda
*/

function data() {
	FB.api('/me', {fields: 'id,first_name,last_name,email'}, function(response) {
    $.each(response, function(index,element){ __data.append(index, element); });

    $.ajax({
      type : "POST",
      url : "api/loginFB",
      contentType:false,
      processData:false,
      data : __data,
      beforeSend: function(){ },
      success : function(json) {
          if (json.hasOwnProperty('nextStep') && json.nextStep) {
            showContinue();
          }else{
            if(json.success == 1) {
                success_message(json.message);
                setTimeout(function(){
                    logoutFB();
                }, 1000);
            } else {
                error_message(json.message);
            }
          }
      },
      error : function(xhr, status) {
          error_message('An internal problem has occurred');
      },
      complete: function(){ } 
    });

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

/*
* Función para completar los datos faltantes
*/
function showContinue(){

  let year = new Date().getFullYear();
  let sports = '';

  $('#load_page').addClass('page-loading');
  $('#load_page').html(`<div class="spinner">
      <div class="double-bounce1"></div>
      <div class="double-bounce2"></div>
  </div>
  <span>Loading</span>`);

  $.ajax({
      type : "GET",
      url : "api/sports",
      beforeSend: function() {},
      success : function(json) { 
        if (false != json) {
          $.each(json, function(i, e){
            sports += `<option value="${e.id_sport}">${e.name}</option>`;
          });
        }


        let html_modal = `<div class="modal fade" id="lastStep" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
              <form class="modal-content modal-sm" id="continue_form" role="form">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Only one more step!</h5>
                </div>
                    <div class="modal-body">
                      <div class="row">
                    
                        <div class="col col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                          <div class="form-group date-time-picker label-floating">
                            <label class="control-label">Your Birthday</label>
                            <input name="datetimepicker" value="01/01/${year - 18}" />
                            <span class="input-group-addon">
                              <svg class="olymp-calendar-icon"><use xlink:href="assets/dragsport/svg-icons/sprites/icons.svg#olymp-calendar-icon"></use></svg>
                            </span>
                          </div>
                          <div class="form-group label-floating is-select">
                            <label class="control-label">Your Gender</label>
                            <select class="selectpicker form-control" name="gender">
                              <option value="male">Male</option>
                              <option value="female">Female</option>
                            </select>
                          </div>

                          <div class="form-group label-floating is-select">
                            <label class="control-label">favorite sports</label>
                            <select class="selectpicker form-control" name="favorite_sports[]" multiple title="Choose your favorites sports">
                              ${sports}
                            </select>
                          </div>

                        </div>
                      </div>
                    </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-purple btn-lg full-width" id="continue_btn">Continue</button>
                  </div>
                </form>
            </div>
          </div>`;

        

        $('.modal-mx').html(html_modal);

        $('#lastStep').modal({
          keyboard: false,
          show: true,
          backdrop: 'static'
        });

        $('.selectpicker').selectpicker();
        dateTimePicker();

        $('form#continue_form').submit(function(e){
          e.defaultPrevented;
          var $Form = $(this);
          $Form.serializeArray().map(function(x){__data.append(x.name, x.value);}); 

          if(undefined == $Form.data('locked') || false == $Form.data('locked')) {
            var l = Ladda.create( document.querySelector( '#continue_btn' ) );
            l.start();

            $.ajax({
                type : "POST",
                url : "api/loginFB",
                contentType:false,
              processData:false,
                data : __data,
                beforeSend: function(){ 
                    $Form.data('locked', true) 
                },
                success : function(json) {
                    if(json.success == 1) {
                        success_message(json.message);
                        setTimeout(function(){
                            logoutFB();
                        }, 1000);
                    } else {
                        error_message(json.message);
                    }
                },
                error : function(xhr, status) {
                    error_message('An internal problem has occurred');
                },
                complete: function(){ 
                    $Form.data('locked', false);
                    l.stop();
                } 
            });
          }

          return false;
        });




      },
      error : function(xhr, status) {
          error_message('An internal problem has occurred');
      },
      complete: function(){  
        $('#load_page').removeClass('page-loading');
        $('#load_page').html(' ');
      } 
  });
  
}





/* -----------------------------
* Date time picker input field
* Script file: daterangepicker.min.js, moment.min.js
* Documentation about used plugin:
* https://v4-alpha.getbootstrap.com/getting-started/introduction/
* ---------------------------*/
function dateTimePicker(){
  var date_select_field = $('input[name="datetimepicker"]');
  if (date_select_field.length) {
    var start = moment().subtract(29, 'days');

    date_select_field.daterangepicker({
      startDate: start,
      autoUpdateInput: false,
      singleDatePicker: true,
      showDropdowns: true,
      locale: {
        format: 'DD/MM/YYYY'
      }
    });
    date_select_field.on('focus', function () {
      $(this).closest('.form-group').addClass('is-focused');
    });
    date_select_field.on('apply.daterangepicker', function (ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY'));
      $(this).closest('.form-group').addClass('is-focused');
    });
    date_select_field.on('hide.daterangepicker', function () {
      if ('' === $(this).val()){
        $(this).closest('.form-group').removeClass('is-focused');
      }
    });

  }
}