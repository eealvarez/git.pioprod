oficinas = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
        
    },
    
    _conectEvents: function(){
         $(".detalleEstacion").click(oficinas.clicVerInfoEstacion);
    },
    
    clicVerInfoEstacion: function(e){
//            console.log("sendMensaje-init");
            $(this).addClass("linkSeleccionado");
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);
            btn.button('loading');
            core.request({
                url : $(this).attr("href"),
                type: "GET",
                dataType: "html",
                async: true,
                successCallback: function(html){
                    btn.button('reset');
                    if($.trim(html) !== ""){
                        core.showMessageDialog({
                            title : "Información de la oficina",
                            message:html,
                            size : BootstrapDialog.SIZE_LARGE,
                            type : BootstrapDialog.TYPE_INFO
                        });
                    }else{
                        core.showMessageDialog({
                            message:'No se pudo obtener los detalles de la estación.'
                        });
                    }
                },
                errorCallback: function(jqXHR, statusText, error){
                    btn.button('reset');
                }
            });
    },
    
    createMapGuatemala: function(){
        var varLocation = new google.maps.LatLng(14.654162,-90.551068);
        var mapOptions = {
            center: varLocation,
            zoom: 6
        };
        var varMap = new google.maps.Map(document.getElementById("mapGuatemata"), mapOptions);
        var varMarker = new google.maps.Marker({
            position: varLocation,
            map: varMap,
            title:"Guatemala"
        });
        varMarker.setMap(varMap);
    }
};


