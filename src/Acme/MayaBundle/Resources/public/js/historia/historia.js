historia = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
        
        
    },
    
    _conectEvents: function(){
         $(".btn.historyBtn").click(function (e){
//            console.log("sendMensaje-init");
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
                            title : "Historia",
                            size : BootstrapDialog.SIZE_LARGE,
                            type : BootstrapDialog.TYPE_INFO,
                            message : html
                        });
                    }else{
                        core.showMessageDialog({
                            message:'No se pudo obtener la historia.'
                        });
                    }
                },
                errorCallback: function(jqXHR, statusText, error){
                    btn.button('reset');
                }
            });
        });
    }
};


