$(document).ready(function () {

  initFilter();


});


function initFilter(){
  if(!$('.filter').length) return;

  $('.filter input[type="checkbox"]').toArray().forEach(i => {
    i.oninput = function(){
      generateFilterUrl()
    }
  });

}


function generateFilterUrl(){
  var url = ''


  $('.filter input[type="checkbox"]').toArray().forEach(i => {
    if(i.checked){
      var parentCharSlug = $(i).closest('.char').attr('data-char-slug');
      var urlArr = url.split('/');
      var charValSlug = $(i).attr('data-slug');
      var isIssetParentInUrl = urlArr.find(urlPart => urlPart.indexOf(parentCharSlug) !== -1);
      
      if(!isIssetParentInUrl){
        url += `/${parentCharSlug}-${charValSlug}`
      } else {
        var parentIndex = urlArr.findIndex((urlPart) => {
          return urlPart.indexOf(parentCharSlug) !== -1;
        });
        urlArr[parentIndex] += `-${charValSlug}`;
        url = urlArr.join('/');
      }
    }
  });

  
  var action = $('.filter-form').attr('action');
  console.log(action + url);
  
  window.location.href = action + url;

}


function burgerMenu(){
  const burger = $('.header_burger');
  const nav = $('.mobile_nav');
  const body = $('body');


  burger.on('click', function(){
     if(burger.hasClass('active')){
      burger.removeClass('active');
      nav.removeClass('active');
      body.removeClass('fixed');
     }else{
      burger.addClass('active');
      nav.addClass('active');
      body.addClass('fixed');
     }
  });
}