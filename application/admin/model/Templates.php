<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Templates extends Model
{
    use SoftDelete;

    public function options()
    {
        return $this->hasMany('TemplatesOption', 'tid', 'tid');
    }

    public function datas()
    {
        return $this->hasMany('TemplatesData', 'tid', 'tid');
    }
    public function getUser()
    {
        return $this->belongsTo('Admin', 'uid', 'id');
    }

    public function getDataByFile($data)
    {
        header("content-type:text/html;charset=utf-8");

        $validate = new \app\admin\validate\Templates;
        if (!$validate->scene('upload')->check($data)) {
            return $validate->getError();
        }

        #获取文件信息
        $fileInfo = $data['tFile']->getInfo();

        //获取文件后缀
        $suffix = explode(".", $fileInfo['name'])[1];
        //判断哪种类型
        if ($suffix == "xlsx") {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }

        $excel = $reader->load($fileInfo['tmp_name'], $encode = 'utf-8');

        //读取活动表
        $sheet = $excel->getActiveSheet();
        //获取总行数
        $row_num = $sheet->getHighestRow(); // 取得总行数
        //获取总列数
        $col_num = $sheet->getHighestColumn();

        $excelData = [];

        for ($colIndex = 'A'; $colIndex <= $col_num; $colIndex++) {
            if ($sheet->getCell($colIndex . 1)->getValue() == "") {
                return "不能有空字段,请重新选择文件";
            }
            for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
                $rowCell = $sheet->getCell($colIndex . $rowIndex)->getValue();
                if ($rowCell != null) {
                    $excelData[$colIndex][$rowIndex] = $rowCell;
                } else {
                    break;
                }
            }
        }
        session('tData',$excelData);
        return 1;
    }

}
