<?php

namespace app\index\model;

use app\common\CommonModel;
use myredis\Redis;
use think\Db;
use think\facade\Log;
use think\Model;
use think\model\concern\SoftDelete;

class Templates extends CommonModel
{
    use SoftDelete;

    /**
     * 判断数据是否在自定义数据集中
     *
     * @param [type] $template
     * @param [type] $content
     * @return void
     */
    public function ifUseData($template, $keyContent)
    {
        $redisKey = "my_data_" . $template['myData'];
        $redis = new Redis();
        //判断是否过期
        $redis_status = $redis->exists($redisKey);
        if ($redis_status == false) {
            //缓存失效，重新存入
            //查询数据
            $myDatas = Db::name('my_data_option')
                ->where([
                    'my_data_id' => $template['myData'],
                ])
                ->field('my_data_id,content')
                ->select();

            $myDatas = array_column($myDatas, 'content');
            //转换成字符串，有利于存储
            $redisInfo = serialize($myDatas);
            //存入缓存
            $redis->set($redisKey, $redisInfo);
            //设置缓存周期，60*10秒
            $redis->expire($redisKey, 60*10);
        }
        //获取缓存
        $myDatas = unserialize($redis->get($redisKey));

        $res = in_array($keyContent, $myDatas);
        if (!$res) {
            // return $template;
            return "系统中未匹配到:" . $template['primaryKey']['title'] . "=" . $keyContent;
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
        $keySid = $template['primaryKey']['field'];
        $res = model('TemplatesDatas')
            ->json(['cotent'])
            ->where([
                'tid' => $template['id'],
                "content->$keySid" => $keyContent,
            ])
            ->field('id,tid,content')
            ->find();
        if ($res) {
            #数据已存在
            cookie('dataid', $res['id']);
            return $res['id'];
        }
        return 0;

    }

    /**
     * 保存新数据
     *
     * @return void
     */
    public function postData($data)
    {
        $dataKey = 'datalists';

        $redis = new Redis();

        $nLen = $redis->lPush($dataKey, serialize($data));

        if ($nLen > 0) {
            return 1;
        } else {
            Log::warning("--未使用redis");
            // 启动事务
            Db::startTrans();
            try {
                model('TemplatesDatas')->save($data);
                
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                // return $e->getMessage();
                Log::error($e->getMessage());
                return '提交失败！';
            }
            return 1;
        }
    }


    /**
     * 保存新数据
     *
     * @return void
     */
    public function updatePostData($data)
    {
        $dataKey = 'updatedatalists';

        $redis = new Redis();

        $nLen = $redis->lPush($dataKey, serialize($data));

        if ($nLen > 0) {
            return 1;
        } else {
            Log::warning("--未使用redis");
            // 启动事务
            Db::startTrans();
            try {
                model('TemplatesDatas')->allowField(true)->save($this->data, ['id' => cookie('dataid')]);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                // return $e->getMessage();
                Log::error($e->getMessage());
                return '提交失败！';
            }
            return 1;
        }
    }
}
