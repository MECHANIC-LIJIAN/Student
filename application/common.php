<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use PHPMailer\PHPMailer\Exception;
/**
 * @param $data array  数据
 * @param $pid  string 父级元素的名称 如 parent_id
 * @param $id     string 子级元素的名称 如 comm_id
 * @param $p_id     int    父级元素的id 实际上传递元素的主键
 * @return array
 */
function getTree($data, $pid = 'pid', $id = 'id', $p_id = 0)
{
    $tmp = array();
    foreach ($data as $key => $value) {
        if ($value[$pid] === $p_id) {
            $value['child'] = getTree($data, $pid, $id, $value[$id]);
            $tmp[] = $value;
        }
    }
    return $tmp;
}

use PHPMailer\PHPMailer\PHPMailer;
use SensitiveFilter\SensitiveFilter;
ini_set("error_reporting", "E_ALL & ~E_NOTICE");
// 应用公共文件
/**
 * 发送邮件
 *
 * @param 邮箱 $email
 * @param 内容 $content
 * @return void
 */
function mailto($email, $content)
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;
        $mail->CharSet = 'utf-8'; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.163.com'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'lijianwzx@163.com'; // SMTP username
        $mail->Password = 'Lj18846135429'; // SMTP password
        $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; // TCP port to connect to
        //Recipients
        $mail->setFrom('lijianwzx@163.com', '快表');
        $mail->addAddress($email); // Add a recipient
        $mail->Subject = "快表系统通知";
        $mail->Body = $content;
        $mail->isHTML(true); // Set email format to HTML

        return $mail->send();
    } catch (Exception $e) {
        return exception($mail->ErrorInfo, 1001);
    }
}

function split($string)
{
    return implode(' ', explode('|', $string));
}

function sensitive($content)
{
    // $filename = "min_word.txt";
    // $handle = fopen($filename, "r"); //读取二进制文件时，需要将第二个参数设置成'rb'
    // //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
    // $contents = fread($handle, filesize($filename));
    // $words=array_merge(explode("\n",$contents),SensitiveFilter::getWord());
    // $words=array_unique($words);
    // fclose($handle);
    // print_r($words);
    // halt(SensitiveFilter::getWord());
    if (SensitiveFilter::filter($content)) {
        return 1;
    } else {
        return 0;
    }

}
