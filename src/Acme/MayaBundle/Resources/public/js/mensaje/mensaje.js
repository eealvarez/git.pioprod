mensaje = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
     
    },
    
    _conectEvents: function(){
         $(".sendMensaje").click(function (e){
//            console.log("sendMensaje-init");
            e.preventDefault();
            e.stopPropagation();
            var form = $("#contactform");
            if(core.customValidateForm(form) === true && mensaje.customValidate() === true){
                var btn = $(this);
                btn.button('loading');
                $(form).ajaxSubmit({
                    target: form.attr('action'),
                    type : "POST",
                    dataType: "html",
                    cache : false,
                    async:false,
                    beforeSubmit: function() { 
                        core.showLoading({showLoading:true});
                   },
                   error: function() {
                       btn.button('reset');
                       core.hideLoading({showLoading:true});
                   },
                   success: function(responseText) {
                       btn.button('reset');
                       core.hideLoading({showLoading:true});
                        if(!core.procesarRespuestaServidor(responseText)){
                            core.showMessageDialog({
                                type : BootstrapDialog.TYPE_SUCCESS,
                                message : "Mensaje enviado satisfactoriamente."
                            });
                            
                            $("#contactform").resetForm();
                            if(grecaptcha){
                                var id = customRecaptcha.getIdRecaptcha("recaptchaMessage");
                                grecaptcha.reset(id);
                            }
                            
                        }else{
                            if(grecaptcha){
                                var id = customRecaptcha.getIdRecaptcha("recaptchaMessage");
                                grecaptcha.reset(id);
                            }
                        }
                   }
               });
            }
        });
    },
    
    customValidate : function(){
//        var recaptcha = $("#recaptcha_response_field").val();
//        if(recaptcha === null || $.trim(recaptcha) === ""){
//            core.showMessageDialog({
//                title: 'Error',
//                message : "Para enviar el mensaje dede definir el captcha."
//            });
//            return false;
//        }
        return true;
    }
};

