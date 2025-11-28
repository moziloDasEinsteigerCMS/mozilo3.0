<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
function files() {
    global $CatPage;

    if(getRequestValue('chancefiles') == "true") {
        require_once(BASE_DIR_ADMIN."jquery/File-Upload/upload.class.php");
        exit();
    }

    if(false !== ($newfile = getRequestValue('newfile','post',false))
            and false !== ($orgfile = getRequestValue('orgfile','post'))
            and false !== ($curent_dir = getRequestValue('curent_dir','post'))) {
        $dir = CONTENT_DIR_REL.$curent_dir."/".CONTENT_FILES_DIR_NAME."/";
        if(true !== ($error = moveFileDir($dir.$orgfile,$dir.$newfile,true))) {
            ajax_return("error",true,$error,true,"js-dialog-reload");
        }
        ajax_return("success",true);
    }

    $pagecontent = "";

    require_once(BASE_DIR_ADMIN."jquery/File-Upload/fileupload.php");

    $pagecontent .= '<div class="js-files mo-ul card">';
    $text_files = getLanguageValue("files");
    foreach ($CatPage->get_CatArray(true,false) as $pos => $cat) {
        $pagecontent .= '<div class="js-file-dir mo-li ui-widget-content card mb">';
        $pagecontent .= getFileUpload($cat,$CatPage->get_HrefText($cat,false),$text_files);
        $pagecontent .= '</div>';
    }
    $pagecontent .= '</div>';

    return $pagecontent;
}
?>