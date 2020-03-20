<?php
namespace app\index\controller;

use app\common\Map;
use think\Controller;

class Ip extends Controller
{
    public function index()
    {
        $map = new Map();
        $data = $map->locationByIP('120.216.132.137');
        return json($data);
    }
}
