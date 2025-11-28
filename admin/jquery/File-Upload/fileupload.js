function file_rename(change_item) {
    var baseName = change_item.val();               // Name ohne Endung
    var ext = change_item.data('ext') || '';       // Endung (inkl. Punkt)
    var oldName = change_item.siblings('.fu-rename-file').text();
    var newName = baseName + ext;

    var template = change_item.closest('.template-download');

    // --- UI: Namen aktualisieren ---
    change_item.siblings('.fu-rename-file')
        .removeClass('fu-nosearch')
        .text(newName)
        .show(0);

    // alten .name-ext Span entfernen
    change_item.siblings('.name-ext').remove();

    // --- Delete-Button URL anpassen ---
    var deleteBtn = template.find('.delete button');
    if (deleteBtn.length && deleteBtn.attr('data-url')) {
        deleteBtn.attr(
            'data-url',
            deleteBtn.attr('data-url').replace(oldName, newName)
        );
    }

    // --- Preview-Link anpassen ---
    var previewLink = template.find('.preview a');
    if (previewLink.length && previewLink.attr('href')) {
    var href = decodeURIComponent(previewLink.attr('href'));
    href = href.replace(oldName, newName);
        previewLink.attr('href', encodeURI(href))
               .attr('title', baseName);
}

    // --- Vorschaubild aktualisieren ---
    var previewImg = template.find('.preview img');
    if (previewImg.length && previewImg.attr('src')) {

        // ALT-Text:
        // 1. wenn .fu-alt vorhanden und nicht leer → diesen nehmen
        // 2. sonst baseName als Fallback
        var fuAltEl = template.find('.fu-alt');
        var fuAltText = fuAltEl.length ? fuAltEl.text().trim() : '';
        var currentAlt = fuAltText !== '' ? fuAltText : baseName;

        previewImg
            .attr('src', previewImg.attr('src').replace(oldName + ext, newName))
            .attr('title', baseName)
            .attr('alt', currentAlt);
    }
    // --- Eingabefeld entfernen ---
    change_item.remove();
}

function is_filename_allowed(name) {
	const variable = new String(mo_acceptFileTypes);                //Erlaubte Dateiendungen zum Umbenennen, von mo_acceptFileTypes
	const type = variable.replace(/\.|\$\/i|\/|\\|/ig,'');
    const regex = new RegExp(`^[\\w\\-\\_]+\\.${type}$`, 'i');	
    if(name.search(regex) != -1)   
        return true;
    return false;
}

function load_files_datajson(that) {
    that.fileupload({
        dropZone: that
    });
    $.get(URL_BASE+ADMIN_DIR_NAME+"/index.php?"+that.serialize(), function (result) {
        var tmpdata = $("<span>"+result+"<\/span>");
        if(tmpdata.find("#json-data").length > 0) {
            result = jQuery.parseJSON(tmpdata.find("#json-data").text());
        } else
            result = "";
        if (result && result.length)
            that.fileupload('option','done').call(that, null,{result: result});
        tmpdata.remove();
    });
}

$(function () {

    $('input[type="file"]').prop('multiple','multiple');

        $('.fileupload').each(function () {
            if($(this).parents('#menu-fix').length > 0) {
                // wie continue
                return true;
            }
            load_files_datajson($(this));
        });
 //   }

    $('.fileupload .preview a:not([target^=_blank])').live('click', function (e) {
        e.preventDefault();
        if(is_img(this.href))
            dialog_img_preview(this.href);
        else
            dialog_iframe_preview(this.href);
    });

// --- Doppelklick auf Dateiname ---
$('.fu-rename-file').live('dblclick', function(e) {
    e.preventDefault();

    var fullName = $(this).text();
    var dotIndex = fullName.lastIndexOf('.');
    var baseName = dotIndex > 0 ? fullName.substring(0, dotIndex) : fullName;
    var ext = dotIndex > 0 ? fullName.substring(dotIndex) : '';

    // Span verstecken, Input daneben einfügen
    $(this).addClass('fu-nosearch').hide(0).after(
        "<input class='fu-rename-in-file' type='text' data-ext='" + ext + "' value='" + baseName + "'><span class='name-ext'>"+ ext +"</span>"
    );

    $(this).siblings('.fu-rename-in-file').focus();
});

// --- Eingabe im Input-Feld ---
$('.fu-rename-in-file').live('keydown', function(e) {
    if (e.which === 13) { // Enter
        e.preventDefault();

        var baseName = $(this).val();
        var ext = $(this).data('ext') || '';
        var new_name = baseName + ext;
        var originalName = $(this).siblings('.fu-rename-file').text();

        var fileupload = $(this).closest('.fileupload');
        var name_twice = false;

        // Prüfen, ob Name schon existiert
        fileupload.find('.fu-rename-file:not(.fu-nosearch)').each(function() {
            if ($(this).text() === new_name) name_twice = true;
        });

        if (name_twice) {
            dialog_open("error_messages", returnMessage(false, mozilo_lang["error_exists_file_dir"]));
            return false;
        }

        if (new_name === originalName) {
            $(this).siblings('.fu-rename-file').removeClass('fu-nosearch').show(0);
            $(this).siblings('.name-ext').removeClass('name-ext').hide(0); 
            $(this).remove();
            return false;
        }

        if (!is_filename_allowed(new_name)) {
            dialog_open("error_messages", returnMessage(false, mozilo_lang["error_datei_file_name"]));
            return false;
        }

        // --- UI-Update zentral über file_rename ---
        file_rename($(this));

        // --- Server-Anfrage ---
        send_item_status = "file_rename";
        var para = "newfile=" + new_name +
                   "&orgfile=" + originalName +
                   "&curent_dir=" + rawurlencode_js(fileupload.find('input[name="curent_dir"]').val());
        send_data(para, $(this));

    } else if (e.which === 27) { // ESC
        e.preventDefault();
        $(this).siblings('.fu-rename-file').removeClass('fu-nosearch').show(0);
        $(this).siblings('.name-ext').removeClass('name-ext').hide(0);
        $(this).remove();
    }
});

    $('.fu-subtitle').live('dblclick', function (e) {
        e.preventDefault();
        $(this).hide(0).after("<input class=\"fu-subtitle-in\" type=\"text\">");
        $(this).siblings('.fu-subtitle-in').val($(this).text()).focus();
    });
	
    $('.fu-alt').live('dblclick', function (e) {
        e.preventDefault();
        $(this).hide(0).after("<input class=\"fu-alt-in\" type=\"text\">");
        $(this).siblings('.fu-alt-in').val($(this).text()).focus();
    });

    $('.fu-subtitle-in').live('keydown', function (e) {
        if(e.which == 13) { // enter
            e.preventDefault();
            send_item_status = "gallery_subtitle";
            var para = "subtitle="+rawurlencode_js($(this).val())+
                "&curent_dir="+rawurlencode_js($(this).closest('.fileupload').find('input[name="curent_dir"]').val())+
                "&file="+$(this).closest('.template-download').find('.fu-rename-file').text();
            send_data(para,$(this));
        } else if(e.which == 27) { // esc
            e.preventDefault();
            $(this).siblings('.fu-subtitle').show(0);
            $(this).remove();
        }
    });
	
	$('.fu-alt-in').live('keydown', function (e) {
        if(e.which == 13) { // enter
            e.preventDefault();
            send_item_status = "gallery_alt";
            var para = "alttext="+rawurlencode_js($(this).val())+
                "&curent_dir="+rawurlencode_js($(this).closest('.fileupload').find('input[name="curent_dir"]').val())+
                "&file="+$(this).closest('.template-download').find('.fu-rename-file').text();
            send_data(para,$(this));
        } else if(e.which == 27) { // esc
            e.preventDefault();
            $(this).siblings('.fu-alt').show(0);
            $(this).remove();
        }
    });

});