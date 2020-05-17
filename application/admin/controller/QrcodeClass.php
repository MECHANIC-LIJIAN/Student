<?php

namespace app\admin\controller;
use app\admin\business\QrcodeServer;

use Endroid\QrCode\QrCode;

class QrcodeClass
{
    
       /**
     * 直接输出二维码 + 生成二维码图片文件
     */
    public function create(){

        // 自定义二维码配置
        $config = [
            'title'         => true,
            'title_content' => 'test',
            'logo'          => false,
        ];

        // 直接输出
        $qr_url = 'http://www.baidu.com?id=' . rand(1000, 9999);

        $qr_code = new QrcodeServer($config);
        $qr_img = $qr_code->createServer($qr_url);
        ob_end_clean();
        echo $qr_img;

        // 写入文件
        $qr_url = '这是个测试二维码';
        $file_name = './';  // 定义保存目录

        $config['file_name'] = $file_name;
        $config['generate']  = 'writefile';

        $qr_code = new QrcodeServer($config);
        $rs = $qr_code->createServer($qr_url);
        print_r($rs);

    }
}
