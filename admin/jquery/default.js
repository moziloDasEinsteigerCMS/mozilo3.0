var anim_speed = '200';
var dialogMaxheightOffset = 40;
var max_menu_tab = false;

  //password toggle  
function togglePassword(e) {
    e.preventDefault();
    const input = document.getElementById("password");
    const btn = document.getElementById("password-toggle");
    input.type = (input.type === "password") ? "text" : "password";
    btn.classList.toggle("visible");
}

//disable login button
function checkform() {
    const formElements = document.forms["loginform"].elements;
    let submitBtnActive = true;

    for (let i = 0; i < formElements.length; i++) {
        const el = formElements[i];
        if (el.type !== "submit" && el.type !== "button" && el.value.length === 0) {
            submitBtnActive = false;
        }
    }

    const btn = document.getElementById("loginbtn");
    if (submitBtnActive) {
        btn.removeAttribute("disabled");
    } else {
        btn.setAttribute("disabled", "disabled");
    }
}

function sleep(milliSeconds) {
    var startTime = new Date().getTime(),
        curTime = null;
    do { curTime = new Date().getTime(); }
    while(curTime - startTime < milliSeconds);
}

function getCaretPos(item) {
    var pos = 0;
    if(!$(item).is(':focus'))
        item.focus();
    if(document.selection) {
        var sel = document.selection.createRange().duplicate();
        sel.moveStart('character',-item.value.length);
        pos = sel.text.length;
    } else if(item.selectionStart)
        pos = item.selectionStart;
    return pos;
}

function setCaretPos(item,pos) {
    if(!$(item).is(':focus'))
        item.focus();
    if(document.selection) {
        var range = item.createTextRange();
        range.move("character", pos);
        range.select();
    } else if(item.selectionStart) {
        item.selectionStart = pos;
        item.selectionEnd = pos;
    }
}

function checkHexValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val().toUpperCase();
    if(new_value.search(/[^A-F0-9]/g) != -1) {
        caret_pos = new_value.search(/[^A-F0-9]/g);
        new_value = new_value.replace(/[^A-F0-9]/g,"");
    }
    ele.val(new_value);
    setCaretPos(event.target,caret_pos);
}

function checkDezValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val();
    if(new_value.search(/[^0-9]/g) != -1) {
        caret_pos = new_value.search(/[^0-9]/g);
        new_value = new_value.replace(/[^0-9]/g,"");
        ele.val(new_value);
        setCaretPos(event.target,caret_pos);
    }
}

function checkChmodValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val();
    if(new_value.search(/[^0-7]/g) != -1) {
        caret_pos = new_value.search(/[^0-7]/g);
        new_value = new_value.replace(/[^0-7]/g,"");
        ele.val(new_value);
        setCaretPos(event.target,caret_pos);
    }
}

function checkDezAutoValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val();
    if(new_value.search(/[auto]/g) != -1) {
        ele.val("auto");
        setCaretPos(event.target,caret_pos);
    } else if(new_value.search(/[^0-9auto]/g) != -1) {
        caret_pos = new_value.search(/[^0-9auto]/g);
        new_value = new_value.replace(/[^0-9auto]/g,"");
        ele.val(new_value);
        setCaretPos(event.target,caret_pos);
    }
}

function checkIsZipFile(file_obj) {
    var file = file_obj.val();
    if(file.length > 5 && file.substring(file.lastIndexOf(".")).toLowerCase() == ".zip") {
        return true;
    }
    file_obj.val("");
    return false;
}

function rawurlencode_js(str) {
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/\~/g, '%7E').replace(/#/g,'%23');
}

function rawurldecode_js(str) {
    return decodeURIComponent(str).replace(/%21/g, '!').replace(/%27/g, "'").replace(/%28/g, '(').replace(/%29/g, ')').replace(/%2A/g, '*').replace(/%7E/g, '~').replace(/%23/g,'#');
}

// das ist die gleiche function wie in der index.php
function returnMessage(success, message) {
    if (success === true) {
        return "<div class=\"mo-message-erfolg flex-100 green mo-align-center\"><span class=\"mo-message-icon mo-icons-information flex mb slideInDown\"><svg xmlns=\"http:\//\www.w3.org/2000/svg\" width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\"> <path d=\"M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z\"></path> <path d=\"M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z\"></path> </svg><\/span><span class=\"mo-message-text\">"+message+"<\/span><\/div>";
    } else {
        return "<div class=\"mo-message-fehler flex-100 red mo-align-center\"><span class=\"mo-message-icon mo-icons-error flex mb slideInDown\"><svg width=\"48\" height=\"48\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http:\//\www.w3.org/2000/svg\"> <path d=\"M11.99 22C6.46846 21.9945 1.99632 17.5149 2 11.9933C2.00368 6.47179 6.48179 1.99816 12.0033 2C17.5249 2.00184 22 6.47845 22 12C21.9967 17.5254 17.5154 22.0022 11.99 22ZM4 12.172C4.04732 16.5732 7.64111 20.1095 12.0425 20.086C16.444 20.0622 19.9995 16.4875 19.9995 12.086C19.9995 7.6845 16.444 4.10977 12.0425 4.08599C7.64111 4.06245 4.04732 7.59876 4 12V12.172ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z\" fill=\"currentColor\"></path> </svg><\/span><span class=\"mo-message-text\">"+message+"<\/span><\/div>";
    }
}

function test_modrewrite(that) {
    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: "mod_rewrite_t_e_s_t.html",
        async: true,
        dataType: "html",
        timeout:20000,
        beforeSend: function(jqXHR) {
            if(dialog_mod_rewrite.dialog("isOpen")) {
                dialog_mod_rewrite.dialog("close");
            }
            send_object_mod_rewrite = jqXHR;
            dialog_mod_rewrite.dialog("open");
        },
        success: function(data, textStatus, jqXHR) {
            send_object_mod_rewrite = false;
            dialog_mod_rewrite.dialog("close");
            var tmp = $("<span>"+data+"<\/span>");
            if(tmp.find("#mod-rewrite-true").length > 0) {
                // in Info die li austauschen
                if($("#mod-rewrite-false").length > 0)
                    $("#mod-rewrite-false").parents(".mo-in-ul-li").replaceWith(tmp.find(".mo-in-ul-li"));
                // in Einstellungen die checkbox activ setzen
                if($("#modrewrite").length > 0) {
                    $("#modrewrite").prop('checked', true);
                    make_para($(that));
                }
            } else {
                if($("#modrewrite").length > 0) {
                    // in Einstellungen die checkbox nicht activ setzen
                    $("#modrewrite").prop('checked', false);
                    // und fehlermeldung ausgeben
                    dialog_open("error_messages",returnMessage(false, mozilo_lang["config_error_modrewrite"]));
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            send_object_mod_rewrite = false;
            dialog_mod_rewrite.dialog("close");
            dialog_open("error_messages",returnMessage(false, mozilo_lang["config_error_modrewrite"]));
            $("#modrewrite").prop('checked', false);
        }
    });
}

$(function() {

    $("body").on("click",".js-no-click", function(event) { event.preventDefault(); });

    /* bei allen input's mit dieser class nur zahlen zulassen */
    $("body").on("keyup",".js-in-digit", function(event) {
        checkDezValue(event);
    });

    /* bei allen input's mit dieser class nur zahlen zulassen */
    $("body").on({
        keyup: function(event) {checkHexValue(event);},
        focusout: function() {
            var v = $(event.target).val().toUpperCase().replace(/[^A-F0-9]/g,"");
            $(event.target).val(v+("000000".substr((Math.min(v.length,6)))));
        }
    },".js-in-hex");

    /* bei allen input's mit dieser class nur zahlen und auto zulassen */
    $("body").on("keyup",".js-in-digit-auto", function(event) {
        checkDezAutoValue(event);
    });

    /* bei allen input's mit dieser class nur zahlen 0-7 zulassen */
    $("body").on("keyup", ".js-in-chmod",function(event) {
        checkChmodValue(event);
    });

    /* toggle für die tools icons */
    
  /*  $("body").on({
        mouseenter: function() { 
            $(this).find(".js-tools-icon-show-hide:not(.mo-icon-blank)").css("opacity", 1);
        },
        mouseleave: function () {
            $(this).find(".js-tools-icon-show-hide").css("opacity", 0);
        }
    },".js-tools-show-hide");
    */

/*    var menu_fix_top = parseInt($("#menu-fix").css("top"));
    $("#menu-fix").css({"width":parseInt($("#menu-fix").css("min-width")),
                        "top":($(window).scrollTop() + menu_fix_top)
    });
    $(window).scroll(function() {
        $("#menu-fix").css("top",($(window).scrollTop() + menu_fix_top));
    }); */

    $("#menu-fix-content .js-li-cat .mo-li-head-tag").addClass("mo-li-head-tag-no-ul");

 /*   $("#menu-fix").on({
        mouseenter: function() {
            $(this).addClass("ui-corner-all").css("border-left-width",1).animate(
                {width: parseInt($(this).css("max-width"))},
                {duration:anim_speed,queue:false});
        },
        mouseleave: function () {
            $(this).animate({width:parseInt($(this).css("min-width")) },
                        {duration:anim_speed,queue:false,
                        complete: function() {
                            $(this).removeClass("ui-corner-all");
                        }}).css("border-left-width",0);
        }
    }); */

    /* toggle für die get_template_truss() php function */
 /*   $("body").on("click",".js-toggle", function(event) {
        if($(this).hasClass('ui-state-disabled')) return;
        var mo_ul = $(this).closest(".mo-li");
        if(mo_ul.find(".js-toggle-content").is(":visible")) {
            $(this).siblings('.js-rename-file, .js-edit-delete').removeClass("ui-state-disabled");
     /*       mo_li.find(".mo-li-head-tag").removeClass("ui-corner-top").addClass("ui-corner-all");*/
    /*        mo_ul.find(".js-toggle-content").hide(anim_speed);
            return;
        } else if(!mo_ul.find(".js-toggle-content").is(":visible")) {
            $(this).siblings('.js-rename-file, .js-edit-delete').addClass("ui-state-disabled");
     /*       mo_li.find(".mo-li-head-tag").removeClass("ui-corner-all").addClass("ui-corner-top");*/
     /*       mo_ul.find(".js-toggle-content").show(anim_speed);
            return;
        }
    });*/

    $("body").on("click",".js-docu-link", function(event) {
        event.preventDefault();
        var iframe = $('<iframe style="overflow:visible; border: none; text-align:left; width:100%; height:100%;" \/>');
        iframe.attr("src",$(this).attr("href"));
        dialog_open("docu",iframe);
    });

    if($("#dialog-auto").length > 0) {
        if($("#lastbackup").length > 0)
            dialog_open("messages_lastbackup",$("#lastbackup"));
        else
            dialog_open("from_php",$("#dialog-auto").contents());
    }

//    $('input[name="username"]').focus();

});

//Admin User Dropdown
class Dropdown {
  constructor(container) {
    this.isOpen = false;
    this.activeIndex = undefined;

    this.container = container;
    this.button = container.querySelector(".dropdown-button");
    this.menu = container.querySelector(".dropdown-menu");
    this.items = container.querySelectorAll(".dropdown-menu-item");
  }

  initEvents() {
    this.button.addEventListener("click", this.toggle.bind(this));
    document.addEventListener("click", this.onClickOutside.bind(this));
    document.addEventListener("keydown", this.onKeyEvent.bind(this));
  }

  toggle() {
    this.isOpen = !this.isOpen;
    this.button.setAttribute("aria-expanded", this.isOpen.toString());
    this.menu.setAttribute("aria-hidden", (!this.isOpen).toString());
    this.container.dataset.open = this.isOpen.toString();
  }

  onClickOutside(e) {
    if (!this.isOpen) return;

    let targetElement = e.target;

    do {
      if (targetElement === this.container) return;

      targetElement = targetElement.parentNode;
    } while (targetElement);

    this.toggle();
  }

  onKeyEvent(e) {
    if (!this.isOpen) return;

    if (e.key === "Tab") {
      this.toggle();
    }

    if (e.key === "Escape") {
      this.toggle();
      this.button.focus();
    }

    if (e.key === "ArrowDown") {
      this.activeIndex =
        this.activeIndex < this.items.length - 1 ? this.activeIndex + 1 : 0;
      this.items[this.activeIndex].focus();
    }

    if (e.key === "ArrowUp") {
      this.activeIndex =
        this.activeIndex > 0 ? this.activeIndex - 1 : this.items.length - 1;
      this.items[this.activeIndex].focus();
    }
  }
}

const dropdowns = document.querySelectorAll(".dropdown");
dropdowns.forEach((dropdown) => new Dropdown(dropdown).initEvents());
