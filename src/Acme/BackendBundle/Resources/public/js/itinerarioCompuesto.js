itinerarioCompuesto = {
    
    funcionesAddOnload : function() {
        console.debug("itinerarioCompuesto.funcionesAddOnload-init");
	this._init();		
	this._conectEvents();
	console.debug("itinerarioCompuesto.funcionesAddOnload-end");
    },
			
    _init : function() {
        
        $("#itinerarioPopUp").select2({});
        
        $("#bajaEnPopUp").select2({
            allowClear: true,
            data: []
        });
        itinerarioCompuesto.renderParadasIntermedias();
    },
    
    _conectEvents : function() {
        
        $(".add.btn").click(itinerarioCompuesto.loadItinerariosSimples); 
        $(".addPopUp.btn").click(itinerarioCompuesto.addItem); 
        $("#itinerarioPopUp").on("change", itinerarioCompuesto.changeItinerario);
        $("#backendbundle_itinerario_compuesto_type_estacionOrigen").on("change", itinerarioCompuesto.changeEstacionOrigen);
    },
    
    changeEstacionOrigen : function(e) {
        console.log("changeEstacionOrigen-init");
        $("#backendbundle_itinerario_compuesto_type_listaParadasIntermediasHidden").val(JSON.stringify([]));
        itinerarioCompuesto.renderParadasIntermedias();
    },
    
    itinerariosSimplesOptions : [],
    loadItinerariosSimples : function(e) {
        console.log("clicAddItem-init");
        $("#itinerarioPopUp").find("option").remove();
        $("#itinerarioPopUp").select2();
        $("#bajaEnPopUp").select2({ allowClear: true, data: [] });
        $("#bajaEnPopUp").select2("val", "");
        $("#bajaEnPopUp").val("");
        var idUltimoDiaSemana = "";
        var idUltimaEstacion = "";
        var lastTr = $(".trSelect:last");
        if(lastTr.length === 0){
            console.log("no existen items, cargado estacion de origen.");
            idUltimaEstacion = $("#backendbundle_itinerario_compuesto_type_estacionOrigen").select2('val');
        }else{
            console.log("existen items....");
            idUltimoDiaSemana = lastTr.data("iddiasemana");
            idUltimaEstacion = lastTr.data("idestacion");
        }
        console.log("idUltimoDiaSemana:"+idUltimoDiaSemana);
        console.log("idUltimaEstacion:"+idUltimaEstacion);
        
        $.ajax({
            url: $("#pathListarItinerariosSimples").val(),
            type: "POST",
            dataType : "json",
            data: {
                idUltimoDiaSemana : idUltimoDiaSemana,
                idUltimaEstacion : idUltimaEstacion
            },
            success: function(data){
                itinerarioCompuesto.itinerariosSimplesOptions = data.options;
                $.each(data.options, function(index, value){
                    $('#itinerarioPopUp').append('<option data-clave="'+value.clave+'" class="" value="' + value.id+ '">'+value.text+'</option>');
                });
                $('#itinerarioModal').modal('show');
            }
        });
    },
    
    findItemInLista : function(idItem) {
        console.log("findItemInLista");
        var item = null;
        $.each(itinerarioCompuesto.itinerariosSimplesOptions, function(index, value){
            if(value.id === idItem){
                item = value;
                return;
            }
        });
        return item;
    },
    
    filterDataItinerarioSelect : function() {
        console.log("filterDataItinerarioSelect-init");
        var selectItemsItinerarios = $("#itinerarioPopUp").select2("data");
        if(selectItemsItinerarios !== null && selectItemsItinerarios.length !== 0){
            var item = itinerarioCompuesto.findItemInLista(selectItemsItinerarios[0].id);
            if(item !== null){
                var claveFilter = item.clave;
                console.log("Clave:"+claveFilter);
                $('#itinerarioPopUp').find("option[data-clave="+claveFilter+"]").removeClass("hidden");
                $('#itinerarioPopUp').find("option").not("option[data-clave="+claveFilter+"]").addClass("hidden");
            }else{
                $('#itinerarioPopUp').find("option").removeClass("hidden");
                itinerarioCompuesto.lastRutaLoaded = null;
            }
            
        }else{
            $('#itinerarioPopUp').find("option").removeClass("hidden");
            itinerarioCompuesto.lastRutaLoaded = null;
        }
    },
    
    lastRutaLoaded : null,
    changeItinerario : function() {
        console.log("changeItinerario-init");
        var selectItemsItinerarios = $("#itinerarioPopUp").select2("data");
        if(selectItemsItinerarios === null || selectItemsItinerarios.length === 0){
            $(".bajeEnDivPopUp").addClass("hidden");
            itinerarioCompuesto.filterDataItinerarioSelect();
            itinerarioCompuesto.lastRutaLoaded = null;
        }else{
            itinerarioCompuesto.filterDataItinerarioSelect();
            var item = itinerarioCompuesto.findItemInLista(selectItemsItinerarios[0].id);
            if(item !== null){
                if(itinerarioCompuesto.lastRutaLoaded !== item.codigoRuta){
                    itinerarioCompuesto.loadEstacionesIntermedias(item.codigoRuta);
                }
            }
        }
    },
    
    loadEstacionesIntermedias : function(ruta) {
        console.log("loadEstacionesIntermedias-init");
        $("#bajaEnPopUp").select2({ data: [] });
        $.ajax({
            url: $("#pathListarEstacionesByRuta").val(),
            type: "POST",
            dataType : "json",
            data: {
                ruta : ruta
            },
            success: function(data){
                var optionEstacionOrigen = data.optionEstacionOrigen;
                var optionEstacionDestino = data.optionEstacionDestino;
                var optionsEstacionesIntermedias = data.optionsEstacionesIntermedias;
                var results = optionEstacionOrigen.concat(optionsEstacionesIntermedias).concat(optionEstacionDestino);
                $("#bajaEnPopUp").select2({
                    allowClear: true,
                    data: { results: results }
                });
                $(".bajeEnDivPopUp").removeClass("hidden");
                itinerarioCompuesto.lastRutaLoaded = ruta; 
            }
        });
    },
    
    addItem : function() {
        console.log("addItem-init");
        var selectItemsItinerarios = $("#itinerarioPopUp").select2("data");
        if(selectItemsItinerarios === null || selectItemsItinerarios.length === 0){
            alert("Debe seleccionar al menos un itinerario.");
            return;
        }
        var bajaEn = $("#bajaEnPopUp").select2('val');
        if(bajaEn === null || $.trim(bajaEn) === ""){
            alert("Debe seleccionar una estación destino.");
            return;
        }
        
        var listaParadasIntermedias = $("#backendbundle_itinerario_compuesto_type_listaParadasIntermediasHidden").val();
        if(listaParadasIntermedias){ listaParadasIntermedias = JSON.parse(listaParadasIntermedias); }
        else{ listaParadasIntermedias = []; }
        
        var clave = "";
        var idDiaSemana = "";
        var codigoRuta = "";
        var item = itinerarioCompuesto.findItemInLista(selectItemsItinerarios[0].id);
        if(item !== null){
            clave = item.clave;
            idDiaSemana = item.idDiaSemana;
            codigoRuta = item.codigoRuta;
        }
        
        var listaItinerariosSimples = [];
        jQuery.each(selectItemsItinerarios, function() {
            listaItinerariosSimples.push({
                idItinerario : this.id,
                nombreItinerario : this.text,
                clave : clave,
                idDiaSemana : idDiaSemana,
                codigoRuta : codigoRuta
            });
        });
        
        var orden = listaParadasIntermedias.length;
        listaParadasIntermedias.push({
            id : "",
            orden : orden+1,
            clave : clave,
            idDiaSemana : idDiaSemana,
            codigoRuta : codigoRuta,
            listaItinerariosSimples : listaItinerariosSimples,
            idEstacion : $("#bajaEnPopUp").select2("data").id,
            nombreEstacion : $("#bajaEnPopUp").select2("data").text
        });
        $("#backendbundle_itinerario_compuesto_type_listaParadasIntermediasHidden").val(JSON.stringify(listaParadasIntermedias));
        itinerarioCompuesto.renderParadasIntermedias();
        $('#itinerarioModal').modal('hide');
    },
    
    renderParadasIntermedias : function() {
        console.log("renderParadasIntermedias-init");
        $("#itinerariosBody").find("tr").not("#itinerariosVacioTR").remove();
        $("#itinerariosBody").find("#itinerariosVacioTR").show();
        
        var listaParadasIntermedias = $("#backendbundle_itinerario_compuesto_type_listaParadasIntermediasHidden").val();
        if(listaParadasIntermedias){ listaParadasIntermedias = JSON.parse(listaParadasIntermedias); }
        else{ listaParadasIntermedias = []; }
        
        if(listaParadasIntermedias.length !== 0){
            $("#itinerariosBody").find("#itinerariosVacioTR").hide();
            console.log("renderParadasIntermedias: " + listaParadasIntermedias.length + " items");
            jQuery.each(listaParadasIntermedias, function() {
                var orden = parseInt(this.orden);
                console.log("Orden:"+orden);
                if( orden === 1){
//                    $("#backendbundle_itinerario_compuesto_type_idDiaSemana").val(this.idDiaSemana);
//                    $("#backendbundle_itinerario_compuesto_type_idHorarioCiclico").val(this.idHorarioCiclico);
                }else if(orden === listaParadasIntermedias.length){
                    $("#backendbundle_itinerario_compuesto_type_idEstacionDestino").val(this.idEstacion);
                }
                
                var itemTrStr =     "<tr class='trSelect'" +
                                    " data-id='"+this.id+"' data-posicion='"+orden+"' data-iddiasemana='"+this.idDiaSemana+"' data-idestacion='"+this.idEstacion+"'> "+
                                    " <td>"+orden+"</td> " +
                                    " <td> ";
                
                jQuery.each(this.listaItinerariosSimples, function() {
                    itemTrStr +=    this.nombreItinerario+"<BR>";
                });         
                            
                    itemTrStr +=    " </td> "+
                                    " <td>"+this.nombreEstacion+"</td> ";
                            
                if($("#actionHidden").val() === "create"){
                    itemTrStr +=    " <td><a class='btn btn-primary btn-xs deleteItem'><span class='glyphicon glyphicon-minus' aria-hidden='true'></span></a></td>";
                }else{
                    itemTrStr +=    " <td></td>";
                }
                    itemTrStr +=    " </tr> ";
                    
                $("#itinerariosBody").append($(itemTrStr));
            });
//            console.log("IdDiaSemana:"+$("#backendbundle_itinerario_compuesto_type_idDiaSemana").val());
//            console.log("IdHorarioCiclico:"+$("#backendbundle_itinerario_compuesto_type_idHorarioCiclico").val());
            console.log("IdEstacionDestino:"+$("#backendbundle_itinerario_compuesto_type_idEstacionDestino").val());
            $("#itinerariosBody").find(".deleteItem").click(itinerarioCompuesto.clickDeletedItem);
        }
    },
    
    clickDeletedItem : function(e){
        console.log("clickDeletedItem-init");
        
        var listaParadasIntermedias = $("#backendbundle_itinerario_compuesto_type_listaParadasIntermediasHidden").val();
        if(listaParadasIntermedias){ listaParadasIntermedias = JSON.parse(listaParadasIntermedias); }
        else{ listaParadasIntermedias = []; }
        
        var posicion = $(this).parent().parent().data("posicion");
        if(posicion === undefined || posicion === null){
            console.debug($(this));
            throw new Error("No se puedo determinar la posición a eliminar");
        }
        posicion = parseInt(posicion)-1;
        console.log("Posicion a eliminar: " + posicion);
        var item = listaParadasIntermedias[posicion];
        if(item !== null){
            listaParadasIntermedias = listaParadasIntermedias.slice(0, posicion); 
            $("#backendbundle_itinerario_compuesto_type_listaParadasIntermediasHidden").val(JSON.stringify(listaParadasIntermedias));
        }
        itinerarioCompuesto.renderParadasIntermedias();
    }
};