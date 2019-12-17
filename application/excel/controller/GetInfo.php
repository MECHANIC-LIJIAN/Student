<?php


namespace app\excel\controller;

use function Complex\add;
use think\Controller;
use think\Db;
use Env;
use PHPExcel_IOFactory;
use think\Model;

class GetInfo extends Controller
{
    public function index()
    {
        return view();
    }

   
}
