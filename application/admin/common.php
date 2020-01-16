<?php

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
    if ($suffix == "xlsx") {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    } else {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    }
    $excel = $reader->load($filePath, $encode = 'utf-8');

    //读取第一张表
    $sheet = $excel->getSheet(0);
    //获取总行数
    $row_num = $sheet->getHighestRow(); // 取得总行数
    //获取总列数
    $col_num = $sheet->getHighestColumn();
    $row = 1;

    $data = array(array());
    for ($colIndex = 'A'; $colIndex <= $col_num; $colIndex++) {
        if($sheet->getCell($colIndex . 1)->getValue()==""){
            return "nullError";
        }
        for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
            $rowCell = $sheet->getCell($colIndex . $rowIndex)->getValue();
            if ($rowCell != null) {
                $data[$colIndex][$rowIndex] = $rowCell;
            } else {
                break;
            }
        }
    }

    unset($data[0]);
    return $data;
}



function uuid()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = 0;
        $uuid =
        substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
        return $uuid;
    }
}
