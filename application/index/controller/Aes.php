<?php
namespace app\index\controller;

use think\Controller;

class Aes extends Controller
{

    /**
     *
     * @param string $string 需要加密的字符串
     * @param string $key 密钥
     * @return string
     */
    public static function encrypt($string)
    {
        $key = "1234567887654321";//秘钥必须为：8/16/32位
        $iv = "1234567887654321";
        $base64_str = base64_encode(json_encode($data));
        $encrypted = openssl_encrypt($base64_str, "aes-128-cbc", $key, OPENSSL_ZERO_PADDING, $iv);
        return base64_encode($encrypted);
    }

    /**
     * @param string $string 需要解密的字符串
     * @param string $key 密钥
     * @return string
     */
    public static function decrypt($string)
    {
        $encrypted = base64_decode($string);
        $key = "1234567887654321";//秘钥必须为：8/16/32位
        $iv = "1234567887654321";
        $decrypted = openssl_decrypt($encrypted, 'aes-128-cbc', $key, OPENSSL_ZERO_PADDING, $iv);
        return json_decode(base64_decode($decrypted), true);
    }
}
