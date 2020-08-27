<?php

namespace app\admin\controller;

class TemplateBase extends Base
{
    public $tInfo;
    public function initialize()
    {
        parent::initialize();

        $tId = my_uuid();
        $n = [0, 1, 2, 3, 4, 5, 6, 7];
        $tId[array_rand($n)] = strtolower($tId[array_rand($n)]);
        $this->tInfo['tid'] = $tId;
        $this->tInfo['uid'] = session('admin.id');

        // $test = [];
        // for ($i = 0; $i < 100000; $i++) {
        //     $t = my_uuid();
        //     $n = [0, 1, 2, 3, 4, 5, 6, 7];
        //     $t[array_rand($n)] = strtolower($t[array_rand($n)]);
        //     $test[] = $t;
        // }
        // halt(count(array_unique($test)) / count($test));
    }
}
