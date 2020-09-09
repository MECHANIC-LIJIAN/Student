<?php

namespace app\admin\model;

use app\common\CommonModel;
use think\Db;
use think\Model;
use think\model\concern\SoftDelete;

class MyData extends CommonModel
{
    use SoftDelete;

    public function getDatas()
    {
        return $this->hasMany('MyDataOption')->field('content,my_data_id,id')->limit(20);
    }

    public function delDatas()
    {
        return $this->hasMany('MyDataOption')->field('content,my_data_id,id');
    }

    public function getUser()
    {
        return $this->belongsTo('Admin', 'uid', 'id')->field('id,username');
    }

    public function addDataByFile($dataInfo)
    {
        header("content-type:text/html;charset=utf-8");

        $validate = new \app\admin\validate\MyData;
        if (!$validate->scene('upload')->check($dataInfo)) {
            return $validate->getError();
        }

        #获取文件信息
        $fileInfo = $dataInfo['dataFile']->getInfo();
        unset($dataInfo['dataFile']);

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

        $dataInfo['count'] = count($excelData);
        $dataInfo['data'] = $excelData;

        $res = $this->saveData($dataInfo);

        return $res;
    }

    public function addDataByText($dataInfo)
    {
        $validate = new \app\admin\validate\MyData;
        if (!$validate->scene('text')->check($dataInfo)) {
            return $validate->getError();
        }
        $textData = array_filter(array_unique(explode("\n", trim($dataInfo['dataText']))));

        unset($dataInfo['dataText']);

        $dataInfo['count'] = count($textData);
        $dataInfo['data'] = $textData;

        $res = $this->saveData($dataInfo);

        return $res;
    }

    private function saveData($dataInfo)
    {
        Db::startTrans();
        try {
            $dataid = Db::name("my_data")->strict(false)->insertGetId($dataInfo);
            $dataSets = [];
            foreach ($dataInfo['data'] as $key => $value) {
                $tmp = [];
                $tmp['my_data_id'] = $dataid;
                $tmp['content'] = $value;
                $tmp['create_time'] = time();
                $dataSets[] = $tmp;
            }
            Db::name('my_data_option')->insertAll($dataSets);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return "提交失败";
        }
        return 1;
    }

    public function appendDataByText($dataInfo)
    {
        $validate = new \app\admin\validate\MyData;
        if (!$validate->scene('text')->check($dataInfo)) {
            return $validate->getError();
        }
        $textData = array_filter(array_unique(explode("\n", trim($dataInfo['dataText']))));

        unset($dataInfo['dataText']);

        $dataInfo['count'] = count($textData) + $dataInfo['count'];

        Db::startTrans();
        try {
            $dataSets = [];
            foreach ($textData as $key => $value) {
                $tmp = [];
                $tmp['my_data_id'] = $dataInfo['id'];
                $tmp['content'] = $value;
                $tmp['create_time'] = time();
                $dataSets[] = $tmp;
            }
            Db::name('my_data')
                ->where('id',$dataInfo['id'])
                ->update(['count'=>$dataInfo['count']]);
            Db::name('my_data_option')->insertAll($dataSets);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return "提交失败";
        }
        return 1;
    }
}
