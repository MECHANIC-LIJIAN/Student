<?php

namespace app\admin\controller;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use think\Db;

class Word extends Base
{

    public function export_word()
    {

        $tId = input('tId');

        $template = model('Templates')
            ->where(['tid' => $tId])
            ->field('id,tid,tname')
            // ->json(['options'])
            ->find();

        #默认排序字段和规则
        $fields = ['id,tid,content,create_time,update_time'];
        $orders = ['update_time' => 'desc'];

        #默认搜索条件
        $map[] = ['tid', '=', $template['id']];

        $list = Db::name('TemplatesDatas')
            ->where($map)
            ->json(['content'])
            ->field($fields)
            ->order($orders)
            ->select();

        #模板文件路径
        $orgFile = Db::name('word_path')->where(['tid' => $template['id']])->column('path');
        $orgFile=$orgFile[0];

        #设置临时文件目录
        $tmpdir = dirname($orgFile);
        \PhpOffice\PhpWord\Settings::setTempDir($tmpdir);

        #设置压缩方式
        \PhpOffice\PhpWord\Settings::setZipClass(Settings::PCLZIP);
        $savedir = env('ROOT_PATH').'public/uploads/word/save/' . md5($tId);
        $savedir = 'uploads/word/save/' . md5($tId);
        if (!file_exists($savedir)) {
            @mkdir($savedir, 0777, true);
        }

        $wordFiles=[];

        $PHPWord = new PhpWord();

        foreach ($list as $record) {
            $word = $PHPWord->loadTemplate($orgFile);
            // unset($record['content']['option_1']);
            $word->setValues($record['content']);
            # 保存文件
            $tmpFileName ="/". $savedir . "/" . $record['content']['option_1'] . ".docx";
            array_push($wordFiles,$tmpFileName);
            
            $word->saveAs($tmpFileName);
        }

        $zipFileName=$savedir."/".$template['tname'].".zip";
        // dump($wordFiles);
        // dump($zipFileName);
        // halt(unlink($zipFileName));

        createZip($wordFiles,$zipFileName);

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($zipFileName)); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: ' . filesize($zipFileName)); //告诉浏览器，文件大小
        ob_clean();
        flush();
        @readfile($zipFileName);
        unlink($zipFileName);
    }
}
