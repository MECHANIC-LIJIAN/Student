<?php

namespace app\admin\controller;
use think\Db;
class DataControl extends Base
{
    public function index()
    {
        $order = "./go_redis_to_mysql.sh";

        exec($order, $output, $return);
        $this->assign("output",$output);
        return view();
    }
}
