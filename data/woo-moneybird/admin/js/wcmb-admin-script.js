
(function( $ ) {
    $(document).ready(function(){
    	$("#wcmb_mbform").on('submit' , function(e){
    		
    		var clientId = $("#wcmb_clientid").val();
    		var secretId = $("#wcmb_clientSecret").val();
            var wcmb_nonce = $("#wcmb_post_security").val();
           
    		$.ajax({
    			type 	: 'POST',
    			url  	: wcmb_moneybird_ajax_script.ajaxurl,
    			dataType: "json",
    			data 	: 	{
			    				'action'	: 'get_wcmb_clientid_secretid_data',
			    				'clientId' 	: clientId,
			    				'secretId' 	: secretId,
                                'wcmb_nonce': wcmb_nonce
			    			},
    			success : function(response){
    				
    				if(response.accesssUrl){
    					window.location.href = response.accesssUrl;
    					$("#wcmb_clientid, #wcmb_clientSecret").attr("disabled", true);
    				}	
    			},
    		});
    		e.preventDefault();
    	});
    	
		$("#wcmb_reset").on('click' , function(e){
			$("#wcmb_clientid").val("").attr("disabled", false);
			$("#wcmb_clientSecret").val("").attr("disabled", false);
            $(".error").hide();
			$(".wcmb_reset").val("").hide();
			$("#wcmb_access_tocken").attr("disabled", false);
			$("#wcmb_reset").hide();
			$(".access_tocken_row").hide();
            
			$.ajax({
				type : 'POST',
				url : wcmb_moneybird_ajax_script.ajaxurl,
				data : { 'action' : 'wcmb_reset_moneybird_api_data' },
                dataType: "json",
				success	: function(response){
                    if(response.resetUrl){
                        window.location.href = response.resetUrl;
                    }
                }	
			});
			e.preventDefault();
		});
	});
})(jQuery);
