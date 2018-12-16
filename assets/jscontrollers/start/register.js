/**
 * Ajax action to api rest
*/
function register(){
    var $ocrendForm = $(this), __data = new FormData(document.getElementById('register_form'));
    //$('#register_form').serializeArray().map(function(x){__data[x.name] = x.value;}); 


    if(undefined == $ocrendForm.data('locked') || false == $ocrendForm.data('locked')) {

        var l = Ladda.create( document.querySelector( '#register_btn' ) );
        l.start();
        $.ajax({
            type : "POST",
            url : "api/register",
            contentType:false,
            processData:false,
            data : __data,
            beforeSend: function(){ 
                $ocrendForm.data('locked', true) 
            },
            success : function(json) {
                if(json.success == 1) {
                    success_message(json.message);
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                } else {
                    error_message(json.message);
                }
            },
            error : function(xhr, status) {
                error_message('An internal problem has occurred');
            },
            complete: function(){ 
                $ocrendForm.data('locked', false);
                l.stop();
            } 
        });
    }
} 

/**
 * Events
 */
$('form#register_form').submit(function(e) {
    e.defaultPrevented;
    register();
    return false;
});
