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
     * 读取Excel文件
     *
     * @param string $filePath
     * @return void array()
     */
    function readExcel($filePath)
    {
        //获取文件后缀
        $suffix = pathinfo($filePath)['extension'];
        //判断哪种类型
        if ($suffix=="xlsx") {
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        } else {
            $reader = PHPExcel_IOFactory::createReader('Excel5');
        }
        $excel = $reader->load($filePath, $encode = 'utf-8');
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num = $sheet->getHighestRow();
        //获取总列数
        $col_num = $sheet->getHighestColumn();
        $row=1;
        $data=array(array());
        for ($colIndex = 'A'; $colIndex != $col_num; $colIndex++) {
            $val = $sheet->getCell($colIndex.$row)->getValue();
            if ($val!=null) {
                $rowLine=array();
                $rowCell = $sheet->getCell($colIndex.($row+1))->getValue();
                for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
                    $rowCell = $sheet->getCell($colIndex.$rowIndex)->getValue();
                    if ($rowCell!=null) {
                        $data[$colIndex][$rowIndex]=$rowCell;
                    } else {
                        break;
                    }
                }
            } else {
                break;
            }
        }
        unset($data[0]);
        return $data;
    }



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