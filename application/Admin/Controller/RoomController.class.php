<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RoomController extends AdminbaseController{

	// 宿舍列表
	public function index(){
		$where = '1=1';
		/**搜索条件**/
		$formget=array_merge($_GET,$_POST);
		$sid = I('request.sid');
		$xiaoqu_id = I('request.xiaoqu_id');
		$loudong_id = I('request.loudong_id');
		$keyword =I('keyword','','trim');
		//$room_id = I('request.room_id');
		if($sid){
			$where .= " and sid='$sid'";
			$xiaoqulist =M('kd_room')->field('xiaoqu_id,xiaoqu')->where("sid='$sid'")->group('xiaoqu,xiaoqu_id')->select();
			$this->assign('xiaoqulist',$xiaoqulist);
		}
		if($xiaoqu_id){
			$where .= " and xiaoqu_id='$xiaoqu_id'";
			
			$loudonglist =M('kd_room')->field('loudong_id,loudong')->where("sid='$sid' and xiaoqu_id='$xiaoqu_id'")->group('loudong_id,loudong')->select();
			$this->assign('loudonglist',$loudonglist);
		}
		if($loudong_id){
			$where .= " and loudong_id='$loudong_id'";
			
			$roomlist =M('kd_room')->field('room_id,room')->where("sid='$sid' and xiaoqu_id='$xiaoqu_id' and loudong_id='$loudong_id'")->group('room_id,room')->select();
			$this->assign('roomlist',$roomlist);
		}
		//if($room_id){
		//	$where .= " and room_id='$room_id'";
		//	$formget['room'] =M('kd_room')->where("sid='$sid' and xiaoqu_id='$xiaoqu_id' and loudong_id='$loudong_id' and room_id='".$room_id."'")->getField('room');
		//}
		if($keyword)
		{
			$where .=" and room like '%$keyword%'";
		}
		$count= M('kd_room')->where($where)->count();
		$page = $this->page($count, 10);
        $list = M('kd_room')->where($where)
                ->limit($page->firstRow, $page->listRows)
				->order('room_id asc')
                ->select();
		foreach($list as $key =>$v)
		{
			$list[$key]['schoolname'] =M('school')->where("id='".$v['sid']."'")->getField('schoolname');
		}
		$this->assign("page", $page->show('Admin'));		
		$this->assign("list",$list);
		if(session('ADMIN_ID') ==1)
		{
			// 学校
			$schoollist =M('school')->order('createtime desc')->select();
			$this->assign("schoollist",$schoollist);
		}else{
			$sid =M('users')->where("id='".session('ADMIN_ID')."'")->getField('sid');
			$schoollist =M('school')->where("id='".$sid."'")->order('createtime desc')->select();
			$this->assign("schoollist",$schoollist);
		}
		
		$this->assign("formget",$formget);
		$this->display();
	}

	// 用电量
	public function dllist()
	{
		$pdata =I('request.');
		$month =date('Y-m',time());
		$starttime =$month.'-01';
		$endtime =date('Y-m-d');
		$formget=array_merge($_GET,$_POST);
		if(!$pdata['starttime'] && !$pdata['endtime'])
		{
			$pdata['starttime'] =$starttime;
			$pdata['endtime'] =$endtime;
		}		
		$where ="xiaoqu_id='".$pdata['xiaoqu_id']."' and room_id='".$pdata['room_id']."' and sid='".$pdata['sid']."'";
		$where .= " and  elecdate between '".$pdata['starttime']."' and '".$pdata['endtime']."'";
		$address = $pdata['schoolname'].'/'.$pdata['xiaoqu'].'/'.$pdata['loudong'].'/'.$pdata['room'];
		$count =M('kd_elec')->where($where)->count();
		
		$page=$this->page($count, 10);
		$list =M('kd_elec')->where($where)->limit($page->firstRow, $page->listRows)->order('elecdate desc')->select();
		
		foreach($list as $k=>$v)
		{
			//剩余电量			
			$list[$k]['lessamp'] = round($v['allamp']-$v['usedamp'],2);
			// 日用电量
			if(empty($list[$k+1]['usedamp']))
			{
				$list[$k]['dayamp'] =0;
			}else
			{
				$list[$k]['dayamp'] =round($list[$k]['usedamp']-$list[$k+1]['usedamp'],2);
			}			
			$list[$k]['usedamp'] = round($v['usedamp'],2);
			$list[$k]['allamp']  = round($v['allamp'],2);
			$list[$k]['elecdate'] = date('Y-m-d H:i:s',strtotime($v['elecdate']));
		}
		$this->assign('address',$address);
		
		if(!$formget['starttime'])
		{
			$formget['starttime'] =$starttime;
			$formget['endtime'] =$endtime;
		}		
		$this->assign("formget",$formget);
	
		$this->assign("page", $page->show('Admin'));
		$this->assign('list',$list);
		
		$this->display();
	}
	// 缴费记录
	public function gflist()
	{
		$pdata =I('request.');
		$month =date('Y-m',time());
		$starttime =$month.'-01';
		$endtime =date('Y-m-d');
		$formget=array_merge($_GET,$_POST);
		if(!$pdata['starttime'] && !$pdata['endtime'])
		{
			$pdata['starttime'] =$starttime;
			$pdata['endtime'] =$endtime;
		}		
		$where ="xiaoqu_id='".$pdata['xiaoqu_id']."' and room_id='".$pdata['room_id']."' and sid='".$pdata['sid']."'";
		$where .= " and  endatatime between '".$pdata['starttime']."' and '".$pdata['endtime']."'";
		$address = $pdata['schoolname'].'/'.$pdata['xiaoqu'].'/'.$pdata['loudong'].'/'.$pdata['room'];
		$count =M('kd_his')->where($where)->count();
		$page=$this->page($count, 10);
		$list =M('kd_his')->where($where)->limit($page->firstRow, $page->listRows)->order('endatatime desc')->select();
		
		foreach($list as $k=>$v)
		{
			$list[$k]['tranamt'] = number_format($v['tranamt'],2);
			$list[$k]['endatatime'] = date('Y-m-d H:i:s',strtotime($v['endatatime']));
			$thisday = date('Y-m-d',strtotime($v['endatatime']));			
			// 缴费下一日 -当日的用电量
			$nextday=M('kd_elec')->where("xiaoqu_id='".$v['xiaoqu_id']."' and room_id='".$v['room_id']."' and elecdate>'".$thisday."'")->limit(2)->order('elecdate asc')->select();			
			$list[$k]['dayamp']=round($nextday[1]['allamp']-$nextday[0]['allamp'],2);
			
		}
		$this->assign('address',$address);
		
		if(!$formget['starttime'])
		{
			$formget['starttime'] =$starttime;
			$formget['endtime'] =$endtime;
		}		
		$this->assign("formget",$formget);
		$this->assign("page", $page->show('Admin'));
		$this->assign('list',$list);
		$this->display();
	}
	public function getxiaoqu()
	{
		if(IS_POST)
		{
			$sid =I('sid');
			$list=M('kd_room')->field('xiaoqu,xiaoqu_id')->where("sid='".$sid."'")->group('xiaoqu,xiaoqu_id')->select();
			$html ='<option value="0">全部</option>';			
			foreach($list as $k=>$v)
			{
				$html .= "<option value='".$v['xiaoqu_id']."'>".$v['xiaoqu']."</option>";
			}
			$this->ajaxReturn(array('html'=>$html));
		}
	}
	public function getloudong()
	{
		if(IS_POST)
		{
			$sid =I('sid');
			$xiaoqu =I('xiaoqu');
			$list=M('kd_room')->field('loudong,loudong_id')->where("sid='".$sid."' and xiaoqu_id='".$xiaoqu."'")->group('loudong,loudong_id')->select();
			$html ='<option value="0">全部</option>';
			foreach($list as $k=>$v)
			{
				$html .= "<option value='".$v['loudong_id']."'>".$v['loudong']."</option>";
			}
			$this->ajaxReturn(array('html'=>$html));
		}
	}
	public function getroom()
	{
		if(IS_POST)
		{
			$sid =I('sid');
			$xiaoqu =I('xiaoqu');
			$loudong =I('loudong');
			$list=M('kd_room')->field('room,room_id')->where("sid='".$sid."' and xiaoqu_id='".$xiaoqu."' and loudong_id='".$loudong."'")->group('room,room_id')->select();
			$html ='<option value="0">全部</option>';
			foreach($list as $k=>$v)
			{
				$html .= "<option value='".$v['room_id']."'>".$v['room']."</option>";
			}
			$this->ajaxReturn(array('html'=>$html));
		}
	}
}