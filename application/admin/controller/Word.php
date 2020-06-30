<?php

namespace app\admin\controller;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
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

        #查询数据
        $list = Db::name('TemplatesDatas')
            ->where($map)
            ->json(['content'])
            ->field($fields)
            ->order($orders)
            ->select();

        #查询word模板
        $orgFile = Db::name('word_path')->where(['tid' => $template['id']])->column('path');
        $orgFile = $orgFile[0];

        #设置phpword临时目录
        $tmpdir = env('ROOT_PATH') . 'public/uploads/word/tmp/';
        if (!file_exists($tmpdir)) {
            @mkdir($tmpdir, 0777, true);
        }
        Settings::setTempDir($tmpdir);
        // Settings::setZipClass(Settings::PCLZIP);

        #设置word保存目录
        $savedir = env('ROOT_PATH') . 'public/uploads/word/save/' . md5($tId);
        // $savedir = 'uploads/word/save/' . md5($tId);
        if (!file_exists($savedir)) {
            @mkdir($savedir, 0777, true);
        }

        try {
            $wordFiles = [];

            $PHPWord = new PhpWord();

            foreach ($list as $record) {
                $word = $PHPWord->loadTemplate($orgFile);
                // unset($record['content']['option_1']);

                $word->setValues($record['content']);
                # 保存文件
                $wordFileName = $savedir . "/" . $record['content']['option_1'] . ".docx";
                array_push($wordFiles, $wordFileName);
                $word->saveAs($wordFileName);
            }
        } catch (\Exception $e) {
            return "生成word失败";
        }

        try {
            $zipFileName = $savedir . "/" . $template['tname'] . ".zip";
            $res = createZip($wordFiles, $zipFileName, $scene = 'word');
        } catch (\Exception $e) {
            return "文件打包失败";
        }

        unlink($savedir);
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
