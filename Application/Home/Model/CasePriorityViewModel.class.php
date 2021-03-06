<?php
// +----------------------------------------------------------------------
// | This project is based on ThinkPHP 3.2, created by Choicky ZHOU (zhoucaiqi@gmail.com).
// +----------------------------------------------------------------------
// | Choicky ZHOU is a lawyer in China, specialized in IP matters such as patent, trademark and copyright.
// +----------------------------------------------------------------------
// | "Think\Model" is for normal Model, "Think\Model\RelationModel" for relation Model, "Think\Model\ViewModel" for view Model.
// +----------------------------------------------------------------------
// | This file is required by: CaseModel, CasePriorityController
// +----------------------------------------------------------------------

namespace Home\Model;

//因为启动数据表视图模型，必须继承ViewModel，注释Model
//use Think\Model;
use Think\Model\ViewModel;

class CasePriorityViewModel extends ViewModel {
	
	//定义本表与其他数据表的视图关系
	protected $viewFields = array(
		
		//定义本表与 CasePriority 表的视图关系
		'CasePriority'	=>	array(
			'case_priority_id',
			'case_id',
			'priority_number',
			'priority_country_id',
			'priority_date',
			'_type'=>'LEFT'
		),
		
		//定义本表与 Country 表的视图关系
		'Country'	=>	array(
			'country_name',
			'_on'	=>	'Country.country_id=CasePriority.priority_country_id'
		),
		
		//定义本表与 Case 表的视图关系
		'CaseInfo'	=>	array(
			'our_ref',			
			'client_ref',
			'_type'=>'LEFT',
			'_table'=>"__CASE__",	//定义数据表
			'_on'	=>	'CaseInfo.case_id=CasePriority.case_id'
		),

	);
	
			
	//返回本数据视图的基本数据
	public function listBasic() {
		$list	=	$this->listAll();
		return $list;
	}
	
	//返回本数据视图的所有数据
	public function listAll() {
		$order['priority_date']	=	'asc';
		$list	=	$this->order($order)->select();
		return $list;
	}

	
	//分页返回本数据视图的所有数据，$p为当前页数，$limit为每页显示的记录条数
	public function listPage($p,$limit) {
		$order['priority_date']	=	'asc';	
		$list	=	$this->order($order)->page($p.','.$limit)->select();
		
		$count	= $this->count();
		
		$Page	= new \Think\Page($count,$limit);
		$show	= $Page->show();
		
		return array("list"=>$list,"page"=>$show, "count"=>$count);
	}
}