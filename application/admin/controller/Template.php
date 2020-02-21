<?php

namespace app\admin\controller;

class Template extends Base
{

    public function index()
    {
        $tId = input('id');
        session('tId', $tId);
        $template = model('Templates')
            ->where(['tid' => $tId])
            ->field('tname')
            ->find();

        $fields = model('TemplatesOption')
            ->where(['tid' => $tId, 'type' => 'p'])
            ->field('sid,title')
            ->select();
        $templateField = [];
        foreach ($fields as $key => $value) {
            $value['field'] = $value['sid'];
            $value['sortable'] = true;
            array_push($templateField, $value['sid']);
        }
        array_push($templateField, 'create_time');
        array_push($templateField, 'update_time');
        array_push($templateField, 'id');
        session('options', $templateField);

        $fields = json_decode($fields, $assoc = false);
        array_unshift($fields, [
            'checkbox' => true,
        ]);
        array_unshift($fields, [
            'field' => 'id',
            'title' => '编号',
            'visible' => false, //这一列隐藏
        ]);
        array_push($fields, [
            'field' => 'create_time',
            'title' => '首次提交时间',
            'sortable' => true,
        ]);
        array_push($fields, [
            'field' => 'update_time',
            'title' => '最后提交时间',
            'sortable' => true,
        ]);

        $shareUrl = url('index/Template/readTemplate', ['id' => $tId], '', true);
        $this->assign([
            'template' => $template,
            'fields' => json_encode($fields, JSON_UNESCAPED_UNICODE),
            'shareUrl' => $shareUrl,
        ]);
        return view();
    }

    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {

        return view();
    }

    /**
     * 删除
     * @return \think\response\View
     */
    public function del()
    {
        if (request()->isAjax()) {
            // $tInfo = model('Templates')->with('options,datas')->find(input('post.id'));
            // $result = $tInfo->together('options,datas')->delete();
            // if ($result == 1) {
            //     $this->success('任务删除成功', 'admin/Templates/list');
            // } else {
            //     $this->error("任务删除失败");
            // }
            $ids=input('ids');
            return explode(',',$ids);
        }
    }

   

    public function dataList()
    {
        if (request()->isAjax()) {
            $tId = session('tId');
            $fields = session('options');

            $offset = input('post.offset');
            $limit = input('post.limit');
            $order = input('post.order');
            $ordername = input('post.ordername');
            $search = input('post.search');

            #排序字段和规则
            if ($ordername) {
                $order = [$ordername => $order, 'update_time' => 'desc'];
            } else {
                $order = ['update_time' => 'desc'];
            }

            #搜索条件
            $map[] = ['tid', '=', $tId];
            #模糊搜索
            if ($search != "") {
                #删除日期字段
                $temp_fields = $fields;
                array_pop($temp_fields);
                array_pop($temp_fields);
                $map[] = [implode("|", $temp_fields), 'like', "%$search%"];
            }

            #判断是否为导出
            if ($limit != null) {
                # 计算 页号
                $page = floor($offset / $limit) + 1;
                $list = model('TemplatesData')
                    ->where($map)
                    ->order($order)
                    ->field($fields)
                    ->page($page, $limit)
                    ->withAttr([
                        'create_time' => function ($value, $data) {
                            return date("Y-m-d H:i", $value);
                        },
                        'update_time' => function ($value, $data) {
                            return date("Y-m-d H:i", $value);
                        },
                    ])
                    ->select();
            } else {
                $list = model('TemplatesData')
                    ->where($map)
                    ->field($fields)
                    ->withAttr([
                        'create_time' => function ($value, $data) {
                            return date("Y-m-d H:i", $value);
                        },
                        'update_time' => function ($value, $data) {
                            return date("Y-m-d H:i", $value);
                        },
                    ])
                    ->select();
            }

            // dump($list);

            $count = model('TemplatesData')
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
