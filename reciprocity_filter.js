//Resources filter

var industries = new Array();
var use_cases = new Array();
var types = new Array();
var text = '';

function search_click(){
  text = $("#search").val()
  document.cookie = 'search_string_resources='+text;
  ajax_search();
}
function ajax_search() {
  var all_data = { action: "resources_ajax", industry: industries, cases:use_cases , content_types: types, string:text };
  
  //var templateUrl = window.location.origin;
  var templateUrl = 'http://localhost/reciprocity-wordpress';
  jQuery.ajax({
    url: templateUrl+"/wp-admin/admin-ajax.php",
    method: "POST",
    async: true,
    data: all_data,
  }).done(function(html) {
    jQuery('#content').html(html);
  }).fail(function(xhr, status, err) { console.log([xhr, status, err]); });
}

function set_tab_cookie(num){
  document.cookie = 'openedtab='+num;
}

///

if($('.filter-wrapper').length){
  if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
    var search_string = getCookie('search_string_resources');
    if(search_string){
      text = search_string;
      ajax_search();

    }else{
      var cookie_industries = getCookie('industries');
      if(cookie_industries){
        industries = cookie_industries.split(',');
        var filter_item_industry = $('.filter-clear[data-name="industry"]');
        $(filter_item_industry).css('display','block');
        $(filter_item_industry).prev().find('.filter-label').css('display','none');
        $(filter_item_industry).prev().find('.filter-count').text(industries.length+' Selected');
        $(filter_item_industry).prev().find('.filter-count').css('display','inline');
      }
      var cookie_use_cases = getCookie('use_cases');
      if(cookie_use_cases){
        use_cases = cookie_use_cases.split(',');
        var filter_item_cases = $('.filter-clear[data-name="use-cases"]');
        $(filter_item_cases).css('display','block');
        $(filter_item_cases).prev().find('.filter-label').css('display','none');
        $(filter_item_cases).prev().find('.filter-count').text(use_cases.length+' Selected');
        $(filter_item_cases).prev().find('.filter-count').css('display','inline');
      }
      var cookie_types = getCookie('types');
      if(cookie_types){
        types = cookie_types.split(',');
        var filter_item_types = $('.filter-clear[data-name="content-types"]');
        $(filter_item_types).css('display','block');
        $(filter_item_types).prev().find('.filter-label').css('display','none');
        $(filter_item_types).prev().find('.filter-count').text(types.length+' Selected');
        $(filter_item_types).prev().find('.filter-count').css('display','inline');
      }
      ajax_search();
    }
  }
  if(industries.length == 0 && use_cases.length == 0 && types.length == 0){
    ajax_search();
  }
  
    $('.filter-data').change(function() {
      var item_id = $(this).data('id');

        if( $(this).is(":checked") ) {

          if( $(this).attr('name') == 'industry' ) {
            industries.push(item_id);
            document.cookie = 'industries='+industries;
            ajax_search();
          }
           
          if( $(this).attr('name') == 'use-cases' ) {
            use_cases.push(item_id);
            document.cookie = 'use_cases='+use_cases;
            ajax_search();
          }

          if( $(this).attr('name') == 'content-types' ) {
            types.push(item_id);
            document.cookie = 'types='+types;
            ajax_search();
          }
        }else{

          if( $(this).attr('name') == 'industry' ){
            industries = $.grep(industries, function(value) {
              return value != item_id;
            });
            document.cookie = 'industries='+industries;
            ajax_search();
          }

          if( $(this).attr('name') == 'use-cases' ){
            use_cases = $.grep(use_cases, function(value) {
              return value != item_id;
            });
            document.cookie = 'use_cases='+use_cases;
            ajax_search();
          }

          if( $(this).attr('name') == 'content-types' ) {
            types = $.grep(types, function(value) {
              return value != item_id;
            });
            document.cookie = 'types='+types;
            ajax_search();
          }
        }
    });

    $('.filter-clear').click(function(){
      if( $(this).data('name') == 'industry' ){
        for(var i=0; i<industries.length; i++){
          $('input[data-id="'+industries[i]+'"]').prop("checked", false);
        }
        industries = [];
        document.cookie = 'industries='+industries;
        ajax_search();
      }
      if( $(this).data('name') == 'use-cases' ){
        for(var i=0; i<use_cases.length; i++){
          $('input[data-id="'+use_cases[i]+'"]').prop("checked", false);
        }
        use_cases = [];
        document.cookie = 'use_cases='+use_cases;
        ajax_search();
      }
      if( $(this).data('name') == 'content-types' ){
        for(var i=0; i<types.length; i++){
          $('input[data-id="'+types[i]+'"]').prop("checked", false);
        }
        types = [];
        document.cookie = 'types='+types;
        ajax_search();
      }

      $('.job-openings-list').isotope({ filter: '*' });
      // reset buttons
      // $buttons.removeClass('is-checked');
      // $anyButtons.addClass('is-checked');
      $(this).css('display', 'none');
      $(this).prev().find('.filter-label').css('display','inline');
      $(this).prev().find('.filter-count').css('display','none');
    });


  $('#search-icon').click(function(){
    industries = [];
    use_cases = [];
    types = [];
    document.cookie = 'search_string_resources=';
    document.cookie = 'industries='+industries;
    document.cookie = 'use_cases='+use_cases;
    document.cookie = 'types='+types;
    $( "input[name='industry']" ).prop( "checked", false );
    $( "input[name='use-cases']" ).prop( "checked", false );
    $( "input[name='content-types']" ).prop( "checked", false );
    text = '';
    

    $('.filter-count').text('Selected');
    $('.filter-count').css('display','none');
    $('.filter-label').css('display','inline');
    $('.filter-clear').css('display','none');
  })

  $("#search").keyup(function(event){
      if(event.keyCode == 13){
        text = $("#search").val()
        document.cookie = 'search_string_resources='+text;
        ajax_search();
      }
  });
}

//Open second tab with resources
if($('#content2').length){
  var boxId = window.location.hash;
  if(boxId){
    $('a[href^="#content1"]').removeClass('active');
    $('a[href^="'+boxId+'"]').addClass('active');
    $('#content1').removeClass('active');
    $(boxId).addClass('active');
    const stateObj = { a: '1' };
    history.replaceState(stateObj, '', window.location.href.split('#')[0]);
    var $target = $(boxId).offset().top - 221;
    $('html, body').animate({
      'scrollTop': $target
    }, 1000, 'swing');
  }
}

});