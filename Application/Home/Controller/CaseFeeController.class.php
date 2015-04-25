<?php
namespace Home\Controller;
use Think\Controller;

class CaseFeeController extends Controller {
    
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
		$case_fee_list = D('CaseFeeView')->listPage($p,$limit);
		$this->assign('case_fee_list',$case_fee_list['list']);
		$this->assign('case_fee_page',$case_fee_list['page']);
		$this->assign('case_fee_count',$case_fee_list['count']);
		
		$this->display();
	}
	
	//分页显示，其中，$p为当前分页数，$limit为每页显示的记录数
	public function listPagePatentFee(){
		$p	= I("p",1,"int");
		$page_limit  =   C("RECORDS_PER_PAGE");
		
		//获取专利的 case_type_id 集合
		$case_type_list	=	D('CaseType')->listPatentCaseTypeId();
		$map_case_fee['case_type_id']  = array('in',$case_type_list);
		
		$case_fee_list = D('CaseFeeView')->listPageSearch($p,$page_limit,$map_case_fee);
		$this->assign('case_fee_list',$case_fee_list['list']);
		$this->assign('case_fee_page',$case_fee_list['page']);
		$this->assign('case_fee_count',$case_fee_list['count']);
		
		$this->display();
	}
	
	//分页显示，其中，$p为当前分页数，$limit为每页显示的记录数
	public function listPageNotPatentFee(){
		$p	= I("p",1,"int");
		$page_limit  =   C("RECORDS_PER_PAGE");
		
		//获取专利的 case_type_id 集合
		$case_type_list	=	D('CaseType')->listNotPatentCaseTypeId();
		$map_case_fee['case_type_id']  = array('in',$case_type_list);
		
		$case_list	=	M('Case')->where($map_case_fee)->select();
		print_r($case_type_list);
		
		$case_fee_list = D('CaseFeeView')->listPageSearch($p,$page_limit,$map_case_fee);
		$this->assign('case_fee_list',$case_fee_list['list']);
		$this->assign('case_fee_page',$case_fee_list['page']);
		$this->assign('case_fee_count',$case_fee_list['count']);
		
		$this->display();
	}

	//新增	
	public function add(){
		$case_id	=	trim(I('post.case_id'));
		$map_case['case_id']	=	$case_id;
		$condition_case	=	M('Case')->where($map_case)->find();
		if(!is_array($condition_case)){
			$this->error('案件编号不正确');
		}
		
		$Model	=	D('CaseFee');
		if (!$Model->create()){ 
			
			 // 如果创建数据对象失败 表示验证没有通过 输出错误提示信息
			 $this->error($Model->getError());

		}else{
			 
			 // 验证通过 写入新增数据
			 $result	=	$Model->add();		 
		}
		if(false !== $result){
			
			// 写入新增数据成功，返回案件信息页面
			$this->success('新增成功', U('CaseFee/view','case_id='.$case_id));
			
		}else{
			$this->error('增加失败');
		}
	}
	
	//批量新增年费	
	public function addMoreAnnualFee(){
		
		//接收参数
		$case_id	=	trim(I('post.case_id'));
		$year_number	=	trim(I('post.year_number'));
		$service_fee	=	trim(I('post.service_fee'))*100;
		
		//检查合法性
		$map_case['case_id']	=	$case_id;
		$case_list	=	M('Case')->where($map_case)->find();
		if(!is_array($case_list)){
			$this->error('案件编号不正确');
		}
		
		//获取申请日
		$application_date	=	$case_list['application_date'];
		if(0 !== $application_date){
			$current_time	=	time();		
			$year_intavel	=	yearInterval($application_date,$current_time);			
		}else{
			$this->error('本案未填写申请日');
		}		
		
		//获取“授权后”对应的ID
		$map_case_phase['case_phase_name']	=	array('like','%授权后%');
		$case_phase_list	=	M('CasePhase')->where($map_case_phase)->find();
		$case_phase_id	=	$case_phase_list[$case_phase_id];
		
		//获取本条费用的案子的 $case_type_name
		$case_type_name	=	D('CaseFee')->returnCaseTypeName($case_fee_id);
		//根据 $case_type_name 构造对应的检索条件
		if(false	!==	strpos($case_type_name,'发明')){
			$map_fee_type['fee_type_name']	=	array('like','%发明%');
		}elseif(false	!==	strpos($case_type_name,'实用新型')){
			$map_fee_type['fee_type_name']	=	array('like','%实用新型%');
		}elseif(false	!==	strpos($case_type_name,'外观设计')){
			$map_fee_type['fee_type_name']	=	array('like','%外观设计%');
		}else{
			$this->error('创建失败');
		}
		
		for($j==0;$j<$year_number;	$j++){
			//根据年度构建搜索
			$year_intavel	=	$year_intavel	+	$j;
			$map_fee_type['fee_type_name_cpc']	=	array('like','%第'.$year_intavel.'年%');
			
			//获取费用类型的ID
			$fee_type_list	=	M('FeeType')->where($map_fee_type)->find();
			$fee_type_id	=	$fee_type_list['fee_type_id'];
			$fee_default_amount	=	$fee_type_list['fee_default_amount'];
			
			//获取期限日
			$due_date	=	strtotime ("+".$year_intavel." year", $application_date);
			
			//构造数组
			$date_case_fee['case_id']	=	$case_id;
			$date_case_fee['case_phase_id']	=	$case_phase_id;
			$date_case_fee['fee_type_id']	=	$fee_type_id;
			$date_case_fee['official_fee']	=	$fee_default_amount;
			$date_case_fee['service_fee']	=	$service_fee;
			$date_case_fee['due_date']	=	$due_date;
			
			$result_case_fee	=	M('CaseFee')->add($date_case_fee);
		}			
		
		if(false !== $result_case_fee){
			
			// 写入新增数据成功，返回案件信息页面
			$this->success('新增成功', U('CaseFee/view','case_id='.$case_id));
			
		}else{
			$this->error('增加失败');
		}
	}
	
	//更新
	public function update(){
		
		//针对 POST 的处理方式
		if(IS_POST){
			$case_fee_data['case_fee_id']	=	trim(I('post.case_fee_id'));
			$case_fee_data['case_id']	=	trim(I('post.case_id'));
			$case_fee_data['case_phase_id']	=	trim(I('post.case_phase_id'));
			$case_fee_data['fee_type_id']	=	trim(I('post.fee_type_id'));
			$case_fee_data['official_fee']	=	100*trim(I('post.official_fee'));
			$case_fee_data['service_fee']	=	100*trim(I('post.service_fee'));			
			$case_fee_data['oa_date']	=	strtotime(trim(I('post.oa_date')));			
			$case_fee_data['due_date']	=	strtotime(trim(I('post.due_date')));
			$case_fee_data['allow_date']	=	strtotime(trim(I('post.allow_date')));
			$case_fee_data['payer_id']	=	trim(I('post.payer_id'));
			$case_fee_data['case_payment_id']	=	trim(I('post.case_payment_id'));
			$case_fee_data['bill_id']	=	trim(I('post.bill_id'));
			$case_fee_data['invoice_id']	=	trim(I('post.invoice_id'));
			$case_fee_data['claim_id']	=	trim(I('post.claim_id'));
			$case_fee_data['cost_id']	=	trim(I('post.cost_id'));
			$case_fee_data['cost_amount']	=	100*trim(I('post.cost_amount'));
			
			$result	=	M('CaseFee')->save($case_fee_data);
			
			if(false !== $result){
				$this->success('修改成功', U('CaseFee/view','case_id='.$case_fee_data['case_id']));
			}else{
				$this->error('修改失败');
			}
			
		//针对 GET 的处理方式
		} else{
			
			//接收要编辑的 $case_fee_id
			$case_fee_id = I('get.case_fee_id',0,'int');
			
			if(!$case_fee_id){
				$this->error('未指明要编辑的费用编号');
			}
			
			//取出相应的信息
			$case_fee_list = M('CaseFee')->getByCaseFeeId($case_fee_id);
			$this->assign('case_fee_list',$case_fee_list);
			
			//取出 CasePhase 表的内容以及数量
			$case_phase_list	=	D('CasePhase')->listBasic();
			$case_phase_count	=	count($case_phase_list);
			$this->assign('case_phase_list',$case_phase_list);
			$this->assign('case_phase_count',$case_phase_count);
			
			//获取本条费用的案子的 $case_type_name
			$case_type_name	=	D('CaseFee')->returnCaseTypeName($case_fee_id);

			//根据 $case_type_name 是否包含“专利”来构造对应的检索条件
			if(false	!==	strpos($case_type_name,'发明')){
				$map_fee_type['fee_type_name']	=	array('like','%发明%');
			}elseif(false	!==	strpos($case_type_name,'实用新型')){
				$map_fee_type['fee_type_name']	=	array('like','%实用新型%');
			}elseif(false	!==	strpos($case_type_name,'外观设计')){
				$map_fee_type['fee_type_name']	=	array('like','%外观设计%');
			}elseif(false	!==	strpos($case_type_name,'商标')){
				$map_fee_type['fee_type_name']	=	array('like','%商标%');
			}elseif(false	!==	strpos($case_type_name,'版权')){
				$map_fee_type['fee_type_name']	=	array('like','%版权%');
			}elseif(false	!==	strpos($case_type_name,'其他')){
				$map_fee_type['fee_type_name']	=	array('like','%其他%');
			}else{
				$map_fee_type['fee_type_name']	=	array('notlike','%专利%');
			}
			$fee_type_list	=	D('FeeType')->where($map_fee_type)->listBasic();
			$fee_type_count	=	count($fee_type_list);
			$this->assign('fee_type_list',$fee_type_list);
			$this->assign('fee_type_count',$fee_type_count);
			
			//取出 Payer 表的内容以及数量
			$payer_list	=	D('Payer')->listBasic();
			$payer_count	=	count($payer_list);
			$this->assign('payer_list',$payer_list);
			$this->assign('payer_count',$payer_count);
			
			//取出其他变量
			$row_limit  =   C("ROWS_PER_SELECT");
			$this->assign('row_limit',$row_limit);
			
			$this->display();
		}
	}
	
	//删除
	public function delete(){
		if(IS_POST){
			
			//通过 I 方法获取 post 过来的 case_fee_id 和 case_id
			$case_fee_id	=	trim(I('post.case_fee_id'));
			$case_id	=	trim(I('post.case_id'));
			$no_btn	=	I('post.no_btn');
			$yes_btn	=	I('post.yes_btn');

			if(1==$no_btn){
				$this->success('取消删除', U('Case/view','case_id='.$case_id));
			}
			
			if(1==$yes_btn){
				
				$map['case_fee_id']	=	$case_fee_id;

				$result = M('CaseFee')->where($map)->delete();
				if($result){
					$this->success('删除成功', U('Case/view','case_id='.$case_id));
				}
			}
			
		} else{
			$case_fee_id = I('get.case_fee_id',0,'int');

			if(!$case_fee_id){
				$this->error('未指明要删除的费用记录');
			}
			
			$case_fee_list = D('CaseFeeView')->field(true)->getByCaseFeeId($case_fee_id);		
			$this->assign('case_fee_list',$case_fee_list);
			
			$this->display();
		}
	}
	
	//搜索专利费用
	public function searchPatentFee(){
		
		//取出 Client 表的内容以及数量
		$client_list	=	D('Client')->listBasic();
		$this->assign('client_list',$client_list);
		
		//取出 Member 表的内容以及数量
		$member_list	=	D('Member')->listBasic();
		$this->assign('member_list',$member_list);
		
		//取出 CasePhase 表的内容以及数量
		$case_phase_list	=	D('CasePhase')->listBasic();
		$this->assign('case_phase_list',$case_phase_list);
		
		//取出 CostCenter 表的内容以及数量
		$cost_center_list	=	D('CostCenter')->listBasic();
		$this->assign('cost_center_list',$cost_center_list);
		
		//默认查询未来1个月期限
		$start_due_date	=	"";
		$end_due_date	=	strtotime('+1 month');
		$this->assign('start_due_date',$start_due_date);
		$this->assign('end_due_date',$end_due_date);
		
		if(IS_POST){
			
			//接收搜索参数			
			$client_id	=	I('post.client_id','0','int');
			$applicant_id	=	I('post.applicant_id','0','int');
			$follower_id	=	I('post.follower_id','0','int');
			$cost_center_id	=	I('post.cost_center_id','0','int');	
			$allow_to_pay	=	I('post.allow_to_pay','0','int');
			$is_paid	=	I('post.is_paid','0','int');
			$case_phase_id	=	I('post.case_phase_id','0','int');
			
			$start_due_date	=	trim(I('post.start_due_date'));
			$start_due_date	=	$start_due_date ? strtotime($start_due_date) : time();			
			$end_due_date	=	trim(I('post.end_due_date'));
			$end_due_date	=	$end_due_date ? strtotime($end_due_date) : strtotime('+1 month');
			
			//构造 maping
			if($client_id){
				$map_case_fee['client_id']	=	$client_id;
			}
			if($applicant_id){
				$map_case_fee['applicant_id']	=	$applicant_id;
			}
			if($follower_id){
				$map_case_fee['follower_id']	=	$follower_id;
			}	
			
			if($cost_center_id){
				$map_case_fee['cost_center_id']	=	$cost_center_id;
			}
			if($allow_to_pay){
				$map_case_fee['allow_date']	=	array('GT',1);
			}
			if(1==$is_paid){
				$map_case_fee['case_payment_id']	=	array('LT',1);
			}
			if(2==$is_paid){
				$map_case_fee['case_payment_id']	=	array('GT',1);
			}
			if($case_phase_id){
				$map_case_fee['case_phase_id']	=	$case_phase_id;
			}
			$map_case_fee['due_date']	=	array('between',$start_due_date.','.$end_due_date);
			
			//获取专利的 case_type_id 集合
			$case_type_list	=	D('CaseType')->listPatentCaseTypeId();
			$map_case_fee['case_type_id']  = array('in',$case_type_list);
			
			
			//分页显示搜索结果
			$p	= I("p",1,"int");
			$page_limit  =   C("RECORDS_PER_PAGE");
			$case_fee_list = D('CaseFeeView')->listPageSearch($p,$page_limit,$map_case_fee);
			$this->assign('case_fee_list',$case_fee_list['list']);
			$this->assign('case_fee_page',$case_fee_list['page']);
			$this->assign('case_fee_count',$case_fee_list['count']);
			
			//返回所接受的检索条件
			$this->assign('client_id',$client_id);
			$this->assign('applicant_id',$applicant_id);
			$this->assign('follower_id',$follower_id);
			$this->assign('cost_center_id',$cost_center_id);
			$this->assign('allow_to_pay',$allow_to_pay);
			$this->assign('is_paid',$is_paid);
			$this->assign('case_phase_id',$case_phase_id);
			$this->assign('start_due_date',$start_due_date);
			$this->assign('end_due_date',$end_due_date);
		
		} 
	
	$this->display();
	}
	
	//搜索非专利费用
	public function searchNotPatentFee(){
		
		//取出 Client 表的内容以及数量
		$client_list	=	D('Client')->listBasic();
		$this->assign('client_list',$client_list);
		
		//取出 Member 表的内容以及数量
		$member_list	=	D('Member')->listBasic();
		$this->assign('member_list',$member_list);
		
		//取出 CasePhase 表的内容以及数量
		$case_phase_list	=	D('CasePhase')->listBasic();
		$this->assign('case_phase_list',$case_phase_list);
		
		//取出 CostCenter 表的内容以及数量
		$cost_center_list	=	D('CostCenter')->listBasic();
		$this->assign('cost_center_list',$cost_center_list);
		
		//默认查询未来1个月期限
		$start_due_date	=	""
		$end_due_date	=	strtotime('+1 month');
		$this->assign('start_due_date',$start_due_date);
		$this->assign('end_due_date',$end_due_date);
		
		if(IS_POST){
			
			//接收搜索参数			
			$client_id	=	I('post.client_id','0','int');
			$applicant_id	=	I('post.applicant_id','0','int');
			$follower_id	=	I('post.follower_id','0','int');
			$cost_center_id	=	I('post.cost_center_id','0','int');	
			$allow_to_pay	=	I('post.allow_to_pay','0','int');
			$is_paid	=	I('post.is_paid','0','int');
			$case_phase_id	=	I('post.case_phase_id','0','int');
			
			$start_due_date	=	trim(I('post.start_due_date'));
			$start_due_date	=	$start_due_date ? strtotime($start_due_date) : time();			
			$end_due_date	=	trim(I('post.end_due_date'));
			$end_due_date	=	$end_due_date ? strtotime($end_due_date) : strtotime('+1 month');
			
			//构造 maping
			if($client_id){
				$map_case_fee['client_id']	=	$client_id;
			}
			if($applicant_id){
				$map_case_fee['applicant_id']	=	$applicant_id;
			}
			if($follower_id){
				$map_case_fee['follower_id']	=	$follower_id;
			}	
			
			if($cost_center_id){
				$map_case_fee['cost_center_id']	=	$cost_center_id;
			}
			if($allow_to_pay){
				$map_case_fee['allow_date']	=	array('GT',1);
			}
			if(1==$is_paid){
				$map_case_fee['case_payment_id']	=	array('LT',1);
			}
			if(2==$is_paid){
				$map_case_fee['case_payment_id']	=	array('GT',1);
			}
			if($case_phase_id){
				$map_case_fee['case_phase_id']	=	$case_phase_id;
			}
			$map_case_fee['due_date']	=	array('between',$start_due_date.','.$end_due_date);
			
			//获取专利的 case_type_id 集合
			$case_type_list	=	D('CaseType')->listNotPatentCaseTypeId();
			$map_case_fee['case_type_id']  = array('in',$case_type_list);
			
			
			//分页显示搜索结果
			$p	= I("p",1,"int");
			$page_limit  =   C("RECORDS_PER_PAGE");
			$case_fee_list = D('CaseFeeView')->listPageSearch($p,$page_limit,$map_case_fee);
			$this->assign('case_fee_list',$case_fee_list['list']);
			$this->assign('case_fee_page',$case_fee_list['page']);
			$this->assign('case_fee_count',$case_fee_list['count']);
			
			//返回所接受的检索条件
			$this->assign('client_id',$client_id);
			$this->assign('applicant_id',$applicant_id);
			$this->assign('follower_id',$follower_id);
			$this->assign('cost_center_id',$cost_center_id);
			$this->assign('allow_to_pay',$allow_to_pay);
			$this->assign('is_paid',$is_paid);
			$this->assign('case_phase_id',$case_phase_id);
			$this->assign('start_due_date',$start_due_date);
			$this->assign('end_due_date',$end_due_date);
		
		} 
	
	$this->display();
	}
	
	//搜索专利费用
	public function searchPatentFeeList(){
		
		//取出 Client 表的内容以及数量
		$client_list	=	D('Client')->listBasic();
		$this->assign('client_list',$client_list);
		
		//取出 Member 表的内容以及数量
		$member_list	=	D('Member')->listBasic();
		$this->assign('member_list',$member_list);
		
		//取出 CasePhase 表的内容以及数量
		$case_phase_list	=	D('CasePhase')->listBasic();
		$this->assign('case_phase_list',$case_phase_list);
		
		//取出 CostCenter 表的内容以及数量
		$cost_center_list	=	D('CostCenter')->listBasic();
		$this->assign('cost_center_list',$cost_center_list);
		
		//默认查询最近一个月
		$start_payment_date	=	strtotime('-1 month');
		$end_payment_date	=	time();
		$this->assign('start_payment_date',$start_payment_date);
		$this->assign('end_payment_date',$end_payment_date);
		
		if(IS_POST){
			
			//接收搜索参数			
			$client_id	=	I('post.client_id','0','int');
			$applicant_id	=	I('post.applicant_id','0','int');
			$follower_id	=	I('post.follower_id','0','int');
			$cost_center_id	=	I('post.cost_center_id','0','int');	
			$case_phase_id	=	I('post.case_phase_id','0','int');
			
			$start_payment_date	=	trim(I('post.start_payment_date'));
			$start_payment_date	=	$start_payment_date ? strtotime($start_payment_date) : time();			
			$end_payment_date	=	trim(I('post.end_payment_date'));
			$end_payment_date	=	$end_payment_date ? strtotime($end_payment_date) : strtotime('+1 month');
			
			//构造 maping
			if($client_id){
				$map_case_fee['client_id']	=	$client_id;
			}
			if($applicant_id){
				$map_case_fee['applicant_id']	=	$applicant_id;
			}
			if($follower_id){
				$map_case_fee['follower_id']	=	$follower_id;
			}	
			
			if($cost_center_id){
				$map_case_fee['cost_center_id']	=	$cost_center_id;
			}			
			if($case_phase_id){
				$map_case_fee['case_phase_id']	=	$case_phase_id;
			}
			$map_case_fee['payment_date']	=	array('between',$start_payment_date.','.$end_payment_date);
			
			//获取专利的 case_type_id 集合
			$case_type_list	=	D('CaseType')->listPatentCaseTypeId();
			$map_case_fee['case_type_id']  = array('in',$case_type_list);
			
			
			//分页显示搜索结果
			$p	= I("p",1,"int");
			$page_limit  =   C("RECORDS_PER_PAGE");
			$case_fee_list = D('CaseFeeView')->listPageSearch($p,$page_limit,$map_case_fee);
			$this->assign('case_fee_list',$case_fee_list['list']);
			$this->assign('case_fee_page',$case_fee_list['page']);
			$this->assign('case_fee_count',$case_fee_list['count']);
			
			//返回所接受的检索条件
			$this->assign('client_id',$client_id);
			$this->assign('applicant_id',$applicant_id);
			$this->assign('follower_id',$follower_id);
			$this->assign('cost_center_id',$cost_center_id);
			$this->assign('case_phase_id',$case_phase_id);
			$this->assign('start_payment_date',$start_payment_date);
			$this->assign('end_payment_date',$end_payment_date);
		
		} 
	
	$this->display();
	}
	
	//搜索非专利费用
	public function searchNotPatentFeeList(){
		
		//取出 Client 表的内容以及数量
		$client_list	=	D('Client')->listBasic();
		$this->assign('client_list',$client_list);
		
		//取出 Member 表的内容以及数量
		$member_list	=	D('Member')->listBasic();
		$this->assign('member_list',$member_list);
		
		//取出 CasePhase 表的内容以及数量
		$case_phase_list	=	D('CasePhase')->listBasic();
		$this->assign('case_phase_list',$case_phase_list);
		
		//取出 CostCenter 表的内容以及数量
		$cost_center_list	=	D('CostCenter')->listBasic();
		$this->assign('cost_center_list',$cost_center_list);
		
		//默认查询最近一个月
		$start_payment_date	=	strtotime('-1 month');
		$end_payment_date	=	time();
		$this->assign('start_payment_date',$start_payment_date);
		$this->assign('end_payment_date',$end_payment_date);
		
		if(IS_POST){
			
			//接收搜索参数			
			$client_id	=	I('post.client_id','0','int');
			$applicant_id	=	I('post.applicant_id','0','int');
			$follower_id	=	I('post.follower_id','0','int');
			$cost_center_id	=	I('post.cost_center_id','0','int');	
			$case_phase_id	=	I('post.case_phase_id','0','int');
			
			$start_payment_date	=	trim(I('post.start_payment_date'));
			$start_payment_date	=	$start_payment_date ? strtotime($start_payment_date) : time();			
			$end_payment_date	=	trim(I('post.end_payment_date'));
			$end_payment_date	=	$end_payment_date ? strtotime($end_payment_date) : strtotime('+1 month');
			
			//构造 maping
			if($client_id){
				$map_case_fee['client_id']	=	$client_id;
			}
			if($applicant_id){
				$map_case_fee['applicant_id']	=	$applicant_id;
			}
			if($follower_id){
				$map_case_fee['follower_id']	=	$follower_id;
			}	
			
			if($cost_center_id){
				$map_case_fee['cost_center_id']	=	$cost_center_id;
			}			
			if($case_phase_id){
				$map_case_fee['case_phase_id']	=	$case_phase_id;
			}
			$map_case_fee['payment_date']	=	array('between',$start_payment_date.','.$end_payment_date);
			
			//获取专利的 case_type_id 集合
			$case_type_list	=	D('CaseType')->listNotPatentCaseTypeId();
			$map_case_fee['case_type_id']  = array('in',$case_type_list);
			
			
			//分页显示搜索结果
			$p	= I("p",1,"int");
			$page_limit  =   C("RECORDS_PER_PAGE");
			$case_fee_list = D('CaseFeeView')->listPageSearch($p,$page_limit,$map_case_fee);
			$this->assign('case_fee_list',$case_fee_list['list']);
			$this->assign('case_fee_page',$case_fee_list['page']);
			$this->assign('case_fee_count',$case_fee_list['count']);
			
			//返回所接受的检索条件
			$this->assign('client_id',$client_id);
			$this->assign('applicant_id',$applicant_id);
			$this->assign('follower_id',$follower_id);
			$this->assign('cost_center_id',$cost_center_id);
			$this->assign('case_phase_id',$case_phase_id);
			$this->assign('start_payment_date',$start_payment_date);
			$this->assign('end_payment_date',$end_payment_date);
		
		} 
	
	$this->display();
	}
	
	//查看主键为 $case_id 的收支流水的所有 case_fee
	public function view(){
		
		//接收对应的 $case_id
		$case_id = I('get.case_id',0,'int');
		if(!$case_id){
			$this->error('未指明要查看的收支流水');
		}
		
		//取出案件的基本信息
		$case_list = D('CaseView')->field(true)->getByCaseId($case_id);
		
		//定义查询
		$map['case_id']	=	$case_id;

		//取出费用信息
		$case_fee_list	=	D('CaseFeeView')->where($map)->listAll();
		$case_fee_count	=	count($case_fee_list);
		
		$this->assign('case_list',$case_list);
		$this->assign('case_fee_list',$case_fee_list);
		$this->assign('case_fee_count',$case_fee_count);
				
		//取出 CasePhase 表的内容以及数量
		$case_phase_list	=	D('CasePhase')->listBasic();
		$case_phase_count	=	count($case_phase_list);
		$this->assign('case_phase_list',$case_phase_list);
		$this->assign('case_phase_count',$case_phase_count);
		
		//获取本案子的 $case_type_name
		$case_type_name	=	$case_list['case_type_name'];
		
		//根据 $case_type_name 是否包含“专利”来构造对应的检索条件
		if(false	!==	strpos($case_type_name,'发明')){
			$map_fee_type['fee_type_name']	=	array('like','%发明%');
		}elseif(false	!==	strpos($case_type_name,'实用新型')){
			$map_fee_type['fee_type_name']	=	array('like','%实用新型%');
		}elseif(false	!==	strpos($case_type_name,'外观设计')){
			$map_fee_type['fee_type_name']	=	array('like','%外观设计%');
		}elseif(false	!==	strpos($case_type_name,'PCT')){
			$map_fee_type['fee_type_name']	=	array('like','%PCT%');
		}elseif(false	!==	strpos($case_type_name,'检索')){
			$map_fee_type['fee_type_name']	=	array('like','%检索%');
		}elseif(false	!==	strpos($case_type_name,'商标')){
			$map_fee_type['fee_type_name']	=	array('like','%商标%');
		}elseif(false	!==	strpos($case_type_name,'版权')){
			$map_fee_type['fee_type_name']	=	array('like','%版权%');
		}elseif(false	!==	strpos($case_type_name,'其他')){
			$map_fee_type['fee_type_name']	=	array('like','%其他%');
		}else{
			$map_fee_type['fee_type_name']	=	array('notlike','%专利%');
		}
		
		//取出 FeeType 表的内容以及数量
		$fee_type_list	=	D('FeeType')->where($map_fee_type)->listBasic();
		$fee_type_count	=	count($fee_type_list);
		$this->assign('fee_type_list',$fee_type_list);
		$this->assign('fee_type_count',$fee_type_count);
		
		//取出 Payer 表的内容以及数量
		$payer_list	=	D('Payer')->listBasic();
		$payer_count	=	count($payer_list);
		$this->assign('payer_list',$payer_list);
		$this->assign('payer_count',$payer_count);
		
		
		//取出其他变量
		$row_limit  =   C("ROWS_PER_SELECT");
		$today	=	time();
		$this->assign('row_limit',$row_limit);
        $this->assign('today',$today);

		$this->display();
	}
}