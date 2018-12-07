/**
 * Ajax action to api rest
*/
function lostpass(){
    var $Form = $(this), __data = {};
    $('#lostpass_form').serializeArray().map(function(x){__data[x.name] = x.value;}); 

    if(undefined == $Form.data('locked') || false == $Form.data('locked')) {

        var l = Ladda.create( document.querySelector( '#lostpass_btn' ) );
        l.start();
        $.ajax({
            type : "POST",
            url : "api/lostpass",
            dataType: 'json',
            data : __data,
            beforeSend: function(){ 
                $Form.data('locked', true) 
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
                $Form.data('locked', false);
                l.stop();
            } 
        });
    }
} 

/**
 * Events
 */
$('form#lostpass_form').submit(function(e) {
    e.defaultPrevented;
    lostpass();
    return false;
});
