/**
 * Ajax action to api rest
*/
function requests(){
    var $ocrendForm = $(this), __data = {};
    $('#requests_form').serializeArray().map(function(x){__data[x.name] = x.value;}); 

    if(undefined == $ocrendForm.data('locked') || false == $ocrendForm.data('locked')) {
        $.ajax({
            type : "POST",
            url : "api/requests",
            dataType: 'json',
            data : __data,
            beforeSend: function(){ 
                $ocrendForm.data('locked', true) 
            },
            success : function(json) {
                if(json.success == 1) {
                    alert(json.message);
                } else {
                    alert(json.message);
                }
            },
            error : function(xhr, status) {
                alert('Ha ocurrido un problema interno');
            },
            complete: function(){ 
                $ocrendForm.data('locked', false);
            } 
        });
    }
} 

/**
 * Events
 */
$('#requests').click(function(e) {
    e.defaultPrevented;
    requests();
});
$('form#requests_form input').keypress(function(e) {
    e.defaultPrevented;
    if(e.which == 13) {
        requests();

        return false;
    }
});
