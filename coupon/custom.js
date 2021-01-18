//Checks if input name has two separated strings
function checkName(){
    var name=document.getElementById("name").value;
    var res = name.split(" ");
    if(res.length>1)
        return true;
    else{
        document.getElementById("hd").style.visibility="visible";
        
        return false;
    }
}

 //Clearing get parameter
 var get = location.search.split('order=')[1];
 //window.history.replaceState(null, null, window.location.pathname);
 (function(get){
     if(get!=undefined){
         
         document.getElementById("win").value=get;
     }
    

 })()//NE ZABORAVI DA DODAS PARAMETAR,ustvari ne treba nam ova f vise
 //if table with coupons is empty disable submit button
function disableAll(){
   document.getElementsByName("submit")[0].disabled=true;
}
//Show popup anketa div
function popup(){
   var style=document.getElementById('take_coupon').style.display;
   if(style=='block')
        document.getElementById('take_coupon').style.display='none';
    else 
    document.getElementById('take_coupon').style.display='block';
}

//If question in admin panel is not checked prevent form submiting
(function($){
    $('#delete').submit(function(){
        if(!$('#delete input[type="checkbox"]').is(':checked')){
          alert("Izaberite pitanje koje želite da obrišete.");
          return false;
        }
    });
})(jQuery)
