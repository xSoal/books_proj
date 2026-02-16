$(document).ready(function () {

  initFilter();
  initSorting();
  initInputRange();
  initSwiper();
  initSearchButton();
  initPopup();
});

function initPopup(params) {
  var modal = document.querySelector('#feedbackModal');
  var openBtn = document.querySelector('.btn-feedback-trigger');
  var closeBtn = document.querySelector('.modal-close');

  function toggleFeedbackModal(show) {
      if (!modal) return;
      
          if (show) {
              modal.style.display = 'flex';
              document.body.style.overflow = 'hidden';
          } else {
              modal.style.display = 'none';
              document.body.style.overflow = '';
          }
      }

      if (openBtn) {
          openBtn.addEventListener('click', () => toggleFeedbackModal(true));
      }

      if (closeBtn) {
          closeBtn.addEventListener('click', () => toggleFeedbackModal(false));
      }

      if (modal) {
          modal.addEventListener('click', function(e) {
              if (e.target === modal) toggleFeedbackModal(false);
          });
      }

      document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') toggleFeedbackModal(false);
      });

    
}



function initSearchButton () {
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if(!form.classList.contains('searchForm')){
      return;
    }
    
    if (form.getAttribute('action') && form.getAttribute('action').includes('/search')) {
      e.preventDefault();

      const formData = new FormData(form);
      const params = new URLSearchParams();

      formData.forEach((value, key) => {
          if (value && value.trim() !== '') {
              params.append(key, value);
          }
      });

      const baseUrl = form.getAttribute('action');
      const queryString = params.toString();

      const finalUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;

      window.location.href = finalUrl;
    }
});
}

function initSwiper(){
  if(!$('.author-works-swiper').length) return;
  var swiperAuthor = new Swiper('.author-works-swiper', {
    direction: 'vertical',
    slidesPerView: 3,
    spaceBetween: 10,
    slidesPerGroup: 3,
    navigation: {
        nextEl: '.next-author',
        prevEl: '.prev-author',
    },
    pagination: {
        el: '.swiper-pagination-custom',
        type: 'fraction',
    },
    watchOverflow: true,
});
}

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


function initSorting() {
  const sortElement = $('#sort');
  if (!sortElement.length) return;

  sortElement[0].onchange = function() {
      const url = new URL(window.location.href);
      const oldParams = new URLSearchParams(url.search);
      
      // Создаем новый объект параметров для контроля порядка
      const newParams = new URLSearchParams();

      // 1. Сначала добавляем order (если выбран), чтобы он был первым
      if (this.value) {
          newParams.set('order', this.value);
      }

      // 2. Добавляем page вторым (если он был в URL)
      // При смене сортировки обычно сбрасывают на 1 страницу
      if (oldParams.has('page')) {
          newParams.set('page', '1'); 
      }

      // 3. Добавляем все остальные параметры, которые могли быть
      oldParams.forEach((val, key) => {
          if (key !== 'order' && key !== 'page') {
              newParams.append(key, val);
          }
      });

      const queryString = newParams.toString();
      window.location.href = url.pathname + (queryString ? '?' + queryString : '');
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