<?php
namespace  app\admin\model;
use  app\admin\model\BaseModel;
/**
 * 权限规则model
 */
class AuthGroup extends BaseModel{

	/**
	 * 传递主键id删除数据
	 * @param  array   $map  主键id
	 * @return boolean       操作是否成功
	 */
	public function deleteData($map){
		$result=$this->where($map)->delete();
		if ($result) {
			$group_map=array(
				'group_id'=>$map['id']
			);
			// 删除关联表中的组数据
			$result=model('AuthGroupAccess')->deleteData($group_map);
		}
		return true;
	}



}
