<?php
namespace app\admin\model;

use app\common\CommonModel;
use think\Model;

/**
 * 基础model
 */
class BaseModel extends Model
{

    public function getProfile()
    {
        return $this->hasOne('Admin','id','uid')->field('id,username');
    }

    /**
     * 添加数据
     * @param  array $data  添加的数据
     * @return int          新增的数据id
     */
    public function addData($data)
    {
        // 去除键值首尾的空格
        foreach ($data as $k => $v) {
            $data[$k] = trim($v);
        }
        $id = $this->save($data);
        return $id;
    }

    /**
     * 修改数据
     * @param   array   $map    where语句数组形式
     * @param   array   $data   数据
     * @return  boolean         操作是否成功
     */
    public function editData($map, $data)
    {
        // 去除键值首位空格
        foreach ($data as $k => $v) {
            $data[$k] = trim($v);
        }
        $result = $this->save($data,$map);
        return $result;
    }

    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map)
    {
        if (empty($map)) {
            die('where为空的危险操作');
        }
        $result = $this->where($map)->delete();
        return $result;
    }

    /**
     * 数据排序
     * @param  array $data   数据源
     * @param  string $id    主键
     * @param  string $order 排序字段
     * @return boolean       操作是否成功
     */
    public function orderData($data, $id = 'id', $order = 'order_number')
    {
        foreach ($data as $k => $v) {
            $v = empty($v) ? null : $v;
            $this->where(array($id => $k))->save(array($order => $v));
        }
        return true;
    }
}
