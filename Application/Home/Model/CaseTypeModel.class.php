<?php
namespace Home\Model;

use Think\Model;
//因为不需要数据表关联，注释RelationModel
//use Think\Model\RelationModel;

class CaseTypeModel extends Model {
	
	//返回本数据表的所有数据
	public function listAll() {
		$Model	=	M('CaseType');
		$order['convert(case_type_name using gb2312)']	=	'asc';
		$list	=	$Model->order($order)->select();
		return $list;
	}
		
	//返回本数据表的基本数据
	public function listBasic() {
		$list	=	$this->listAll();
		return $list;
	}
	
	//返回本数据表的 case_type_name 含有 $p 所有数据
	public function listAllLike($p) {
		$Model	=	M('CaseType');
		$map['case_type_name']	=	array('like', $p);
		$order['convert(case_type_name using gb2312)']	=	'asc';
		$list	=	$Model->field(true)->where($map)->order($order)->select();
		return $list;
	}
		
	//返回本数据表的 case_type_name 含有 $p 所有数据
	public function listBasicLike($p) {
		$list	=	$this->listAllLike($p);
		return $list;
	}
	
	//分页返回本数据表的所有数据，$current_page 为当前页数，$recodes_per_page 为每页显示的记录条数
	public function listPage($current_page,$recodes_per_page) {
		$Model	=	M('CaseType');
		$order['convert(case_type_name using gb2312)']	=	'asc';
		$list	= $Model->field(true)->order($order)->page($current_page.','.$recodes_per_page)->select();
		
		$count	= $Model->count();
		
		$Page	= new \Think\Page($count,$recodes_per_page);
		$show	= $Page->show();
		
		return array("list"=>$list,"page"=>$show);
	}
	
	//更新本数据表中主键为$case_type_id的记录，$data是数组
	public function edit($case_type_id,$data){
		$Model	=	M('CaseType');
		$map['case_type_id']	=	$case_type_id;
		$result	=	$Model->where($map)->save($data);
		return $result;
	}

}