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
    public function ifUseData($template, $content)
    {
        $mydata = Db::name('my_data_option')
            ->where([
                'my_data_id' => $template['myData'],
                'content' => $content,
            ])
            ->find();

        if (!$mydata) {
            return "系统中未匹配到:" . $template['primaryKey']['content'] . "=" . $content;
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
        $res = model('TemplatesData')
            ->where([
                'tid' => $template['tid'],
                $template['primaryKey']['sid'] => $keyContent,
            ])
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
            $data['create_time']=time();
            Db::name('templates_data')->insert($data);
            #记录数加1
            Db::name('templates')
                ->where('tid', $template['tid'])
                ->setInc('count');
            // 提交事务
            $res = Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return '提交失败！';
        }
        return 1;
    }
}
