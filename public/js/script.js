$(document).ready(function () {

  initFilter();
  initSorting();
  initInputRange();

});

function initInputRange(){
  var inputsRange = $('.input__numericRange').toArray();
  if(!inputsRange.length) return;

  inputsRange.forEach(inputRange => {
    
    var sliderOne = $(inputRange).find(".slider_range_1")[0];
    var sliderTwo = $(inputRange).find(".slider_range_2")[0];
    var displayValOne = $(inputRange).find(".range_1");
    var displayValTwo = $(inputRange).find(".range_2");
    var minGap = 1; // Минимальный разрыв между годами



    $(inputRange).find('.slider_range_1')[0].oninput = slideOne;
    $(inputRange).find('.slider_range_2')[0].oninput = slideTwo;

    // var numeric_slugs = $('.input__numericRange').toArray().map(el => $(this).attr('data-slug'));

    $(inputRange).find('.input__numericRangeButton').click(function(){

      console.log(sliderOne, sliderOne.value, sliderTwo.value);


      var slug = $(this).closest('.input__numericRange').attr('data-slug');
      var url = updateRangeInUrl(window.location.href, slug, sliderOne.value, sliderTwo.value);
      window.location.href = url;
    });

    function slideOne() {
        if (parseInt(sliderTwo.value) - parseInt(sliderOne.value) <= minGap) {
            sliderOne.value = parseInt(sliderTwo.value) - minGap;
        }
        displayValOne.textContent = sliderOne.value;
    }

    function slideTwo() {
        if (parseInt(sliderTwo.value) - parseInt(sliderOne.value) <= minGap) {
            sliderTwo.value = parseInt(sliderOne.value) + minGap;
        }
        displayValTwo.textContent = sliderTwo.value;
    }
  });


}


function initSorting(){
  if(!$('.filter').length) return;
  
  $('#sort')[0].onchange = function(){
    var url = window.location.href.split('?')[0];
    var filter = this.value ? `?order=${this.value}` : '';
    window.location.href = `${url}${filter}`;
  }
}


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
  // console.log(action + url);
  
  // window.location.href = action + url;

}



function updateRangeInUrl(currentUrl, slug, newMin, newMax) {
  var url = new URL(currentUrl);
  // Разбиваем путь на части и убираем пустые элементы от слешей
  var pathParts = url.pathname.split('/').filter(p => p !== "");
  
  // Регулярное выражение для поиска: "slug-число-число"
  var rangeRegex = new RegExp(`^${slug}-\\d+-\\d+$`);
  var newSegment = `${slug}-${newMin}-${newMax}`;
  
  // Ищем индекс сегмента, который совпадает с регуляркой
  var index = pathParts.findIndex(part => rangeRegex.test(part));

  if (index !== -1) {
      // Если нашли — заменяем строго по этому индексу (сохраняем позицию)
      pathParts[index] = newSegment;
  } else {
      // Если не нашли (новый фильтр) — добавляем в конец списка фильтров
      pathParts.push(newSegment);
  }

  // Собираем путь обратно, добавляя ведущий слеш
  url.pathname = '/' + pathParts.join('/');
  
  // Возвращаем полный путь с параметрами (search)
  return url.pathname + url.search;
}

window.updateRangeInUrl = updateRangeInUrl;
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