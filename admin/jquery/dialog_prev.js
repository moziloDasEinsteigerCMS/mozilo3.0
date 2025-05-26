var dialog_prev = false;

function dialog_img_preview(file_href) {
    var prev_img = $('<img id="js-imgload" alt="" title="">').prop('src', file_href);
    var file_title = file_href.substring(file_href.lastIndexOf("/")+1);
    $("#dialog-test-w").append(prev_img);
    dialog_img_preview_wait(file_title);
    $("#dialog-test-w img").load(function() {
        dialog_prev.dialog("close").css("background","transparent");
        dialog_set_max_from_test(dialog_prev);
        dialog_prev.append(prev_img)
            .dialog({
                title: file_title,
                width: 'fit-content',
                buttons: [{
                text: mozilo_lang["close"],
                click: function() {
                    $(this).dialog("close");
                }
        }]
            }).dialog("open");
        if(prev_img.width() > dialog_prev.width() || prev_img.height() > dialog_prev.height()) {
            dialog_prev.css('overflow','hidden');
            prev_img.wrap('<div id="js-imgload-box">');
            prev_img.css('cursor','move');
        }
    });
}

function dialog_img_preview_wait(file_title) {
    dialog_prev.css("background", "url(" + ICON_URL + "ajax-loader.gif) center center no-repeat")
        .dialog({
            title: "Warte auf Bild: "+file_title,
            width: 300,
            buttons: [{
                text: mozilo_lang["button_cancel"],
                click: function() {
                    $("#dialog-test-w img").remove();
                    $(this).dialog("close");
                }
            }]
    }).dialog("open");
}

function dialog_iframe_preview(file_href) {
    var prev_iframe = $('<iframe  style="overflow:visible; border: none; text-align:left; width:100%; height:100%;"><\/iframe>').prop('src', file_href);
    dialog_prev.dialog({
        title: file_href.substring(file_href.lastIndexOf("/")+1),
  //      width: $(".mo-td-content-width").eq(0).width(),
        height: (parseInt($(window).height()) - dialogMaxheightOffset)
    }).append(prev_iframe).dialog("open");
}

function is_img(file_href) {
    var ext = file_href.substring(file_href.lastIndexOf(".")+1).toLowerCase();
    if(typeof ext == "string" && (ext == "png" || ext == "webp" ||   ext == "bmp" || ext == "gif" || ext == "jpg" || ext == "jpeg" || ext == "ico"))
        return true;
    return false;
}

$(function () {
    $('body').append('<div id="prev-dialog"><\/div>');

    $("#js-imgload-box img").live({
        mousedown: function(event) {
            event.preventDefault();
            var box = $("#js-imgload-box"),
                img = $("#js-imgload");
            if(event.which != 1)
                return;
            var x = event.pageX + box.scrollLeft(),
                y = event.pageY + box.scrollTop();
            img.bind('mousemove', function(event) {
                box.scrollLeft((event.pageX - x) * -1)
                    .scrollTop((event.pageY - y) * -1)
            });
            $("body").bind('mouseup', function(event) {
                img.unbind('mousemove');
                $("body").unbind('mouseup');
            });
        }
    });

    $("#prev-dialog").dialog({
        autoOpen: false,
        width: "auto",
        height: "auto",
        modal: true,
        position: "center",
        resizable: false,
        create: function(event, ui) {
            dialog_prev = $(this);
        },
        open: function(event, ui) {
            $("#menu-fix").hide(0).attr("id","menu-fix-close-prev");
            $('body').css('overflow','hidden');
            $('.ui-widget-overlay').bind('click', function () { $(this).siblings('.ui-dialog').find('.ui-dialog-content').dialog('close'); });
        },
        close: function(event, ui) {
            $("#menu-fix-close-prev").show(0).attr("id","menu-fix");
            $('body').css('overflow','');
            $(this).css({
                overflow:"auto",
                background:"transparent"
            }).dialog({
                buttons: [{
                text: mozilo_lang["close"],
                click: function() {
                    $(this).dialog("close");
                }
        }],
                title: "",
                width: "auto",
                height: "auto"
            }).html("");
        }
    });
});
