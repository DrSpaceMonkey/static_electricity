jQuery(document).ready( function() {
	//'static_electricity_sideloader';
      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : staticElectricity.ajaxurl,
         //data : {action: "my_user_vote", post_id : post_id, nonce: nonce},
         success: function(response) {
            if(response.type == "success") {
               jQuery("#static_electricity_sideloader").html(response.vote_count)
            }
            else {
               alert("Your vote could not be added")
            }
         }
      })   
});