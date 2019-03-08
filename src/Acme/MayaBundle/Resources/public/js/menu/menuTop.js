customMenuTop = {
    
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
        
    },
    
    _conectEvents: function(){

        $('#menuTop a, a.menuHelp').click(function(e){
            e.preventDefault();
            e.stopPropagation();            
            var btn = $(this);
            btn.button('loading');
            btn.addClass("active");
            $('#menuTop a').not(btn).removeClass('active');
            $('.pls-container').remove(); //remove recaptcha
            core.request({
                url : btn.attr("href"),
                dataType:"html",
                method: 'POST', 
                async: true,
                extraParams : { 'movil' : core.isMovil() },
                successCallback: function(data){
                    btn.button('reset');
                    $('#contenidoSitio').replaceWith("<div id='contenidoSitio'>"+data+"</div>");
                    if($(".navbar-toggle").css("display") !== "none"){
                        customMenuTop.moveTopBody();
                        $("button.navbar-toggle").click(); //Only cell
                    }
                },
                errorCallback: function(e){
                    btn.button('reset');
//                    console.debug("error");
                }
            });
        });
    },
    
    moveTopBody : function (){
        $('html,body').animate({ scrollTop: $("body").offset().top }, 500);
    }
};
customMenuTop.funcionesAddOnload();