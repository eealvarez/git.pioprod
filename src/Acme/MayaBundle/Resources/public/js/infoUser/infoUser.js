infoUser = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
       
        core.request({
            url : $("#loginCheckUserPath").val(),
            type: "GET",
            dataType: "json",
            async: true,
            successCallback: function(data){
                if(data && data.login === true){
    
                    $(".iniciarSesion").addClass("hidden");
                    $(".cerrarSesion").removeClass("hidden");
                    
                    var picture = data.picture;
                    if(picture === null || $.trim(picture) === ""){
                        $(".verPerfilUsuario").addClass("hidden");
                    }else{
                        $(".pictureProfile").attr("src", picture);
                        $(".verPerfilUsuario").removeClass("hidden");
                    }
                    $("abbr.codigoUsuario").text(data.codigo);
                    
                }else{

                    $(".iniciarSesion").removeClass("hidden");
                    $(".cerrarSesion").addClass("hidden");
                }
            }
        });
        
    },
    
    _conectEvents: function(){
        $(".iniciarSesion").click(function(e){
//            console.log("iniciarSesion-init");
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);
            btn.button('loading');
            core.request({
                url : btn.attr("href"),
                type: "GET",
                dataType: "html",
                async: true,
                successCallback: function(response){
                    btn.button('reset');
                    core.showMessageDialog({
                        title : "INICIAR SESION",
                        type : BootstrapDialog.TYPE_INFO,
                        message : response
                    });
                },
                errorCallback: function(jqXHR, statusText, error){
                    btn.button('reset');
                }
            });
        });
        
        $(".verPerfilUsuario").click(function(e){
//            console.log("verPerfilUsuario-init");
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);
//            btn.button('loading');
            core.request({
                url : btn.attr("href"),
                type: "GET",
                dataType: "html",
                async: true,
                successCallback: function(response){
                    btn.button('reset');
                    core.showMessageDialog({
                        title : "PERFIL DE USUARIO",
                        type : BootstrapDialog.TYPE_INFO,
                        message : response
                    });
                },
                errorCallback: function(jqXHR, statusText, error){
                    btn.button('reset');
                }
            });
        });
    }
};
infoUser.funcionesAddOnload();

