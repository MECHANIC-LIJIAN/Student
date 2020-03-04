<?php

namespace app\index\model;

use think\Db;
use think\Model;
use think\model\concern\SoftDelete;

class Templates extends Model
{
    use SoftDelete;

    public function options()
    {
        return $this->hasMany('TemplatesOption', 'tid', 'tid')->field('tid,sid,pid,content,rule');
    }

    /**
     * 判断数据是否在自定义数据集中
     *
     * @param [type] $template
     * @param [type] $content
     * @return void
     */
    public function ifUseData($template, $keyContent)
    {
        $mydata = Db::name('my_data_option')
            ->where([
                'my_data_id' => $template['myData'],
                'content' => $keyContent,
            ])
            ->find();
            
        if (!$mydata) {
            return "系统中未匹配到:" . $template['options'][$template['primaryKey']]['title'] . "=" . $keyContent;
        }
        return 1;

    }

    /**
     * 判断数据是否存在
     *
     * @param [type] $template
     * @param [type] $keyContent
     * @return void
     */
    public function ifExist($template, $keyContent)
    {
        $keySid=$template['primaryKey'];
        $res = model('TemplatesDatas')
            ->json(['cotent'])
            ->where([
                'tid' => $template['tid'],
                "content->$keySid" => $keyContent
            ])
            ->field('id,tid,content')
            ->find();
        if ($res) {
            #数据已存在
            cookie('ifCheck', 1);
            cookie('dataid', $res['id']);
            return 1;
        }
        return 0;

    }
    /**
     * 保存新数据
     *
     * @return void
     */
    public function saveData($template, $data)
    {
        // 启动事务
        Db::startTrans();
        try {
            model('TemplatesDatas')->save($data);
            #记录数加1
            model('templates')
                ->where('tid', $template['tid'])
                ->setInc('count');
            // 提交事务
           Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
            return '提交失败！';
        }
        return 1;
    }
}
