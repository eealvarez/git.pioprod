buscarConexion = {
    
    procesandoDatos : false,
    correlativoIda : 1,
    correlativoRegreso : 1,
    cantAsientosSelectEsquemaIda:[],
    asientosSelectEsquemaIda: [],
    cantAsientosSelectEsquemaRegreso:[],
    asientosSelectEsquemaRegreso: [],
    datosfullPasajero: false,
    datosfullCliente: false,
    reservaciones : [],
      
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    _init: function(){
        
        $('#waitDiv').hide();
        buscarConexion.initFormularioBusqueda();
        buscarConexion.initComponentesFechas();
        //$('.col3row4').append($('.telefono'));
        buscarConexion.resetDivContenido();
        buscarConexion.validarFormularios();
        buscarConexion.initWizardCompra();
        
        buscarConexion.styleResize();
        buscarConexion.resetComponentes();
        
        buscarConexion.loadOficinas();
    },
    
    _conectEvents: function(){
        //boton de busqueda de conexiones
         $('#btnBuscar').click(function(event){
            buscarConexion.buscarConexiones(event);
         });
        
         //terminos en el formulario de cliente
         $('.terminos').click(function(event){
             event.preventDefault();
             var data = buscarConexion.loadTerminosCondiciones();
             BootstrapDialog.show({
                type: BootstrapDialog.TYPE_DEFAULT,
                title: 'Términos y condiciones del sitio',
                cssClass: 'form',
                "message" : "<div class='form-control textoTerminos'>"+data+"</div>",
                buttons: [{
                    label: "<i class='glyphicon glyphicon-ok'></i> Acepto",
                    cssClass: 'btnAcepto btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm col-lg-4 col-lg-offset-2 col-sm-4 col-sm-offset-2 col-md-4 col-md-offset-2 col-xs-5 col-xs-offset-1',
                    action: function(dialog) {
                        $('.terminos').html("<i class='glyphicon glyphicon-ok'></i><span> Términos y condiciones del sitio </span>");
                        $('.aceptoTerminos').val("1");
                        $('.terminos i').css('color','green');
                        $($('.terminos span')[0]).css('color','green');
                          
                        dialog.close();
                    }
                }, {
                    label: "<i class='glyphicon glyphicon-remove' style='margin-right:6px;'></i>No Acepto",
                    cssClass: 'btnNoAcepto btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm col-lg-4 col-sm-4 col-md-4 col-xs-5',
                    action: function(dialog) {
                        $('.terminos').html("<span>Términos y condiciones del sitio</span>");
                        $('.terminos span').css('color','#840000');
                        $('.aceptoTerminos').val("0");
                        dialog.close();
                    }   
             }]
            });
     });
     $('.detallesSeguro').click(function(event){
             event.preventDefault();
             var data = buscarConexion.loadDetallesSeguro();
             BootstrapDialog.show({
                type: BootstrapDialog.TYPE_DEFAULT,
                title: 'Detalles del seguro de viaje',
                cssClass: 'form',
                "message" : "<div class='form-control textoSeguro'>"+data+"</div>",
                buttons: [{
                    label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                    cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm col-lg-4 col-lg-offset-7 col-sm-4 col-sm-offset-7 col-md-4 col-md-offset-7 col-xs-5 col-xs-offset-5',
                    action: function(dialog) {
                        dialog.close();
                    }
                }]
            });
     });
     
     //boton pagar
         $('a[name=finish]').click(function(e){
             e.preventDefault();
             buscarConexion.mostrarOcultarBotonAtras(false);
             buscarConexion.pagar();
         });
         //boton de nueva busqueda
         $('#nuevaBusqueda').click(function(e){
             $('#contenidoBusqueda').hide();
             buscarConexion.resetComponentes();
             $('#divBusqueda').show();
         });
         //onChange bonto ida_regreso del formulario de busqueda
        $('#buscar_conexiones_command_idaRegreso_1').change(function(){
              if($('#buscar_conexiones_command_idaRegreso_1').is(':checked'))
                        $('#fechaRegresoDiv').show();
         });
         //onChange bonto solo ida del formulario de busqueda
         $('#buscar_conexiones_command_idaRegreso_0').change(function(){
             if($('#buscar_conexiones_command_idaRegreso_0').is(':checked'))
                $('#buscar_conexiones_command_fechaRegreso').clearInputs();
                $('#fechaRegresoDiv').hide();
         });
        
         $('.cantPasajeros').on('change', function(e){
            e.preventDefault();
            buscarConexion.verDetallesPrecioTotal();
            buscarConexion.resetAsientosSeleccionados("Ida");
            if(buscarConexion.hayRegresos())
                buscarConexion.resetAsientosSeleccionados("Regreso");
            buscarConexion.datosfullPasajero = false;
            buscarConexion.setPrecioResumenProvisionalCompra();
            $(".formPasajeroItem").empty();
        });
        //boton que indica si el cliente sera un pasajero para pedir datos detallados que exige la ruta
        $('.btnPasajero').on('click', function(e){
             e.preventDefault();
             buscarConexion.pintarFormularioDatosClienteDetalle();
        });
    },
    
    loadOficinas : function(){
        var listaEstaciones = $("#buscar_conexiones_command_listaEstaciones").val();
        if(listaEstaciones){ listaEstaciones = JSON.parse(listaEstaciones); }
        else{ listaEstaciones = []; }
//        if(!core.isMovil()){
            $("#buscar_conexiones_command_estacionOrigen").select2({
                placeholder: "Estación de origen",
                data: listaEstaciones
            });
            $("#buscar_conexiones_command_estacionDestino").select2({
                placeholder: "Estación de destino",
                data: listaEstaciones
            });
            $("#buscar_conexiones_command_cantidadPasajeros").select2({
                placeholder: "Pasajeros"
            });
//        }else{
//            
//        }
        
    },
    
    //titulo del panel de salidas de viaje de ida
    getTitleResultadosIda: function(){
        return 'Viaje de Ida :' + $('#buscar_conexiones_command_estacionOrigen').select2("data").text + ' - ' + $('#buscar_conexiones_command_estacionDestino').select2("data").text + '     ' + $('#buscar_conexiones_command_fechaSalida').val();
    },
    //titulo del panel de salidas de viaje de regreso
    getTitleResultadosRegreso: function(){
        return 'Viaje de Regreso :' +$('#buscar_conexiones_command_estacionDestino').select2("data").text + ' - ' + $('#buscar_conexiones_command_estacionOrigen').select2("data").text + '     ' + $('#buscar_conexiones_command_fechaRegreso').val();
    },
    //iniciar los timer (onExhausted es para cuando termine uno de ellos, el primero)
    loadTimer: function(timer, primerTimer){
        timer.backward_timer({
        seconds: 600,
        step: 1,
        value_setter: undefined,
        format: 'm%:s%',
        on_exhausted: function(timer) {
            if(primerTimer === true){
                core.showMessageDialog({
                    "title": "Tiempo de compra agotado.",
                    "message": "El tiempo para efectuar su compra ha terminado. Realice su operación nuevamente.",
                    buttons:[{
                        label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            dialog.close();
                            }
                    }]
                });
                $('#wizard').bootstrapWizard('show',0);
                buscarConexion.setStylePasos(1);
                buscarConexion.styleLabelPasos(1);
                $('#divBusqueda').show();
                $('#contenidoBusqueda').hide();
                
            }
        },
        on_tick: function(timer) {}
    });
    
     timer.backward_timer('start');
    },
    
    buscarConexiones: function(e){
//        console.debug("buscarConexiones-init");
        e.preventDefault();
        e.stopPropagation();
        var fechaIda = $('#buscar_conexiones_command_fechaSalida').val() === "" ? null: core.formatDate($('#buscar_conexiones_command_fechaSalida').val());
        var fechaRegreso = $('#buscar_conexiones_command_fechaRegreso').val() === "" ? null: core.formatDate($('#buscar_conexiones_command_fechaRegreso').val());
        var fechaIdaRequest = fechaIda === null ? null : fechaIda.getFullYear() + "-" + (parseInt(fechaIda.getMonth()) + 1) + "-" + fechaIda.getDate();
        var fechaRegresoRequest = fechaRegreso === null ? null : fechaRegreso.getFullYear() + "-" + (parseInt(fechaRegreso.getMonth()) + 1) + "-" + fechaRegreso.getDate();
        if(core.customValidateForm($("#conexionForm")) === true){
            var params = {
                    'idOrigen': $('#buscar_conexiones_command_estacionOrigen').val(),
                    'idDestino': $('#buscar_conexiones_command_estacionDestino').val(),
                    'ida_regreso': ($('#buscar_conexiones_command_idaRegreso_1').is(':checked')) ? true: false,
                    'fechaSalida': fechaIdaRequest,
                    'cantBoletos': $('#buscar_conexiones_command_cantidadPasajeros').val(),
                    'fechaRegreso': fechaRegresoRequest,
                    'viaje_directo': $('#buscar_conexiones_command_conexionesDirectas').is(':checked') ? true: false
                };
          buscarConexion.resetComponentes();
            $('#btnBuscar').button('loading');
            core.request({
                url : $("#conexionForm").attr("action"),
                extraParams : params,
                dataType:"html",
                method: 'POST', 
                async:true,
                successCallback: function(data){
                    $('#btnBuscar').button('reset');
                    buscarConexion.mostrarDivResultados(1,data,false);
                    $(".siglaEstacion").click(oficinas.clicVerInfoEstacion);
                    buscarConexion.checkedPrecios();
                    localStorage.setItem('detallesCC', null);
                    buscarConexion.connectDetallesCompuesta();
                    buscarConexion.resetCorrelativos();
                    buscarConexion.resetWizard();
                    if($('#totalConexionesIda').val() === "0" && $('#totalConexionesRegreso').val() === "0"){
                        $('#divBusqueda').show();
                        $('#contenidoBusqueda').hide();
                    }
                    else{
                        $('#divBusqueda').hide();
                        $('#contenidoBusqueda').show();
                    }
                },
                errorCallback: function(){
                    $('#btnBuscar').button('reset');
                    core.showMessageDialog({
                        title: 'Error',
                        type: BootstrapDialog.TYPE_DANGER,
                        message: 'Ha ocurrido un error en el sistema',
                        buttons: [{
                            label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                            cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                            action: function(dialog){
                                buscarConexion.resetWizard();
                                $('#divBusqueda').show();
                                $('#contenidoBusqueda').hide();
                                dialog.close();

                            }
                        }]
                    });
                    //buscarConexion.mostrarDivResultados(0,null,true);

                }
            });
        }
    },
    
    verDetallesEstacion: function(){
        $( "a.siglaEstacion" ).each(function(){
            $(this).on("click", function(event){
                event.preventDefault();
                var item =  $(this);
                var nombre = item.data("nombre"); 
                var direccion = item.data("dir"); 
                var telefonos = item.data("telefonos");
                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_DEFAULT,
                    title: 'Detalles de la Estación:',
                    cssClass: 'form',
                    message: "<div class='form-group col-xs-offset-3 col-sm-offset-2'>Nombre: "+nombre+"</div>"+
                      "<div class='form-group col-xs-offset-3 col-sm-offset-2'>Dirección: "+direccion+"</div>"+
                      "<div class='form-group col-xs-offset-3 col-sm-offset-2'>Telefonos: "+telefonos+"</div>",
                    buttons: [{
                        label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            dialog.close();
                            }
                        }]
                });
                
                localStorage.setItem('conexiones', $('#contenidoResultados').html());
            });
        });
     },
     checkedPrecios: function(){
        $.each($("input[name=claseIda]"), function(){
            $(this).change(function(event){
                event.preventDefault();
                var precioIda = 0;
                var detallesIda = "";
                var precioRegreso = 0;
                var detallesRegreso = "";
                var cantPasajeros = $('#buscar_conexiones_command_cantidadPasajeros').val();
                if($('#buscar_conexiones_command_idaRegreso_1').is(':checked') && $('#totalConexionesRegreso').val() !== "0" && $("input[name=claseRegreso]").is(":checked")){
                    precioRegreso = parseFloat($('input[name=claseRegreso]:checked').val());
                    detallesRegreso = "Regreso: "+$("input[name=claseRegreso]:checked").data("detalles");
                }
                if($(this).is(":checked")){
                    precioIda = parseFloat($(this).val());
                    detallesIda = "<div>Ida: "+$(this).data("detalles")+"</div>";
                    $('.detallesViaje').replaceWith(function(){return "<div class='detallesViaje'>"+detallesIda+detallesRegreso+"<div>Cantidad de Pasajeros: "+cantPasajeros+" </div></div>";});
                    $('.precioViaje').replaceWith(function(){return "<div class='row precioViaje'><hr></hr> Q "+(precioIda+precioRegreso)+"</div>"});
                }
                localStorage.setItem('conexiones', $('#contenidoResultados').html());
                $(".salidas_Ida").empty();
                $(".ul_salidas_Ida").empty();
                
            });
        });
        $.each($("input[name=claseRegreso]"), function(){
            $(this).change(function(event){
                var detallesRegreso = "";
                var precioRegreso = 0;
                var precioIda = 0;
                var detallesIda = "";
                var cantPasajeros = $('#buscar_conexiones_command_cantidadPasajeros').val();
                if($("input[name=claseIda]").is(":checked")){
                    precioIda = parseFloat($("input[name=claseIda]:checked").val());
                    detallesIda = "Ida: "+$("input[name=claseIda]:checked").data("detalles");
                }
                event.preventDefault();
                if($('#buscar_conexiones_command_idaRegreso_1').is(':checked')){
                    if($(this).is(":checked")){
                        detallesRegreso = "<div>Regreso: "+$(this).data("detalles")+"</div>";
                        precioRegreso = parseFloat($(this).val());
                        $('.detallesViaje').replaceWith(function(){return "<div class='detallesViaje'>"+detallesIda+detallesRegreso+"<div>Cantidad de Pasajeros: "+cantPasajeros+" </div></div>";});
                        $('.precioViaje').replaceWith(function(){return "<div class='row precioViaje'><hr></hr> Q "+(precioIda+precioRegreso)+"</div>"});
                    }
                }
                localStorage.setItem('conexiones', $('#contenidoResultados').html());
                $(".salidas_Regreso").empty();
                $(".ul_salidas_Regreso").empty();
            });
        });
        
    },
     
     mostrarDivResultados: function(paso,data,error){
        if(error === true){
            $('#contenidoSuccesCompraDiv').hide();
            $('#contenidoErrorCompraDiv').show(); 
         }
         else{
            $('#contenidoSuccesCompraDiv').show();
            $('#contenidoErrorCompraDiv').hide();
            if(paso === 1){
                $('#contenidoResultados').empty();
               $('#contenidoResultados').append(data);
               if($('#totalConexionesIda').val() === "0"){
                   $('.resultadosIda').replaceWith("<div style='display: block;' class='resultadosIda'>No se encontraron resultados del viaje de ida. Intente con otros parametros</div>");
               }
               if($('#buscar_conexiones_command_idaRegreso_1').is(':checked') && $('#totalConexionesRegreso').val() === "0"){
                   $('.resultadosRegreso').replaceWith("<div style='display: block;' class='resultadosRegreso'>No se encontraron resultados del viaje de regreso. Intente con otros parametros</div>");
               }
             if($('#totalConexionesIda').val() === "0" && $('#totalConexionesRegreso').val() === "0"){
                    $('#resultados').hide();
                    $('#noResultados').show();
             }
             else{
                $('#resultados').show();
                $('#noResultados').hide();
                $('.titlePanelResultadosIda').replaceWith(function(){return "<div class='titlePanelResultadosIda'>"+buscarConexion.getTitleResultadosIda()+"</div>"});
                if($('#buscar_conexiones_command_idaRegreso_1').is(':checked'))
                    $('.titlePanelResultadosRegreso').replaceWith(function(){return "<div class='titlePanelResultadosRegreso'>"+buscarConexion.getTitleResultadosRegreso()+"</div>"});
             }
            
            
        }
         }
    },
     resetDivContenido: function(){
        $('#fechaRegresoDiv').hide();
        $('#contenidoSuccesCompraDiv').hide();
        $('#contenidoErrorCompraDiv').hide();
        $('#timerSesion').hide();
        $('.iconosMapaBus').hide();
        $('#noResultados').hide();
        $('.formPasajeroDetallado').hide();
        $('.formPasajeroNoDetallado').hide();
        $('.camposForm').hide();
        $('.is_pasajeroDIV').css('visibility', 'hidden');
     },
     
     hayRegresos: function(){
         return $('#buscar_conexiones_command_idaRegreso_1').is(':checked') && ($('#totalConexionesRegreso').val() !== "0");
     },
     hayIda: function(){
         return $('#totalConexionesIda') !== 0;
     },
     validarSeleccionHorarios: function(){
         var mensaje = "";
            if(!$("input[name=claseIda]").is(':checked') && !$('#buscar_conexiones_command_idaRegreso_1').is(':checked'))
                mensaje = "Debe escoger un viaje seleccionando su precio.";
             else 
                if(!buscarConexion.hayIda() && !buscarConexion.hayRegresos())
                    mensaje = "Debe cambiar sus parámetros de búsqueda y realizarla nuevamente.";
            else 
                if((buscarConexion.hayIda() && !$("input[name=claseIda]").is(':checked')) && (buscarConexion.hayRegresos() && $("input[name=claseRegreso]").is(':checked')))
                    mensaje = "Debe escoger un viaje de ida seleccionando su precio.";
            else 
                if((buscarConexion.hayIda() && !$("input[name=claseIda]").is(':checked')) && (buscarConexion.hayRegresos() && !$("input[name=claseRegreso]").is(':checked')))
                    mensaje = "Debe escoger un viaje de ida y otro de regreso seleccionando sus precios.";
            else 
                if($("input[name=claseIda]").is(':checked') && (buscarConexion.hayRegresos() && !$("input[name=claseRegreso]").is(':checked')))
                   mensaje = "Debe escoger un viaje de regreso seleccionando su precio.";
           if(mensaje !== ""){
                BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                title: 'Error',
                cssClass: 'form',
                message: mensaje,
                buttons: [{
                    label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                    cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                    action: function(dialog){
                        dialog.close();
                        }
                    }]
                });
                return false;
            }
            return true;
      },
     validarFormularios: function(){
         core.customNotEqual("Las estaciones deben ser diferentes");
         var form = $("#conexionForm");
         form.find("div label.text-error").parent().remove();
         form.validate({
                errorClass: "text-error",
//                errorElement: "div",
                wrapper: "div",
                onclick: function(element){
                    //$(element).valid();
                },
                onchange: function(element){
                    $(element).valid();
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent('div'));
                },
                rules: {
                    buscar_conexiones_command_fechaRegreso: {
                        required: {
                            depends: function(element){
                                return $("#buscar_conexiones_command_idaRegreso_1").is(':checked');
                            }
                        }
                    },
                    buscar_conexiones_command_estacionOrigen :  { 
                        notEqualTo: "#buscar_conexiones_command_estacionDestino"
                    }
                },
                messages: {
                    buscar_conexiones_command_estacionOrigen: {
                        required: 'Requerido',
                        notEqualTo: 'No debe coincidir origen y destino de la búsqueda'
                    },
                    buscar_conexiones_command_estacionDestino: 'Requerido',
                    buscar_conexiones_command_fechaSalida: 'Requerido',
                    buscar_conexiones_command_fechaRegreso: 'Requerido',
                    "buscar_conexiones_command[cantidadPasajeros]": 'Requerido',
                    
                },
                success: function(element){
                }
            });
         $('form[name=form2]').validate({
                errorClass: "text-error",
                wrapper: "div",
                onclick: function(element){
                    $(element).valid();
                },
                onfocusout: function(element){
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent());
                },
                rules: {
                    'cliente_anonimo[correo][second]': {
                        equalTo: "#cliente_anonimo_correo_first"
                    }
                },
                messages: {
                    'cliente_anonimo[nombreApellidos]': 'Requerido',
                    'cliente_anonimo[nacionalidad]': 'Requerido',
                    'cliente_anonimo[sexo]': 'Requerido',
                    'cliente_anonimo[fechaNacimiento]': 'Requerido',
                    'cliente_anonimo[fechaVencimiento]': 'Requerido',
                    'cliente_anonimo[numeroDocumento]': 'Requerido',
                    'cliente_anonimo[tipoDocumento]': 'Requerido',
                    'cliente_anonimo[correo][first]': {
                        required:'Requerido',
                        email:'Inválido'
                    },
                    'cliente_anonimo[correo][second]': {
                        equalTo: 'Los correos deben coincidir.',
                        required: 'Requerido',
                        email:'Inválido'
                    }
                },
                success: function(element){
                 }
            });
            
            
     },
    loadEsquemas: function(){
//        console.debug("seleccion asientos init");
        var arrayIdConexiones = [];
        if($(".salidas_Ida").children().length === 0){
            var tipoIda = 'simple';
            if($('input[name=claseIda]:checked').data('tipoconexion') === 1)
                tipoIda = 'compuesta';
            arrayIdConexiones.push({"idSalida":$('input[name=claseIda]:checked').data('idconexion'), "tipo":tipoIda, "regreso":false}); 
        }
        if($(".salidas_Ida").children().length === 0){
            if($('#buscar_conexiones_command_idaRegreso_1').is(':checked')){
                var tipoRegreso = 'simple';
                if($('input[name=claseRegreso]:checked').data('tipoconexion') === 1)
                    tipoRegreso = 'compuesta';
               arrayIdConexiones.push({"idSalida":$('input[name=claseRegreso]:checked').data('idconexion'), "tipo":tipoRegreso, "regreso":true});
            }
        }
        if(arrayIdConexiones.length !== 0){
            var resultado = buscarConexion.getInfoConexionSimple(arrayIdConexiones);
            if(resultado[0] !== null && resultado[1] !== null){
                buscarConexion.mostrarEsquemas(resultado[0],resultado[1]);
                return true;
            }
            return false;
        }
        
    },
        
    getInfoConexionSimple: function(arrayData){
//        console.debug("buscar info conexion init");
        var infoConexiones = null;
        var resultado = [];
        var asientosOcupados = null;
        core.request({
            url : $("#basePath").val() + "listados/conSimples.json",
            extraParams :{
                data: JSON.stringify(arrayData),
                origen:$('#buscar_conexiones_command_estacionOrigen').val(),
                destino: $('#buscar_conexiones_command_estacionDestino').val()
            },
            dataType:"json",
            method: 'POST', 
            async:false,
            successCallback: function(result){
                if(result !== null){
                    infoConexiones = result.data;
                    asientosOcupados = result.asientosOcupados;
                }
           },
            errorCallback: function(){
                core.showMessageDialog({
                    "title": "Error",
                    "message" : 'Error en el servidor. ',
                    buttons:[{
                        label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            dialog.close();
                            }
                    }]
                });
            }
        });
        resultado.push(asientosOcupados);
        resultado.push(infoConexiones);
        return resultado;
        
    },
     getAsientosOcupados : function(idConexiones){
        var asientosOcupados = null;
        core.request({
            url : "https://autobusesfuentedelnorte.com/internal/integrations/portal/test/is.json",
            extraParams : {
                idWeb: $('.idApp').val()+"",
                data : JSON.stringify(idConexiones),
                tokenAut : $('.token').val()
            },
            dataType:"json",
            method: 'POST', 
            async:false,
            successCallback: function(result){
                if(result !== null){
                    asientosOcupados = result.data;
                }
            },
            errorCallback: function(e){
//                console.debug("error");
            }
        });
        return asientosOcupados;
    },
    
    mostrarEsquemas: function(asientosOcupados, data){
         buscarConexion.verDetallesPrecioTotal("Ida");
         $('.esquemasViajeRegreso').hide(); 
         $('.detallesPrecioTotalRegreso').hide();
         if(buscarConexion.hayRegresos() && $("input[name=claseRegreso]").is(':checked'))
         {
            $('.esquemasViajeRegreso').show();
            $('.detallesPrecioTotalRegreso').show();
            buscarConexion.verDetallesPrecioTotal("Regreso");
         }
         if(data['simp'].length !== 0){
            $.each(data['simp'], function(index, itemSimple){
                var tipoViaje = "Ida";
                var correlativo = buscarConexion.correlativoIda;
                var claseAsiento = $('input[name=claseIda]:checked').data('claseasiento');
                var detalles = $('input[name=claseIda]:checked').data('tiemposintermedios');
                if(itemSimple.regreso === true){
                    tipoViaje = "Regreso";
                    correlativo = buscarConexion.correlativoRegreso;
                    claseAsiento = $('input[name=claseRegreso]:checked').data('claseasiento');
                    detalles = $('input[name=claseRegreso]:checked').data('tiemposintermedios');
                }
                
                buscarConexion.mostrarEsquemaBus(tipoViaje, itemSimple, asientosOcupados[itemSimple.idExterno], correlativo, claseAsiento, detalles);
                if(itemSimple.regreso === true){
                   buscarConexion.correlativoRegreso ++;
                }
                else buscarConexion.correlativoIda ++;
            });
            buscarConexion.resetEsquemasTabLeft();
        }
        if(data['comp'].length !== 0){
            $.each(data['comp'], function(id, arraySimples){
                var tipoViaje = "Ida";
                var correlativo = buscarConexion.correlativoIda;
                var claseAsiento = $('input[name=claseIda]:checked').data('claseasiento');
                var detalles = $('input[name=claseIda]:checked').data('tiemposintermedios');
                if(arraySimples.regreso === true){
                    tipoViaje = "Regreso";
                    correlativo = buscarConexion.correlativoRegreso;
                    claseAsiento = $('input[name=claseRegreso]:checked').data('claseasiento');
                    detalles = $('input[name=claseRegreso]:checked').data('tiemposintermedios');
                }
                $.each(arraySimples['items'], function(index, item){
                     buscarConexion.mostrarEsquemaBus(tipoViaje, item, asientosOcupados[item.idExterno], correlativo, claseAsiento, detalles[item.idExterno]);
                     correlativo ++;
               });
            });
            buscarConexion.resetEsquemasTabLeft();
        }
    },
    resetEsquemasTabLeft : function(){
        $(".ul_salidas_Ida li").first().addClass("active");
        $(".salidas_Ida> div").first().addClass("active");
        $(".ul_salidas_Ida li:not(:first)").removeClass("active");
        $(".salidas_Ida >div:not(:first)").removeClass("active");
        
        $(".ul_salidas_Regreso li").first().addClass("active");
        $(".salidas_Regreso> div").first().addClass("active");
        $(".ul_salidas_Regreso li:not(:first)").removeClass("active");
        $(".salidas_Regreso >div:not(:first)").removeClass("active");
    },
    mostrarEsquemaBus : function(tipoViaje, infoConexion, asientosOcupados, correlativo, claseSeleccionada, detalles) {
        var claveBase = tipoViaje+"_"+correlativo;
        $('.asientos').show();
        $('#timerSesion').show();
        buscarConexion.createDivEsquema(tipoViaje,correlativo);
        var idSalida = infoConexion.idExterno;
        var listaAsientos = infoConexion.asientos;
        var listaSenales = infoConexion.senales;
        var clasesAsientos = infoConexion.clasesAsientos;
        $('.detallesViaje_'+claveBase).append("<span class='subtituloTrayectos' style='font-style: italic;'> Salida: </span><span> "+detalles.fechaSalida+", "+detalles.horaSalida+" de  "+detalles.origen+".</span>"+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Llegada: </span><span> "+detalles.fechaLlegada+" "+detalles.horaLlegada+" a "+detalles.destino+".</span>"+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Clase de Bus: </span> <span> "+detalles.claseBus+".</span>");
        
        if(infoConexion.nivel2 === false)
            $("#nav_"+claveBase+"_2").addClass("hidden");
        var asientosPosicionados = [];
        
        $.each(listaAsientos, function(index, asiento) {
            var numero = asiento.numero;
            var idAsiento = asiento.id;
            var clase = asiento.clase; //Tipo de asiento a seleccionar: 1:claseA o 2:claseB  
            asientosPosicionados[numero] = asiento;
            var estado = "libre";
            if(buscarConexion.isAsientoOcupado(numero, asientosOcupados))
                estado = "vendido";
             else if(!buscarConexion.isAsientoPermitido(asiento, clasesAsientos, claseSeleccionada))
                estado = "bloqueado";
            var item = $(".icono." + clase + "." + estado).clone();
            item.removeClass("icono");
            item.addClass("item");
            item.css("top" , core.ajustarPosicion(asiento.coordenadaX));
            item.css("left" , core.ajustarPosicion(asiento.coordenadaY));
            
            item.on('click', function(e){
                e.preventDefault();
                buscarConexion.clickSeleccionarAsiento($(this),tipoViaje, correlativo, numero, estado, idSalida, idAsiento, clase);
            });
            var nivel2 = asiento.nivel2; //
            if(eval(nivel2)) { 
                $("#nav_"+claveBase+"_2").removeClass("hidden");
                $(".nivel_2_"+claveBase).append(item); 
            }
            else { 
                $(".nivel_1_"+claveBase).append(item); 
            }            
            
            asiento.jsId = core.uniqId();
            item.attr('jsId', asiento.jsId);
            item.find(".detalleAsiento").text(numero);
            item.data("numero", numero);
            item.data("id", asiento.id); 
         
        });

        $(".esquema_"+claveBase+" .containment .active").removeClass("active");
        $("#tab_"+claveBase+"_1").addClass("active");
        $("#nav_"+claveBase+"_1").addClass("active");
        $.each(listaSenales, function(pos, senal) {                                
            var tipo = senal.tipo;   
            var item = $(".icono." + tipo).clone(); 
            item.removeClass("icono");
            item.addClass("item"); 
            var nivel2 = senal.nivel2; 
            if(eval(nivel2)) { $(".nivel_2_"+claveBase).append(item); }
            else { $(".nivel_1_"+claveBase).append(item); }  
            item.css("top" , core.ajustarPosicion(senal.coordenadaX));
            item.css("left" , core.ajustarPosicion(senal.coordenadaY));
            var id = senal.id; 
            senal.jsId = core.uniqId();
            item.attr('jsId', senal.jsId);
        });
       
    },
    createDivEsquema : function(tipoViaje, correlativo){
        var claveBase = tipoViaje+"_"+correlativo;
        var panelDetalles = "<p class='help-block'><div class='detallesViaje_"+claveBase+"'></div></p>";
        var itemLi = "<li class='active'><a href='#item_salida_"+claveBase+"'  class='ulTrayectos' data-toggle='tab'>Trayecto "+correlativo+" - (<span class='contSelect_salida_"+claveBase+"'>0/"+$("#buscar_conexiones_command_cantidadPasajeros").val()+"</span>) asientos</a></li>";
        $(".ul_salidas_"+tipoViaje).append(itemLi);
        
        var itemTab = "<div class='item_salida_"+claveBase+" tab-pane' id='item_salida_"+claveBase+"'>"+
                            panelDetalles+
                            "<div class='tabbable col-lg-9 col-sm-12 col-md-12 col-xs-12'>"+
                                "<div class='esquema_"+claveBase+"'>"+
                                    "<div class='tabbable'>"+
                                        "<ul class='nav nav-tabs nivel'>"+
                                            "<li id='nav_"+claveBase+"_1' class='active'><a href='#tab_"+claveBase+"_1' data-toggle='tab'>Nivel 1</a></li> "+
                                            "<li id='nav_"+claveBase+"_2' ><a href='#tab_"+claveBase+"_2' data-toggle='tab'>Nivel 2</a></li>"+  
                                        "</ul>"+
                                        "<div class='tab-content'>"+
                                            "<div class='tab-pane active' id='tab_"+claveBase+"_1'>"+    
                                                "<div class='lista nivel_1_"+claveBase+"'></div>"+
                                            "</div>"+
                                            "<div class='tab-pane' id='tab_"+claveBase+"_2'>"+
                                                "<div class='lista nivel_2_"+claveBase+"'></div>"+
                                            "</div>"+
                                        "</div>"+
                                     "</div>"+
                                  "</div>"+
                               "</div>"+
                            "</div>";
        $(".salidas_"+tipoViaje).append(itemTab);
    },
    isAsientoOcupado: function(numeroAsiento, listaAsientosOcupados){
         var ocupado = false;
         if(listaAsientosOcupados.length > 0){
            $.each(listaAsientosOcupados, function(index,numero){
                if(numero === numeroAsiento)
                   ocupado = true;
            });
        }
         return ocupado;
     },
     isAsientoPermitido: function(asiento, clasesAsientos, claseSeleccionada){
         var permitido = false;
         if(clasesAsientos.length === 1){
             permitido = true;
         }
         else{
             $.each(clasesAsientos, function(pos, item){
                if(claseSeleccionada === item && claseSeleccionada === asiento.clase)
                    permitido = true;
            });
         }
         return permitido;
     },
    connectDetallesCompuesta : function(){
        if($("input[name='hayCompuestasIda']").val() === "1" || $("input[name='hayCompuestasRegreso']").val() === "1"){
            $.each($('.detallesConexion'), function(){
                $(this).on('click', function(event){
                    event.preventDefault();
                    var detallesConexion = "";
                    var data = $(this).data('tiemposintermedios');
                    var index = 1;
                    $.each(data, function(id, item){
                        detallesConexion = detallesConexion+"<div><div class='col-lg-2 col-sm-2 col-md-2 col-xs-12 sinPadding'>Trayecto "+(index)+"</div>"+
                                "<div class='col-lg-10 col-sm-10 col-md-10 col-xs-12'>"+
                                    "<div class='form-group'>Sale: "+item.fechaSalida+ " a las "+item.horaSalida+" de "+item.origen+"</div>"+
                                    "<div class='form-group'>Llega: "+item.fechaLlegada+" a las "+item.horaLlegada+" a "+item.destino+"</div>"+
                                    "<div class='form-group'>Clase de Bus:    "+item.claseBus+"</div>"+
                                "</div></div>";
                        index++;
                     });
                     var origen = $('#buscar_conexiones_command_estacionOrigen').select2("data").text;
                     var destino = $('#buscar_conexiones_command_estacionDestino').select2("data").text;
                     BootstrapDialog.show({
                        type: BootstrapDialog.TYPE_DEFAULT,
                        title: "<div>De "+origen+ " a "+destino+"</div>",
                        cssClass: 'form',
                        message: detallesConexion,
                        buttons: [{
                            label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                            cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                            action: function(dialog){
                                dialog.close();
                                }
                            }]
                        });
                });
            });
        }
    },
    
    clickSeleccionarAsiento : function(item, tipoViaje, numeroEsquema, asientoSeleccionado, estado, idSalida, idAsiento, clase){
        var asientosSelectEsquema = buscarConexion.asientosSelectEsquemaIda;
        var cantAsientosSelectEsquema = buscarConexion.cantAsientosSelectEsquemaIda;
        if(tipoViaje === "Regreso"){
            asientosSelectEsquema = buscarConexion.asientosSelectEsquemaRegreso;
            cantAsientosSelectEsquema = buscarConexion.cantAsientosSelectEsquemaRegreso;
        }
        var isSelect = item.hasClass("asientoSelect");
        if(estado === "vendido" || estado === "bloqueado" || isSelect === true)
            item.bind('click', false);
        else{
            var cant = $(".cantPasajeros").val();
            if(cantAsientosSelectEsquema[tipoViaje+numeroEsquema] === undefined || cantAsientosSelectEsquema[tipoViaje+numeroEsquema] < parseInt(cant)){
                if(cantAsientosSelectEsquema[tipoViaje+numeroEsquema] === undefined)
                    cantAsientosSelectEsquema[tipoViaje+numeroEsquema] = 1;
                else{
                    cantAsientosSelectEsquema[tipoViaje+numeroEsquema] = cantAsientosSelectEsquema[tipoViaje+numeroEsquema] + 1;
                }
                item.addClass("asientoSelect");
                 if(asientosSelectEsquema[tipoViaje+numeroEsquema] === undefined)
                    asientosSelectEsquema[tipoViaje+numeroEsquema]= [];
                asientosSelectEsquema[tipoViaje+numeroEsquema].push({"numero":asientoSeleccionado,"idSalida":idSalida, "clase":clase, "idAsiento":idAsiento});
                $('.ul_salidas_'+tipoViaje+' span.contSelect_salida_'+tipoViaje+'_'+numeroEsquema).text(cantAsientosSelectEsquema[tipoViaje+numeroEsquema]+'/'+$('.cantPasajeros').val());
            }
            else{
                var arrayAsientosSelect = asientosSelectEsquema[tipoViaje+numeroEsquema];
                var firstAsientoSelect = arrayAsientosSelect[0];
                $.each($(".esquema_"+tipoViaje+"_"+numeroEsquema+" .detalleAsiento"), function(index,item1){
                    if(item1.innerHTML === (firstAsientoSelect.numero+"")){
                        $(item1.parentNode).removeClass("asientoSelect");
                        $(item1.parentNode).bind('click', function(e){
                            e.preventDefault();
                            buscarConexion.clickSeleccionarAsiento($(this),tipoViaje, numeroEsquema, asientoSeleccionado, estado, idSalida, idAsiento, clase);
                        });
                        return false;
                    }
                });
                item.addClass("asientoSelect");
                asientosSelectEsquema[tipoViaje+numeroEsquema].splice(0,1);
                asientosSelectEsquema[tipoViaje+numeroEsquema].push({"numero":asientoSeleccionado,"idSalida":idSalida, "clase":clase, "idAsiento":idAsiento});
            }
                
        }
        
    },
    verDetallesPrecioTotal : function(){
        $(".precioTotalPasajerosIda").html(buscarConexion.precioCalculado("Ida"));
        $(".precioTotalPasajerosRegreso").html(buscarConexion.precioCalculado("Regreso"));
        var precioTotalIda = $($(".precioTotalPasajerosIda")[0]).text() !== "" ? parseFloat($($(".precioTotalPasajerosIda")[0]).text()) : 0;
        var precioTotalRegreso = $($(".precioTotalPasajerosRegreso")[0]).text() !== "" ? parseFloat($($(".precioTotalPasajerosRegreso")[0]).text()) : 0;
        $(".precioTotalViaje").html("Q "+(precioTotalIda+precioTotalRegreso));
    },
    precioCalculado : function(tipoViaje){
        var precioXPersona = parseFloat($("input[name=clase"+tipoViaje+ "]:checked").data('preciounitario'));
        var cantPersonas = parseInt($(".cantPasajeros").val());
        return cantPersonas * precioXPersona;
    },
    resetAsientosSeleccionados : function(tipoViaje){
        $(".ul_salidas_"+tipoViaje+" span").html("0/"+$(".cantPasajeros").val());
        var asientosSelectEsquema = buscarConexion.asientosSelectEsquemaIda;
        var cantAsientosSelectEsquema = buscarConexion.cantAsientosSelectEsquemaIda;
        var cantPersonas = $(".cantPasajeros").val();
        var asientosSelect = $(".salidas_"+tipoViaje+" .lista .asientoSelect");
        if(tipoViaje === "Regreso"){
            asientosSelectEsquema = buscarConexion.asientosSelectEsquemaRegreso;
            cantAsientosSelectEsquema = buscarConexion.cantAsientosSelectEsquemaRegreso;
        }
        var cantItemSalidas = $('.salidas_'+tipoViaje).children().length;
        for (var i = 1; i <= cantItemSalidas; i++){
            if(cantAsientosSelectEsquema[tipoViaje+i] !== undefined){
            if(cantPersonas < cantAsientosSelectEsquema[tipoViaje+i]){
                var nuevaCantAsientosSelect = cantAsientosSelectEsquema[tipoViaje+i]-cantPersonas;
                for(var j = 0; j < nuevaCantAsientosSelect; j++){
                    $.each($(".esquema_"+tipoViaje+"_"+i+" .detalleAsiento"), function(index,item1){
                        if(item1.innerHTML === (asientosSelectEsquema[tipoViaje+i][j].numero+"")){
                            $(item1.parentNode).removeClass("asientoSelect");
                            return false;
                        }
                    });
                }
                asientosSelectEsquema[tipoViaje+i].splice(0,(cantAsientosSelectEsquema[tipoViaje+i]-cantPersonas));
                cantAsientosSelectEsquema[tipoViaje+i] = asientosSelectEsquema[tipoViaje+i].length;
            }
            else{
                cantAsientosSelectEsquema[tipoViaje+i] = asientosSelectEsquema[tipoViaje+i].length;
            }
         }
         
        }
        
    },
    initWizardCompra: function(){
        $('#wizard').bootstrapWizard({
            'tabClass': 'nav nav-pills',
            'nextSelector': '.btnStep-next',
            'previousSelector': '.btnStep-previous',
             
            onInit : function(tab, navigation,index){
              buscarConexion.styleLabelPasos(1);
            },

            onTabClick : function(tab, navigation, index){
                    return false;
                }, 

            onTabShow: function(tab, navigation, index) {
                var $total = navigation.find('li').length;
                var $current = index+1;
                var wizard = navigation.closest('.wizard-card');
                
                if($current >= $total) {
                    $(wizard).find('.btnStep-next').hide();
                    $(wizard).find('.btnStep-finish').show();
                } else {
                    $(wizard).find('.btnStep-next').show();
                    $(wizard).find('.btnStep-finish').hide();
                }
            },
            onPrevious: function(tab, navigation, index){
                $('#anclaCompra')[0].click();
                buscarConexion.setStylePasos(index+1);
                buscarConexion.styleLabelPasos(index+1);
                if(index === 0 || index === 3)
                    buscarConexion.mostrarOcultarBotonAtras(false);
                else buscarConexion.mostrarOcultarBotonAtras(true);
                return true;
            },
            onNext: function(tab, navigation, index){
                //submit de seleccion de horarios
                if(index === 1){
                    if(buscarConexion.submitSeleccionHorarios() === true)
                    return true;
                    else{
                        return false;
                    }
                }
                //submit de datos de cliente
                else if(index === 2){
                    if(buscarConexion.submitDatosCliente()=== true)
                        return true;
                    return false;
                }
                //submit de seleccion de asientos
                else if(index === 3){
                    if(buscarConexion.submitSeleccionAsientos() === true)
                        return true;
                    return false;
                }
                //submit de datos de pasajeros
                else if(index === 4){
                   if(buscarConexion.submitDatosPasajeros() === true)
                    return true;
                return false;
                }
            }
        });
    },
    salvarPaquetes : function(){
        var pasajeros = [];
        var det = "";
        var detallado = "false";
        var error = null;
        
        if(buscarConexion.rutaNecesitaDetalles() === true){
           det = "_det";
           detallado = "true";
        }
        var paquete = {
            "tipoConexionIda" : $('input[name=claseIda]:checked').data('tipoconexion'),
            "conexionIda" : $('input[name=claseIda]:checked').data('idpadre'),
            "regreso" : buscarConexion.hayRegresos(),
            "subeEn" : $('#buscar_conexiones_command_estacionOrigen').val(),
            "bajaEn" : $('#buscar_conexiones_command_estacionDestino').val()
        };
        if(buscarConexion.hayRegresos() === true){
            paquete.conexionRegreso = $('input[name=claseRegreso]:checked').data('idpadre');
            paquete.tipoConexionRegreso = $('input[name=claseRegreso]:checked').data('tipoconexion');
        }
        
        $.each($('.formPasajeroItem > div'), function(index,item){
            var item = $(item);
            var pasajero = {
                "nombreApell" : item.find("input[name='nom"+det+"']").val(),
                "nacionalidad" : item.find("select[name='nac"+det+"']").val(),
                "tipoDoc" : item.find("select[name='td"+det+"']").val(),
                "numDoc" : item.find("input[name='nd"+det+"']").val(),
                "detallado" : detallado
            };
            if(detallado === "true"){
                pasajero.sexo = item.find("select[name='s_det']").val();
                pasajero.fechaNac = item.find("input[name='fn_det']").val();
                pasajero.fechaVenc = item.find("input[name='fv_det']").val();
             }
             pasajeros.push(pasajero);
        });
       core.request({
            url : $('#form4').attr('action'),
            extraParams : {
                paquete: JSON.stringify(paquete),
                pasajeros : JSON.stringify(pasajeros),
                reservaciones : JSON.stringify(buscarConexion.reservaciones)
            },
            dataType:"json",
            method: 'POST', 
            async:false,
            successCallback: function(result){
                if(result !== null){
                    error = result.error;
                    var estacionesFacturacion = result.estacionesFacturacion;
                    if(estacionesFacturacion !== null){
                        $.each(estacionesFacturacion, function(i,item){
                            $('.estacionesFacturas').append("<option value='"+item.id+"'>"+item.nombre+"</option>");
                        });
                        $(".estacionesFacturas").val($('#buscar_conexiones_command_estacionOrigen').val());
                    }
                 }
            },
            errorCallback: function(e){
                error = e.error;
            }
        });
        return error;
    },
    salvarDatosCompra : function(successCallback){
        var status = "";
        var compra = {
            'estacion' : $('.estacionesFacturas').val()
        };
        $('#form3').ajaxSubmit({
            target: $('#form3').attr('action'),
            type : "POST",
            dataType: "json",
            cache : false,
            async:false,
            data : {
                compra : JSON.stringify(compra)
            },
            beforeSubmit: function() { 
                core.showLoading({showLoading:true});
           },
           error: function(e) {
               status = "2";
               if(e.message !== null){
                   core.showMessageDialog({
                    title: "Error",
                    type : BootstrapDialog.TYPE_DANGER,
                    message : e.message,
                    buttons:[{
                        label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            dialog.close();
                            }    
                    }]
                });
               }
                
           },
           success: function(responseText) {
               status = responseText.status;
                if(responseText.status === "1"){
                    if(successCallback && $.isFunction(successCallback)){
                        successCallback(responseText.message);
                    }
                }else{
                    core.showMessageDialog({
                        type : BootstrapDialog.TYPE_DANGER,
                        message : responseText.message,
                        buttons:[{
                            label: "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                            cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                            action: function(dialog){
                                dialog.close();
                                }    
                        }]
                    });
                }
           }
       });

        return status;
    },
    submitCliente : function(){
        var result = null;
        if($('#cliente_anonimo_nit').val() === "")
            $('#cliente_anonimo_nit').val("C/F");
        $('#form2').ajaxSubmit({
            target: $('#form2').attr('action'),
            type : "POST",
            dataType: "json",
            cache : false,
            async:false,
            beforeSubmit: function() { 
                core.showLoading({showLoading:true});
           },
           error: function(e) {
                $('.errorCliente .alert').text(e.error);
                $('.errorCliente').show();
                result = false;
           },
           success: function(responseText) {
               result = responseText.valid;
               if(result === false){
                    $('.errorCliente .alert').text(responseText.error);
                    $('.errorCliente').show();
               }
               else{
                   $('.errorCliente .alert').text("");
                    $('.errorCliente').hide();
               }
           }
       });
       return result;
    },
    validateDatosPasajeros : function(){
        var valid = true;
        $.each($('.formPasajeroItem > div form'), function(index, item){
            if($(item).valid() === false){
                valid = false;
                return false;
            }
        });
        return valid;
        
    },
    resumenCompra : function(tipoViaje, origen, destino){
        $(".res_pasajeros_"+tipoViaje).empty();
        var asientosArray = buscarConexion.asientosSelectEsquemaIda;
        if(tipoViaje === "Regreso")
             asientosArray = buscarConexion.asientosSelectEsquemaRegreso;
        $.each($(".formPasajeroItem > div"), function(index, item){
            var item = "<div><i class='glyphicon glyphicon-user' style='margin-right:7px;'></i>"+$($(item).find("input")[0]).val()+"</div>";
            $(".res_pasajeros_"+tipoViaje).append(item);
         });
        $(".res_total_"+tipoViaje).text("Q "+$($(".precioTotalPasajeros"+tipoViaje)[0]).text());
        var detalles = $("input[name='clase"+tipoViaje+"']:checked").data("tiemposintermedios");
        if($("input[name=clase"+tipoViaje+"]:checked").data("tipoconexion") === 0){
            $(".est_fecha_hora_"+tipoViaje).text("De "+origen+ ", "+ detalles.fechaSalida+ " a las "+ detalles.horaSalida+" a "+
               destino+",  "+detalles.fechaLlegada+" a las "+detalles.horaLlegada);
            var clase = "Económica";
            if(asientosArray[tipoViaje+1][0].clase === "B")
                clase = "Ejecutiva";
            $(".bus_asiento_"+tipoViaje).text("Clase de Bus: "+detalles.claseBus+ ", Clase de Asiento: "+clase);
            $(".resumenDetallesCompuesta"+tipoViaje).hide();
            $(".divClaseAsientoResumen"+tipoViaje).show();
        }
        else{
            $(".divClaseAsientoResumen"+tipoViaje).hide();
            var index = 1;
            var detallesTrayectos = "";
            var detallesCompuesta = $("input[name='clase"+tipoViaje+"']:checked").data("detalles");
            $(".est_fecha_hora_"+tipoViaje).text(detallesCompuesta);
            $(".divClasesResumen"+tipoViaje).hide();
            $.each(detalles, function(id, item){
                detallesTrayectos = detallesTrayectos+"<div style='padding-bottom: 10px;'>"+
                        "<div class='row'><div class='col-lg-12 cuadroResumenTrayecto'>"+
                         "<div class='subtituloTrayectos' style='border-bottom: 1px solid #eee; text-align: center;'><i class='glyphicon glyphicon-road' style='margin-right:7px;'></i>Trayecto "+(index)+"</div>"+
                            "<div class='' style='margin-left: 11px;'><span class='subtituloTrayectos'>Sale: </span> "+item.fechaSalida+" a las "+item.horaSalida+" de "+item.origen+"</div>"+
                            "<div class='' style='margin-left: 11px;'><span class='subtituloTrayectos'>Llega: </span>"+item.fechaLlegada+" a las "+item.horaLlegada+" a "+item.destino+"</div>"+
                            "<div class='' style='margin-left: 11px;'><span class='subtituloTrayectos'>Clase de Bus: </span>"+item.claseBus+"</div>"+
                            "<div class='' style='margin-left: 11px;'><span class='subtituloTrayectos'>Clase de asiento: </span>"+buscarConexion.getClaseAsiento(tipoViaje,id)+"</div>"+
                        "</div></div></div>";
                index++;
            });
            $(".res_compuesta_"+tipoViaje).append(detallesTrayectos);
            $(".resumenDetallesCompuesta"+tipoViaje).show();
        }
         $(".resumen"+tipoViaje).show();
        
    },
    getClaseAsiento: function(tipoViaje, id){
        var cant_items = $('.salidas_'+tipoViaje).children().length;
        var asientosMap = buscarConexion.asientosSelectEsquemaIda;
        if(tipoViaje === "Regreso")
            asientosMap = buscarConexion.asientosSelectEsquemaRegreso;
        var claseAsiento = "";
        for(var i = 1; i <= cant_items; i++){
            $.each(asientosMap[tipoViaje+i], function(index,item){
                if(item.idSalida === id){
                    claseAsiento = item.clase;
                    return false;
                }
            });
            if(claseAsiento !== "")
                break;
        }
        if(claseAsiento === "A")
            return "Económica";
        return "Ejecutiva";
    },
    setStylePasos: function(index){
        if(index === 1){
            $('.nav-pills>li>a').first().addClass('pasoActivo');
            $('.nav-pills>li>a').first().removeClass('pasoInactivo');
            $('.nav-pills>li>a:not(:first)').addClass('pasoInactivo');
            $('.nav-pills>li>a:not(:first)').removeClass('pasoActivo');
        }
        else if(index === 2){
            $($('.nav-pills>li>a')[1]).addClass('pasoActivo');
            $('.nav-pills>li>a:not(:eq(1))').addClass('pasoInactivo');
            
            $($('.nav-pills>li>a')[1]).removeClass('pasoInactivo');
            $('.nav-pills>li>a:not(:eq(1))').removeClass('pasoActivo');
        }
        else if(index === 3){
            $($('.nav-pills>li>a')[2]).addClass('pasoActivo');
            $('.nav-pills>li>a:not(:eq(2))').addClass('pasoInactivo');
            
            $($('.nav-pills>li>a')[2]).removeClass('pasoInactivo');
            $('.nav-pills>li>a:not(:eq(2))').removeClass('pasoActivo');
        }
        else if(index === 4){
            $($('.nav-pills>li>a')[3]).addClass('pasoActivo');
            $('.nav-pills>li>a:not(:eq(3))').addClass('pasoInactivo');
            
            $($('.nav-pills>li>a')[3]).removeClass('pasoInactivo');
            $('.nav-pills>li>a:not(:eq(3))').removeClass('pasoActivo');
        }
        else if(index === 5){
            $($('.nav-pills>li>a')[4]).addClass('pasoActivo');
            $('.nav-pills>li>a:not(:eq(4))').addClass('pasoInactivo');
            
            $($('.nav-pills>li>a')[4]).removeClass('pasoInactivo');
            $('.nav-pills>li>a:not(:eq(4))').removeClass('pasoActivo');
        }
        
    },
    validarSeleccionAsientos : function(){
        var cantItemSalidasIda = $('.salidas_Ida').children().length;
        var cantItemSalidasRegreso = $('.salidas_Regreso').children().length;
        var validIda = true;
        var validRegreso = true;
        for (var i = 1; i <= cantItemSalidasIda; i++){
            var faltantes = $('.cantPasajeros').val();
            if(buscarConexion.cantAsientosSelectEsquemaIda['Ida'+i] !== undefined)
                faltantes = $('.cantPasajeros').val() - buscarConexion.cantAsientosSelectEsquemaIda['Ida'+i];
            if(faltantes !== 0){
                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_DEFAULT,
                    title: 'Error',
                    cssClass: 'form',
                    message: 'Debe seleccionar '+faltantes + " asiento (s) en el trayecto "+i+ " del viaje de ida",
                    buttons: [{
                        label: 'Aceptar',
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            dialog.close();
                            }
                        }]
                });
                validIda = false;
                break;
            }
         }
         for (var j = 1; j <= cantItemSalidasRegreso; j++){
             var faltantes = $('.cantPasajeros').val();
            if(buscarConexion.cantAsientosSelectEsquemaRegreso['Regreso'+j] !== undefined)
                faltantes = $('.cantPasajeros').val() - buscarConexion.cantAsientosSelectEsquemaRegreso['Regreso'+j];
            if(faltantes !== 0){
                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_DEFAULT,
                    title: 'Error',
                    cssClass: 'form',
                    message: 'Debe seleccionar '+faltantes + " asiento (s) en el trayecto "+j+ " del viaje de regreso",
                    buttons: [{
                        label: 'Aceptar',
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            dialog.close();
                            }
                        }]
                });
            
                validRegreso = false;
                break;
            }
         }
         return validIda && validRegreso;
     },
     reservarAsientos: function(){
            var detallado = $('.copyDatosDetalladosClientePasajero').val() === "1" ? "true" : "false";
            var items = [];
            var esquemasIda = buscarConexion.asientosSelectEsquemaIda;
            var esquemasRegreso = buscarConexion.asientosSelectEsquemaRegreso;
            var cantIda = $('.salidas_Ida').children().length;
            var cantRegreso = $('.salidas_Regreso').children().length;
            for(var i = 1; i <= cantIda; i++){
                $.each(esquemasIda["Ida"+i], function(index,item){
                    items.push({"idAsiento":item.idAsiento, "idSalida":item.idSalida});
                });
            }
            for(var j = 1; j <= cantRegreso; j++){
                $.each(esquemasRegreso["Regreso"+j], function(index,item){
                    items.push({"idAsiento":item.idAsiento, "idSalida":item.idSalida});
                });
            }
            var jsonCliente = {
                    "nacionalidad" : $('#cliente_anonimo_nacionalidad').val(),
                    "tipoDocumento" : $('#cliente_anonimo_tipoDocumento').val(),
                    "numeroDocumento" : $('#cliente_anonimo_numeroDocumento').val(),
                    "nit" : $('#cliente_anonimo_nit').val(),
                    "detallado" : detallado,
                    "items" : items
            };
             
            if(detallado === "true"){
                var nombres = $('#cliente_anonimo_nombreApellidos').val().split(" ");
                var segundoApellido = nombres.splice(3);
                if(segundoApellido.length > 0)
                    segundoApellido = segundoApellido.join();
                else segundoApellido = "";
                jsonCliente.primerNombre = nombres[0];
                jsonCliente.segundoNombre = (nombres.length > 3) ? nombres[1] : "";
                jsonCliente.primerApellido = (nombres.length > 3) ? nombres[2] : nombres[1];
                jsonCliente.segundoApellido = segundoApellido;
                jsonCliente.sexo = $('#cliente_anonimo_sexo').val();
                jsonCliente.fechaNacimiento = $('#cliente_anonimo_fechaNacimiento').val();
                jsonCliente.fechaVencimientoDocumento = $('#cliente_anonimo_fechaVencimientoDocumento').val();
            }
            else{
                jsonCliente.fullname = $('#cliente_anonimo_nombreApellidos').val();
            }
            var response = {};
            core.request({
                url : $("#basePath").val() + "ajax/cr.json",
                extraParams : {
                    idWeb: $('.idApp').val()+"",
                    data : JSON.stringify(jsonCliente),
                    tokenAut : $('.token').val()
                },
                dataType:"json",
                method: 'POST', 
                async:false,
                successCallback: function(result){
                    if(result !== null){
                        if(result.data)
                        buscarConexion.reservaciones = result.data;
                        if(result.status)
                        response.status = result.status;
                        if(result.message)
                        response.message = result.message;
                     }
                },
                errorCallback: function(e, statusText, error){
                    response.status = statusText;
                    response.message = error;
                }
            });
            var error = false;
            if(response.status !== "1"){
                error = true;
                var title = "";
                var mensaje = response.message;
                if(mensaje.indexOf("m1") !== -1){
                    title = "Alerta";
                    mensaje = mensaje.replace("m1", "");
                }else{
                    title = "Servidor Ocupado";
                    mensaje = "Espere unos segundos e intente de nuevo.";
                }
                core.showMessageDialog({
                    "title": title,
                    "message" : mensaje,
                    "buttons": [{
                        "label": "<i class='glyphicon glyphicon-log-out' style='margin-right:7px;'></i>Salir",
                        cssClass: 'btn btnUpper btnStep btnStep-next btnStep-fill btnStep-warning btnStep-wd btnStep-sm',
                        action: function(dialog){
                            buscarConexion.resetUlTrayectosEsquemas();
                            buscarConexion.resetMapAsientos();
                            buscarConexion.resetCorrelativos();
                            if(buscarConexion.loadEsquemas() === true)
                            {
                                buscarConexion.initAllTimer();
                                buscarConexion.showPasoSeleccionAsientos();
                                dialog.close();
                            }
                            else{
                                buscarConexion.showPasoDatosCliente();
                                dialog.close();
                            }
                        }
                    }]
                });
            }
            return error;
     },
     pintarFormularioDatosClienteDefault : function(){
         if(buscarConexion.datosfullCliente === false){
            $('.col1row1').append($('.nombreApell'));
            $('.col2row1').append($('.correo1'));
            $('.col3row1').append($('.correo2'));
            $('.col4row1').append($('.telefono'));
            $('.col1row2').append($('.nac'));
            $('.col2row2').append($('.tipoDoc'));
            $('.col3row2').append($('.numeroDoc'));
            $('.col4row2').append($('.nit'));
            $('.datosAdicionales').hide();
            buscarConexion.initFechasFormCliente();
            buscarConexion.datosfullCliente = true;
        }
        
     },
     pintarFormularioDatosClienteDetalle : function(){
         if($('#is_pasajero').is(':checked') === false){
                $('.datosAdicionales').show();
                $('.col1row3').append($('.fechaVenc'));
                $('.col2row3').append($('.sexo'));
                $('.col3row3').append($('.fechaNac'));
                buscarConexion.initFechasFormCliente();
                buscarConexion.datosfullCliente = true;
                $('.copyDatosDetalladosClientePasajero').val("1");
            }
            else{
                $('.datosAdicionales').hide();
                $('.copyDatosDetalladosClientePasajero').val("0");
            }
     },
     pintarFormularioPasajeros: function(){
         
         if(buscarConexion.datosfullPasajero === false){
            var clienteDetalladoIda = $('input[name=claseIda]:checked').data("clientedetallado");
            var clienteDetalladoRegreso = 0;
            if($("input[name='totalConexionesRegreso']").val() !== "0")
                clienteDetalladoRegreso = $('input[name=claseRegreso]:checked').data("clientedetallado");

            var cantPasajeros = $('.cantPasajeros').val();
            
            buscarConexion.initSelectFormPasajeros();
            for(var i = 0; i < cantPasajeros; i++){
                var item = null;
                var messages = "";
                if(clienteDetalladoIda === 1 || clienteDetalladoRegreso === 1){
                    item = buscarConexion.clonarFormPasajero_ConDetalle();
                    messages = {
                        "nom_det": "Requerido",
                        "nac_det": "Requerido",
                        "td_det": "Requerido",
                        "nd_det": "Requerido",
                        "fv_det": "Requerido",
                        "fn": "Requerido",
                        "s_det": "Requerido"
                    };
                }
                else{
                    item = buscarConexion.clonarFormPasajero_SinDetalle();
                    messages = {
                        "nom": "Requerido",
                        "nac": "Requerido",
                        "td": "Requerido",
                        "nd": "Requerido"
                    };
                }
                $(item).show();
                $(item).find('.numeroPasajero').text("Pasajero "+ (i+1));
                
                $(item).find('form').validate({
                    errorClass: "text-error",
                    wrapper: "div",
                    onclick: function(element){
                        $(element).valid();
                    },
                    onfocusout: function(element){
                    },
                    errorPlacement: function(error, element) {
                        error.appendTo(element.parent('div'));
                    },
                    rules: {},
                    messages: messages,
                    success: function(element){
                    }
                });
                
                $('.formPasajeroItem').append(item);
            }
            buscarConexion.datosfullPasajero = true;
            buscarConexion.cargarDatosPrimerPasajero(clienteDetalladoIda, clienteDetalladoRegreso);
        }
     },
     cargarDatosPrimerPasajero : function(clienteDetalladoIda, clienteDetalladoRegreso){
         if(($('.is_pasajeroDIV').css('visibility') === "visible" && $('#is_pasajero').is(':checked')) || (clienteDetalladoIda === 0 && clienteDetalladoRegreso === 0)){
            var class_detalles = "";
            if(clienteDetalladoIda === 1 || clienteDetalladoRegreso === 1){
                class_detalles = "_det";
                $('.formPasajeroItem div:eq(0) .pasaj_fechaVenc_det').val($('#cliente_anonimo_fechaVencimientoDocumento').val());
                $('.formPasajeroItem div:eq(0) .pasaj_fechaNac_det').val($('#cliente_anonimo_fechaNacimiento').val());
                $('.formPasajeroItem div:eq(0) .pasaj_sexo_det').val($('#cliente_anonimo_sexo').val());
            }
            $('.formPasajeroItem div:eq(0) .pasaj_nombre'+class_detalles).val($('#cliente_anonimo_nombreApellidos').val());
            $('.formPasajeroItem div:eq(0) .pasaj_nac'+class_detalles).val($('#cliente_anonimo_nacionalidad').val());
            $('.formPasajeroItem div:eq(0) .pasaj_tipoDoc'+class_detalles).val($('#cliente_anonimo_tipoDocumento').val());
            $('.formPasajeroItem div:eq(0) .pasaj_numDoc'+class_detalles).val($('#cliente_anonimo_numeroDocumento').val());
         }
        
     },
     clonarFormPasajero_ConDetalle : function(){
        var item = $('.basePasajeroDetalle').clone();
        item.removeClass("basePasajeroDetalle");
        item.find(".pasaj_tipoDoc_det").val(1);
        item.find(".pasaj_nac_det").val(21);
        item.find(".pasaj_sexo_det").val(1);
        item.find('.pasaj_fechaVenc_det').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-1d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        item.find('.pasaj_fechaNac_det').datepicker({
            format: "dd/mm/yyyy",
            endDate: "-16y",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        return item;
     },
     clonarFormPasajero_SinDetalle : function(){
        var item = $('.basePasajeroNoDetalle').clone();
        item.removeClass("basePasajeroNoDetalle");
        item.find(".pasaj_tipoDoc").val(1);
        item.find(".pasaj_nac").val(21);
        return item;
     },
     
     
     initSelectFormPasajeros : function(){
        if($('.pasaj_nac_det option').length === 0){
            var options_nac1 = $('#cliente_anonimo_nacionalidad > option').clone();
            $('.pasaj_nac_det').append(options_nac1);
            $('.pasaj_nac_det').val(21);
            
            var options_nac2 = $('#cliente_anonimo_nacionalidad > option').clone();
            $('.pasaj_nac').append(options_nac2);
            $('.pasaj_nac').val(21);
        }
        if($('.pasaj_tipoDoc_det option').length === 0){
            var options_tipo1 = $('#cliente_anonimo_tipoDocumento > option').clone();
            $('.pasaj_tipoDoc_det').append(options_tipo1);
            $('.pasaj_tipoDoc_det').val(1);
           
            var options_tipo2 = $('#cliente_anonimo_tipoDocumento > option').clone();
            $('.pasaj_tipoDoc').append(options_tipo2);
            $('.pasaj_tipoDoc').val(1);
        }
        if($('.pasaj_sexo_det option').length === 0){
            var options_sexo = $('#cliente_anonimo_sexo > option').clone();
            $('.pasaj_sexo_det').append(options_sexo);
            $('.pasaj_sexo_det').val(1);
        }
    },
     
     initFechasFormCliente : function(){
        if($('.existeCliente').val() === "0"){
            $('#cliente_anonimo_fechaVencimientoDocumento').clearInputs();
            $('#cliente_anonimo_fechaNacimiento').clearInputs();
        }
     },
     initSelectFormularioCliente : function(){
         if($('.existeCliente').val() === "0"){
            $('#cliente_anonimo_tipoDocumento').val(1);
            $('#cliente_anonimo_sexo').val(1);
            $('#cliente_anonimo_nacionalidad').val(21);
        }
     },
     
     styleResize: function(){
        $('.modal-dialog').css('margin','auto 10%');
        buscarConexion.styleLabelPasos($('ul.nav-pills a').index($('a.pasoActivo'))+1);
        if ($(window).width() > 768){  
            $('#wizard').css('margin-left','0');
            $('#wizard').css('width','100%');
            $("label:not(.btn.btn-default)").show();
            $(".labelPasajero").show();
            $(".labelPasajero").css('visibility','hidden');
            $('.labelPasos').hide();
            $('.panel-body').attr('style','');
            $('#wizard div').removeClass('padding');
            buscarConexion.setTextPasosWizard(true);
            //$('select').select2();
        }
        if ($(window).width() <= 768){  
            $('heading').css('padding-top','0px');
            $('heading').css('padding-bottom','0px');
            $("label:not(.btn.btn-default)").hide();
            $(".labelPasajero").hide();
            $('#wizard').css('min-height','0');
            $('#wizard div').addClass('padding');
            $('.labelPasos').addClass('paddingLabelPasos');
             $('.wizard-card > .tab-content').css('min-height','0');
             buscarConexion.setTextPasosWizard(false);
             $('.labelPasos').show();
        }
        if ($(window).width() <= 320){  
            $('#wizard').css('margin-left','-41px');
            $('#wizard').css('width','142%');
        }
        else if ($(window).width() <= 360){  
            $('#wizard').css('margin-left','-41px');
            $('#wizard').css('width','135%');
       }
        else if ($(window).width() <= 768){  
            $('#wizard').css('margin-left','-41px');
            $('#wizard').css('width','112%');
       }
    },
    styleLabelPasos: function(index){
        if ($(window).width() <= 768){  
            if(index === 1){
                $('.labelPasos div').first().show();
                $('.labelPasos div:not(:first)').hide();
            }
            else if(index === 2){
                $($('.labelPasos div')[1]).show();
                $('.labelPasos div:not(:eq(1))').hide();
            }
            else if(index === 3){
                $($('.labelPasos div')[2]).show();
                $('.labelPasos div:not(:eq(2))').hide();
            }
            else if(index === 4){
                $($('.labelPasos div')[3]).show();
                $('.labelPasos div:not(:eq(3))').hide();
            }
            else if(index === 5){
                $($('.labelPasos div')[4]).show();
                $('.labelPasos div:not(:eq(4))').hide();
            }
        }
        else{
             $('.labelPasos div').first().show();
             $($('.labelPasos div')[1]).show();
             $($('.labelPasos div')[2]).show();
             $($('.labelPasos div')[3]).show();
             $($('.labelPasos div')[4]).show();
         }
    },
    setStylePlaceholders: function(){
        $("input[placeholder]").focus(function() {
        var input = $(this);
        if (input.val() === "") {
          input.removeClass("placeholder");
        }
        }).blur(function() {
          var input = $(this);
          if (input.val() !== "") {
            input.addClass("placeholder");
           }
        }).blur();
    },
    createSelectCantPasajeros: function(){
        var cant = $('#buscar_conexiones_command_cantidadPasajeros').val();
        if(cant !== $('.cantPasajeros option').length){
            $('.cantPasajeros').empty();
            for (var i = 1; i <= cant; i++){
                $('.cantPasajeros').append("<option value='"+i+"'>"+i+"</option>");
            }
        }
        $('.cantPasajeros').val($('.cantPasajeros option:last').val());
        buscarConexion.verDetallesPrecioTotal("Ida");
        buscarConexion.resetAsientosSeleccionados("Ida");
        buscarConexion.datosfullPasajero = false;
        $(".formPasajeroItem").empty();
        buscarConexion.verDetallesPrecioTotal("Regreso");
        buscarConexion.resetAsientosSeleccionados("Regreso");
    },
    resetWizard : function(){
       $('#wizard').bootstrapWizard('show',0);
        buscarConexion.setStylePasos(1);
        buscarConexion.styleLabelPasos(1);
    },
    setTextPasosWizard : function(fullText){
        if(fullText === true){
           $('#wizard a:eq(0)').text("1. Selección de Horarios");
           $('#wizard a:eq(1)').text("2. Datos de Cliente");
           $('#wizard a:eq(2)').text("3. Selección de Asientos");
           $('#wizard a:eq(3)').text("4. Datos de Pasajeros");
           $('#wizard a:eq(4)').text("5. Resumen de Compra");
        }
        else{
            $('#wizard a:eq(0)').text("1");
           $('#wizard a:eq(1)').text("2");
           $('#wizard a:eq(2)').text("3");
           $('#wizard a:eq(3)').text("4");
           $('#wizard a:eq(4)').text("5");
        }
    },
    loadClienteUsuarioAutenticado: function(){
        var error = false;
        core.request({
            url : $('#contenidoResultados').attr('action'),
            dataType:"json",
            method: 'POST', 
            async:false,
            successCallback: function(result){
                var cliente = result.cliente;
                if(cliente === null){
                    $('#cliente_anonimo_nombreApellidos').val("");
                    $('#cliente_anonimo_correo_first').val("");
                    $('#cliente_anonimo_correo_second').val("");
                    $('#cliente_anonimo_telefono').val("");
                    $('#cliente_anonimo_numeroDocumento').val("");
                    $('#cliente_anonimo_nit').val("");
                    $('#cliente_anonimo_fechaVencimientoDocumento').val("");
                    $('#cliente_anonimo_fechaNacimiento').val("");
                    
                    $('.existeCliente').val("0");
                 }
                 else{
                    $('#cliente_anonimo_nombreApellidos').val(cliente.nombre);
                    $('#cliente_anonimo_correo_first').val(cliente.correo);
                    $('#cliente_anonimo_correo_second').val(cliente.correo);
                    $('#cliente_anonimo_telefono').val(cliente.telefono);
                    $('#cliente_anonimo_numeroDocumento').val(cliente.numDoc);
                    $('#cliente_anonimo_nit').val(cliente.nit);
                    $("#cliente_anonimo_nacionalidad").val(cliente.nacionalidad);
                    $("#cliente_anonimo_tipoDocumento").val(cliente.tipoDoc);
                    if(cliente.fechaVenc !== null)
                        $('#cliente_anonimo_fechaVencimientoDocumento').datepicker("setDate", core.formatDate(cliente.fechaVenc));
                    if(cliente.fechaNac !== null)
                        $('#cliente_anonimo_fechaNacimiento').datepicker("setDate", core.formatDate(cliente.fechaNac));
                    $("#cliente_anonimo_sexo").val(cliente.sexo);
                    
                    $('.existeCliente').val("1");
                 }
           },
            errorCallback: function(){
//                error = true;
//                core.showMessageDialog({
//                    "title": "Error",
//                    "message" : 'Error en el servidor. '
//                });
            }
        });
        return error;
    },
    customValidateCliente : function(){
        if($('.aceptoTerminos').val() !== "1"){
            $('.errorCliente .alert').html("Debe aceptar los t&eacute;rminos.");
            $('.errorCliente').show();
            return false;
        }
        else{
            $('.errorCliente alert').html("");
            $('.errorCliente').hide();
           return true; 
        }
        
    },
    pagar : function(){
         //submit de resumen de compra
//        console.debug("voy a pagar");
        $($('#c_1')[0]).button('loading');
        $('#p_2').button('loading');
        var status = buscarConexion.salvarDatosCompra(function (url){
            window.location.replace(url + "?i=true");
        });
        if(status !== "1"){
            var id = customRecaptcha.getIdRecaptcha("recaptchaCompra");
            grecaptcha.reset(id);
            buscarConexion.showPasoResumenCompra();
            $($('#p_2')[0]).button('reset');
            return false;
        }
    },
    resetComponentes: function(){
        $('.errorCliente').hide();
        $('.errorPasajero').hide();
        buscarConexion.datosfullPasajero = false;
        buscarConexion.datosfullCliente = false;
        buscarConexion.reservaciones = [];
        $('.aceptoTerminos').val("0");
        $('.terminos').html("<span>Términos y condiciones del sitio</span>");
        $($('.terminos span')[0]).css("color","#840000");
        if($('#form2').length > 0)
            $('#form2')[0].reset();
        $(".salidas_Ida").empty();
        $(".ul_salidas_Ida").empty();
        $(".salidas_Regreso").empty();
        $(".ul_salidas_Regreso").empty();
        $(".formPasajeroItem").empty();
        buscarConexion.resetMapAsientos();
        buscarConexion.mostrarOcultarBotonAtras(false);
        $('#contenidoBusqueda').hide();
        $('.copyDatosDetalladosClientePasajero').val("0");
        
     },
    rutaNecesitaDetalles : function(){
        var clienteDetalladoIda = $('input[name=claseIda]:checked').data("clientedetallado");
        var clienteDetalladoRegreso = null;
        if($("input[name='totalConexionesRegreso']").val() !== "0")
            clienteDetalladoRegreso = $('input[name=claseRegreso]:checked').data("clientedetallado");
        if(clienteDetalladoIda === 1 || clienteDetalladoRegreso === 1){
           return true;
        }
        return false;
    },
    initComponentesFechas : function(){
        $('#buscar_conexiones_command_fechaSalida').datepicker({
            format: "dd/mm/yyyy",
            endDate: "+3m",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#buscar_conexiones_command_fechaSalida').datepicker("setDate", new Date());
        
        $('#buscar_conexiones_command_fechaRegreso').datepicker({
            format: "dd/mm/yyyy",
            startDate: $('#buscar_conexiones_command_fechaSalida').val(),
            endDate: "+3m",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#cliente_anonimo_fechaVencimientoDocumento').datepicker({
            format: "dd/mm/yyyy",
            startDate: "+1d",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#cliente_anonimo_fechaNacimiento').datepicker({
            format: "dd/mm/yyyy",
            startDate: "-120y",
            endDate: "-16y",
            todayBtn: true,
            language: "es",
            autoclose: true,
            todayHighlight: true
        });
        $('#buscar_conexiones_command_fechaRegreso').clearInputs();
    },
    initFormularioBusqueda : function(){
        $('#buscar_conexiones_command_idaRegreso_0').prop('checked', true);
        $('#buscar_conexiones_command_idaRegreso_1').prop('checked', false);
        
        $('#buscar_conexiones_command_estacionOrigen').attr('name', "buscar_conexiones_command_estacionOrigen");
        $('#buscar_conexiones_command_estacionDestino').attr('name', "buscar_conexiones_command_estacionDestino");
        $('#buscar_conexiones_command_fechaSalida').attr('name', "buscar_conexiones_command_fechaSalida");
        $('#buscar_conexiones_command_fechaRegreso').attr('name', "buscar_conexiones_command_fechaRegreso");
    },
    resetMapAsientos: function(){
        buscarConexion.asientosSelectEsquemaIda = [];
        buscarConexion.asientosSelectEsquemaRegreso = [];
        buscarConexion.cantAsientosSelectEsquemaIda = [];
        buscarConexion.cantAsientosSelectEsquemaRegreso = [];
    },
    initAllTimer : function(){
        $.each($('.timer'), function(index, item){
            var primerTimer = false;
            if(index === 0)
                primerTimer = true;
            buscarConexion.loadTimer($(item), primerTimer);
       });
    },
    connectClickBotonesAceptarTerminos : function(dialog){
         $('.btnAceptarTerminos').click(function(event){
            event.preventDefault();
//            console.debug("click en botones de terminos");
            var accept = $('.acceptTer.active').text();
            if(accept === "No Acepto"){
//                console.debug("click en acepto");
                $('.terminos').html("<span>Términos y condiciones del sitio</span>");
                $($('.terminos span')[0]).css('color','#840000');
                $('.aceptoTerminos').val("0");
            }
            else{
//                console.debug("click en no acepto");
                $('.terminos').html("<i class='glyphicon glyphicon-ok'></i><span> Términos y condiciones del sitio </span>");
                $('.aceptoTerminos').val("1");
                $('.terminos i').css('color','green');
                $($('.terminos span')[0]).css('color','green');
            }
//            console.debug(dialog);
            dialog.close();
         });
    },
    resetCorrelativos : function(){
        buscarConexion.correlativoIda = 1;
        buscarConexion.correlativoRegreso = 1;
    },
    submitSeleccionHorarios : function(){
        $($('#c_1')[0]).button('loading');
        var valid = buscarConexion.validarSeleccionHorarios();
        if(valid === false){
            buscarConexion.showPasoSeleccionHorarios();
            return false;
        }
        else{
            buscarConexion.initSelectFormularioCliente();
            if(buscarConexion.rutaNecesitaDetalles() === true){
               buscarConexion.mostrarDatosAdicionalesCliente();
            }
            else{ 
                buscarConexion.hideDatosAdicionalesCliente();
            }
            buscarConexion.loadClienteUsuarioAutenticado();
            buscarConexion.pintarFormularioDatosClienteDefault(); 
            $('#anclaCompra')[0].click();
            $('.btnPasajero label').removeClass("active");
            buscarConexion.showPasoDatosCliente();
            buscarConexion.setInfoResumenProvisionalCompra();
            return true;
        }
    },
    submitDatosCliente : function(){
        //mostrar boton atras
        buscarConexion.mostrarOcultarBotonAtras(true);
        $($('#c_1')[0]).button('loading');
        if($(".salidas_Ida").children().length === 0){
            var validDatos = $('form[name=form2]').valid();
            var validCustom = buscarConexion.customValidateCliente();
            if(validDatos === true && validCustom === true && buscarConexion.submitCliente() === true){
               buscarConexion.resetCorrelativos();
               if(buscarConexion.loadEsquemas() === true){
                    buscarConexion.initAllTimer();
                    buscarConexion.createSelectCantPasajeros();
                    buscarConexion.showPasoSeleccionAsientos();
                    return true;
                }
               else{
                    buscarConexion.showPasoDatosCliente();
                    return false; 
               }
            }
            else{
                buscarConexion.showPasoDatosCliente();
                return false;
            }
         }
        else{
            buscarConexion.createSelectCantPasajeros();
            buscarConexion.showPasoSeleccionAsientos();
            return true;
        }
    },
    submitSeleccionAsientos : function(){
        $($('#c_1')[0]).button('loading');
        var validAsientos = buscarConexion.validarSeleccionAsientos();
        if(validAsientos === false){
            buscarConexion.showPasoSeleccionAsientos();
             return false;
        }
        else{
            var error = buscarConexion.reservarAsientos();
            if(error === true){
                return false;
            }
            else{
                buscarConexion.pintarFormularioPasajeros();
                buscarConexion.showPasoDatosPasajeros();
                return true;
            }
        }
    },
    submitDatosPasajeros : function(){
        $($('#c_1')[0]).button('loading');
        var validDatosPasajeros = buscarConexion.validateDatosPasajeros();
        if(validDatosPasajeros === false){
            buscarConexion.showPasoDatosPasajeros();
             return false;
        }
        else{
            var error = buscarConexion.salvarPaquetes();
            if(!(error === null || $.trim(error) === "")){
                $('.errorPasajero .alert').text(error);
                $('.errorPasajero').show();
                buscarConexion.showPasoDatosPasajeros();
                return false;
            }
            else{
                $('.errorPasajero .alert').text("");
                $('.errorPasajero').hide();
                var origen = $("#buscar_conexiones_command_estacionOrigen").select2("data").text;
                var destino = $("#buscar_conexiones_command_estacionDestino").select2("data").text;
                buscarConexion.resumenCompra("Ida", origen, destino);
                if(buscarConexion.hayRegresos() === true){
                    buscarConexion.setStyleResumenIda(true);
                    buscarConexion.resumenCompra("Regreso", destino, origen);
                     $('.precioTotalResumen').show();
                }
                else{
                    buscarConexion.setStyleResumenIda(false);
                }
                var id = customRecaptcha.getIdRecaptcha("recaptchaCompra");
                grecaptcha.reset(id);
                $('.res_total').text($($('.precioTotalViaje')[0]).text());
                buscarConexion.showPasoResumenCompra();
                buscarConexion.mostrarOcultarBotonAtras(true);
                return true;
            }
        }
    },
    showPasoSeleccionHorarios : function(){
        //$('.nav-pills>li>a').first().tab('show');
        buscarConexion.setStylePasos(1);
        buscarConexion.styleLabelPasos(1);
        $($('#c_1')[0]).button('reset');
        buscarConexion.mostrarOcultarBotonAtras(false);
    },
    showPasoDatosCliente : function(){
        //$($('.nav-pills>li>a')[1]).tab('show');
        buscarConexion.setStylePasos(2);
        buscarConexion.styleLabelPasos(2);
        $($('#c_1')[0]).button('reset');
        buscarConexion.mostrarOcultarBotonAtras(true);
    },
    showPasoSeleccionAsientos : function(){
        //$($('.nav-pills>li>a')[2]).tab('show');
        buscarConexion.setStylePasos(3);
        buscarConexion.styleLabelPasos(3);
        $('#anclaCompra')[0].click();
        $($('#c_1')[0]).button('reset');
        buscarConexion.mostrarOcultarBotonAtras(true);
    },
    showPasoDatosPasajeros : function(){
        //$($('.nav-pills>li>a')[3]).tab('show');
        buscarConexion.setStylePasos(4);
        buscarConexion.styleLabelPasos(4);
        $('#anclaCompra')[0].click();
        $($('#c_1')[0]).button('reset');
        buscarConexion.mostrarOcultarBotonAtras(false);
    },
    showPasoResumenCompra : function(){
        //$($('.nav-pills>li>a')[4]).tab('show');
        buscarConexion.setStylePasos(5);
        buscarConexion.styleLabelPasos(5);
        $('#anclaCompra')[0].click();
        $($('#c_1')[0]).button('reset');
        buscarConexion.mostrarOcultarBotonAtras(false);
    },
    showDatosAdicionalesCliente : function(){
        $('.is_pasajeroDIV').css('visibility','visible');
        $('.datosClienteRutaDetallesDIV').show();
    },
    hideDatosAdicionalesCliente : function(){
        $('.is_pasajeroDIV').css('visibility','hidden');
        $('.datosClienteRutaDetallesDIV').hide();
    },
    resetUlTrayectosEsquemas : function(){
        $(".salidas_Ida").empty();
        $(".ul_salidas_Ida").empty();
        $(".salidas_Regreso").empty();
        $(".ul_salidas_Regreso").empty();
    },
    setStyleResumenIda : function(hayRegreso){
        if(hayRegreso === true){
            $('.resumenIda').css('width','49%');
            $('.resumenIda').css('margin-right','10px');
            if(!$('.resumenIda').hasClass('col-lg-6'))
                $('.resumenIda').addClass('col-lg-6');
            if(!$('.resumenIda').hasClass('col-md-6'))
                $('.resumenIda').addClass('col-md-6');
            if($('.resumenIda').hasClass('col-lg-8'))
                $('.resumenIda').removeClass('col-lg-8');
            if($('.resumenIda').hasClass('col-md-10'))
                $('.resumenIda').removeClass('col-md-10');
            $('.labelprecioTotalResumenIda').text("PRECIO DE VIAJE DE IDA: ");
        }
        else{
            if($('.resumenIda').hasClass('col-lg-6'))
                $('.resumenIda').removeClass('col-lg-6');
            if($('.resumenIda').hasClass('col-md-6'))
                $('.resumenIda').removeClass('col-md-6');
            if(!$('.resumenIda').hasClass('col-lg-8'))
                $('.resumenIda').addClass('col-lg-8');
            if(!$('.resumenIda').hasClass('col-md-10'))
                $('.resumenIda').addClass('col-md-10');
            $('.labelprecioTotalResumenIda').text("IMPORTE TOTAL: ");
            $('.precioTotalResumen').hide();
            $('.resumenRegreso').hide();
        }
    },
    setInfoResumenProvisionalCompra : function(){
        var origen = $('#buscar_conexiones_command_estacionOrigen').select2("data").text;
        var destino = $('#buscar_conexiones_command_estacionDestino').select2("data").text;
        var infoIda = "<span class='subtituloTrayectos' style='font-style: italic;'> Ida: </span> "+$('#buscar_conexiones_command_fechaSalida').val()+". "+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Origen: </span>  "+origen+". "+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Destino: </span>  "+destino+". "+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Precio: </span><span class='precioResumenProvisional'>  "+$("input[name=claseIda]:checked").val()+". </span>";
        var infoRegreso = "";
        if(buscarConexion.hayRegresos() === true){
           
            infoRegreso = "<span class='subtituloTrayectos' style='font-style: italic;'> Regreso: </span> "+$('#buscar_conexiones_command_fechaRegreso').val()+". "+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Origen: </span>  "+destino+". "+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Destino: </span>  "+origen+". "+
                "<span class='subtituloTrayectos' style='font-style: italic;'> Precio: </span>  "+$("input[name=claseRegreso]:checked").val()+". ";
        }
        var infoPasajeros = "<span class='subtituloTrayectos' style='font-style: italic;'> Cantidad de Pasajeros: </span> "+$('#buscar_conexiones_command_cantidadPasajeros').val(); 
        $.each($('.resumenProvisionalCompra'), function(){
            $(this).html(infoIda+infoRegreso+infoPasajeros);
        });
    },
    loadTerminosCondiciones : function(){
        var data = null;
        core.request({
            url : $("#loadTerm").attr("href"),
            dataType:"html",
            method: 'POST', 
            async:false,
            successCallback: function(result){
                data = result;
            },
            errorCallback: function(){
                data = "Intente nuevamente en unos segundos.";
            }
        });
        return data;
    },
    loadDetallesSeguro : function(){
        var data = null;
        core.request({
            url : $("#loadSeguro").attr("href"),
            dataType:"html",
            method: 'POST', 
            async:false,
            successCallback: function(result){
                data = result;
            },
            errorCallback: function(){
                data = "Intente nuevamente en unos segundos.";
            }
        });
        return data;
    },
    mostrarOcultarBotonAtras : function(mostrar){
        if(mostrar === true)
            $("#pull-left1 a").show();
        else $("#pull-left1 a").hide();
    },
    setPrecioResumenProvisionalCompra : function(){
        $('.precioResumenProvisional').text($($('.precioTotalViaje')[0]).text());
    }
};


