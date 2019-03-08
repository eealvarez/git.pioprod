encomienda = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
        $('#waitDiv').hide();
        
    },
    
    _conectEvents: function(){
        
        $("#clausulasEncomienda").click(function (e){
//            console.log("clausulasEncomienda-init");
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
                    if(html !== null && $.trim(html) !== ""){
                        core.showMessageDialog({
                            size : BootstrapDialog.SIZE_LARGE,
                            type : BootstrapDialog.TYPE_INFO,
                            title : 'Términos y Condiciones',
                            buttons : [{
                                label: 'Aceptar',
                                cssClass: 'btn-primary',
                                action: function(dialog){
                                    dialog.close();
                                }
                            }],
                            message : html
                        }); 
                    }                
                },
                errorCallback: function(jqXHR, statusText, error){
                    btn.button('reset');
                }
            });
        });
        
         $("#buscarInfoEncomiendaBtn").click(function (e){
//            console.log("sendMensaje-init");
            e.preventDefault();
            e.stopPropagation();
            $("#itemsPanel").html("");
            var idEncomienda = $("#idEncomienda").val();
            
            if(idEncomienda === null || $.trim(idEncomienda) === ""){
                core.showMessageDialog({
                    type : BootstrapDialog.TYPE_DANGER,
                    message : "Debe especificar el número de guía."
                });
                return;
            }
            
            var form = $("#encomiendaForm");
            if(core.customValidateForm(form) === true){
                var btn = $(this);
                btn.button('loading');
                core.request({
                    url : $(this).attr("href"),
                    type: "GET",
                    dataType: "json",
                    async: true,
                    extraParams : { id : idEncomienda },
                    successCallback: function(data){
//                        console.debug(data);
                        if(data){
                            btn.button('reset');
                            var str = "<div class='panel panel-default'>"+
                                      "<div class='panel-heading'>Encomienda Nro: "+idEncomienda+"</div>"+
                                      "<ul class='list-group'>";
                            var items = data.data;
                            var cant = 1;
                            $.each(items, function(i, item){
                               str += "<li class='list-group-item'>" + cant + ": " + item + "</li>";
                               cant++;
                            });
                            str += "</ul></div>";
                            $("#itemsPanel").append($(str));
                        }                        
                    },
                    errorCallback: function(jqXHR, statusText, error){
                        btn.button('reset');
                    }
                });
            }
        });
        
        $("#buscarInfoEncomiendaByClienteBtn").click(function (e){
//            console.log("sendMensaje-init");
            e.preventDefault();
            e.stopPropagation();
            $("#itemsPanel").html("");
            var btn = $(this);
            btn.button('loading');
            core.request({
                url : $(this).attr("href"),
                type: "GET",
                dataType: "json",
                async: true,
                successCallback: function(json){
//                    console.debug(json);
                    btn.button('reset');
                    var itemsEnc = json.data;
//                    console.debug(itemsEnc);
                    $.each(itemsEnc, function(idEncomienda, values){
                        var str = "<div class='panel panel-default'>"+
                        "<div class='panel-heading'>Encomienda"+(idEncomienda === 0 ? "" : " Nro: "+idEncomienda)+"</div>"+
                        "<ul class='list-group'>";
                        if(Array.isArray(values)){
                            var cant = 1;
                            $.each(values, function(i, item){
                                str += "<li class='list-group-item'>" + cant + ": " + item + "</li>";
                                cant++;
                            });
                        }else{
                            str += "<li class='list-group-item'>1: " + values + "</li>";
                        }
                        str += "</ul></div>";
                        $("#itemsPanel").append($(str));
                    });
                },
                errorCallback: function(jqXHR, statusText, error){
                    btn.button('reset');
                }
            });
        });
    }
};


