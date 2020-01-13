<?php


namespace app\excel\controller;

use function Complex\add;
use think\Controller;
use think\Db;
use Env;
use PHPExcel_IOFactory;
use think\Model;


class OutExcel extends Controller{

    public function index(){
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');

        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J");
        $arrHeader = array('学号','姓名','专业','班级','高中学籍','团籍','备注');
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };
        $xlsData=model("S2015")->select();
        //填充表格信息
        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['sno']);
            $objActSheet->setCellValue('B'.$k, $v['sname']);
            $objActSheet->setCellValue('C'.$k, $v['smajor']);
            $objActSheet->setCellValue('D'.$k, $v['sclass']);
            $objActSheet->setCellValue('E'.$k, $v['shigh']);
            $objActSheet->setCellValue('F'.$k, $v['stuan']);
            $objActSheet->setCellValue('G'.$k, $v['sremarks']);
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

        $width = array(10,15,20,25,30);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[1]);
        $objActSheet->getColumnDimension('B')->setWidth($width[2]);
        $objActSheet->getColumnDimension('C')->setWidth($width[3]);
        $objActSheet->getColumnDimension('D')->setWidth($width[4]);
        $objActSheet->getColumnDimension('E')->setWidth($width[1]);
        $objActSheet->getColumnDimension('F')->setWidth($width[1]);
        $objActSheet->getColumnDimension('G')->setWidth($width[1]);
        $objActSheet->getColumnDimension('H')->setWidth($width[1]);
        $objActSheet->getColumnDimension('I')->setWidth($width[1]);


        $outfile = "2015.xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
}