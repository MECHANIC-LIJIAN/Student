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
    function getOptionList($data, $pid, $id, $p_id='0')
    {
        $tmp = array();
        foreach ($data as $key => $value) {
            if ($value[$pid] == $p_id) {
                // dump($value);
                $value['child'] =getOptionList($data, $pid, $id, $value[$id]);
                $tmp[] = $value;
            }
        }
        return $tmp;
    }