<?php

namespace app\admin\controller;

use PhpOffice\PhpWord\PhpWord;
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

        $wordFile = Db::name('word_path')->where(['tid' => $template['id']])->column('path');

        $tmpdir = dirname($wordFile[0]);
        if (!file_exists($tmpdir)) {
            @mkdir($tmpdir, 0777, true);
        }
        // halt($tmpdir);
        \PhpOffice\PhpWord\Settings::setTempDir($tmpdir);
        // \PhpOffice\PhpWord\Settings::setZipClass(\PhpOffice\PhpWord\Settings::PCLZIP);

        $savedir = env('ROOT_PATH').'public/uploads/word/save/' . md5($tId);
        if (!file_exists($savedir)) {
            @mkdir($savedir, 0777, true);
        }

        dump($wordFile[0]);

        $PHPWord = new PhpWord();

        foreach ($list as $record) {
            $word = $PHPWord->loadTemplate(env('ROOT_PATH')."public/".$wordFile[0]);
            unset($record['content']['option_1']);
            foreach ($record['content'] as $k => $v) {
                dump($k);
            }
            $word->setValue('ketiname', '测试题目');
            $word->setValues($record['content']);
            # 保存文件
            # 生成临时文件以供下载
            $tmpFileName = $savedir . "/" . $record['id'] . ".docx";

            dump($tmpFileName);
            $word->saveAs($tmpFileName);
        }
    }
}
