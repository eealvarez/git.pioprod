core = {
       
        /*
	Función general para mostrar un dialog. 
	Recibe un arreglo donde se espcifican todas las opciones que tiene la petición.
	text: Texto del body del dialogo. (opciola)
	keyMessage: Id de un mensaje que exista en window['messages'].
	url:String que representa la url del body del dialogo.(opcional)	
	Nota.Es obligatorio especificar el text, keyMessage o la url.
	argsMessage: Si utiliza keyMessage aqui puede enviar los paremetros del mensaje. Opcional.
	type: String que representa el tipo de mensaje. (opcional)
	Puede tomar los valores: warning, info, success, error
	buttons: Lista de botones del dialogo. (opcional) Si no se especifica se crea uno por defecto que cierra el dialog.
	Cuando se definan los botones se puede asignar una funcion directamente o un objeto boton para especificar mas detalles.
	Ej. de 3 botones
	buttons: {
			Aceptar: {
				click: function() {
				//here code
				}, 
				primary: true, // if this button is the primary button (will be styled accordingly)
				type: "info" // basically additional classes to be attached to the button
			}, 
			Cancelar: function() {
				 //here code
				 this.dialog2("close");					
			},
			button3: function() {
				 //here code
				 this.dialog2("close");					
			}, 				    
	},
	defaultButtonOFF: No se crea el botton por defecto. Opcional.
	successCallback: Función que se encargará de manejar la respuesta de la petición.
	return;
	Ej. USO FACIL
	core.showMessageDialog({
		message:'Esto es una alerta'
	})
	*/
	
	showMessageDialog : function(config) {
		if(!config.type){
                    config["type"] = BootstrapDialog.TYPE_WARNING;
                }
                
                if(!config.cssClass){
                    config["cssClass"] = 'form';
                }
                
		if(!config.title)
                    config["title"] = "Fuente del Norte";
                
                if(!config.message)
                    config["message"] = "";
		
                if(!config.size)
                    config["size"] = BootstrapDialog.SIZE_NORMAL;
                
		if(!config.defaultButtonOFF || config.defaultButtonOFF === false)
		{
                    if(!config.buttons){
			config["buttons"] = [{
                            label: 'Cerrar',
                            cssClass: 'btn-primary',
                            action: function(dialog){
                                dialog.close();
                            }
                        }];
                    }
		}
//                console.debug(config);
                return BootstrapDialog.show(config);
	},
        
	uniqId : function (prefijo) {
            if(!prefijo) prefijo = "internal_" ;
            return prefijo + Math.round(new Date().getTime() + (Math.random() * 10045));
	},
        
        /*
	Función general para realizar peticiones. 
	Recibe un arreglo donde se espcifican todas las opciones que tiene la petición.
	form: String que representa el id de la forma que se manda en la petición.
	url:String que representa la url de la petición.
	Nota.Es obligatorio mandar el form o la url.
	extraParams:Es un arreglo con los parámetros extras que necesitemos pasar en nuestra petición.(opcional)
	successCallback: Función que se encargará de manejar la respuesta de la petición.
	(opcional, por defecto pone la respuesta recibida en el nodo especificado por nodeResponse)
	nodeResponse: String que representa el id del elemento HTML en el que se desplegará la respuesta de la petición. 
	(opcional, por defecto es "main")
	writeDocument:Es un valor booleano que indica si la repuesta se va a escribir en el document, ignarando el valor de nodeResponse.
	(opcional, por defecto es false)	
	errorCallback: Función que se encargará de manejar la respuesta de la petición si hay un error.
	(opcional, por defecto muestra el mensaje de error en la consola)
	dataType:String que representa la forma en la que se va a tratar la respuesta que viene del servidor.
	Los posibles valores son: *,text,html,xml,json,script,jsonp.
	async:Es un valor booleano que representa si la petición será síncrona o no, en caso de "true" se hace de forma
	asíncrona, "false" se hace síncrona. Por defecto es true (asíncrona).
	method:String que representa el método que se usará para realizar la petición, los posibles valores son: POST y GET.
	(opcional, por defecto es POST)
	cache:Es un valor booleano que establece si la petición será guardada en la cache del navegador. 
	De forma predeterminada es true para todos los dataType excepto para “script” y “jsonp”. 
	Cuando posee el valor false, se agrega una cadena de caracteres anti-cache al final de la URL de la petición.
		
	Ej.
	core.request({
	url : 'moduloPlantilla1/listarPaginando.htm',
	extraParams : {
	par1: 'val1'
	},
	successCallback: function(success, statusText, jqXHR){},
	errorCallback: function(jqXHR, statusText, error){}});
	
	@return 
	*/
        requestsCount : 0,
	request : function(request) {
                if (!request['form'] && !request['url']){
                    throw new Error("Tiene que especificarse la URL o el Form, uno de los dos.");
                }
                
                var data = {};
		if (request['extraParams']){
                    data = request['extraParams'];
                }
		
                var showLoading = request['showLoading'];
		if(!showLoading) showLoading = false;
                
		var url = "";
		if (request['form']) {			
			var form = $("#"+request['form']);
			if(form.length == 0)
				throw new Error("No se pudo encontrar el formulario:"+request['form']);			
			data['form'] = form;
			url = form.attr("action");
		} else
			url = request['url'];		
		
		request["targetUrl"] = url;
		
		var ajaxRequest = true;	
		if (request['ajaxRequest'] !== null)
			ajaxRequest = request['ajaxRequest'];		
		data['ajaxRequest'] = ajaxRequest;
		
		if(ajaxRequest === false){
                    if(showLoading){
                        core.showLoading(request); //Se muestra el loading para evitar multiples clic...
                    }
                    window.location.replace(url);
                    return;
		}
                
                var alwaysCallback = request['alwaysCallback'];
		var successCallback = request['successCallback'];
		_successCallback = function(success, statusText, jqXHR) {
			//console.debug("_successCallback.init");
			//console.debug(jqXHR);
                        if(showLoading){
                            core.hideLoading(request);
                        }
			
			if(core.procesarRespuestaServidor(success, request, jqXHR)){
                            if(alwaysCallback){
                                alwaysCallback(success, statusText, jqXHR)
                            }
                            return;
                        }
                           
			
			if(successCallback){
                            return successCallback(success, statusText, jqXHR);				
			}
			else{	
                            core._defaulSuccessCallback(success, request);
			}
                        
                        if(alwaysCallback){
                            alwaysCallback(success, statusText, jqXHR)
                        }
		};
		
		var errorCallback = request['errorCallback'];
		_errorCallback = function(jqXHR, statusText, error) {
//			console.debug("_errorCallback.init");
//                        console.debug(jqXHR);
//                        console.debug(statusText);
//                        console.debug(error);
                        
                        if(showLoading){
                            core.hideLoading(request);
                        }			
//			console.debug("Error en petición ajax, statusText:" + statusText + ",error:"+error);
			core.procesarRespuestaServidor(jqXHR, request);
			if(errorCallback)
				errorCallback(jqXHR, statusText, error);
                            
                        if(alwaysCallback){
                            alwaysCallback(jqXHR, statusText, error)
                        }
			
		};

		var beforeSendCallback = request['beforeSendCallback'];
		_beforeSendCallback = function(jqXHR, statusText, error) {
			//console.debug("_beforeSendCallback.init");			
			if(beforeSendCallback)
				beforeSendCallback(jqXHR, statusText, error);			
		};
		
		var dataType = request['dataType'];
		if(!dataType) dataType = "*";
			
		//Defaul is asincrono
		var async = request['async'];
		if (async) async = true;

		var method = request['method'];
		if(!method) method = "POST";
		method = jQuery.trim(method).toUpperCase();
		if(method === "POST" || method === "GET" )
		{		
                        if(showLoading){
                            core.showLoading(request);
                        }
			
			var config = {
				url: url,
				type: method,
				dataType: dataType, 
				async: async,				
				data: data,
				success:_successCallback,
				error:_errorCallback
			};

//                        if (request['beforeSend'] !== null)
//                            config['beforeSend'] = request['beforeSend'];
//                        
                        if (request['crossDomain'] !== null){
                            jQuery.support.cors = true;
                            config['crossDomain'] = request['crossDomain'];
                        }
                        
			if (request['cache'] !== null){
                            config['cache'] = request['cache'];
                        }
//                        if (request['headers'] !== null){
//                            config['headers'] = request['headers'];
//                        }
                        
                        jQuery.ajaxSetup({ cache: false });
			return jQuery.ajax(config).done(function(response) {
				return response;
			});			
			
		}else{
			throw new Error("La función request no soporta el método: "+method+".");			
		}
	},
        
        elementWait: 'waitDiv',
        hideLoading : function(request) {
		var showLoading = request['showLoading'];
		showLoading = (showLoading === undefined) ? true : showLoading;
		if(showLoading){
			core.requestsCount--;
			if (core.requestsCount === 0){
				var waitDiv = $("#"+core.elementWait);
				if(waitDiv.length !== 0) {
					waitDiv.hide();
				}else{
//					console.debug("No se pudo ocultar el elemento 'loading' porque no se encontro el elemento:"+elementWait+".");
				}
			}
		}
	},
        
	showLoading : function(request) {
            return;
            
//                console.log("showLoading-init");
                var showLoading = request['showLoading'];
		showLoading = (showLoading === undefined) ? true : showLoading;
		if(showLoading){
			var waitDiv = $("#"+core.elementWait);
			if(waitDiv.length === 0) {
				var container = document.createElement("div");
				container.setAttribute('id', core.elementWait);
				var image = document.createElement("img");
				image.setAttribute('id', 'loaderGif');
				image.setAttribute('src', $("#loader1").attr("href"));
				container.appendChild(image);
				$("body").append($(container));
			}
			//waitDiv.show();
			core.requestsCount++;
		}
	},
	
        _defaulSuccessCallback : function(success, request) {

		var writeDocument = request['writeDocument'];
		if(writeDocument){
			/*
			 * Importante, está opcion no se debe utilizar   
			 * en caso de que carge una pagina con un path distinto
			 * al que esta en el navegador.
			*/
			document.open();
			document.write(success);
			document.close();					
			return;
		}
		
		var nodeName = request['nodeResponse'] ? request['nodeResponse'] : 'content';
		if(request['dataType'] !== "script" && request['dataType'] !== "json")
		{
//			console.debug("_successCallback. seteando repuesta en el node:"+nodeName);
			var node = $("#"+nodeName);
			if(node.length !== 0) {
                           node.html($(success));
                           core.moveTopBody();
//                           if(jQuery.parser){
//                                jQuery.parser.parse();
//                           }
                           core.focus();
                           
                           
			}else {
				throw new Error("No se pudo encontrar el elemento HTML:"+nodeName+ " para setear la respuesta de la petición.");					
			}
		}
	},
        
        focus : function( item ) {
           if(item){
               $(item).focus();
           }else{
               $(".focus-n1").focus();
               $(".focus-n2").focus();
           }
        },
        
	
	/*
	Función interna del menu para cargar las páginas
	@param url: Url de la página que se quiere mostrar.
	@return 
	Ej. core.insertScript('js/commun/xx.js');
	*/	
        htmlBase : '',
	getPageForMenu : function(url) {
//            console.log("request page menu.");
            core.clearAllDialogFromBody();
            if($(".btn.btn-navbar.menutop").css("display") !== "none"){
//                console.log("request page menu.init");
                $("#content").html(core.htmlBase);
                $(".btn.btn-navbar.menutop").click(); //Only cell
                core.moveTopBody();
//                console.log("request page menu.end");
            }
            return core.request({
                url : url,
                method: 'GET',
		dataType:"html",
		async:true
            });
	},
        
        getValueFromResponse : function(response, name, tagName) {
            var element = core.getElementFromResponse(response, name, tagName);
            if(element === undefined || element === null){
                return "";
            }else{
                return element.value;
            }
        },
        
        getElementFromResponse : function(response, name, tagName) {
            var component = document;
            if(response !== null){
                component = document.createElement("div");
		component.innerHTML = response;
            }
            if(!tagName){ tagName = 'input'; }
            var elements = component.getElementsByTagName(tagName);
            return elements[name];  
        },
        
        /*
	Función para procesar el response de una peticion ajax.
	@return
	*/
	procesarRespuestaServidor : function(response, request, jqXHR) {
            
//		console.debug("procesarRespuestaServidor-init...");
		if(!jqXHR) jqXHR = response;
		
		var codigoMensaje = "";
		var mensajeServidor = "";
		var component = "";
		if(response.status && response.status===401){
//                    console.debug("Pagina principal de loggin.");
                    window.location.replace(response.statusText);
		}
		else if((response.status && response.status===500) || (response.statusText && response.statusText==="error"))
		{
//                    console.debug("Error interno del servidor");
                    codigoMensaje = "m1";
                    mensajeServidor = response.statusText.replace(codigoMensaje, "");
                    if(mensajeServidor === "error"){
                         mensajeServidor = "Ha ocurrido un error en el sistema.";
                    }
                    
		}
		else if(response.status && response.status===403)
		{
//                    console.debug("Acceso prohibido");
                    codigoMensaje = "m3";
                    mensajeServidor = response.statusText.replace(codigoMensaje, "");			
		}		
		else
		{
                    component = document;
                    if(response !== null){
                        component = document.createElement("div");
			component.innerHTML = response;
                    }
                    var inputs = component.getElementsByTagName('input');
                    var mensaje = inputs['mensajeServidor']!==undefined && inputs['mensajeServidor']!==null ? inputs['mensajeServidor'].value : "";
                    codigoMensaje   = mensaje.substring(0, 2);
                    mensajeServidor = mensaje.substring(2);
		}
		
//            console.debug("procesarRespuestaServidor-datos.init...");
//            console.debug("codigoMensaje:"+codigoMensaje);
//            console.debug(component);
//            console.debug(request);
//            console.debug("procesarRespuestaServidor-datos.end...");
		
            switch(codigoMensaje) {
        	case "m0":
//                        console.log("case m0");
        		return false;
        	case "m1": //error de mostrar en pagina
//                        console.log("case m1");
//        		alert(mensajeServidor);
//                        var modal = $("div[id*='dialog_internal']").parent();
//                        var modal = $('.modal[style^="display: block;"]'); //Modal Activo
//                        modal.hide();
                         core.showMessageDialog({
                             title: 'Error',
                             message : mensajeServidor
                         });
        		return true;
        	case "m2": //error de popup
//                        console.log("case m2");
        		alert(mensajeServidor);
        		return true;
        		
        	default:
//                        console.log("case default");
        		return false;
            }
            
	},       
        
        //Debe tener el formato dd/mm/yyyy o dd-mm-yyyy o  dd.mm.yyyy y la funcion retorna un objeto date
    formatDate : function(value){
        items = value.split(/[\.\-\/]/);
        dia = items[0];
        mes = items[1];
        anho = items[2];
        fecha = anho + "," + mes + "," + dia;
        var fecha = new Date(fecha);
        if (!isNaN(fecha)){
            return fecha;
        } else {
            fecha = new Date(parseInt(anho), (parseInt(mes)-1), parseInt(dia), 0, 0, 0);
            if (!isNaN(fecha)){
                return fecha;
            }else{
                throw new Error("No se pudo formatear a fecha el valor:" + value);
            }
        }
    },
    
    dateComapreTo : function(fecha1, fecha2) {
        return  this.formatDate(fecha1).getTime() - this.formatDate(fecha2).getTime();
    },
    
    isDate : function(str) {    
        var parms = str.split(/[\.\-\/]/);
        var yyyy = parseInt(parms[2],10);
        var mm   = parseInt(parms[1],10);
        var dd   = parseInt(parms[0],10);
        var date = new Date(yyyy, mm-1,dd,0,0,0,0);
        return mm === (date.getMonth()+1) && dd === date.getDate() && yyyy === date.getFullYear();
     },
    
    areDateEquals: function(date1, date2){
        if(date1.getFullYear() === date2.getFullYear() && 
           date1.getMonth() === date2.getMonth() && 
           date1.getDate() === date2.getDate())
            return true;
        else 
            return false;
    },
    
    getSelectedItemId : function(grid){
        if(grid === null){
            return $('.trSelected').data("id");
        }else{
          return $(grid).find('.trSelected').data("id");
        }
    },
    
    ajustarPosicion : function(valor) {
        valor = Math.abs(valor);
        var result = parseInt(valor / 50);
        result = result * 50;
        if(valor%50 >= 25)
            result += 50;
        return result;
    },
    
    /* Retorna el array sin el elemento */
    removeItemArray : function(array, item) {
       return $.grep(array, function(value) {
           return value !== item; 
       });
    },
    removeObjectArray : function(array, item) {
       return $.grep(array, function(value) {
           return value.text() !== item.text(); 
       });
    },
    
    customValidateForm: function(form) {
//        console.debug("customValidateForm-init");
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
    
    isMovilInternal : null,
    isMovil :  function (){  
        
        if(core.isMovilInternal === null){
            try {
                var value = navigator.userAgent||navigator.vendor||window.opera;
                if(/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(value)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(value.substr(0,4))){  
                    core.isMovilInternal = true;
                }else{
                    core.isMovilInternal = false;
                }
            }catch (e){
                core.isMovilInternal = true;
            }
        }
        return core.isMovilInternal;
    },
    
    addDiasAFecha :  function (days, fecha){
        if(fecha === null || fecha === undefined){
            fecha = new Date();
        }
        if(days === null || fecha === undefined){
           days = 0;  
        }
        days = parseInt(days);
        var ms = fecha.getTime() + (86400000 * days);
        return new Date(ms);
    },
    
    checkExistError :  function (data){
        
        if(data.status === "1"){
            return false;
        } else if(data.status === "4"){
            return "Código de barra incorecto";
        }else if(data.status === "7"){
            return "El identificador de encomienda no existe.";
        }else if(data.status === "6"){
            return data.message;
        }else if(data.status === "2"){
            return "Ha ocurrido un error en el sistema.";
        }else if(data.status === "3"){
            return "Error con las credenciales.";
        }else{
            return "Ha ocurrido un error en el sistema.";
        }
    },
    
    moveTopBody : function (){
        $('html,body').animate({ scrollTop: $("body").offset().top }, 500);
    },
    
    isInt : function (value){
        return typeof value === "number" && isFinite(value) && value%1 === 0;
    },
    
    customParseFloat : function (value){
        if(typeof value === "string"){
            return parseFloat(value.replace(/,/g,"."));
        }else{
            return parseFloat(value);
        }
    },
    
    /*
	Función general para mostrar una notificacion. 
	Recibe un arreglo donde se espcifican todas las opciones siguientes:
	text:String que representa la información de la notificacion
	title:String que representa el titulo de la notificacion.
	(opcional) Por defecto se muestra el nombre del sistema
	type:String que representa el tipo de notificacion. 
	Posibles valores: notice, info, success, error
	(opcional) Por defecto se muestra info
	icon:String que representa el nombre de la clase css del icono.
	(opcional) Por defecto se muestra el que corresponda segun el type. Ej. icon:'picon picon-32 picon-fill-color',
	onClick: Función que se ejecuta cuando se presione click sobre la notificación. (opcional)	
	return;
	Ej. core.showNotification({text:"Esta es una notificacion"});
	*/
    showNotification : function(config) {
        
        if(core.isMovil()){
            return;
        }
        
        if(!config) config = {};
        
	if(config["text"] && $.trim(config["text"]) !== ""){
            if(!config["title"])
            config["title"] = "FDN";
		
            if(!config["type"])
                config["type"] = "info";

            config["styling"] = "bootstrap2";
            config["delay"] = 15000; //10 segundos
            new PNotify(config);   
        }else{
//            console.log("No se especifico el texto del mensaje.");
        }
    },
    
    customNotEqual : function(message){
        $.validator.addMethod("notEqualTo", function(value, element, param) {
        return this.optional(element) || value !== $(param).val();
        }, message);
    },
    isIterable:function(obj){
        if(obj === undefined || obj === null){
           return false;
        }else{
           return obj.iterator !== undefined;
        }
    },
    
    previewDataPDF : function(url, extraParams, alwaysCallback) {
        if(!url || $.trim(url) === ""){
            throw new Error("Debe definir la url.");
        }
        
        if(!extraParams){
             extraParams = {};
        }
        
        core.request({
                url : url,
                method: 'POST', //Obligatorio
                dataType: "html",
                async: false,
                extraParams: extraParams, 
                successCallback: function(responseText){
                    if(!core.procesarRespuestaServidor(responseText)){
                        if(alwaysCallback && $.isFunction(alwaysCallback)){
                            alwaysCallback();
                        }
                        setTimeout(function(){
                            var urlPDF = core.getValueFromResponse(responseText, "data");
                            window.open(urlPDF);
                        }, 500);     
                    }
                },
                alwaysCallback: function(){
                    if(alwaysCallback && $.isFunction(alwaysCallback)){
                        alwaysCallback();
                    }
                }
        });
    },

};