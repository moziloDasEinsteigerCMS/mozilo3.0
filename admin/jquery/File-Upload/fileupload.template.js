
$(function () {
    'use strict';

$('.fileupload').fileupload({
            prev_img: false,
    uploadTemplate: function (o) {
        var rows = $();
        $.each(o.files, function (index, file) {

            var row = $('<div class="template-upload ui-widget-content ui-corner-all flex">'+
   //             '<div class="flex flex-100">'+
  //              '<tbody>'+
  //              '<tr><td colspan="6" class="error"><\/td><\/tr>'+
  //              '<tr>'+
                '<div class="preview"><\/div>'+
                '<div class="name"><\/div>'+                
   //             '<td class="mo-pading-l-r" width="1%">'+
                 '<div class="progress progress-success progress-striped active mr ml"><div class="bar" style="width:0%;"><\/div><\/div>'+
    //           '<\/td>'+
						'<div class="size mo-ma-l-auto mo-nowrap"><\/div>'+
                '<span class="start">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-save"><\/button>'+
                '<\/span>'+
                '<span class="cancel">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-stop"><\/button>'+
                '<\/span>'+
    //            '<\/tr><\/tbody><\/table>'+
    //            '<\/div>'+
                '<\/div>');
                if(!o.options.prev_img) {
                    row.find('.preview').append('<a class="fu-ext-imgs fu-ext-'+o.mimeType(file)+'"><\/a>');
                    row.find('a').prop('title', file.name);
                }
            row.find('.name').text(file.name);
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
            }
            rows = rows.add(row);
        });
        return rows;
    },
    downloadTemplate: function (o) {
        var rows = $();
        $.each(o.files, function (index, file) {

            var row = $('<div class="template-download ui-widget-content ui-corner-all flex">'+
  //              '<div class="flex flex-100">'+
  //              '<tbody>'+
                    '<div class="error red"><\/div>'+
  //                  '<tr>'+
                    '<div class="preview"><\/div>'+
                    '<div class="name"><span class="fu-rename-file"><\/span><\/div>'+
                    '<div class="size mo-ma-l-auto mr mo-nowrap"><\/div>'+
                '<div class="delete flex">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-delete"><\/button>'+
                '<input type="checkbox" name="delete" value="1" \/>'+
  //              '<\/tr><\/tbody>'+
  //              '<\/div>'+
                '<\/div>');
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
                row.find('.delete button').addClass('js-nodialog');
            } else {
                row.find('.name span').text(file.name);

                if(!o.options.prev_img) {
                    row.find('.preview').append('<a class="fu-ext-imgs fu-ext-'+o.mimeType(file)+'"><\/a>');
                    row.find('a').prop('title', file.name);
                }
                row.find('a').prop('href', file.url);
                row.find('.delete button')
                    .attr('data-type', file.delete_type)
                    .attr('data-url', file.delete_url);
            }
            rows = rows.add(row);
        });
        return rows;
    }
});


});
