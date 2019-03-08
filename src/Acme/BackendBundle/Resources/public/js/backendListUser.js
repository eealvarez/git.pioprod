backendListUser = {
    
    funcionesAddOnload : function() {
        console.debug("backendListUser.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("backendListUser.funcionesAddOnload-end");
    },
			
    _init : function() {
        

    },
    
    _conectEvents : function() {
        
        $('.changePassword').bind("click", this.changePassword);
        $(".aceptarPopUp").click(function(e) {
            console.log("clic..");
                e.preventDefault(); // <-- important
                var form = $(".changePasswordForm");
                if(backendListUser.customValidateForm(form) === true){
                   $(form).ajaxSubmit({
                        success : function(success) {
                            console.debug("changePasswordForm-success-init");
                            if(success.result === "ok"){
                                $('#usuarioModal').modal('hide');                         
                                alert("Operación realizada satisfactoriamente.");
                                return;
                            }else{
                                var component = document.createElement("div");
                                component.innerHTML = success;
                                var listDiv = component.getElementsByTagName('div');
                                var bodyFormDiv = $(listDiv[0]);
                                $('#usuarioModal').find(".modal-body").html(bodyFormDiv);
                                console.debug("changePasswordForm-success-end"); 
                            }
                        },
                        error : function(jqXHR) {
                            console.debug("error");
                            console.debug(jqXHR);
                            alert("Ha ocurrido un error.")
                        }
                    }); 
                }
                
            }); 
    },
    
    changePassword : function(event) {
        console.debug("changePassword-clic");
        event.preventDefault();
        console.debug($(this).data("href"));
        $.ajax({
            url: $(this).data("href"),
            type: "GET",
            dataType : "html",
            success: function(html){
                $('#usuarioModal').find(".modal-body").html(html);
                $('#usuarioModal').modal('show');
            }
        });
        
    },
    
    customValidateForm: function(form) {
        form = $(form);
        if(form.length === 0){
            throw new Error("No se encontro el formulario. Selector:"+form);
        }
        form.find("label.text-error").remove(); //Eliminar mensajes de error antes de validar.
        form.find("div.text-error, input.text-error").removeClass("text-error");
        var validator = $(form).validate({
            ignoreTitle: true,
            errorClass: "text-error"
        });
        return validator.form();
    },
    
}