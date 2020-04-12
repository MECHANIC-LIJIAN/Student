<?php
namespace app\admin\model;
use  app\admin\model\BaseModel;
use think\Model;

/**
 * 权限规则model
 */
class AuthRule extends BaseModel{

	/**
	 * 删除数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function deleteData($map){
		$count=$this
			->where(array('pid'=>$map['id']))
			->count();
		if($count!=0){
			return false;
		}
		$result=$this->where($map)->delete();
		return $result;
	}




}
