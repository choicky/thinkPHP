<?php
namespace Home\Controller;
use Think\Controller;

class ClaimController extends Controller {
    
	//默认跳转到listPage，分页显示
	public function index(){
        header("Location: listPage");
    }
	
	//默认跳转到listPage，分页显示
	public function listAll(){
        header("Location: listPage");
    }
	
	//分页显示，其中，$p为当前分页数，$limit为每页显示的记录数
	public function listPage(){
		$p	= I("p",1,"int");
		$page_limit  =   C("RECORDS_PER_PAGE");
		$claim_list = D('ClaimView')->listPage($p,$limit);
		$this->assign('claim_list',$claim_list['list']);
		$this->assign('claim_page',$claim_list['page']);
		$this->assign('claim_count',$claim_list['count']);
		
		$this->display();
	}

	//新增	
	public function add(){
		$balance_id	=	trim(I('post.balance_id'));
		
		$Model	=	D('Claim');
		if (!$Model->create()){ // 创建数据对象
			 // 如果创建失败 表示验证没有通过 输出错误提示信息
			 $this->error($Model->getError());
			 //exit($Model->getError());
		}else{
			 // 验证通过 写入新增数据
			 $result	=	$Model->add();		 
		}
		if(false !== $result){
			$this->success('新增成功', 'view/balance_id/'.$balance_id);
		}else{
			$this->error('增加失败');
		}
	}
	
	//更新	
	public function update(){
		if(IS_POST){
			$balance_id	=	trim(I('post.balance_id'));
			$claim_id	=	trim(I('post.claim_id'));
			
			$Model	=	D('Claim');
			if (!$Model->create()){ // 创建数据对象
				 // 如果创建失败 表示验证没有通过 输出错误提示信息
				 $this->error($Model->getError());
				 //exit($Model->getError());
			}else{
				 // 验证通过 写入新增数据
				 $result	=	$Model->save();		 
			}
			if(false !== $result){
				$this->success('修改成功', 'view/balance_id/'.$balance_id);
			}else{
				$this->error('修改失败');
			}
		} else{
			$claim_id = I('get.claim_id',0,'int');

			if(!$claim_id){
				$this->error('未指明要编辑的认领单号');
			}

			$claim_list = M('Claim')->getByClaimId($claim_id);
			$this->assign('claim_list',$claim_list);
			
			//取出 Member 表的内容以及数量
			$member_list	=	D('Member')->listBasic();
			$member_count	=	count($member_list);
			$this->assign('member_list',$member_list);
			$this->assign('member_count',$member_count);			
			
			//取出 CostCenter 表的内容以及数量
			$cost_center_list	=	D('CostCenter')->listBasic();
			$cost_center_count	=	count($cost_center_list);
			$this->assign('cost_center_list',$cost_center_list);
			$this->assign('cost_center_count',$cost_center_count);
			
			//取出其他变量
			$row_limit  =   C("ROWS_PER_SELECT");
			$this->assign('row_limit',$row_limit);
			
			$this->display();
		}
	}
	
	//删除
	public function delete(){
		if(IS_POST){
			
			//通过 I 方法获取 post 过来的 claim_id 和 balance_id
			$claim_id	=	trim(I('post.claim_id'));
			$balance_id	=	trim(I('post.balance_id'));
			$no_btn	=	I('post.no_btn');
			$yes_btn	=	I('post.yes_btn');

			if(1==$no_btn){
				$this->success('取消删除', U('Claim/view','balance_id='.$balance_id));
			}
			
			if(1==$yes_btn){
				
				$map['claim_id']	=	$claim_id;

				$result = M('Claim')->where($map)->delete();
				if($result){
					$this->success('删除成功', U('Claim/view','balance_id='.$balance_id));
				}
			}
			
		} else{
			$claim_id = I('get.claim_id',0,'int');

			if(!$claim_id){
				$this->error('未指明要删除的流水');
			}
			
			$claim_list = D('ClaimView')->field(true)->getByClaimId($claim_id);			
			$this->assign('claim_list',$claim_list);
			

			$this->display();
		}
	}
	//搜索
	public function search(){
		
		//取出 Member 表的基本内容，作为 options
		$member_list	=	D('Member')->listBasic();
		$this->assign('member_list',$member_list);
		
		//取出 CostCenter 表的基本内容，作为 options
		$cost_center_list	=	D('CostCenter')->listBasic();
		$this->assign('cost_center_list',$cost_center_list);
				
		//默认查询 0 元 至 20000 元
		$start_amount	=	0;
		$end_amount	=	2000000;
		$this->assign('start_income_amount',$start_amount);
		$this->assign('end_income_amount',$end_amount);
		$this->assign('start_outcome_amount',$start_amount);
		$this->assign('end_outcome_amount',$end_amount);
				
		if(IS_POST){
			
			//接收搜索参数
			$claimer_id	=	I('post.claimer_id','0','int');
			$cost_center_id	=	I('post.cost_center_id','0','int');				
			$summary	=	I('post.summary');
			$start_income_amount	=	trim(I('post.start_income_amount'))*100;
			$start_income_amount	=	$start_income_amount	?	$start_income_amount	:	'0';
			$end_income_amount	=	trim(I('post.end_income_amount'))*100;			
			$end_income_amount	=	$end_income_amount	?	$end_income_amount	:	'2000000';
			$start_outcome_amount	=	trim(I('post.start_outcome_amount'))*100;
			$start_outcome_amount	=	$start_outcome_amount	?	$start_outcome_amount	:	'0';
			$end_outcome_amount	=	trim(I('post.end_outcome_amount'))*100;			
			$end_outcome_amount	=	$end_outcome_amount	?	$end_outcome_amount	:	'2000000';
			
			//构造 maping
			if($claimer_id){
				$map['claimer_id']	=	$claimer_id;
			}
			if($cost_center_id){
				$map['cost_center_id']	=	$cost_center_id;
			}
			if($summary){
				$map['summary']	=	$summary;
			}			
			
			$map['income_amount']	=	array('between',array($start_income_amount,$end_income_amount));
			$map['outcome_amount']	=	array('between',array($start_outcome_amount,$end_outcome_amount));
			
			$p	= I("p",1,"int");
			$page_limit  =   C("RECORDS_PER_PAGE");
			$claim_list = D('ClaimView')->where($map)->listPage($p,$page_limit);
			$claim_count = count($claim_list['list']);
			$this->assign('claim_list',$claim_list['list']);
			$this->assign('claim_page',$claim_list['page']);
			$this->assign('claim_count',$claim_count);
			
			//返回搜索参数
			$this->assign('claimer_id',$claimer_id);
			$this->assign('cost_center_id',$cost_center_id);
			$this->assign('summary',$summary);
			$this->assign('start_income_amount',$start_income_amount);
			$this->assign('end_income_amount',$end_income_amount);
			$this->assign('start_outcome_amount',$start_outcome_amount);
			$this->assign('end_outcome_amount',$end_outcome_amount);
		
		} 
	
	$this->display();
	}
	
	//查看主键为 $balance_id 的收支流水的所有 claim
	public function view(){
		$balance_id = I('get.balance_id',0,'int');

		if(!$balance_id){
			$this->error('未指明要查看的收支流水');
		}

		$balance_list = D('Balance')->relation(true)->field(true)->getByBalanceId($balance_id);			
		$this->assign('balance_list',$balance_list);
		
		
		//取出内部结算单的数量
		$claim_count	=	count($balance_list['Claim']);
		$this->assign('claim_count',$claim_count);		
		
		
		//取出 Member 表的内容以及数量
		$member_list	=	D('Member')->listBasic();
		$member_count	=	count($member_list);
		$this->assign('member_list',$member_list);
		$this->assign('member_count',$member_count);
				
		//取出 CostCenter 表的内容以及数量
		$cost_center_list	=	D('CostCenter')->listBasic();
		$cost_center_count	=	count($cost_center_list);
		$this->assign('cost_center_list',$cost_center_list);
		$this->assign('cost_center_count',$cost_center_count);
		
		//取出其他变量
		$row_limit  =   C("ROWS_PER_SELECT");
		$today	=	time();
		$this->assign('row_limit',$row_limit);
        $this->assign('today',$today);


		$this->display();
	}
}