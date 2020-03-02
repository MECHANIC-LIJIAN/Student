<?php

namespace app\admin\model;

use Overtrue\Pinyin\Pinyin;
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
        return $this->hasMany('TemplatesData', 'tid', 'tid');
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
        if ($col_num>'Y') {
            return "字段数大于25";
        }
        $excelData = [];

        #获取数组形式的原始数据并存到session
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

        #获取最后一个字段的键值
        end($excelData);
        $finalKey = key($excelData);

        $optionList = [];
        $tFields = [];
        $pinyin = new Pinyin();

        for ($col = 'A'; $col <= $finalKey; $col++) {
            $option = 'option_' . $col;
            for ($row = 1; $row <= count($excelData[$col]); $row++) {
                $tmp = [];
                $tmp['tid'] = $tInfo['tid'];
                $tmp['content'] = $excelData[$col][$row];
                $tmp['abbr'] = $pinyin->abbr($excelData[$col][1]);
                if ($row == 1) {
                    $tmp['pid'] = '0';
                    $tmp['sid'] = $option;
                    $tmp['type'] = 'p';
                    $tFields[$option] = $excelData[$col][1];
                } else {
                    $tmp['pid'] = $option;
                    $tmp['sid'] = $option . "_" . $row;
                    $tmp['type'] = 'c';
                }
                $optionList[] = $tmp;
            }
        }
        #存储可存入数据库的数据
        session('tData', $optionList);

        return [
            'tFields' => $tFields,
            'optionList' => $optionList,
        ];
    }

    public function createByFile($tInfo)
    {
        $template = model('Templates')
            ->where('tid', $tInfo['tid'])
            ->find();

        if ($template['status'] == '1') {
            return "表单已经初始化，不可更改";
        }

        $pinyin = new Pinyin();
        $tInfo['tabbr'] = $pinyin->abbr($tInfo['tname']);
        $tInfo['tData'] = session('tData');
        session('tData',null);
        $res = $this->saveData($tInfo);

        return $res;
    }

    public function createByHand($tInfo)
    {
        $params=$tInfo['params'];
        unset($tInfo['params']);
        $tDdata = [];
        $pinyin = new Pinyin();

        foreach ($params as $key => $value) {
            #分割字段 option_A,option_A_rule
            $temp = explode("_", $key);
            
            if (count($temp) == 2) {
                
                #输入框
                $field = [];
                $field['pid'] = '0';
                $field['sid'] = $key;
                $field['type'] = 'p';
                $field['rule'] = $params[$key . "_rule"];
                
                $field['tid'] = $tInfo['tid'];
                $field['content'] = $value;
                $field['abbr'] = $pinyin->abbr($value);

                $tDdata[] = $field;
            } elseif ($temp[2] != "rule") {
                #下拉菜单
                $field = [];
                $pId = implode("_", array_splice($temp, 0, 2));
                $field['pid'] = $pId;
                $field['sid'] = $key;
                $field['type'] = 'c';
                $field['rule'] = $params[$pId . "_rule"];
                
                $field['tid'] = $tInfo['tid'];
                $field['content'] = $value;
                $field['abbr'] = $pinyin->abbr($value);

                $tDdata[] = $field;
            }
        }
        
        $tInfo['tData']=$tDdata;
        
        return $this->saveData($tInfo);
    }

    private function saveData($tInfo)
    {
        Db::startTrans();
        try {
            $tInfo['create_time']=time();
            $tId = Db::name('templates')->strict(false)->insertGetId($tInfo);
            
            $tData=[];
            foreach ($tInfo['tData'] as $v) {
                $tmp=[];
                $tmp=$v;
                $tmp['create_time']=time();
                $tData[]=$tmp;
            }
            Db::name("templates_option")->insertAll($tData);
            Db::name("templates_sum")->where('id', 1)->setInc('count');

            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return "初始化失败";
        }
        return 1;
    }
}
