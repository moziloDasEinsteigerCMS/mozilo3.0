
var parentWidget = ($.blueimpIP || $.blueimp).fileupload;
$.widget('blueimpUI.fileupload', $.blueimpUI.fileupload, {

        options: {

//$(function () {
//    'use strict';

//$('.fileupload').fileupload({
            prev_img: true,
    uploadTemplate: function (o) {
//$("#out").html($("#out").html()+"<br>files=");
        var rows = $();
        $.each(o.files, function (index, file) {
//$("#out").html($("#out").html()+"<br>files=0");

            var row = $('<div class="template-upload fadeIn file-item ui-widget ui-widget-content ui-corner-all card flex">' +
                '<div class="flex flex-100" style="flex-direction:column; width:100%">' +
  //              '<tbody>' +
  //                  '<tr>' +
                        '<div class="error red"><\/div>' +
  //                  '<\/tr>' +
  //                  '<tr>' +
                        '<div class="preview flex-100"><span><\/span><\/div>' +
                        '<div class="upload-footer flex">'+
                        '<div class="flex" style="width:100%">'+
                        '<span class="name"><\/span>' +
                        '<span class="size mo-ma-l-auto mo-nowrap"><\/span>' +
								'</div>'+
  //                      '<td class="mo-pading-l-r" width="93%">'+
                            '<div class="progress progress-success progress-striped active mr ml"><div class="bar" style="width:0%;"><\/div><\/div>'+
  //                      '<\/td>' +
  //                      '<td rowspan="2" width="1%" class="mo-nowrap">' +
  									'<div class="flex">'+
                            '<span class="start">'+
                                '<button class="fu-img-button mo-icons-icon mo-icons-save"><\/button>'+
                            '<\/span>'+
                            '<span class="cancel">'+
                                '<button class="fu-img-button mo-icons-icon mo-icons-stop"><\/button>'+
                            '<\/span>'+
                            '</div>'+
  //                      '<\/td>' +
  //                  '<\/tr>' +
  //                  '<tr>' +
                        
                        '</div>'+
  //                  '<\/tr>' +
 //               '<\/tbody>' +
                '<\/div>' +
            '<\/div>');
//$("#out").html($("#out").html()+"<br>files=1");
            row.find('.name').text(file.name);
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
            }
//$("#out").html($("#out").html()+"<br>files=2");
            rows = rows.add(row);
        });
//$("#out").html($("#out").html()+"<br>files=3");
        return rows;
    },
    downloadTemplate: function (o) {
        var rows = $();
        var new_width = $(o.options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="new_width"]');
        var new_height = $(o.options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="new_height"]');

        $.each(o.files, function (index, file) {

            var row = $('<div class="template-download fade file-item ui-widget ui-widget-content ui-corner-all card flex">' +
                '<div class="flex-100 flex">' +
                '<div>' +
                        '<span class="error"><\/span>' +
                    '<\/div>' +
                    '<div class="mo-padding-bottom">' +
                        '<span class="preview"><\/span>' +
                        '</div>'+
                '</div>'+
                '<div style="width:100%">' +
                
                '<div class="flex">'+
                '<div class="delete flex flex-100">' +
                        '<span class="fu-img-button resize mo-icons-icon mo-icons-img-scale"></span>'+
                        '<button class="fu-img-button mo-icons-icon mo-icons-delete"><\/button>'+
                                        '<input type="checkbox" name="delete" value="1" \/><\/div>'+
                       
                        
                        '</div>'+
                        
                '<div class="c-content">'+
                        '<div class="size" ><\/div>' +
                        '<div class="pixelsize mo-ma-l-auto"><span><\/span> '+mozilo_lang["pixels"]+'<\/div>' +
   //                     '<div class="delete mo-ma-l-auto">' +
   //                     '<button class="fu-img-button mo-icons-icon mo-icons-delete"><\/button>'+
   //                    '<span class="fu-img-button resize mo-icons-icon mo-icons-img-scale"></span>'+
   //                     '<input type="checkbox" name="delete" value="1" \/><\/div>'+
                    '<\/div>' +
                    '<div class="c-content">' +
                        '<div class="subtitle-lang">'+mozilo_lang["gallery_text_subtitle"]+'<\/div>' +
                        '<div class="subtitle flex flex-100 ml"><span class="fu-subtitle"><\/span><\/div>' +// style="border:1px solid #ff0000;"
                    '<\/div>' +
                    '<div class="c-content">' +
                        '<span class="name"><span class="fu-rename-file"><\/span><\/span>' +
                    '<\/div>' +
                '</div>'+
            '<\/div>');


            if(file.pixel_w && file.pixel_h) {
                row.find('.pixelsize span').text(file.pixel_w+" x "+file.pixel_h);
                if($('input[name="new_global_width"]').val() == "auto" && new_width.val() < file.pixel_w)
                    new_width.val(file.pixel_w);
                if($('input[name="new_global_height"]').val() == "auto" && new_height.val() < file.pixel_h)
                    new_height.val(file.pixel_h);
           } else
                row.find('.pixelsize').html("");
            if(file.subtitle)
                row.find('.subtitle span').text(rawurldecode_js(file.subtitle));
            else  {
                row.find('.subtitle span').addClass('fu-empty');
                if(typeof file.subtitle == "undefined")
                    row.find('.subtitle-lang').text("");
            }

            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.find('.name').text(file.name);
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
                row.find('.delete button').addClass('js-nodialog');
            } else {
                row.find('.name span').text(file.name);
                if (file.thumbnail_url) {
                    row.find('.preview').append('<a><img alt=""><\/a>')
                        .find('img').prop('src', file.thumbnail_url+"?"+(new Date()).getTime());
                    row.find('a').prop('title', file.name);
                }
                row.find('a').prop('href', file.url);
                row.find('.delete button')
                    .attr('data-type', file.delete_type)
                    .attr('data-url', file.delete_url);
            }

/*
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
            } else {
                row.find('.name span').text(file.name);

                if(!o.options.prev_img) {
                    row.find('.preview').append('<a><img></a>')
                        .find('img').prop('src', o.mimeType(file));
                    row.find('a').prop('title', file.name);
                }
                row.find('a').prop('href', file.url);
                row.find('.delete button')
                    .attr('data-type', file.delete_type)
                    .attr('data-url', file.delete_url);
            }*/
            rows = rows.add(row);
        });
        return rows;
    }
}
});


//});
