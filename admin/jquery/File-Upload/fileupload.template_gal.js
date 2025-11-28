
var parentWidget = ($.blueimpIP || $.blueimp).fileupload;
$.widget('blueimpUI.fileupload', $.blueimpUI.fileupload, {

        options: {

            prev_img: true,
    uploadTemplate: function (o) {

        var rows = $();
        $.each(o.files, function (index, file) {


            var row = $('<div class="template-upload fadeIn file-item ui-widget ui-widget-content ui-corner-all card flex">' +
                '<div class="flex flex-100" style="flex-direction:column; width:100%">' +
                        '<div class="error"><\/div>' +
                        '<div class="preview flex-100"><span><\/span><\/div>' +
                        '<div class="upload-footer flex">'+
                        '<div class="flex" style="width:100%">'+
                        '<span class="name"><\/span>' +
                        '<span class="size mo-ma-l-auto mo-nowrap"><\/span>' +
								'</div>'+
                            '<div class="progress progress-success progress-striped active mr ml"><div class="bar" style="width:0%;"><\/div><\/div>'+
  									'<div class="flex">'+
                            '<span class="start">'+
                                '<button class="fu-img-button mo-icons-icon mo-icons-save" title="'+mozilo_lang["button_save"]+'"><\/button>'+
                            '<\/span>'+
                            '<span class="cancel">'+
                                '<button class="fu-img-button mo-icons-icon mo-icons-stop" title="'+mozilo_lang["button_cancel"]+'"><\/button>'+
                            '<\/span>'+
                            '</div>'+                       
                        '</div>'+
                '<\/div>' +
            '<\/div>');

            if (file.error) {
                row.addClass('red');
                row.find('.error').html((locale.fileupload.errors[file.error] || file.error));
            } else {
            	row.find('.name').text(file.name);
            	row.find('.size').text(o.formatFileSize(file.size));
            }

            rows = rows.add(row);
        });

        return rows;
    },
    downloadTemplate: function (o) {
        var rows = $();
        var new_width = $(o.options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="new_width"]');
        var new_height = $(o.options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="new_height"]');

        $.each(o.files, function (index, file) {

            var row = $('<div class="template-download fadeIn file-item ui-widget ui-widget-content ui-corner-all card flex">' +
			'<fieldset style="border:none; width:100%;"><legend class="sr-only">'+mozilo_lang["template_title_editor"]+' '+mozilo_lang["images"]+' / '+mozilo_lang["admin_delete"]+'<\/legend>' +
                '<div class="flex-100 flex">' +
					'<div class="error">' +
                    '<\/div>' +
                    '<div class="mo-padding-bottom">' +
                        '<span class="preview"><\/span>' +
                    '<\/div>'+
                '<\/div>'+
				
                '<div style="width:100%">' +
                
					'<div class="flex">'+
						'<div class="delete flex flex-100">' +
                        '<span class="fu-img-button resize mo-icons-icon mo-icons-img-scale" title="'+mozilo_lang["gallery_scale_thumbs"]+'"><\/span>'+
                        '<button class="fu-img-button mo-icons-icon mo-icons-delete" title="'+mozilo_lang["admin_delete"]+'"><span class="sr-only">'+mozilo_lang["file"]+' '+mozilo_lang["admin_delete"]+'<\/span><\/button>'+
                        '<label for=""><span class="sr-only"> '+mozilo_lang["plugins_checkbox_toggle"]+'<\/span><input id="" type="checkbox" name="delete" value="1" \/><\/label>'+
						'<\/div>'+
					'<\/div>'+
                        
					'<div class="c-content">'+
                        '<div class="size" ><\/div>' +
                        '<div class="pixelsize mo-ma-l-auto"><span><\/span> '+mozilo_lang["pixels"]+'<\/div>' +
                    '<\/div>' +
                    '<div class="c-content">' +
                        '<div class="subtitle-lang">'+mozilo_lang["gallery_text_subtitle"]+'<\/div>' +
                        '<div class="subtitle flex"><span class="fu-subtitle"><\/span><\/div>' +
                    '<\/div>' +
                    '<div class="c-content">' +
                        '<div class="alt-lang">'+mozilo_lang["gallery_text_alt"]+'<\/div>' +
                        '<div class="alt flex"><span class="fu-alt"><\/span><\/div>' +
                    '<\/div>' +
                    '<div class="c-content">' +
                        '<span class="name"><span class="fu-rename-file"><\/span><\/span>' +
                    '<\/div>' +
                '<\/div>' +
			'<\/fieldset>'+
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
                row.find('.subtitle span').addClass('fu-empty flex-100');
                if(typeof file.subtitle == "undefined")
                    row.find('.subtitle-lang').text("");
            } 
            
            if(file.alt)
                row.find('.alt span').text(rawurldecode_js(file.alt));
            else  {
                row.find('.alt span').addClass('fu-empty flex-100');
                if(typeof file.alt == "undefined")
                    row.find('.alt-lang').text("");
            }

            if (file.error) {

                row.addClass('red');
                row.find('.error').html((locale.fileupload.errors[file.error] || file.error));
                row.find('.delete button').addClass('js-nodialog');
            } else {
                row.find('.name span').text(file.name);
                row.find('.size').text(o.formatFileSize(file.size));
                
                if (file.thumbnail_url) {
    const baseName = file.name.split('.').slice(0, -1).join('.');
    const thumbSrc = `${file.thumbnail_url}?${Date.now()}`;
    const fullSrc = file.url || file.thumbnail_url.replace('/vorschau/', '/'); // Originalbild-Fallback

    const $row = $(row);
    const $preview = $row.find('.preview');

    // Alt-Text prüfen, fallback auf Dateiname
    let altText = $row.find('.fu-alt').text().trim();
    if (!altText) altText = baseName;

    // Vorschaubereich aufbauen (gleiches HTML wie vorher)
    $preview.empty().append(
        $('<a>', { href: fullSrc, title: baseName }).append(
            $('<img>', {
                src: thumbSrc,
                alt: altText,
                title: baseName
            })
        )
    );

    // Label und Input synchronisieren
    $row.find('label').attr('for', baseName);
    $row.find('input').attr({ id: baseName, name: 'delete', value: 1 });
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
}
});
