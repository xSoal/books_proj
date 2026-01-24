$(document).ready(function () {
  popupAuth();
  select();
  rebuild_visual_editor();
  pickers();
  toggle();
  authBtnPopup();
  burgerMenu();
  showHidePassword();
  scrollTopButton();

  if($('#togglePassword').length !=0 ){
    passwordShowToggle();
  }

  $('.exitBtn').click(function(){
    $(this).closest('.headerSubmenu').find('form').submit();
  })


  const menuToggle = document.querySelector('.navToggleBtn');
    const mainNav = document.getElementById('mobileNavOverlay');
    const body = document.body;

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('headerMoblie');
            
            const isExpanded = mainNav.classList.contains('headerMoblie');
            menuToggle.setAttribute('aria-expanded', isExpanded);
            
            body.classList.toggle('bodyNoScroll');
        });
    }


    // popup contact
    var box = document.getElementById('slideInBox');

    function hideBox() {
        box.classList.add('hidden');
    }
    window.hideBox = hideBox;

    if(box){
      box.classList.add('visible');
    }

    /// popup contact





});


function passwordShowToggle() {
  if( window.location.href.indexOf('login') === -1 ){
    console.log('qwe');
  }
  var toggleButton = document.getElementById('togglePassword');
  var passwordInput = document.getElementById('passwordInput');
  var divEye = document.querySelector('.div-fa-eye');
  var divEyeSlashed = document.querySelector('.div-fa-eye-slash');

  toggleButton.addEventListener('click', function() {
      
      if (passwordInput.getAttribute('type') === 'password') {
        passwordInput.type = 'text';
        divEye.classList.add('hidden')
        divEyeSlashed.classList.remove('hidden')
        
      } else {
        passwordInput.type = 'password';
        divEyeSlashed.classList.add('hidden')
        divEye.classList.remove('hidden')
      }
  });
}


function scrollTopButton(){
  var scrollBtn = document.querySelector('.scrollToTopBtn');

  var scrollFunction = () => {
      if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
          scrollBtn.style.display = "block";
      } else {
          scrollBtn.style.display = "none";
      }
  };

  var topFunction = () => {
      window.scrollTo({
          top: 0,
          behavior: 'smooth' 
      });
  };

  window.onscroll = scrollFunction;
  scrollBtn.addEventListener('click', topFunction);
}

function showHidePassword(){
  let passBtn = $(".pwd_show");
  passBtn.each(function () {
    $(this).on("click", function () {
      let input = $(this).closest(".popup_field_item").find("input");
      $(this).toggleClass("active");
      input.attr("type") === "password" ?
        input.attr("type", "text") :
        input.attr("type", "password");
    });
  });
}

function popupAuth(){
 
  // Отримуємо елементи, які потрібно переключати
  const popupTitle = $('.popup_headline h4');
  const popupAuth = $('.popup_item');
  const popupFormSignIn = $('.popup_form.sign_in');
  const popupFormRegistr = $('.popup_form.registration');
  const popupForgot = $('.popup_form.forgot_password');
  const button = $('#toggleButton');
  const forgotBtn = $('#forgotButton');
  const socialItems = $('.popup_uath_icons');

  // При кліку на кнопку Реєстрація
  button.on('click', function () {
    popupForgot.removeClass('active');

      if ( ((popupAuth.hasClass('sign_in')) || (popupAuth.hasClass('forgot_password'))) &&  button.hasClass('registration')) {
          // Переключаємо на режим Реєстрація
            popupTitle.text('Реєстрація');
            popupAuth.removeClass('sign_in forgot_password').addClass('registration');
            popupFormSignIn.removeClass('active');
            popupFormRegistr.addClass('active');
            button.removeClass('registration').addClass('sign_in').text('Вхід');
            socialItems.removeClass('hide');
          
      } else {
          // Переключаємо на режим Вхід
            popupTitle.text('Авторизація');
            popupAuth.removeClass('registration forgot_password').addClass('sign_in');
            popupFormSignIn.addClass('active');
            popupFormRegistr.removeClass('active');
            button.removeClass('sign_in').addClass('registration').text('Реєстрація');
            socialItems.removeClass('hide');
      }
  });

  forgotBtn.on('click', function(){
    if ( (popupAuth.hasClass('sign_in')) || (popupAuth.hasClass('registration')) ) {
      popupTitle.text('Відновлення пароля');
      popupAuth.removeClass('sign_in registration').addClass('forgot_password')
      popupFormSignIn.removeClass('active');
      popupFormRegistr.removeClass('active');
      popupForgot.addClass('active');
      button.removeClass('sign_in').addClass('registration').text('Реєстрація');
      socialItems.addClass('hide');
    }
  })
}


function select() {
  //select
  $(".current_select").off();
  $(".current_select").each(function () {
    $(this).click(function () {
      if ($(this).hasClass("active")) {
        $(this).removeClass("active");
        $(this).closest(".select").find(".options").removeClass("active").slideUp();
      } else {
        $(this).addClass("active");
        $(this).closest(".select").find(".options").addClass("active").slideDown();
      }
    });
  });


  $(".option").off();
  $(".option").each(function () {
    $(this).on("click", function () {
      let optText = $(this).text();
      let optId = $(this).attr("data-id");
      $(this).closest(".select").find(".current_select span").text(optText);
      $(this)
        .closest(".select")
        .find(".current_select span")
        .css("color", "#64667D");
      $(this).closest(".fb_input_inside").find("input").val(optId);
      $(this).closest(".select").find(".current_select").removeClass("active");
      $(this).closest(".options").removeClass("active").slideUp();
    });
  });


  //-------------------------
  $('.postList .option').each(function(){
    $(this).click(function(){
      $(this).closest('.postList').nextAll('.postList').remove();

      var row = $(this);
      var id = row.attr('data-id');

      var fd = new FormData()
      fd.append('id', id )
      $.ajax({
        url: '/api/find-next-post',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (resp) {
          
          if( resp !='' ){
            var html = `<div class="fb_input_inside postList">
                          <input type="hidden" name="post_id" value="">
                          <div class="select">
                              <div class="current_select">
                                  <span>Оберіть дані</span>
                              </div>
                              <div class="options">
                                  <div>`;
                                      resp.forEach(function(item, index ) { 
                                        html += `<div class="option" data-id="`+item.id+`">`+item.name+`</div>`;
                                      });
                                       
                          html += `</div>
                              </div>
                          </div>
                      </div>`;

            row.closest('.input_select').append(html);
            select();
          
          }
          
        },
        error: function (jqXHR, textStatus, errorThrown) {

        },
      });

    });
  });
  //-------------------------

}


function rebuild_visual_editor() {
  /*-------- TinyMCE -----------*/
  tinyMCE.init({
    selector: '.textarea_item',

    codesample_languages: [{
        text: 'HTML/XML',
        value: 'markup'
      },
      {
        text: 'JavaScript',
        value: 'javascript'
      },
      {
        text: 'CSS',
        value: 'css'
      },
      {
        text: 'PHP',
        value: 'php'
      },
      {
        text: 'Ruby',
        value: 'ruby'
      },
      {
        text: 'Python',
        value: 'python'
      },
      {
        text: 'Java',
        value: 'java'
      },
      {
        text: 'C',
        value: 'c'
      },
      {
        text: 'C#',
        value: 'csharp'
      },
      {
        text: 'C++',
        value: 'cpp'
      },
    ],

    plugins: [
      'advlist autolink link image lists charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking',
      'table contextmenu directionality emoticons paste textcolor responsivefilemanager code',
      'codesample fullscreen',
    ],

    extended_valid_elements: 'script[src|type|language]',

    valid_children: '+body[style]',

    toolbar1: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect',
    toolbar2: '| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code | codesample | fullscreen ',
    image_advtab: true,
    fullscreen_native: false,

    convert_urls:0,



    relative_urls : false,
    remove_script_host : true,
    
    external_filemanager_path: '/filemanager/',
    filemanager_title: 'Responsive Filemanager',
    external_plugins: {
      filemanager: '/filemanager/plugin.min.js'
    },


    


  })
  /*-------- /TinyMCE -----------*/
}


function pickers() {

  $('.datepicker').removeClass('hasDatepicker').removeAttr('id');
  $('.time_picker').removeClass('hasDatepicker').removeAttr('id');
  $('.datepickersmall').removeClass('hasDatepicker').removeAttr('id');

  $('.datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    closeText: 'Вибрати',
    currentText: 'Сьогодні',
    firstDay: 1,
    monthNames: [
      'січня',
      'лютого',
      'березня',
      'квітня',
      'травеня',
      'червня',
      'липня',
      'серпня',
      'вересня',
      'жовтня',
      'листопада',
      'грудня',
    ],
    monthNamesShort: [
      'січ',
      'лют',
      'бер',
      'квіт',
      'трав',
      'черв',
      'лип',
      'серп',
      'верес',
      'жовт',
      'лист',
      'груд',
    ],
    dayNamesShort: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dateFormat: 'dd MM yy',
		yearRange: "-100:+50",
  });


  $('.datepickersmall').datepicker({
    changeMonth: true,
    changeYear: true,
    closeText: 'Вибрати',
    currentText: 'Сьогодні',
    firstDay: 1,
    monthNames: [
      'січня',
      'лютого',
      'березня',
      'квітня',
      'травеня',
      'червня',
      'липня',
      'серпня',
      'вересня',
      'жовтня',
      'листопада',
      'грудня',
    ],
    monthNamesShort: [
      'січ',
      'лют',
      'бер',
      'квіт',
      'трав',
      'черв',
      'лип',
      'серп',
      'верес',
      'жовт',
      'лист',
      'груд',
    ],
    dayNamesShort: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dateFormat: 'yy-mm-dd',
  });

  $('.time_picker').datetimepicker({
    changeMonth: true,
    changeYear: true,
    closeText: 'Вибрати',
    currentText: 'Сьогодні',
    firstDay: 1,
    monthNames: [
      'січня',
      'лютого',
      'березня',
      'квітня',
      'травеня',
      'червня',
      'липня',
      'серпня',
      'вересня',
      'жовтня',
      'листопада',
      'грудня',
    ],
    monthNamesShort: [
      'січ',
      'лют',
      'бер',
      'квіт',
      'трав',
      'черв',
      'лип',
      'серп',
      'верес',
      'жовт',
      'лист',
      'груд',
    ],
    dayNamesShort: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dateFormat: 'yy-mm-dd',
    yearRange: '-1:+5',
  });
}


function toggle() {
  //toggle
  $('.toggle').off();
  $('.toggle').each(function () {
    $(this).on('click', function () {
      $(this).toggleClass('active')
      if ($(this).hasClass('active')) {
        $(this).closest('.fb_input_inside').find('input').val('1')
      } else {
        $(this).closest('.fb_input_inside').find('input').val('0')
      }

      if( $(this).closest('.form_block').hasClass('requests')){
        $(this).closest('.form_block').find('.row.hide').toggleClass('active')
      }

    });
  });
}



function authBtnPopup(){
  $('.auth_link, .need_auth').on('click', function(){
    $('.popup_item').addClass('active');
    $('.popup_bg').addClass('active');
    $('body').addClass('fixed');
    $('.mobile_nav, .header_burger').removeClass('active');
  })

  $('.popup_item .popup_item_close_btn, .popup_bg').on('click', function(){
    $('.popup_item').removeClass('active');
    $('.popup_bg').removeClass('active');
    $('body').removeClass('fixed');
  })
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