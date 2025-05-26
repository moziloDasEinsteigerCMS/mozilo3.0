<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function getFileUpload($curent_dir,$dir = false,$count_text = false,$newcss = "") {
    $head = "";
    if(ACTION != "template") {
        $count = "0";
        $gallery_tools = "";
        if(ACTION == "gallery") {
            $count = count(getDirAsArray(GALLERIES_DIR_REL.$curent_dir,"img"));
            $gallery_tools = '<span class="js-tools-icon-show-hide js-rename-file'.$newcss.'"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="ml" height="18" width="18" fill="currentColor"><title>'.getLanguageValue("admin_edit").'</title> <g> <path fill="none" d="M0 0h24v24H0z"></path> <path d="M21 6.757l-2 2V4h-9v5H5v11h14v-2.757l2-2v5.765a.993.993 0 0 1-.993.992H3.993A1 1 0 0 1 3 20.993V8l6.003-6h10.995C20.55 2 21 2.455 21 2.992v3.765zm.778 2.05l1.414 1.415L15.414 18l-1.416-.002.002-1.412 7.778-7.778z"></path> </g> </svg></span>'
                    .'<span class="js-tools-icon-show-hide js-edit-delete'.$newcss.'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="ml" viewBox="0 0 16 16"><title>'.getLanguageValue("admin_delete").'</title><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/> <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/> </svg></span>';
        } elseif(ACTION == "files") {
            global $CatPage;
            $count = count($CatPage->get_FileArray($curent_dir));
        }
        $head = '<summary><span class="flex">'
            .'<span class="flex-100"><span class="js-gallery-name mo-padding-left mo-bold">'.$dir.'</span></span>'
                .'<span class="mo-staus mo-font-small'.$newcss.'">( '
                .'<span class="files-count">'.$count.'</span> '.$count_text.' )</span>'
                .$gallery_tools
        .'</span></summary>';
    }

    $css = "mo-ul";
    if(ACTION != "template")
        $css = "mo-in-ul-ul";
    $fileupload = '<div class="card fadeIn">'
        .'<div class="ui-widget-content ui-corner-all">'
            #  hier die zusätzlichen para meter setzen
            .'<input type="hidden" name="curent_dir" value="'.$curent_dir.'">'
            .'<input type="hidden" name="chancefiles" value="true">'
            .'<input type="hidden" name="action" value="'.ACTION.'">'
            # The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload
            .'<div class="fileupload-buttonbar mo-li-head-tag mo-li-head-tag-no-ul ui-widget-header ui-corner-top flex">'
            	.'<div class="upload-bar-left flex flex-100">'
                .'<span class="fileinput-button">'
                    .'<span class="mo-icons-icon mo-icons-add-file"></span>'
                    .'<input type="file" name="files[]">'
                .'</span>'
                .'<button type="submit" class="fu-img-button start mo-icons-icon mo-icons-save" title="'.getLanguageValue("button_save").'"></button>'
                .'<button type="reset" class="fu-img-button cancel mo-icons-icon mo-icons-stop" title="'.getLanguageValue("button_cancel").'"></button>'
                .'<button class="fu-img-button delete mo-icons-icon mo-icons-delete" title="'.getLanguageValue("admin_delete").'"></button>';
                if(ACTION == "gallery") {
                    $fileupload .= '<button class="fu-img-button resize mo-icons-icon mo-icons-img-scale" title="'.getLanguageValue("gallery_scale_thumbs").'"></button>';
                }
                $fileupload .= '<input type="checkbox" class="toggle"></div>';
                if(ACTION == "gallery") {
                    global $GALLERY_CONF;
                    $tmp_w = "";
                    $tmp_h = "";
                    if($GALLERY_CONF->get('maxwidth') != "auto" and $GALLERY_CONF->get('maxwidth') > 0)
                        $tmp_w = $GALLERY_CONF->get('maxwidth');
                    if($GALLERY_CONF->get('maxheight') != "auto" and $GALLERY_CONF->get('maxheight') > 0)
                        $tmp_h = $GALLERY_CONF->get('maxheight');
                    $fileupload .= ''
                    .'<div class="upload-bar-middle flex flex-100"><span class="">'.getLanguageValue("gallery_image_size").'</span><span><input type="text" name="new_width" value="'.$tmp_w.'" size="4" maxlength="4" class="mo-input-digit js-in-digit mo-align-center"> x <input type="text" name="new_height" value="'.$tmp_h.'" size="4" maxlength="4" class="mo-input-digit js-in-digit mo-align-center"></span></div>'
                    .'<div class="upload-bar-right flex flex-100"><span class="">'.getLanguageValue("gallery_preview_size").'</span><span><input type="text" name="thumbnail_max_width" value="'.$GALLERY_CONF->get('maxthumbwidth').'" size="4" maxlength="4" class="mo-input-digit js-in-digit mo-align-center"> x <input type="text" name="thumbnail_max_height" value="'.$GALLERY_CONF->get('maxthumbheight').'" size="4" maxlength="4" class="mo-input-digit js-in-digit mo-align-center"></span></div>'
                    .'';
                }
                $fileupload .= '</div>'
            .'<div class="files"></div>'
        .'</div>'
    .'</div>';

    $form_start = '<form class="fileupload flex-100" action="index.php" method="post" enctype="multipart/form-data">';
    if(ACTION != "template") {
    $form_start .= '<details>';
    }    
     if(ACTION != "template") { 
     $form_end = '</details></form>';
     } else {
     	$form_end = '</form>';
     }
    
    return $form_start.$head.$fileupload.$form_end;
}
?>
