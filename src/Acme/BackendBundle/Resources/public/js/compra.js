compra = {
    
    funcionesAddOnload : function() {
	this._init();		
	this._conectEvents();
    },
    
//    selectedIdRows : [],
    _init : function() {
        
        // -------------------  FILTROS INIT ------------------

        // -------------------  FILTROS END ------------------
        // -------------------  GRID INIT ------------------
        
        $("#grid").bootgrid({
            selection: true,
            multiSelect: true,
            keepSelection: true,
            rowCount : [10, 25, 50, 100],
            sorting : false,
            post: function () { return { id: "b0df282a-0d67-40e5-8558-c9e93b7befed" }; },
            formatters: {
                "commands": function(column, row)
                {
//                    console.debug(column);
//                    console.debug(row);
                    var str = '<button type="button" class="btn btn-xs btn-default command-consultar"  data-loading-text="Cargando..." title="Consultar" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-eye-open"></span></button>';
                    if(row.idEstadoCompra === "2"){ //Compra Pagada
                        if(row.cantidadBoletos > 0){
                            str += '<button type="button" class="btn btn-xs btn-default command-facturar"  data-loading-text="Procesando..." title="Facturar" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-list-alt"></span></button>';
                        }else{
                            str += '<button type="button" class="btn btn-xs btn-default command-generar-boletos"  data-loading-text="Procesando..." title="Generar Boletos" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-repeat"></span></button>';
                        }
                        
                    }else if(row.idEstadoCompra === "3"){ //Facturada
                        
                        str += '<button type="button" class="btn btn-xs btn-default command-visualizar-factura"  data-loading-text="Cargando..." title="Visualizar factura" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-file"></span></button>';
                        
                        if(row.idEstadoFactura === 1){ //CREADA
                            str += '<button type="button" class="btn btn-xs btn-default command-actualizar-datos"  data-loading-text="Procesando..." title="Actualizar datos de la factura" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-edit"></span></button>';
                            str += '<button type="button" class="btn btn-xs btn-default command-enviar-factura"  data-loading-text="Procesando..." title="Enviar factura" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-send"></span></button>';
                        }else if(row.idEstadoFactura === 2){ //Enviada
                            str += '<button type="button" class="btn btn-xs btn-default command-recibir-factura"  data-loading-text="Procesando..." title="Recibiendo factura" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-flag"></span></button>';
                        }else if(row.idEstadoFactura === 3){ //Recibida
                            str += '<button type="button" class="btn btn-xs btn-default command-entregar-factura"  data-loading-text="Entregando..." title="Entregando factura" data-row-id="' + row.id + '"><span class="glyphicon glyphicon-hand-right"></span></button>';
                        }
                        
                    }
                    return str;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function() {
//            compra.selectedIdRows = [];
            $(this).find(".command-consultar").on("click", compra.consultarCompra).end()
                   .find(".command-facturar").on("click", compra.facturarCompra).end()
                   .find(".command-generar-boletos").on("click", compra.generarBoletos).end()
                   .find(".command-visualizar-factura").on("click", compra.visualizarFactura).end()
                   .find(".command-actualizar-datos").on("click", compra.facturarCompra).end() 
                   .find(".command-enviar-factura").on("click", compra.enviarFactura).end()
                   .find(".command-recibir-factura").on("click", compra.recibirFactura).end()
                   .find(".command-entregar-factura").on("click", compra.entregarFactura);
        })
//        .on("selected.rs.jquery.bootgrid", function(e, rows) {
//            console.log("selected-init");
//            for (var i = 0; i < rows.length; i++){
//                compra.selectedIdRows.push(rows[i].id);
//            }
//            console.log("Select: " + compra.selectedIdRows.join(","));
//        })
//        .on("deselected.rs.jquery.bootgrid", function(e, rows) {
//            console.log("deselected-init");
//            for (var i = 0; i < rows.length; i++){
//                compra.selectedIdRows = core.removeItemArray(compra.selectedIdRows, rows[i].id);
//            }
//            console.log("Select: " + compra.selectedIdRows.join(","));
//        })
        ;
        
        var actions = $("<div class='actions btn-group actions' style='margin-right: 15px;'></div>").insertBefore(".actions.btn-group");
        actions.append($("<button title='Enviar todo lo seleccionado' type='button' class='btn btn-default btn-enviar' data-loading-text='Enviando...'><span class='icon glyphicon glyphicon-send'></span></button>"));
        actions.append($("<button title='Recibir todo lo seleccionado' type='button' class='btn btn-default btn-recibir' data-loading-text='Recibiendo...'><span class='icon glyphicon glyphicon-flag'></span></button>"));
        
        
        // -------------------  GRID END ------------------
        
     },
     
     _conectEvents : function() {
         
         $(".btn-enviar").click(compra.enviarAllFactura);
         $(".btn-recibir").click(compra.recibirAllFactura);
         
     },
     
     enviarAllFactura : function(e) {
        console.log("Enviar All Facturas");
        var ids = $("#grid").bootgrid("getSelectedRows");
        if(ids.length < 1){
            core.showMessageDialog({
                title: 'Error',
                message : "Debe seleccionar al menos una compra."
            });
            return;
        }
        
        var btn = $(this);
        BootstrapDialog.confirm("Esta seguro que desea enviar las facturas " + ids.join(",") + ".", function (result){
            if(result){
                btn.button('loading');
                $.ajax({
                    url: $('#pathEnviarAllFacturas').val(),
                    type: "POST",
                    dataType : "html",
                    data : { ids : ids.join(",") },
                    success: function(html){
                        if(!core.procesarRespuestaServidor(html)){
                            core.showMessageDialog({
                                type : BootstrapDialog.TYPE_SUCCESS,
                                message : "Operación realizada satisfactoriamente."
                            }); 
                            $("#grid").bootgrid("reload");
                        }
                    }
                }).always(function () {
                    btn.button('reset');
                });
            }
        });
     },
     
     recibirAllFactura : function(e) {
        console.log("Recibir All Facturas");
        var ids = $("#grid").bootgrid("getSelectedRows");
        if(ids.length < 1){
            core.showMessageDialog({
                title: 'Error',
                message : "Debe seleccionar al menos una compra."
            });
            return;
        }
        
        var btn = $(this);
        BootstrapDialog.confirm("Esta seguro que desea recibir las facturas " + ids.join(",") + ".", function (result){
            if(result){
                btn.button('loading');
                $.ajax({
                    url: $('#pathRecibirAllFacturas').val(),
                    type: "POST",
                    dataType : "html",
                    data : { ids : ids.join(",") },
                    success: function(html){
                        if(!core.procesarRespuestaServidor(html)){
                            core.showMessageDialog({
                                type : BootstrapDialog.TYPE_SUCCESS,
                                message : "Operación realizada satisfactoriamente."
                            }); 
                            $("#grid").bootgrid("reload");
                        }
                    }
                }).always(function () {
                    btn.button('reset');
                });
            }
        });
     },
     
     generarBoletos : function(e) {
        var id = $(this).data("row-id");
        console.log("Generar Boletos:" + id);
        console.log("PENDIENTE DE IMPLEMENTACION");
     },
     
     consultarCompra : function(e) {
        var id = $(this).data("row-id");
        console.log("Consultar Compra:" + id);
        var btn = $(this);
        btn.button('loading');
         $.ajax({
            url: $('#pathConsultarCompra').val(),
            type: "GET",
            dataType : "html",
            data : { id : id },
            success: function(html){
                if(!core.procesarRespuestaServidor(html)){
                    BootstrapDialog.show({
                        title : 'Consultar Compra',
                        cssClass : 'form',
                        size : BootstrapDialog.SIZE_NORMAL,
                        type : BootstrapDialog.TYPE_PRIMARY,
                        message : html,
                        buttons : [{
                            label: 'Aceptar',
                            cssClass: 'btn-primary',
                            action: function(dialog) {
                                typeof dialog.getData('callback') === 'function' && dialog.getData('callback')(true);
                                dialog.close();
                            }
                        }]
                    });
                }
            }
        }).always(function () {
            btn.button('reset');
        });  
     },
     
     entregarFactura : function(e) {
        var id = $(this).data("row-id");
        console.log("Entregar Factura:" + id);
        var btn = $(this);
        btn.button('loading');
         $.ajax({
            url: $('#pathEntregarFactura').val(),
            type: "GET",
            dataType : "html",
            data : { id : id },
            success: function(html){
                if(!core.procesarRespuestaServidor(html)){
                    BootstrapDialog.show({
                        title : 'Entregar Factura',
                        cssClass : 'form',
                        size : BootstrapDialog.SIZE_NORMAL,
                        type : BootstrapDialog.TYPE_PRIMARY,
                        message : html,
                        buttons : [{
                            label: 'Aceptar',
                            cssClass: 'btn-primary',
                            action: compra.submitForm
                        }]
                    });
                }
            }
        }).always(function () {
            btn.button('reset');
        });  
     },
     
     recibirFactura : function(e) {
        var id = $(this).data("row-id");
        console.log("Recibir Factura:" + id);
        var btn = $(this);
        btn.button('loading');
         $.ajax({
            url: $('#pathRecibirFactura').val(),
            type: "GET",
            dataType : "html",
            data : { id : id },
            success: function(html){
                if(!core.procesarRespuestaServidor(html)){
                    BootstrapDialog.show({
                        title : 'Recibir Factura',
                        cssClass : 'form',
                        size : BootstrapDialog.SIZE_NORMAL,
                        type : BootstrapDialog.TYPE_PRIMARY,
                        message : html,
                        buttons : [{
                            label: 'Aceptar',
                            cssClass: 'btn-primary',
                            action: compra.submitForm
                        }]
                    });
                }
            }
        }).always(function () {
            btn.button('reset');
        }); 
     },
     
     enviarFactura : function(e) {
        var id = $(this).data("row-id");
        console.log("Enviar Factura:" + id);
        var btn = $(this);
        btn.button('loading');
         $.ajax({
            url: $('#pathEnviarFactura').val(),
            type: "GET",
            dataType : "html",
            data : { id : id },
            success: function(html){
                if(!core.procesarRespuestaServidor(html)){
                    BootstrapDialog.show({
                        title : 'Enviar Factura',
                        cssClass : 'form',
                        size : BootstrapDialog.SIZE_NORMAL,
                        type : BootstrapDialog.TYPE_PRIMARY,
                        message : html,
                        buttons : [{
                            label: 'Aceptar',
                            cssClass: 'btn-primary',
                            action: compra.submitForm
                        }]
                    });
                }
            }
        }).always(function () {
            btn.button('reset');
        }); 
     },
     
     visualizarFactura : function(e) {
         var id = $(this).data("row-id");
         console.log("Visualizar Factura:" + id);
         var btn = $(this);
         btn.button('loading');
         core.previewDataPDF($("#pathViewFactura").val(), { id : id }, function(){
             btn.button('reset');
         });
     },
     
     facturarCompra : function(e) {
         var id = $(this).data("row-id");
         console.log("Facturar Compra:" + id);
         var btn = $(this);
         btn.button('loading');
         $.ajax({
            url: $('#pathFacturar').val(),
            type: "GET",
            dataType : "html",
            data : { id : id },
            success: function(html){
                if(!core.procesarRespuestaServidor(html)){
                    BootstrapDialog.show({
                        title : 'Registrar Factura',
                        cssClass : 'form',
                        size : BootstrapDialog.SIZE_NORMAL,
                        type : BootstrapDialog.TYPE_PRIMARY,
                        message : html,
                        buttons : [{
                            label: 'Aceptar',
                            cssClass: 'btn-primary',
                            action: compra.submitForm
                        }]
                    });
                }
            }
        }).always(function () {
            btn.button('reset');
        });
     },
     
     submitForm : function(dialog) {
         console.log("crearFactura-init");
         var form = $("#facturaForm");
         if(core.customValidateForm(form) === true){
            var btn = $(this);
            btn.button('loading');
            $(form).ajaxSubmit({
                target: form.attr('action'),
                type : "POST",
                dataType: "html",
                cache : false,
                async:false,
                error: function() {
                    btn.button('reset');
                },
                success: function(responseText) {
                    btn.button('reset');
                    if(!core.procesarRespuestaServidor(responseText)){
                        dialog.close();
                        core.showMessageDialog({
                            type : BootstrapDialog.TYPE_SUCCESS,
                            message : "Operación realizada satisfactoriamente."
                        }); 
                        $("#grid").bootgrid("reload");
                    }
                }
            });
         }         
     }
     
};
compra.funcionesAddOnload();