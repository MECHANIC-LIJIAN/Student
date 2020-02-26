<?php

namespace app\admin\model;

use think\Db;
use think\Model;
use think\model\concern\SoftDelete;

class MyData extends Model
{
    use SoftDelete;

    public function options()
    {
        return $this->hasMany('MyDataOption');
    }

    public function getDataByFile($data)
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $validate = new \app\admin\validate\MyData;
        if (!$validate->scene('upload')->check($data)) {
            return $validate->getError();
        }

        #获取文件信息
        $fileInfo = $data['dataFile']->getInfo();

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
        $row_num = $sheet->getHighestRow();
        $colIndex = 'A';
        $excelData = [];
        for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
            $rowCell = $sheet->getCell($colIndex . $rowIndex)->getValue();
            if ($rowCell != null) {
                $excelData[$rowIndex] = $rowCell;
            } else {
                break;
            }
        }
        if (empty($excelData)) {
            return '不能有空字段,请重新选择文件';
        }
        
       
        
        Db::startTrans();
        try {
            $dataInfo = [
                'uid' => session('admin.id'),
                'title' => $data['dataName'],
                'count' => count($excelData),
                'create_time'=> time(),
            ];
            $dataid=Db::name("my_data")->insertGetId($dataInfo);

            $dataSets = [];
            for ($row = 1; $row <= count($excelData); $row++) {
                $tmp = [];
                $tmp['my_data_id'] = $dataid;
                $tmp['content'] = $excelData[$row];
                $tmp['create_time'] = time();
                $dataSets[] = $tmp;
            }
            Db::name('my_data_option')->insertAll($dataSets);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
            return "提交失败";
        }
        return 1;
    }
}
