(function($){
    $("#formica").on('submit',function(){
        event.preventDefault();
        ajax_request();
      });

    function ajax_request(){
        $.post(
            "http://localhost/barcode/wp-content/plugins/coupon/insert.php",
            $( "#formica" ).serialize(),
            function(data){
                var tmp=data;
                if(tmp=='dbempty'){
                    document.getElementsByName("submit")[0].disabled=true;
                }else if(tmp=='exist'){
                    alert('Sa ovom Email adresom je vec iskorišćen i preuzet gratis kupon');
                }else{
                    $('#set').val(tmp);
                }
                
            });
        
    }
})(jQuery)