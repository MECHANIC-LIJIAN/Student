<?php

namespace app\admin\controller;

use think\Db;

class Template extends Base
{

    public function index()
    {
        $tId = input('id');
        
        $template = model('Templates')
            ->where(['tid' => $tId])
            ->field('id,tid,tname,primaryKey,myData,options')
            // ->json(['options'])
            ->find();

        #显示在页面的字段
        $fields[] = [
            'checkbox' => true,
        ];
        $fields[] = [
            'field' => 'id',
            'title' => '编号',
            'visible' => false, //这一列隐藏
        ];

        $template['options'] = json_decode($template['options'], true);
        
        #查询字段
        foreach ($template['options'] as $key => $value) {
            $tmp = [];
            $tmp['field'] ='content.'. $key;
            $tmp['title'] = $value['title'];
            if (!empty($template['primaryKey'])&&$key==$template['primaryKey']) {
                $tmp['sortable'] = true;
            }
            $fields[] = $tmp;
        }

        $fields[] = [
            'field' => 'create_time',
            'title' => '首次提交时间',
            'sortable' => true,
        ];
        $fields[] = [
            'field' => 'update_time',
            'title' => '最后提交时间',
            'sortable' => true,
        ];
        
        $fields = json_encode($fields);

        unset($template['options']);

        session('tInfo', $template);

        #构造分享链接
        $shareUrl = url('index/Template/readTemplate', ['id' => $tId], '', true);

        $this->assign([
            'template' => $template,
            'fields' => $fields,
            'shareUrl' => $shareUrl,
            'tId' => $tId,
        ]);
        return view();
    }

    /**
     * 删除表单数据
     * @return \think\response\View
     */
    public function del()
    {
        if (request()->isAjax()) {
            
            $tId = session('tInfo.id');
            $ids = explode(',', input('ids'));
            sort($ids, SORT_NUMERIC);
            $result = model('TemplatesDatas')->destroy($ids);
            // $t = model("Templates")->where('id', $tId)->field('count')->find();
            // $t->count = $t->count - count($ids);
            // $result = $t->save();
            if ($result) {
                $this->success('数据删除成功');
            } else {
                $this->error("数据删除失败");
            }
        }
    }

    /**
     * 请求数据
     */

    public function dataList()
    {
        if (request()->isAjax()) {
            $template=session('tInfo');
            $tId = $template['id'];
            $primaryKey=$template['primaryKey'];
            $offset = input('post.offset');
            $limit = input('post.limit');
            $order = input('post.order');
            $ordername = input('post.ordername');
            $search = input('post.search');

            #默认排序字段和规则
            $fields=['id,tid,content,create_time,update_time'];
            $orders = ['update_time' => 'desc'];

            #排序字段和规则
            if ($ordername) {
                if ($ordername=='content.'.$primaryKey) {
                    array_push($fields,"JSON_EXTRACT(content,'$.$primaryKey') as jsonOrder");
                    $orders = ['jsonOrder' => $order];
                } else {
                    $orders = [$ordername => $order];
                }
            }
            
            #默认搜索条件
            $map[] = ['tid', '=', $tId];
            #模糊搜索
            if ($search != "") {
                $map[] = ["content->$primaryKey", 'like', "%$search%"];
            }

            #判断是否为导出
            if ($limit != null) {
                # 计算页号
                $page = floor($offset / $limit) + 1;
                $list = model('TemplatesDatas')
                    ->where($map)
                    ->json(['content'])
                    ->field($fields)                    
                    ->order($orders)
                    ->page($page, $limit)
                    ->withAttr([
                        'create_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                        'update_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                    ])
                    ->select();
            } else {
                $list = model('TemplatesDatas')
                    ->where($map)
                    ->json(['content'])
                    ->field($fields)
                    ->withAttr([
                        'create_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                        'update_time' => function ($value) {
                            return date("Y-m-d H:i", $value);
                        },
                    ])
                    ->select();
            }

            $count = model('TemplatesDatas')
                ->where(['tid' => $tId])
                ->where($map)
                ->count();
            $res = [
                'total' => $count,
                'rows' => $list,
            ];
            # 返回JSON数据
            return $res;
        }
    }
}
