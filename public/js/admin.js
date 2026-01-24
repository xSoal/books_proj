var currKey = null;
var bookChars, bookCharsVals, bookCharsValsFromDB, bookTags, currentBookTags;
var bookCharTemplate = `
<div class="book__char">
  <div class="book__charMain">
      <select class="book__charMain__select">
        !!!option_main!!!
      </select>
  </div>
  <div class="book__charVal">
      <select name="book_chars_vals[]" class="book__charVal__select">
        !!!option_char_val!!!
      </select>
  </div>
  <div class="bookCharDel">del</div>
</div>
`;

$(document).ready(function () {
  select();
  toggle();
  pickers();
  rebuild_visual_editor();
  searchValues();
  photoBTN();
  expeditionRows();
  expeditionRowsEvent();
  recountLimit();
  recountLimitTotal();
  makeLanguagesTabs();
  makePaGagesTabs();

  if($('.bookPage').length){
    bookInit();
    bookAddCreate();
    reinitBookSelects();
    tagsInit();
  }


  $('.log__header').click(function() {
    $(this).closest('td').find('.log__body').toggleClass('show')
  });
  


  var datepickersNodes = $('.datepicker_node');
  if(!datepickersNodes.val()){
    datepickersNodes.val(getCurrentDateFormatted())
  }

  datepickersNodes.datepicker({
      dateFormat: 'yy-mm-dd' 
  });

  $('form').on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13 && !$(e.target).is('textarea')) { 
      e.preventDefault();
      return false;
    }
  });


$('.menu_parent').each(function(){
  $(this).click(function () {
    $(this).toggleClass('active');
    $(this).toggleClass('more');
    $(this).closest('.admin_menu_section').find('.admin_menu_links').toggleClass('active');
  });
});

  $('.menu-btn').click(function () {
    $(this).toggleClass('open');
    $('.main_section').toggleClass('active');
    $('.container_full').toggleClass('active');
    $('.admin_menu').toggleClass('active');
    $('.menu_parent').toggleClass('active');
    if ($('.admin_menu_links').hasClass('active')) {
      $('.admin_menu_links').removeClass('active')
    }
    $('.main_section').toggleClass('withOpenMenu')
  });


  $('[name="name"]').blur(function () {
    var fd = new FormData()
    fd.append('name', $(this).val())
    $.ajax({
      url: '/api/admin/genslug',
      data: fd,
      processData: false,
      contentType: false,
      type: 'POST',
      success: function (resp) {
        if ($('[name="slug"]').val() == '') {
          $('[name="slug"]').val(resp)
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {

      },
    });
  });


  $('[name="search"]').keyup(function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) { 
      $(this).closest('form').submit();
    }
  });

  $('.filter_categories .option').each(function(){ 
    $(this).click(function(){

      var params = [];
      
      if( $('.filter_categories [name="parrent_id"]').val() != 0 && $('.filter_categories [name="parrent_id"]').val() != undefined ){
        params.push('parrent_id=' + $('.filter_categories [name="parrent_id"]').val() );
      }

      if( $('.filter_categories [name="court_id"]').val() != 0 && $('.filter_categories [name="court_id"]').val() != undefined ){
        params.push('court_id=' + $('.filter_categories [name="court_id"]').val() );
      }

      if( $('.filter_categories [name="judge_id"]').val() != 0 && $('.filter_categories [name="judge_id"]').val() != undefined ){
        params.push('judge_id=' + $('.filter_categories [name="judge_id"]').val() );
      }
      
      
      if( $('[name="search"]').val() !='' ){
        params.push('search='+$('[name="search"]').val() );
      }
      params = params.join('&');
      
      location.href = (params !='' ? '?'+params : '?') ;

    });
  });


  
  

  //-----------------------------------
  $('.sortablePhoto').sortable({placeholder: "ui-state-highlight"});

  $('.addPhotoMultiBtn').each(function () {
    $(this).click(function () {
      $(this).closest('.fb_label_inside').find('.addPhotoMulti').click();
    });
  });

  $('.addPhotoMulti').each(function () {
    $(this).change(function () {
      var current = $(this);

      $.each($(this)[0].files, function (key, file) {

        var file_name = file.name.split('.').pop().toLowerCase();
        file_name = makeid(15) + '.' + file_name;
        var fd = new FormData;
        fd.append('file', file);
        fd.append('file_name', file_name);
        $.ajax({
          url: '/upload.php',
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function (resp) {

            var html = '';
            
            if( 
                resp.indexOf('.png') != -1 ||
                resp.indexOf('.jpg') != -1 ||
                resp.indexOf('.jpeg') != -1 ||
                resp.indexOf('.webp') != -1
              ){
                html = `<li class="preview">
                          <img src="` + resp + `">
                          <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                          <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                        </li>`;
            }else if( 
                    resp.indexOf('.mp4') != -1 ||
                    resp.indexOf('.webm') != -1 
                  ){
                    html = `<li class="preview">
                              <video src="` + resp + `" controls></video>
                              <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                              <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                            </li>`;
                  }else if( 
                          resp.indexOf('.mp3') != -1 ||
                          resp.indexOf('.ogg') != -1 
                        ){
                          html = `<li class="preview">
                                    <audio src="` + resp + `" controls></audio>
                                    <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                    <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                                  </li>`;
                        }else if( 
                                  resp.indexOf('.mp3') != -1 ||
                                  resp.indexOf('.ogg') != -1 
                                ){
                                  html = `<li class="preview">
                                            <audio src="` + resp + `" controls></audio>
                                            <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                            <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                                          </li>`;
                                }else{
                                  html = `<li class="preview">
                                            <iframe src="` + resp + `" controls></iframe>
                                            <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                            <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                                          </li>`;
                                }

            current.closest('.form_block').find('.sortablePhoto').append(html);
            current.val('');
          },
          error: function (jqXHR, textStatus, errorThrown) {

          }
        });

      });

    });
  });
  //-----------------------------------

  //-----------------------------------
  $('[name="count_montaj"],[name="count_programs"]').keyup(function(){
    if( $('[name="count_montaj"]').val() != '' && $('[name="count_programs"]').val() !='' ){
      $('.total_count_changes').val( parseFloat($('[name="count_montaj"]').val()) * parseFloat($('[name="count_programs"]').val()) );
    }
  })
  //-----------------------------------

  //-----------------------------------
  if( $('.addProjectCalendar').length !=0 ){
    var calendarTPL = $('.addProjectCalendar').closest('.table_items').find('.hidden').html();
    $('.addProjectCalendar').closest('.table_items').find('.hidden').html('');

    $('.addProjectCalendar').click(function(){
      $(this).closest('.table_items').find('.tbody').append(calendarTPL);
      pickers();
      recountRows();
    })
  }
  //-----------------------------------


  //-----------------------------------
  if( $('.addProjectExpedition').length !=0 ){
    var expeditionTPL = $('.addProjectExpedition').closest('.table_items').find('.hidden').html();
    $('.addProjectExpedition').closest('.table_items').find('.hidden').html('');

    $('.addProjectExpedition').click(function(){
      $(this).closest('.table_items').find('.tbody').append(expeditionTPL);
      recountRows();
      expeditionRows();
    })
  }
  //-----------------------------------

  //-----------------------------------
  if( $('.addLimitRowBlock').length !=0 ){
    
    var limitRowTPL = $('.addLimitRowBlock').closest('.table_items').find('.limitRowTPL').html();
    $('.addLimitRowBlock').closest('.table_items').find('.limitRowTPL').html('');

    var addLimitRowBlockTPL = $('.addLimitRowBlock').closest('.table_items').find('.addLimitRowBlockTPL').html();
    $('.addLimitRowBlock').closest('.table_items').find('.addLimitRowBlockTPL').html('');
    
    limitRowBlockEvent(limitRowTPL);
    limitBlockRowAutocomplate();
    $('.addLimitRowBlock').click(function(){
      $(this).closest('.table_items').find('.tbody.blocks').append(addLimitRowBlockTPL);
      recountBlock();
      limitRowBlockEvent(limitRowTPL);
    })
  }
  //-----------------------------------

  //-----------------------------------
  rebuilProjectPKKEvent();
  if( $('.addKPPRow').length !=0 ){
    var rowTPL = $('.rowTPL').html();
    $('.rowTPL').html('');

    $('.addKPPRow').each(function(){
      $(this).click(function(){
        var count = $(this).closest('.row').attr('data-row');
        var limit =  parseInt( $(this).closest('.row').attr('data-limit') );
        limit++;
        $(this).closest('.row').attr('data-limit',limit)
        var label = 'data-row-'+count+'="'+limit+'"';
        $(this).closest('.row').after(`<div class="row" `+label+` data-row-id="`+count+`" data-count-id="`+limit+`">
                                              <textarea class="block_name"></textarea>
                                              <div class="edit_link removeBlock" onclick="removeKPPRow(`+count+`,`+limit+`)">
                                                <object type="image/svg+xml" data="/images/admin/icons/delete.svg"></object>
                                            </div>
                                        </div>`);

        $('.dayList .row[data-row="'+count+'"]').after( rowTPL.replace('rep', label).replace(/row_id/g, count).replace(/count_id/g, limit) );

        var fd = new FormData()
        fd.append('project_id', $('[name="id"]').val())
        fd.append('row_id', count)
        fd.append('count_id', limit)
        $.ajax({
          url: '/api/admin/create-row',
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function (resp) {
            
          },
          error: function (jqXHR, textStatus, errorThrown) {
    
          },
        });

        rebuilProjectPKKEvent();

      })
    })
  }
  //-----------------------------------

  //-----------------------------------
  $(window).keydown(function (event) {
    currKey = event.which;
  });
  $(window).keyup(function (event) {
      currKey = null;
  });
  //-----------------------------------

});


function rebuilProjectPKKEvent(){

  //-----------------------------------
  $('.block_name').off();
  $('.block_name').each(function(){
    $(this).blur(function(){
        var fd = new FormData()
        fd.append('project_id', $('[name="id"]').val())
        fd.append('row_id', $(this).closest('.row').attr('data-row-id'))
        fd.append('count_id', $(this).closest('.row').attr('data-count-id'))
        fd.append('title', $(this).val() )
        $.ajax({
          url: '/api/admin/update-row-name',
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function (resp) {
            
          },
          error: function (jqXHR, textStatus, errorThrown) {
    
          },
        });
    });
  })
  //-----------------------------------

  //-----------------------------------



  $('.day').off();
  $('.day').each(function(){
    $(this).click(function(event){
      if (currKey != null) {
        console.log( currKey )
        switch(currKey){
          case 48 : $(this).attr('id','color_48'); break; // 0
          case 49 : $(this).attr('id','color_49'); break; // 1
          case 50 : $(this).attr('id','color_50'); break; // 2
          case 51 : $(this).attr('id','color_51'); break; // 3
          case 52 : $(this).attr('id','color_52'); break; // 4
          case 53 : $(this).attr('id','color_53'); break; // 5
          case 54 : $(this).attr('id','color_54'); break; // 6
          case 55 : $(this).attr('id','color_55'); break; // 7
          case 56 : $(this).attr('id','color_56'); break; // 8
          case 57 : $(this).attr('id','color_57'); break; // 9
          case 27 : $(this).removeAttr('id'); break; // esc
        }

        if( (currKey >= 48 && currKey <= 57) || currKey == 27 ){
          var fd = new FormData()
          fd.append('project_id', $('[name="id"]').val())
          fd.append('row_id', $(this).closest('.row').attr('data-row-id'))
          fd.append('count_id', $(this).closest('.row').attr('data-count-id'))
          fd.append('day', $(this).attr('data-day') )
          fd.append('color', currKey )
          $.ajax({
            url: '/api/admin/update-row-color',
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            async : false,
            success: function (resp) {
              
            },
            error: function (jqXHR, textStatus, errorThrown) {
      
            },
          });
        }

      }
    });
  });
  //-----------------------------------

}

function removeKPPRow(count,limit){
  $('.row[data-row-'+count+'="'+limit+'"]').remove();
  var fd = new FormData()
  fd.append('project_id', $('[name="id"]').val())
  fd.append('row_id', count)
  fd.append('count_id', limit)
  $.ajax({
    url: '/api/admin/remove-row',
    data: fd,
    processData: false,
    contentType: false,
    type: 'POST',
    success: function (resp) {
      
    },
    error: function (jqXHR, textStatus, errorThrown) {

    },
  });
}

function recountLimit(){
  
  $('.limitRowBlock .block_v2, .limitRowBlock .block_v4, .limitRowBlock .block_v6, .limitRowBlock .block_v14').off();
  $('.limitRowBlock .block_v2, .limitRowBlock .block_v4, .limitRowBlock .block_v6, .limitRowBlock .block_v14').each(function(){
      $(this).change(function(){
        var count = $(this).closest('.tr_block').find('.block_v2').val();
        if( count !='' && count != undefined ){
          count = count.replace(',','.');
          count = parseFloat(count);
        }else{
          count = 0;
        }
        var count2 = $(this).closest('.tr_block').find('.block_v4').val();
        if( count2 !='' && count2 != undefined ){
          count2 = count2.replace(',','.');
          count2 = parseFloat(count2);
        }else{
          count2 = 0;
        }
        var count3 = $(this).closest('.tr_block').find('.block_v6').val();
        if( count3 !='' && count3 != undefined ){
          count3 = count3.replace(',','.');
          count3 = parseFloat(count3);
        }else{
          count3 = 0;
        }

        var total_sum = count * count2 * count3
        $(this).closest('.tr_block').find('.block_v7').val( total_sum );

        var tax = $(this).closest('.tr_block').find('.block_v14').val();
        
        if( tax !='' && tax != undefined ){
          tax = tax.replace(',','.');
          tax = parseFloat(tax);
        }else{
          tax = 0;
        }
        
        var total_tax = (total_sum * tax / 100 )

        $(this).closest('.tr_block').find('.block_v8').val( total_tax );

        $(this).closest('.tr_block').find('.block_v9').val( total_sum + total_tax );
        recountLimitTotal()
      });
  });

  $('.block_v10').change(function(){
    recountLimitTotal()
  })
}

function recountLimitTotal(){
  var total_sum_not_tax = 0;
  var total_tax_fop = 0;
  var total_tax_shtat = 0;
  var total_tax_pdv = 0;
  
  $('.limitRowBlock').each(function(){
    var total_sum = 0;
    $(this).find('.tr_block .block_v7').each(function(){
      var sum = $(this).val();
      if( sum !='' && sum != undefined ){
        sum = sum.replace(',','.');
        total_sum += parseFloat(sum);
      }
    });
    total_sum_not_tax += total_sum;
    total_sum = total_sum.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
    $(this).find('.tr_foot .block_v7').html(total_sum);
    
    var total_tax = 0;
    $(this).find('.tr_block .block_v8').each(function(){
      var sum = $(this).val();
      if( sum !='' && sum != undefined ){
        sum = sum.replace(',','.');
        total_tax += parseFloat(sum);
      }
    });
    total_tax = total_tax.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
    $(this).find('.tr_foot .block_v8').html(total_tax);

    var total_fsum = 0;
    $(this).find('.tr_block .block_v9').each(function(){
      var sum = $(this).val();
      if( sum !='' && sum != undefined ){
        sum = sum.replace(',','.');
        total_fsum += parseFloat(sum);
      }
    });
    total_fsum = total_fsum.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
    $(this).find('.tr_foot .block_v9').html(total_fsum);


    $(this).find('.tr_block .block_v10').each(function(){
      if( $(this).val().indexOf('ФОП') != -1 ){
        var sum = $(this).closest('.tr_values').find('.block_v8').val();
        if( sum !='' && sum != undefined ){
          sum = sum.replace(',','.');
          total_tax_fop += parseFloat(sum);
        }
      }
      if( $(this).val().indexOf('Штат') != -1 ){
        var sum = $(this).closest('.tr_values').find('.block_v8').val();
        if( sum !='' && sum != undefined ){
          sum = sum.replace(',','.');
          total_tax_shtat += parseFloat(sum);
        }
      }
      if( $(this).val().indexOf('НДС') != -1 ){
        var sum = $(this).closest('.tr_values').find('.block_v8').val();
        if( sum !='' && sum != undefined ){
          sum = sum.replace(',','.');
          total_tax_pdv += parseFloat(sum);
        }
      }
      
    });

    

  });


  total = total_sum_not_tax + total_tax_shtat + total_tax_fop + total_tax_pdv;

  total_sum_not_tax = total_sum_not_tax.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
  $('.row_1 .block_v7').html(total_sum_not_tax);

  total_tax_shtat = total_tax_shtat.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
  $('.row_2 .block_v8').html(total_tax_shtat);

  total_tax_fop = total_tax_fop.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
  $('.row_3 .block_v8').html(total_tax_fop);

  total_tax_pdv = total_tax_pdv.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
  $('.row_4 .block_v8').html(total_tax_pdv);

  total_season = total / parseInt( $('.row_6').attr('data-count')  );
  total_season = total_season.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
  $('.row_6 .block_v9').html(total_season);

  $('.row_5 [name="total_amount"]').val(total);
  total = total.toLocaleString("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0, });
  $('.row_5 .block_v9').html(total);

}

function limitRowBlockEvent(limitRowTPL){
  $('.limit .addLimitRow').off();
  $('.limit .addLimitRow').each(function(){
    $(this).click(function(){
      $(this).closest('.tr_values').find('.tbody .tr_foot').before(limitRowTPL);
      recountBlockRows();
      recountLimit();
      limitBlockRowAutocomplate();
    })
  });
}

function limitBlockRowAutocomplate(){
  $('.block_v11').off();
  $('.block_v11').each(function(){
    $(this).keyup(function(){
      if( $(this).val().length > 3 ){
        var current = $(this);
        var fd = new FormData()
        fd.append('name', current.val())
        $.ajax({
          url: '/api/admin/getUserInfo',
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function (resp) {
            $('.modalSearch').remove();
            var html = `<div class="modalSearch">`;
            resp.forEach(function(item, index ) {
              html += `<div class="modalSearch_row" data-percent="`+item.percent+`" data-employment="`+item.employment_text+`" data-name="`+item.name+`">`+item.name+`</div>`;
            });
            html += `</div>`;
            
            if( resp.length !=0 ){
              current.closest('.td').append(html);
            }

            $('.modalSearch_row').each(function(){
              $(this).click(function(){
                $(this).closest('.td').find('.block_v11').val( $(this).attr('data-name') );
                $(this).closest('.tr_values').find('.block_v14').val( $(this).attr('data-percent') + '%' );
                $(this).closest('.tr_values').find('.block_v10').val( $(this).attr('data-employment') );
                $('.modalSearch').remove();
              });
            })
          },
          error: function (jqXHR, textStatus, errorThrown) {

          },
        });
      }
    })
  })
}

function recountBlockRows(){
  $('.tbody.rows .tr_block').each(function(){
    var count = $(this).closest('.limitRowBlock').find('.addLimitRow').attr('data-count');
    $(this).find('input').each(function(){
      $(this).attr('name', $(this).attr('data-name').replace(/REP/g , count) )
    })   
  });
}

function recountBlock(){
  var count = 1;
  $('.table .limitRowBlock').each(function(){
      $(this).find('.addLimitRow').attr('data-count',count);
      $(this).find('.blockName').attr('name', $(this).find('.blockName').attr('data-name').replace(/REP/g , count)  );
      count++;
  });
}

function recountRows(){
  var count = 1;
  $('.table .tbody .tr_block').each(function(){
      $(this).find('.number').html(count);
      count++;
  });
}

function expeditionRowsEvent(){
  $('.expedition .tbody input').each(function(){
    $(this).blur(function(){
      expeditionRows();
    });
  });
}

function expeditionRows(){
  var distance = 0;
  var count_work_days = 0;
  var count_days = 0;
  var count_nights = 0;

  $('.expedition .tbody input').each(function(){
    if( $(this).hasClass('distance') && $(this).val() !='' ){
      distance += parseFloat( $(this).val() )
    }
    if( $(this).hasClass('count_work_days') && $(this).val() !='' ){
      count_work_days += parseFloat( $(this).val() )
    }
    if( $(this).hasClass('count_days') && $(this).val() !='' ){
      count_days += parseFloat( $(this).val() )
    }
    if( $(this).hasClass('count_nights') && $(this).val() !='' ){
      count_nights += parseFloat( $(this).val() )
    }
  });
  $('.expedition .tfood .distance').html(distance)
  $('.expedition .tfood .count_work_days').html(count_work_days)
  $('.expedition .tfood .count_days').html(count_days)
  $('.expedition .tfood .count_nights').html(count_nights)

}


function makeid(length) {
  var result = '';
  var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  var charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
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
      filemanager: '/js/tinymce/js/tinymce/plugins/responsivefilemanager/plugin.min.js'
    },



  })
  /*-------- /TinyMCE -----------*/
}

function searchValues(){
  let inputSearchVal = '<input type="text" placeholder="Пошук" class="search_val">';

  $('.options .search_char').remove();

  $('.options').prepend(inputSearchVal);
  

    $('.options .search_val').each(function (){
      $(this).keyup(function () {
        let search = $(this).val().toLowerCase()
        $(this).closest('.options').find('.option').each(function () {
          if ($(this).text().toLowerCase().indexOf(search) != -1) {
            $(this).show()
          } else {
            $(this).hide()
          }
        })
      })
    })

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

  $('.start_production, .end_production').change(function(){
    var ds = $(this).closest('.tr_block').find('.start_production').val();
		var de = $(this).closest('.tr_block').find('.end_production').val();
    
    if( ds !='' && de !='' ){
      de = new Date(de);
      de = de.getTime();
      ds = new Date(ds);
      ds = ds.getTime();
      $(this).closest('.tr_block').find('.day_count').val( parseFloat((de-ds)/(24*3600*1000)) + 1 ) ;
    }
    
  })
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
      
      if( $(this).closest('.tr_block').length !=0){
        var fd = new FormData()
        fd.append('id', $(this).closest('.tr_block').attr('data-id') )
        fd.append('type', $(this).closest('.tr_block').attr('data-type') )
        fd.append('active', ( $(this).hasClass('active') ? 1 : 0 ) )
        $.ajax({
          url: '/api/admin/change-active',
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function (resp) {
            
          },
          error: function (jqXHR, textStatus, errorThrown) {

          },
        });
      }

    });
  });
}

function select() {
  //select
  $('.current_select').off();
  $('.current_select').each(function () {
    $(this).on('click', function () {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active')
        $(this).closest('.select').find('.options').removeClass('active')
        $('.select_bg').removeClass('active')
      } else {
        $(this).addClass('active')
        $(this).closest('.select').find('.options').addClass('active')
        $('.select_bg').addClass('active')
      }
    });
  });

  $('.select_bg').off();
  $('.select_bg').on('click', function () {
    $(this).removeClass('active')
    $('.options').removeClass('active')
    $('.current_select').removeClass('active')
  });

  $('.option').off();
  $('.option').each(function () {

    $(this).on('click', function () {

      let optText = $(this).text()
      let optId = $(this).attr('data-id')
      
      $(this).closest('.select').find('.current_select span').text(optText)
      $(this).closest('.fb_input_inside').find('input[type="hidden"]').val(optId)
      
      if( !$(this).closest('.input_select').hasClass('multi') ){
        $(this).closest('.select').find('.current_select').removeClass('active')
        $(this).closest('.options').removeClass('active')
        $('.select_bg').removeClass('active')
      }
    });
  });


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
 
}

function photoBTN(){
  $('.addPhotoBtn').off();
  $('.addPhotoBtn').each(function () {
    $(this).click(function () {
      $(this).closest('.fb_input').find('.addPhoto').click();
    });
  });

  $('.addPhoto').off();
  $('.addPhoto').each(function () {

    $(this).change(function () {
      var current = $(this);
      var file = $(this)[0].files[0];
      var file_name = file.name.split('.').pop().toLowerCase();
      file_name = makeid(15) + '.' + file_name;
      var fd = new FormData;
      fd.append('file', file);
      fd.append('file_name', file_name);
      var dataType = $(this).attr('data-type');
      fd.append('data-type', dataType);
      $.ajax({
        url: '/upload.php',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (resp) {
          var html = '';
            
            if( 
                resp.indexOf('.png') != -1 ||
                resp.indexOf('.jpg') != -1 ||
                resp.indexOf('.jpeg') != -1 ||
                resp.indexOf('.webp') != -1
              ){
                html = `<li class="preview">
                          <img src="` + resp + `">
                          <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                          <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                        </li>`;
            }else if( 
                    resp.indexOf('.mp4') != -1 ||
                    resp.indexOf('.webm') != -1 
                  ){
                    html = `<li class="preview">
                              <video src="` + resp + `" controls></video>
                              <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                              <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                            </li>`;
                  }else if( 
                          resp.indexOf('.mp3') != -1 ||
                          resp.indexOf('.ogg') != -1 
                        ){
                          html = `<li class="preview">
                                    <audio src="` + resp + `" controls></audio>
                                    <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                    <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                                  </li>`;
                        }else if( 
                                  resp.indexOf('.mp3') != -1 ||
                                  resp.indexOf('.ogg') != -1 
                                ){
                                  html = `<li class="preview">
                                            <audio src="` + resp + `" controls></audio>
                                            <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                            <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                                          </li>`;
                                }else{
                                  html = `<li class="preview">
                                            <iframe src="` + resp + `" controls></iframe>
                                            <div class="btn btn_del del_elem" onClick="this.parentNode.remove()"></div>
                                            <input type="hidden" name="` + current.attr('data-name') + `" value="` + resp + `">
                                          </li>`;
                                }

            current.closest('.form_block').find('.photoPreview').html(html);
            current.val('');
        },
        error: function (jqXHR, textStatus, errorThrown) {

        }
      });
    });
  });

}


  function makeLanguagesTabs(){

    var tabsContNodes = $('.langsForm').toArray();
    tabsContNodes.forEach(tabContNode => {
    var headers = $(tabContNode).find('.langsForm__lang');

    headers.each(function(i, el){
      el.onclick = function(){
        $(tabContNode).find('.langsForm__lang').removeClass('current');
        $(this).addClass('current');
        $(tabContNode).find('.langsForm__langFields').removeClass('show');
        $(tabContNode).find(`.langsForm__langFields:nth-child(${i+1})`).addClass('show');
      };
    });

  });



}

function makePaGagesTabs(){
  var tabPagesHeader = $('.tabPagesHeader');
  
  tabPagesHeader.each(function(i,el){
    el.onclick = function(){
      tabPagesHeader.removeClass('active');
      $(this).addClass('active');

      $('.tabBody__page').removeClass('show');
      $(`.tabBody__page[data-index="${i}"]`).addClass('show')
    }

  })
};


function bookInit(){
  if(!$('#characteristics')[0]) return;
  bookChars = JSON.parse($('#characteristics')[0].innerHTML);
  bookCharsVals = JSON.parse($('#chars_vals')[0].innerHTML);
  bookTags = JSON.parse($('#tags')[0].innerHTML);

  if($('#book_chars_vals')[0]){
    bookCharsValsFromDB = JSON.parse($('#book_chars_vals')[0].innerHTML);
  }


  reinitBookSelects();
  
}

function bookAddCreate(){
  if(!$('#characteristics')[0]) return;

  var charsOptionsHTML = ``;
  bookChars.forEach(c => {
    charsOptionsHTML += `<option value="${c.id}">${c.translates.ua.name}</option>`
  });

  
  var currentBookCharsVals = bookCharsVals.filter(char_val => {
    return +char_val.characteristic_id === +bookChars[0].id;
  });
  var charValsOpetionsHTML = ``;
  
  currentBookCharsVals.forEach(c => {
    charValsOpetionsHTML += `<option  value="${c.id}">${c.translates.ua.name}</option>`
  });

  $('.book__addCharCont').click(function(){
    $('.book__charsCont').append(bookCharTemplate);
    var e = $('.book__charsCont .book__char:last-child');
    e.find('.book__charMain__select').html(charsOptionsHTML);
    e.find('.book__charVal__select').html(charValsOpetionsHTML);
    reinitBookSelects();
  });

};


function reinitBookSelects(){
  if(!$('#characteristics')[0]) return;

  var chars_selects = $('.book__charMain__select').toArray();

  chars_selects.forEach(select => {
    select.onchange = () => {
      
      var currentBookCharsVals = bookCharsVals.filter(char_val => {
        return +char_val.characteristic_id === +select.value
      });

      var charValsOpetionsHTML = ``;
      
      currentBookCharsVals.forEach(c => {
        charValsOpetionsHTML += `<option  value="${c.id}">${c.translates.ua.name}</option>`
      });
      
      $(select).closest('.book__char').find('.book__charVal__select').html(charValsOpetionsHTML)
    }
  })

  $('.bookCharDel').toArray().forEach(d => {
    d.onclick = () => {
      $(d).closest('.book__char').remove();
    }
  });
}


function tagsInit(){
  var searchResultCont = $('.addTags__searchResultCont');
  var currentTags = $('.currentTags');

  $('#add_tag')[0].oninput = function(){
    searchResultCont.html('');
    
    // if(this.value.length < 3) return;
    if(this.value.trim() === ''){
      searchResultCont.removeClass('show');
      return;
    }

    var tagsFilter = bookTags.filter(t => {
      return t.translates.ua.name.indexOf(this.value.trim()) !== -1
    });

    var html = ``;
    tagsFilter.forEach(t => {
      html += `<div class="addTags__searchResult" data-id="${t.id}">${t.translates.ua.name}</div>`;
    });
    searchResultCont.append(html);
    searchResultCont.addClass('show');
  }

  searchResultCont.click(function(e){
    if(e.target.classList.contains('addTags__searchResult')){
      currentTags.append(`
        <div class="tag" data-id="${e.target.getAttribute('data-id')}">
            ${e.target.innerHTML}
            <input hidden name="tags[]" value="${e.target.getAttribute('data-id')}">
            <span class="tag-remove" data-id="${e.target.getAttribute('data-id')}">×</span>
        </div>
      `);
      searchResultCont.html('');
      $('#add_tag')[0].value = '';
      $('#add_tag')[0].focus();
    }

  });

  $('.currentTags').click(function(e){
    if(e.target.classList.contains('tag-remove')){
      $(`.tag[data-id="${e.target.getAttribute('data-id')}"]`).remove();
    }
  });
}

function getCurrentDateFormatted() {
  // 1. Создаем новый объект Date, содержащий текущую дату и время
  var today = new Date();

  // 2. Получаем части даты
  var year = today.getFullYear();
  // Месяцы нумеруются с 0 (Январь = 0, Ноябрь = 10), поэтому добавляем 1
  var month = today.getMonth() + 1; 
  var day = today.getDate();

  // 3. Форматируем месяц и день, добавляя ведущий ноль (например, 5 -> 05)
  // String(month).padStart(2, '0')
  var formattedMonth = String(month).padStart(2, '0');
  var formattedDay = String(day).padStart(2, '0');

  // 4. Объединяем части в требуемый формат ГГГГ-ММ-ДД
  return `${year}-${formattedMonth}-${formattedDay}`;
}


