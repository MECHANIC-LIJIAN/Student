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

/*压缩多级目录 
    $openFile:目录句柄 
    $zipObj:Zip对象 
    $sourceAbso:源文件夹路径 
*/  
function addFilesToZip($openFile,$zipObj,$sourceAbso,$newRelat = '')  
{  
    while(($file = readdir($openFile)) != false)  
    {  
        if($file=="." || $file=="..")  
            continue;  
          
        /*源目录路径(绝对路径)*/  
        $sourceTemp = $sourceAbso.'/'.$file;  
        /*目标目录路径(相对路径)*/  
        $newTemp = $newRelat==''?$file:$newRelat.'/'.$file;  
        if(is_dir($sourceTemp))  
        {  
            // echo '创建'.$newTemp.'文件夹<br/>';  
            $zipObj->addEmptyDir($newTemp);/*这里注意：php只需传递一个文件夹名称路径即可*/  
            addFilesToZip(opendir($sourceTemp),$zipObj,$sourceTemp,$newTemp);  
        }  
        if(is_file($sourceTemp))  
        {  
            //echo '创建'.$newTemp.'文件<br/>';  
            $zipObj->addFile($sourceTemp,$newTemp);  
        }  
    }  
} 

//foreach遍历后unset删除,这种方法也是最容易想到的方法
function delByValue($arr, $value){
    if(!is_array($arr)){
      return $arr;
    }
    foreach($arr as $k=>$v){
      if($v == $value){
        unset($arr[$k]);
      }
    }
    return $arr;
  }



   /**
     * 直接输出二维码 + 生成二维码图片文件
     */
    function create(){
        // 自定义二维码配置
        $config = [
            'title'         => true,
            'title_content' => '嗨，老范',
            'logo'          => true,
            'logo_url'      => './logo.png',
            'logo_size'     => 80,
        ];

        // 直接输出
        $qr_url = 'http://www.baidu.com?id=' . rand(1000, 9999);

        $qr_code = new QrcodeServer($config);
        $qr_img = $qr_code->createServer($qr_url);
        echo $qr_img;

        // 写入文件
        $qr_url = '这是个测试二维码';
        $file_name = './static/qrcode';  // 定义保存目录

        $config['file_name'] = $file_name;
        $config['generate']  = 'writefile';

        $qr_code = new QrcodeServer($config);
        $rs = $qr_code->createServer($qr_url);
        print_r($rs);

        exit;
    }