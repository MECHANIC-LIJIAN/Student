<?php


namespace app\excel\controller;

use think\Controller;
use think\Db;
use Env;
use PHPExcel_IOFactory;
use think\Model;

class Import extends Controller
{
    public function index()
    {
        return view();
    }

    public function upload()
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('excel');
        $fileInfo=$file->getInfo();
        // dump($fileInfo);
        //将文件保存到public/uploads目录下面
        $info = $file->validate(['size'=>1048576,'ext'=>'xls,xlsx'])->move('./uploads');
        if ($info) {
            //获取上传到后台的文件名
            $fileName = $info->getSaveName();
            //获取文件路径
            $filePath = Env::get('root_path').'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$fileName;
            //获取文件后缀
            $suffix = $info->getExtension();
            //判断哪种类型
            if ($suffix=="xlsx") {
                $reader = \PHPExcel_IOFactory::createReader('Excel2007');
            } else {
                $reader = PHPExcel_IOFactory::createReader('Excel5');
            }
        } else {
            $this->error('文件过大或格式不正确导致上传失败-_-!');
        }
        //载入excel文件
        $excel = $reader->load("$filePath", $encode = 'utf-8');
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num = $sheet->getHighestRow();
        //获取总列数
        $col_num = $sheet->getHighestColumn();
    
        // print($fileInfo['name']."的行数为".$row_num."列数为".$col_num);

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
        // dump($data['B']);
        $template           = new \app\excel\model\Templates;
        $template->tuser    = 'lijian';
        $template->tname    = 'test';
        $template->tfile    = $fileInfo['name'];
        $template->save();
        $tid=$template->id;
        
        foreach ($data as $key => $value) {
            // dump($value);
            $this->dataToTemplate($tid, $value);
        }
        return  $this->fetch('template', ['data'=>$data]);
    }


    public function dataToTemplate($tid, $data)
    {
        $dateLen=count($data);
        $option           = new \app\excel\model\TemplatesOption;
        $option->opid     = '0';
        $option->tid      = $tid;
        $option->otype     = 'p';
        $option->ocontent = $data[1];
        $res=$option->save();
        if ($dateLen>1) {
            // 获取自增ID
            $opid=$option->id;
            $data2=array();
            for ($i=2; $i <= $dateLen; $i++) {
                $line=[
                'tid'=>$tid,
                'otype'=>'c',
                'opid'=>$opid,
                'ocontent'=>$data[$i],
            ];
                $data2[$i-1]=$line;
            }
            $res=model("TemplatesOption")->isUpdate(false)->saveAll($data2);
        }
        if ($res) {
            return 1;
        }
    }
}
