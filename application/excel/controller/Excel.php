<?php


namespace app\excel\controller;

use function Complex\add;
use think\Controller;
use think\Db;
use Env;
use PHPExcel_IOFactory;
use think\Model;

class Excel extends Controller
{
    public function excel()
    {
        return $this->fetch();
    }

    public function importExcel()
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('excel');
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
        $row_num = $sheet->getHighestRowAndColumn(1);
        //获取总列数
        $col_num = $sheet->getHighestColumn();
        $data = []; //数组形式获取表格数据
        $n=0;
        for ($i=9; $i <=$row_num; $i++) {
            $data['a'] = $sheet->getCell("A".$i)->getValue();
            $data['b'] = $sheet->getCell("B".$i)->getValue();
            $data['c'] = $sheet->getCell("C".$i)->getValue();
            $data['d'] = $sheet->getCell("D".$i)->getValue();
            $data['e'] = $sheet->getCell("E".$i)->getValue();
            $data['f'] = $sheet->getCell("F".$i)->getValue();
            $data['g'] = $sheet->getCell("G".$i)->getValue();
            $data['h'] = $sheet->getCell("H".$i)->getValue();
            $data['i'] = $sheet->getCell("I".$i)->getValue();
            $data['j'] = $sheet->getCell("J".$i)->getValue();
            if ($data['a']) {
                $res=model("Dui")->insert($data);
            }
        }
        return $n;
    }
    public function importChaxun()
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('excel');
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
        $row_num = $sheet->getHighestRowAndColumn(1);
        //获取总列数
        $col_num = $sheet->getHighestColumn();
        $data = []; //数组形式获取表格数据
        $n=0;
        for ($i=3; $i <=$row_num; $i++) {
            $data['a'] = $sheet->getCell("A".$i)->getValue();
            $data['b'] = $sheet->getCell("B".$i)->getValue();
            $data['c'] = $sheet->getCell("C".$i)->getValue();
            $data['d'] = $sheet->getCell("D".$i)->getValue();
            $data['e'] = $sheet->getCell("E".$i)->getValue();
            $data['f'] = $sheet->getCell("F".$i)->getValue();
            $data['g'] = $sheet->getCell("G".$i)->getValue();
            $data['h'] = $sheet->getCell("H".$i)->getValue();
            $data['i'] = $sheet->getCell("I".$i)->getValue();
            $data['j'] = $sheet->getCell("J".$i)->getValue();
            if ($data['a']) {
                $res=model("Cha")->insert($data);
            }
        }
        return $n;
    }
    

    public function delExcel()
    {
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = request()->file('excel');
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
        $row_num = $sheet->getHighestRowAndColumn(1);
        //获取总列数
        $col_num = $sheet->getHighestColumn();
        $data = []; //数组形式获取表格数据
        for ($i=2; $i <=$row_num; $i++) {
            $data['sno'] = $sheet->getCell("B".$i)->getValue();
            //将数据保存到数据库
            $res=model("Doc")->where('sno', $data['sno'])->delete();
        }
    }

    public function unique()
    {
        $sql="select * from stu_cha where not EXISTS(select * from stu_dui where stu_cha.b=stu_dui.a)";
        $sql2="select * from stu_dui where stu_cha.a=stu_dui.a";
        $res=Db::query($sql);
        $name=array();
        foreach ($res as $re) {
            array_push($name, $re['b']);
        }
        dump($name);
    }

    public function reset()
    {
        Db::execute('truncate table `stu_dui`');
        Db::execute('truncate table `stu_cha`');
    }
}
