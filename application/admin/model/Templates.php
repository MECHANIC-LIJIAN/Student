<?php

namespace app\admin\model;

use Overtrue\Pinyin\Pinyin;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use think\Db;
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
        return $this->hasMany('TemplatesDatas', 'tid', 'tid')->field('id,tid');
    }
    public function getUser()
    {
        return $this->belongsTo('Admin', 'uid', 'id');
    }

    /**
     * 第一步，上传表单文件
     *
     * @param [type] $tInfo
     * @return void
     */
    public function uploadTempate($tInfo)
    {
        #获取文件信息
        $fileInfo = $tInfo['tFile']->getInfo();
        unset($tInfo['tFile']);

        #存储信息，第二步使用
        session('tInfo', $tInfo);

        //获取文件后缀
        $suffix = explode(".", $fileInfo['name'])[1];
        //判断哪种类型
        if ($suffix == "xlsx") {
            $reader = new Xlsx();
        } else {
            $reader = new Xls();
        }

        $reader->setReadDataOnly(true);
        $excel = $reader->load($fileInfo['tmp_name'], $encode = 'utf-8');

        //读取活动表
        $sheet = $excel->getActiveSheet();
        //获取总行数
        $row_num = $sheet->getHighestRow(); // 取得总行数
        //获取总列数
        $col_num = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        if ($col_num > 25) {
            return "字段数大于25";
        }
        $excelData = [];

        #获取数组形式的原始数据并存到session
        for ($colIndex = 1; $colIndex <= $col_num; $colIndex++) {
            if ($sheet->getCellByColumnAndRow($colIndex, 1)->getValue() == "") {
                return "不能有空字段,请重新选择文件";
            }
            for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
                $rowCell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
                if ($rowCell != null) {
                    $excelData[$colIndex][$rowIndex] = $rowCell;
                } else {
                    break;
                }
            }
        }
        // return $excelData;

        // #获取数组形式的原始数据并存到session
        // for ($colIndex = 'A'; $colIndex <= $col_num; $colIndex++) {
        //     if ($sheet->getCell($colIndex . 1)->getValue() == "") {
        //         return "不能有空字段,请重新选择文件";
        //     }
        //     for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
        //         $rowCell = $sheet->getCell($colIndex . $rowIndex)->getValue();
        //         if ($rowCell != null) {
        //             $excelData[$colIndex][$rowIndex] = $rowCell;
        //         } else {
        //             break;
        //         }
        //     }
        // }
        #存储数据，第二步使用
        session('excelData', $excelData);
        return 1;
    }

    /**
     * 第二步,读取模板数据
     *
     * @param [type] $tInfo
     * @return void
     */
    public function getOptionList($tInfo)
    {
        #读数据
        $excelData = session('excelData');

        $optionList = [];
        $tFields = [];
        for ($col = 1; $col <= count($excelData); $col++) {
            $option = 'option_' . $col;
            $optionList[$option]['title'] = $excelData[$col][1];
            for ($row = 2; $row <= count($excelData[$col]); $row++) {
                $optionList[$option]['options'][$option . "_" . ($row - 1)] = $excelData[$col][$row];
            }
            $tFields[$option] = $excelData[$col][1];
        }

        // for ($col = 'A'; $col <= $finalKey; $col++) {
        //     $option = 'option_' . $col;
        //     for ($row = 1; $row <= count($excelData[$col]); $row++) {
        //         $tmp = [];
        //         $tmp['tid'] = $tInfo['tid'];
        //         $tmp['content'] = $excelData[$col][$row];
        //         $tmp['abbr'] = $pinyin->abbr($excelData[$col][1]);
        //         if ($row == 1) {
        //             $tmp['pid'] = '0';
        //             $tmp['sid'] = $option;
        //             $tmp['type'] = 'p';
        //             $tFields[$option] = $excelData[$col][1];
        //         } else {
        //             $tmp['pid'] = $option;
        //             $tmp['sid'] = $option . "_" . $row;
        //             $tmp['type'] = 'c';
        //         }
        //         $optionList[] = $tmp;
        //     }
        // }

        #存储可存入数据库的数据
        session('tData', $optionList);
        return $optionList;
    }

    public function createByFile($tInfo)
    {
        $template = model('Templates')
            ->where('tid', $tInfo['tid'])
            ->field('tid,status')
            ->find();

        if ($template['status'] == '1') {
            return "表单已经初始化，不可更改";
        }
        $pinyin = new Pinyin();
        $tInfo['tabbr'] = $pinyin->abbr($tInfo['tname']);
        $tInfo['options'] = session('tData');
        session('tData', null);
        $res = $this->saveData($tInfo);

        return $res;
    }

    public function createByHand($tInfo)
    {
        $params = $tInfo['params'];
        unset($tInfo['params']);
        $tDdata = [];
        $pinyin = new Pinyin();
        foreach ($params as $key => $value) {
            #分割字段 option_A,option_A_rule
            $temp = explode("_", $key);
            if (count($temp) == 2) {
                $tDdata[$key]['title'] = $value;
                $tDdata[$key]['rule'] = $params[$key . "_rule"];
            } elseif ($temp[2] != "rule") {
                $pId = implode("_", array_splice($temp, 0, 2));
                $tDdata[$pId]['options'][$key] = $value;
            }
        }
        // foreach ($params as $key => $value) {
        //     #分割字段 option_A,option_A_rule
        //     $temp = explode("_", $key);

        //     if (count($temp) == 2) {

        //         #输入框
        //         $field = [];
        //         $field['pid'] = '0';
        //         $field['sid'] = $key;
        //         $field['type'] = 'p';
        //         $field['rule'] = $params[$key . "_rule"];

        //         $field['tid'] = $tInfo['tid'];
        //         $field['content'] = $value;
        //         $field['abbr'] = $pinyin->abbr($value);

        //         $tDdata[] = $field;
        //     } elseif ($temp[2] != "rule") {
        //         #下拉菜单
        //         $field = [];
        //         $pId = implode("_", array_splice($temp, 0, 2));
        //         $field['pid'] = $pId;
        //         $field['sid'] = $key;
        //         $field['type'] = 'c';
        //         $field['rule'] = $params[$pId . "_rule"];

        //         $field['tid'] = $tInfo['tid'];
        //         $field['content'] = $value;
        //         $field['abbr'] = $pinyin->abbr($value);

        //         $tDdata[] = $field;
        //     }
        // }

        $tInfo['options'] = $tDdata;

        return $this->saveData($tInfo);
    }

    private function saveData($tInfo)
    {
        Db::startTrans();
        try {
            $tInfo['create_time'] = time();
            Db::name('templates')->strict(false)->json(['options'])->insertGetId($tInfo);
            Db::name("templates_sum")->where('id', 1)->setInc('count');

            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
            return "初始化失败";
        }
        return 1;
    }
}
