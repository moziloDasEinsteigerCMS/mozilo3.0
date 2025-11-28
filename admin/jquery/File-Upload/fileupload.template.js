
$(function () {
    'use strict';

$('.fileupload').fileupload({
            prev_img: false,
    uploadTemplate: function (o) {
        var rows = $();
        $.each(o.files, function (index, file) {

            var row = $('<div class="template-upload fadeIn ui-widget-content ui-corner-all">'+
            	 '<div class="error fadeIn mt" style="text-align: center"><\/div>'+
            	 '<div class="flex">'+
                '<div class="preview"><\/div>'+
                '<div class="name"><\/div>'+                
                 '<div class="progress progress-success progress-striped active mr ml flex"><div class="bar" style="width:0%;"><\/div><\/div>'+
						'<div class="size mo-ma-l-auto mo-nowrap"><\/div>'+
                '<span class="start">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-save" title="'+mozilo_lang["button_save"]+'"><\/button>'+
                '<\/span>'+
                '<span class="cancel">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-stop" title="'+mozilo_lang["button_cancel"]+'"><\/button>'+
                '<\/span>'+
					 '<\/div>'+
                '<\/div>');

            if (file.error) {
                row.addClass('red');
                row.find('.error').html((locale.fileupload.errors[file.error] || file.error));

            } else {            
row.find('.name').text(file.name);
            	row.find('.size').text(o.formatFileSize(file.size));
            	if(!o.options.prev_img) {
                    row.find('.preview').append('<a class="fu-ext-imgs fu-ext-'+o.mimeType(file)+'"><\/a>');
                    row.find('a').prop('title', file.name);
                    
                }
            }
            rows = rows.add(row);
        });
        return rows;
    },
    downloadTemplate: function (o) {
        var rows = $();
        $.each(o.files, function (index, file) {

            var row = $('<div class="template-download fadeIn ui-widget-content ui-corner-all flex">' +
                 '<div class="error"><\/div>' +
                 '<div class="preview"><\/div>' +
                     '<fieldset style="border:none;" class="flex flex-100"><legend class="sr-only">'+mozilo_lang["file"]+' '+mozilo_lang["admin_delete"]+'<\/legend>' +
                         '<div class="name"><span class="fu-rename-file"><\/span><\/div>' +
                         '<div class="size mo-ma-l-auto mr mo-nowrap"><\/div>' +
                         '<div class="delete flex">' +
                             '<button class="fu-img-button mo-icons-icon mo-icons-delete" title="'+mozilo_lang["admin_delete"]+'"><span class="sr-only">'+mozilo_lang["file"]+' '+mozilo_lang["admin_delete"]+'<\/span><\/button>' +
                             '<label for=""><span class="sr-only"> '+mozilo_lang["plugins_checkbox_toggle"]+'<\/span><input id="" type="checkbox" name="delete" value="1" \/><\/label>' +
                         '<\/div>' +
                     '<\/fieldset>' +
             '<\/div>'
             );

            if (file.error) {
                row.addClass('red');
                row.find('.error').html((locale.fileupload.errors[file.error] || file.error));
                row.find('.delete button').addClass('js-nodialog');
            } else {
                row.find('.name span').text(file.name);
					 row.find('.size').text(o.formatFileSize(file.size));

                if(!o.options.prev_img) {
                    row.find('.preview').append('<a class="fu-ext-imgs fu-ext-'+o.mimeType(file)+'"><\/a>');
                    row.find('a').prop('title', file.name);
row.find('label').prop('for', file.name.split('.').slice(0, -1).join('.'));
							row.find('input').prop('id', file.name.split('.').slice(0, -1).join('.'));
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
