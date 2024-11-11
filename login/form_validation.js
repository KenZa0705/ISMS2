$(document).ready(function(){
    $('#login_form').validate({

        rules:{
            
            email:{
                required: true,
                email: true
            },

            password:{
                required: true,
                minlength: 6
            }

        },

        highlight: function(element) {  
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        }

    })
});

$(document).ready(function(){
    $('#new_password_form').validate({

        rules:{
            
            email:{
                required: true,
                email: true
            },

            password:{
                required: true,
                minlength: 6
            }

        },

        highlight: function(element) {  
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        }

    })
});

$("#signin").click(function (event) {
    event.preventDefault(); 

    if ($("#login_form").valid()) {
        var recaptchaResponse = grecaptcha.getResponse();
        console.log("reCAPTCHA Response: ", recaptchaResponse);

        
        if (recaptchaResponse.length === 0) {
            $('#recaptchaModal').modal({
            }).modal('show');
        } else {
            $("#login_form").submit();
        }
    }
});

$('#recaptchaModal').on('shown.bs.modal', function () {
    $(this).find('button').focus();
});

$("#recaptchaModal button").click(function () {
    $("#recaptchaModal").modal('hide');
});



