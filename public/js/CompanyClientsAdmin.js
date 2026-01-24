$(document).ready(function () {
  $('.addRequest').click(function(){
    $('.addRequestForm').addClass('visible');
    $('.editRequestForm').removeClass('visible');

    $([document.documentElement, document.body]).animate({
      scrollTop: $(".addRequestForm").offset().top
  }, 400);
  })

  $('.editButton').click(function(){
    var currentId = $(this).attr('data-id');

    $('.editRequestForm').find('.editRequestId').val(currentId);
    var code = $(this).closest('tr').find('.code').html().trim();
    var name = $(this).closest('tr').find('.name').html().trim();
    var model = $(this).closest('tr').find('.model').html().trim();
    var manufacturer = $(this).closest('tr').find('.manufacturer').html().trim();
    var country = $(this).closest('tr').find('.country').html().trim();
    var quantity = $(this).closest('tr').find('.quantity').html().trim();
    var activeInput =  $(this).closest('tr').find('.status input[type="checkbox"]')[0]
    
    $('.editRequestForm').find('input[name="code"]').val(code)
    $('.editRequestForm').find('input[name="name"]').val(name)
    $('.editRequestForm').find('input[name="model"]').val(model)
    $('.editRequestForm').find('input[name="manufacturer"]').val(manufacturer)
    $('.editRequestForm').find('input[name="quantity"]').val(quantity)

    // activeInput.checked ? 
    //   $('.editRequestForm').find('input[name="active"]')[0].checked = true : 
    //   $('.editRequestForm').find('input[name="active"]')[0].checked = false;
      
    $('.editRequestForm').find('.country option').toArray().forEach(o => {
      if(o.value === country){
        o.selected = true;
      }
      
    });

    $('.editRequestForm').addClass('visible');
    $('.addRequestForm').removeClass('visible');

    $([document.documentElement, document.body]).animate({
        scrollTop: $(".editRequestForm").offset().top
    }, 400);
  });

  var dialogClass = $('#my-modal').hasClass('open');
  var dialog_settings = $( "#my-modal" ).dialog({
    modal: true,         // Делает окно модальным
    autoOpen: false,      // Открывать сразу при загрузке
    classes: {
      "ui-dialog": dialogClass == true ? "my-modal open" : "my-modal"
    },
    buttons: {
      // "Ок": function() {
      //   $( this ).dialog( "close" );
      // }
    },
    open: function() {
      $('.ui-widget-overlay').bind('click', function() {
        $( "#my-modal" ).dialog('close');
      });
    }
  });

  $('.settings-button').click(function(){
    dialog_settings.dialog('open')
  })

  if(is_need_to_modal_settings_open){
    dialog_settings.dialog('open');
  }

  



  // form save user settings
  $('.save_form_settings').on('click', function(e) {
    e.preventDefault();

    var $form = $(this).closest('form');
    var url = $form.attr('action');
    var formData = new FormData($form[0]);
    var $scrollContainer = $form.closest('div[style*="overflow-y: scroll"], .modal-body, .modal-content').first();
    $form.find('.errors').html('').hide();
    $form.find('input').removeClass('is-invalid'); 
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        processData: false, 
        contentType: false, 
        success: function(response) {
          $(".header_modal_message").addClass('show')
          if ($scrollContainer.length) {
            $scrollContainer.animate({ scrollTop: 0 }, 450);
          }
          $(`div[role="dialog"]`).scrollTop(0);
          $form.find('input[name*="password"]').val('');
          if(response.photo_url) {
            $('#current_photo_preview').attr('src', response.photo_url);
          }

          $('#logo_input').val('')
          var $input = $form.find('input');
          var $textarea = $form.find('textarea');

          $input.removeClass('is-invalid');
          $textarea.removeClass('is-invalid');
          $('#my-modal').removeClass('isNeedPostProfile')
        },
        error: function(xhr) {
          $(".header_modal_message").removeClass('show')
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;

                $.each(errors, function(key, messages) {
                    var $input = $form.find('input[name="' + key + '"]');
                    var $textarea = $form.find('textarea[name="' + key + '"]');

                    $input.addClass('is-invalid');
                    $textarea.addClass('is-invalid');
                    
                    var errorBlock = $form.find('.form_field.' + key + ' .errors');
                    
                    if (errorBlock.length) {
                        var errorHtml = '<ul style="list-style: none; padding: 0; margin: 5px 0;">';
                        $.each(messages, function(index, message) {
                            errorHtml += '<li style="color: #e3342f; font-size: 13px; font-weight: bold;"><i class="fa-solid fa-circle-exclamation"></i>' + message + '</li>';
                        });
                        errorHtml += '</ul>';
                        
                        errorBlock.html(errorHtml).show();
                    }
                });

            } else {
                alert('Error: ' + xhr.status);
            }
        }
    });
});
  // form save user settings


  changeRequestEquipmentStatus();

  companyFilterSearch();
});


function changeRequestEquipmentStatus(){

  var updateStatusUrl = '/clientAdmin/updateRequestStatus'; 

  $('.status-toggle').on('change', function() {
      var $toggleElement = $(this);
      var itemId = $toggleElement.data('id');
      var isChecked = $toggleElement.is(':checked');
      var newStatus = isChecked ? 'active' : 'disabled';
      
      var $slider = $toggleElement.next('.slider');
      var oldStatusWasChecked = !isChecked;
      

      var token = $('input[name="_token"]').val()

      $slider.addClass('disabled-temp');
      
      $.ajax({
          url: updateStatusUrl + '/' + itemId,
          type: 'POST',
          data: {
              _token: token,
              status: newStatus
          },
          success: function(response) {
              $('#status-label-' + itemId).text(
                  newStatus === 'active' ? 'Активний' : 'Неактивний'
              ).css('color', newStatus === 'active' ? '#1e90ff' : '#ff6347');
              
              console.log('Статус оновлено:', response.message);
          },
          error: function(xhr, status, error) {
              console.error('Помилка оновлення статусу:', error);
              
              $toggleElement.prop('checked', oldStatusWasChecked);
              
              alert('Failed to update status. Try refreshing the page and trying again.');
          },
          complete: function() {
              $slider.removeClass('disabled-temp');
          }
      });
  });
}


function companyFilterSearch(){
  var inputs = $('.equipmentFilter__contryList input[type="checkbox"]').toArray()
  var companyId = $('#companyId').val();

  inputs.forEach(e => {
    e.oninput = () => {
      var inputs = $('.equipmentFilter__contryList input[type="checkbox"]').toArray()
      var filter = '';
      inputs.forEach(i => {
        if(i.checked){
          var separator = filter ? '|' : ''
          filter += separator + i.value;
        }
      })

      if(window.location.href.indexOf('superAdmin') === -1){
        window.location.href = filter ? 
          `/companyAdmin/equipment/${filter}/${companyId}`
          : `/companyAdmin/equipment/${companyId}`
      } else {
        window.location.href = filter ? 
          `/superAdmin/equipment/${filter}`
          : `/superAdmin/equipment`
      }

      
    }
  })

  $('.equipmentFilter__reset button')[0].onclick = () => {
    if(window.location.href.indexOf('superAdmin') === -1){
      window.location.href = `/companyAdmin/equipment/${companyId}`
    } else {
      window.location.href = `/superAdmin/equipment/`
    }
  }
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