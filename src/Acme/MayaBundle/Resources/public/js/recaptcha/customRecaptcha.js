customRecaptcha = {
    
    recaptchaList : {},
    funcionesAddOnload : function() {
        this._init();		
	this._conectEvents();
    },
    
    _init: function(){
        
        var items = $(".g-recaptcha");
        $.each(items, function (pos, item){
            var id = $(item).attr("id");
            var idRecaptcha = grecaptcha.render(item, {
                'sitekey' : $("#keyRecapcha").val(),
                'theme' : 'clean'
            });
            customRecaptcha.recaptchaList[id] = idRecaptcha;
            $(item).removeClass("hidden");
        });  
        
    },
    
    _conectEvents : function(){
        
    },
    
    getIdRecaptcha : function(id){
        return customRecaptcha.recaptchaList[id];
    }
};
