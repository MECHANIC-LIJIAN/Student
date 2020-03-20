<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Request;
class Ip extends Controller
{
    public function index()
    {
        $request = Request::instance();
        $request->ip();
        return get_client_ip();
    }
}
