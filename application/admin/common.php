<?php

function uuid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = 0;
        $uuid =
        substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
        return $uuid;
    }
}

/* create a compressed zip file */

function createZip($files = array(), $destination = '',$scene='' ,$overwrite = false)
{

    $path = env('ROOT_PATH') . 'public';

    if ($scene==='word') {
        $path='';
    }
    $validFiles = [];

    if (is_array($files)) {

        foreach ($files as $file) {
            
            if (file_exists($path . $file)) {

                $validFiles[] = $file;

            }

        }

    }

    // halt($destination);
    if (count($validFiles)) {

        $zip = new ZipArchive();
        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {

            return false;

        }
        
        foreach ($validFiles as $file) {
            $zip->addFile($path . $file, basename($file));
        }

        $zip->close();

        return file_exists($destination);

    } else {

        return false;

    }

}

/*压缩多级目录
$openFile:目录句柄
$zipObj:Zip对象
$sourceAbso:源文件夹路径
 */
function addFilesToZip($openFile, $zipObj, $sourceAbso, $newRelat = '')
{
    while (($file = readdir($openFile)) != false) {
        if ($file == "." || $file == "..") {
            continue;
        }

        /*源目录路径(绝对路径)*/
        $sourceTemp = $sourceAbso . '/' . $file;
        /*目标目录路径(相对路径)*/
        $newTemp = $newRelat == '' ? $file : $newRelat . '/' . $file;
        if (is_dir($sourceTemp)) {
            // echo '创建'.$newTemp.'文件夹<br/>';
            $zipObj->addEmptyDir($newTemp); /*这里注意：php只需传递一个文件夹名称路径即可*/
            addFilesToZip(opendir($sourceTemp), $zipObj, $sourceTemp, $newTemp);
        }
        if (is_file($sourceTemp)) {
            //echo '创建'.$newTemp.'文件<br/>';
            $zipObj->addFile($sourceTemp, $newTemp);
        }
    }
}

//foreach遍历后unset删除,这种方法也是最容易想到的方法
function delByValue($arr, $value)
{
    if (!is_array($arr)) {
        return $arr;
    }
    foreach ($arr as $k => $v) {
        if ($v == $value) {
            unset($arr[$k]);
        }
    }
    return $arr;
}

function sort_array($array, $keyid, $order = 'asc', $type = 'number')
{
    if (is_array($array)) {
        foreach ($array as $val) {
            $order_arr[] = $val[$keyid];
        }
        $order = ($order == 'asc') ? SORT_ASC : SORT_DESC;
        $type = ($type == 'number') ? SORT_NUMERIC : SORT_STRING;
        array_multisort($order_arr, $order, $type, $array);
    }
}
