home = {
    
    cantidadImagesLoaded : 0,
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
     
    },
    
    _conectEvents: function(){
        
        $('#contentHome a').click(function(e){
            e.preventDefault();
            core.request({
                url : $(this).attr("href"),
                dataType:"html",
                method: 'POST', 
                async:false,
                successCallback: function(data){
                    $('#contenidoSitio').replaceWith("<div id='contenidoSitio'>"+data+"</div>");
                },
                errorCallback: function(){
//                   console.debug("error");
                }
            });
        });
        
        var items = $(".carousel-inner img");
        home.cantidadImagesLoaded = items.length;
        items.one("load", function() {
//            console.log("loaded");
            home.cantidadImagesLoaded--;
            if(home.cantidadImagesLoaded <= 0){
//                console.log("remove hidden banner");
                $("#banner").removeClass("hidden");
            }
        }).each(function(){
            var item = $(this);
            var src = item.data("src") + "&full=" + (!core.isMovil());
//            console.log("setting new src: " + src);
            item.attr("src", src);
            if(this.complete) item.load();
        });
    }
};
home.funcionesAddOnload();


