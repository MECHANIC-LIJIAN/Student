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

function createZip($files = array(), $destination = '', $overwrite = true)
{

    if (file_exists($destination) && !$overwrite) {return false;}

    $validFiles = [];

    if (is_array($files)) {

        foreach ($files as $file) {

            if (file_exists($file)) {

                $validFiles[] = $file;

            }

        }

    }

    if (count($validFiles)) {

        $zip = new ZipArchive();

        if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {

            return false;

        }

        foreach ($validFiles as $file) {

            $zip->addFile($file, $file);

        }

        $zip->close();

        return file_exists($destination);

    } else {

        return false;

    }

}

function addFileToZip($path, $zip)
{
    $handler = opendir($path); //打开当前文件夹由$path指定。
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..'，不要对他们进行操作
            if (is_dir($path . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归
                addFileToZip($path . "/" . $filename, $zip);
            } else { //将文件加入zip对象
                $zip->addFile($path . "/" . $filename);
            }
        }
    }
    closedir($path);
}
