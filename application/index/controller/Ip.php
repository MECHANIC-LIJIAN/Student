<?php
namespace app\index\controller;

use app\common\Map;
use think\Controller;

class Ip extends Controller
{
    public function index()
    {
        $map = new Map();
        $data = $map->locationByIP(get_client_ip());
        return $data;
    }
}
