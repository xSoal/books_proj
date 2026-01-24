<?php

set_time_limit(0);
ini_set('memory_limit','-1');


if( !strstr($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST']) ){ exit(); }


function sanitize_filename($str, $relative_path = FALSE)
{
    $bad = array(
        '../', '<!--', '-->', '<', '>',
        "'", '"', '&', '$', '#',
        '{', '}', '[', ']', '=',
        ';', '?', '%20', '%22',
        '%3c',      // <
        '%253c',    // <
        '%3e',      // >
        '%0e',      // >
        '%28',      // (
        '%29',      // )
        '%2528',    // (
        '%26',      // &
        '%24',      // $
        '%3f',      // ?
        '%3b',      // ;
        '%3d'       // =
    );
    if ( ! $relative_path)
    {
        $bad[] = './';
        $bad[] = '/';
    }
    $str = remove_invisible_characters($str, FALSE);
    return stripslashes(str_replace($bad, '', $str));
}

function remove_invisible_characters($str, $url_encoded = TRUE)
{
    $non_displayables = array();
    if ($url_encoded)
    {
        $non_displayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
        $non_displayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
    }
    $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127
    do
    {
        $str = preg_replace($non_displayables, '', $str, -1, $count);
    }
    while ($count);
    return $str;
}



$array_allowed = array('.png','.jpg','.jpeg','.gif','.pdf','.doc','.docx','.xls','.xlsx','.mp4','.txt','.mov','.svg','.webp','.webm','.mp3','.ogg');

if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
    $filename = $_FILES["file"]["name"];

    if ($filename === sanitize_filename($filename)) {
        $fileInfos = pathinfo($filename);
        $ext = array_key_exists('extension', $fileInfos) ? '.'. strtolower($fileInfos['extension']) : '';
        if (in_array($ext, $array_allowed)) {
            $folder = date('Y-m');
            
            if($_POST['data-type'] === 'partner'){
                $folder = 'partners';
            }

            if($_POST['data-type'] === 'characteristic'){
                $folder = 'characteristics';
            }

            if($_POST['data-type'] === 'seo_images'){
                $folder = 'seo_images';
            }

            if($_POST['data-type'] === 'book'){
                $folder = 'book';
            }

            $ext = explode('.', $filename);
            $url = getcwd().'/upload/'.$folder;
            if( !file_exists( $url ) ){
                mkdir($url, 0777, true);
            }
            $file_name = $url.'/'.$_POST['file_name'];
            move_uploaded_file($_FILES["file"]["tmp_name"], $file_name);


            $name = explode('.',$_POST['file_name']);


            // без конвертации
            echo '/upload/'.$folder.'/'.$_POST['file_name'];


            // Для включения конвертации 
            // if( mb_strtolower($name[1]) == 'jpg' || mb_strtolower($name[1]) == 'jpeg' || mb_strtolower($name[1]) == 'png' ){
            //     echo '/upload/'.date('Y-m').'/'.$name[0].'.webp';
            // }else{
            //     echo '/upload/'.date('Y-m').'/'.$_POST['file_name'];
            // }

            // // конвернтием с jpg и png в Webp
            // //---------------------
            
            // $file = getcwd().'/upload/'.date('Y-m').'/';
            
            // if( mb_strtolower($name[1]) == 'jpg' || mb_strtolower($name[1]) == 'jpeg' ){
            //     system('/usr/bin/cwebp -quiet -q 90 '.$file.$name[0].'.'.$name[1].' -o '.$file.$name[0].'.webp'  );
            // }
            // if( mb_strtolower($name[1]) == 'png' ){
            //     system('/usr/bin/cwebp -quiet -lossless '.$file.$name[0].'.'.$name[1].' -o '.$file.$name[0].'.webp' );
            // }
            // //---------------------

        }
    }
} 


?>
