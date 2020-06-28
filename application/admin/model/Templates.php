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

    #关联模板数据
    public function datas()
    {
        return $this->hasMany('TemplatesDatas', 'tid', 'id')->field('tid,id');
    }

    #关联自定义数据集
    public function getMyData()
    {
        return $this->hasOne('MyData', 'id', 'myData')->field('id,title');
    }

    #关联用户信息
    public function getUser()
    {
        return $this->belongsTo('Admin', 'uid', 'id')->field('id,username');
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
        $suffix = end(explode(".", $fileInfo['name']));


        //判断哪种类型
        if ($suffix == "xlsx") {
            $reader = new Xlsx();
        } else if ($suffix == "xls")  {
            $reader = new Xls();
        }else{
            return "不是有效的excel文件";
        }

        // $reader->setReadDataOnly(true);
        $excel = $reader->load($fileInfo['tmp_name']);

        //读取活动表
        $sheet = $excel->getActiveSheet();
        //获取总行数
        $row_num = $sheet->getHighestRow(); // 取得总行数
        //获取总列数
        $col_num = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        // if ($col_num > 25) {
        //     return "字段数大于25";
        // }
        $excelData = [];

        #获取数组形式的原始数据并存到session

        #按列读
        for ($colIndex = 1; $colIndex <= $col_num; $colIndex++) {
            if ($sheet->getCellByColumnAndRow($colIndex, 1)->getValue() == "") {
                return "不能有空字段,请重新选择文件";
            }
            #列中的每一行
            for ($rowIndex = 1; $rowIndex <= $row_num; $rowIndex++) {
                $rowCell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
                if ($rowCell !== null) {
                    $excelData[$colIndex][$rowIndex] = $rowCell;
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
        #读excel数据
        $excelData = session('excelData');

        $optionList = [];
        $tFields = [];
        for ($col = 1; $col <= count($excelData); $col++) {
            #拼接字段名
            $option = 'option_' . $col;
            $optionList[$option]['title'] = $excelData[$col][1];
            $optionList[$option]['rule'] ='required';
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
        
        foreach ($params as $key => $value) {
            #分割字段 option_A,option_A_rule
            $temp = explode("_", $key);
            if (count($temp) == 2) {
                #单选项
                $tDdata[$key]['title'] = $value;
                $tDdata[$key]['rule'] = $params[$key . "_rule"];
            } elseif ($temp[2] != "rule") {
                #多选项
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
        // halt($tInfo);
        return $this->saveData($tInfo);
    }

    private function saveData($tInfo)
    {
        Db::startTrans();
        try {
            $tInfo['create_time'] = time();
            $tid=Db::name('templates')->strict(false)->json(['options'])->insertGetId($tInfo);
            Db::name("templates_sum")->where('id', 1)->setInc('count');

            if ($tInfo['ttype']==1) {
                Db::name("word_path")->insert([
                    'tid'=>$tid,
                    'path'=>$tInfo['word_path'],
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $e->getMessage();
            return "初始化失败";
        }
        // $redis = new Redis();
        // $redisKey = 'templatelist_' . session('admin.id');
        // $redis->del($redisKey);
        // $this->getTemplates();
        return 1;
    }

    // public function getTemplates()
    // {
    //     $where = [];
    //     if (session('admin.id') >10) {
    //         $where=['uid' => session('admin.id')];
    //     }

    //     $redis = new Redis();
    //     $redisKey = 'templatelist_' . session('admin.id');
    //     //判断是否过期
    //     $redis_status = $redis->exists($redisKey);
    //     if ($redis_status == false) {
    //         //缓存失效，重新存入
    //         //查询数据
    //         $templates = model('Templates')
    //             ->where($where)
    //             ->field('id,tid,uid,tname,myData,primaryKey,create_time,status')
    //             ->withCount('datas')
    //             ->with('getUser,getMyData')
    //             ->order(['status' => 'asc', 'create_time' => 'desc'])
    //             ->select()
    //             ->toArray();

    //         foreach ($templates as $value) {
    //             $tmp = $value;
    //             $tmp['shareUrl'] = url('index/Template/readTemplate', ['id' => $value['tid']], '', true);
    //             $tmp['username'] = $tmp['get_user']['username'];
    //             $tmp['mydata'] = $tmp['get_my_data']['title'];
    //             // $value['pcon'] = json_decode($value['options'], true)[$value['primaryKey']]['title'];
    //             $redis->sAdd($redisKey, json_encode($tmp));
    //         }

    //         $redis->expire($redisKey,60);
    //     }

    //     $templates=$redis->sMembers($redisKey);

    //     return $templates;
    // }
}
