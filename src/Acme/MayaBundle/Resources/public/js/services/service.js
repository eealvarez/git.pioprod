service = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    _init: function(){
   
        $('.fancybox').fancybox({
            helpers : {
                title: {
                    type: 'inside',
                    position: 'bottom'
                }
            }
        });
        
    },
    
    _conectEvents: function(){
        
    },
    
    clicLinkLoadImage : function(e) {
        e.preventDefault();
        e.stopPropagation();
        var item = $(this);
        core.request({
            url : $("#pathDataImagen").val(),
            type: "POST",
            dataType: "json",
            async: true,
            extraParams : { id : item.data("idimagen"), full : true },
            successCallback: function(json){
                if(json.image){
                    $.fancybox.open({
                        content : json.image,
                        title : json.title,
                        helpers : {
                            title: {
                                type: 'inside',
                                position: 'bottom'
                            }
                        }
                    });
                }                
            }
        });
    }
};


