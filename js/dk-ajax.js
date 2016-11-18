jQuery( document ).ready(function( $ ) {
  $( '#similar3' ).click(function() {

     $.ajax({
         type: 'post',
         dataType: 'html',
         url: dk_vars.ajaxurl,
         data: { action: 'dk_add_related', post_id: dk_vars.post_id, nonce: dk_vars.nonce },
         success: function( result, status, xhr )  {
           $( '.similar3container' ).html( result );
         },
         error: function( xhr, status, error ) {
           $( '.similar3container' ).html( error + status );
         }
       });
       return false;
  });
});
