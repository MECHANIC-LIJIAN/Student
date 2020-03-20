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

/**
 * @param $data array  数据
 * @param $pid  string 父级元素的名称 如 parent_id
 * @param $id     string 子级元素的名称 如 comm_id
 * @param $p_id     int    父级元素的id 实际上传递元素的主键
 * @return array
 */
function getOptionList($data, $pid, $id, $p_id = '0')
{
    $tmp = array();
    foreach ($data as $key => $value) {
        if ($value[$pid] == $p_id) {
            $value['child'] = getOptionList($data, $pid, $id, $value[$id]);
            $tmp[] = $value;
        }
    }
    return $tmp;
}

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
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
        exception($mail->ErrorInfo, 1001);
    }
}

function split($string)
{

    return implode(' ', explode('|', $string));
}
