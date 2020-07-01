<?php

namespace app\card\controller;

use app\card\business\QrcodeServer;
use think\Controller;
use think\facade\Cookie;
use think\Image;
use think\facade\Request;

class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view();
    }

    public function read()
    {
        $data = input('post.');

        $d = mktime(9, 12, 31, 6, 10, 2016);
        $res = [
            'stuname' => $data['stuname'],
            'stuno' => $data['stuno'],
            'firstTime' => date("Y-m-d h:i", $d),
            'firstBook' => "钢铁是怎样炼成的",
            'count' => '100',
            'sort' => '99%',
        ];

        Cookie::set('studata', $res);

        if ($res['stuno'] == '2016156034') {
            $this->success('查询成功，请稍后', url('card/Index/create'), $data);
        } else {
            $this->success('查询失败，请重试');
        }
    }

    public function create()
    {
        if (!Cookie::has('studata')) {
            $this->redirect('card/Index/index');
        }

        $data = cookie('studata');

        $n = "\n\n";
        $header = "亲爱的 " . $data['stuname'] . " 同学：";
        $main = "在你离开母校之前" . $n;
        $main .= "我们为你准备了一份清单" . $n;
        $main .= "希望成为你大学时代的美好回忆" . $n. $n;

        $main .= $data['firstTime'] . " 第一次见到你" . $n ;
        $main .= "在书架边认真挑书的你" . $n;
        $main .= "带走了心爱的《" . $data['firstBook'] . "》" . $n;
        $main .="大学期间，你一共借了".$data['count']."本书，\n超越了".$data['sort']."的同学";
        $msg = "分别总是伤感

        一直压抑着的情绪最终在第一个舍友离开时崩塌

        总觉得来日方长

        朝夕相处的人儿却要说了再见

        愿你们以梦为马、不负韶华

        愿你们一生努力、一生被爱

        愿你们历尽千帆、归来仍少年。";

        $image = \think\Image::open('./static/imgs/card/cardbg.jpg');
        // 返回图片的宽度
        $width = $image->width();
        // 返回图片的高度
        $height = $image->height();

        $textSize = 0.015*($image->height());
        $textColor = '#000000';
        $textLocate = \think\Image::WATER_CENTER;
        $textOffset = [0, 0];
        $textAngle = 0;

        $imgSavePath = 'uploads/card/';
        if (!file_exists($imgSavePath)) {
            @mkdir($imgSavePath, 0777, true);
        }

        $imgSaveName = $imgSavePath . $data['stuno'] . "." . $image->type();
        try {
            #添加水印
            $image->text($header, getcwd() . '/static/fonts/mkbfsg.ttf', $textSize, $textColor, $textLocate, [-70, -130], $textAngle)->text($main, getcwd() . '/static/fonts/mkbfsg.ttf', $textSize, $textColor, $textLocate, $textOffset, $textAngle)->save($imgSaveName);
        } catch (\Exception $e) {
            return "服务器错误，请稍后重试";
        }

        // Cookie::delete('studata');

        $this->assign([
            'imgPath' => $imgSaveName."?".time(),
        ]);
        return view();
        // main("Cache-Control: public");
        // main("Content-Description: File Transfer");
        // main('Content-disposition: attachment; filename=' . basename($zipFileName)); //文件名
        // main("Content-Type: application/zip"); //zip格式的
        // main("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        // main('Content-Length: ' . filesize($zipFileName)); //告诉浏览器，文件大小
        // ob_clean();
        // flush();
        // @readfile($zipFileName);

        // #删除压缩包
        // unlink($zipFileName);

    }

    public function createQrCode($key)
    {
        if ($key === 'create') {
            // 自定义二维码配置
            $config = [
                'title' => true,
                'title_content' => '扫码前往制作',
                'logo' => false,
            ];

            // 直接输出
            $qr_url=Request::domain()."/card";
            $qr_code = new QrcodeServer($config);
            $qr_img = $qr_code->createServer($qr_url);
            ob_end_clean();
            echo $qr_img;

            // // 写入文件
            // $qr_url = '这是个测试二维码';
            // $file_name = './'; // 定义保存目录

            // $config['file_name'] = $file_name;
            // $config['generate'] = 'writefile';

            // $qr_code = new QrcodeServer($config);
            // $rs = $qr_code->createServer($qr_url);
            // print_r($rs);
        }
    }

   
}
