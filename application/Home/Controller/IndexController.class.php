<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 业余爱好者 <649180397@qq.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
       echo $_GET['mid'];
    }

     // 通过code 获取openid
    public function getcode()
    {
    	$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	$url ="https://api.weixin.qq.com/sns/jscode2session?appid=wxbffcacc44840a18f&secret=f1a9a2c162b7a2e7b7587108e105d3dd&js_code=".$forget['code']."&grant_type=authorization_code";
    	$result =get_request($url);
    	$result =json_decode($result,ture);
    	$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$result));
    }
    // 获取学校列表接口
    public function getschoollist()
    {
		$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}

    	if(!empty($forget['schoolname']))
    	{
    		$where ="schoolname like '%".$forget['schoolname']."%'";
    		$list=M('school')->field('id,schoolname')->where($where)->order('id desc')->select();
    	}else
    	{
    		$list=M('school')->field('id,schoolname')->order('id desc')->select();
    	}
    	
    	$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$list));
    }  
    // 获取校区列表接口
    public function getxiaoqulist()
    {
		$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	$schoolinfo=M('school')->field('id,schoolname,avartar')->where("id='".$forget['id']."'")->find();    	
    	if($schoolinfo)
    	{
    		$list =M('schoollinkxiaoqu')->field('xiaoqu,xiaoqu_id')->where("sid='".$forget['id']."'")->select();
			if(count($list)==0)
			{
				$list =M('kd_room')->field("xiaoqu,xiaoqu_id")->where("sid='".$forget['id']."'")->group('xiaoqu_id,xiaoqu')->select();
			}
    		foreach ($list as $key => $value) {
    			$loudong =M('kd_room')->field('loudong_id,loudong')->where("sid='".$forget['id']."' and xiaoqu_id='".$value['xiaoqu_id']."'")->group('loudong_id,loudong')->select();
    			foreach ($loudong as $ke => $val) {
    				//$room =M('kd_room')->field('room_id,room')->where("sid='".$forget['id']."' and xiaoqu_id='".$value['xiaoqu_id']."' and loudong_id='".$val['loudong_id']."'")->group('room_id,room')->select();
    				
					//$loudong[$ke]['room'] =$room;
					$loudong[$ke]['loudong'] =trim($val['loudong']);
    			}
    			$list[$key]['loudong'] =$loudong;
				$list[$key]['xiaoqu'] =trim($value['xiaoqu']);
    		}
    		$data['xiaoqu'] =$list;
    		if($schoolinfo['avartar'])
    		{
    			$schoolinfo['avartar'] ='https://'.$_SERVER['HTTP_HOST'].'/'.$schoolinfo['avartar'];
    		}
    		$data['info'] =$schoolinfo;
    		$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$data));
    	}else
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'参数有误','data'=>null));
    	}
    	
    }  

    // 根据校区,楼栋选择，输入房间号查询
    public function getroomslist()
    {
    	$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	if(intval($forget['id'])==0 || intval($forget['xiaoqu_id']) ==0)
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'请选择校区','data'=>null));
    	}
    	$list=M('kd_room')->field('loudong,loudong_id')->where("sid='".$forget['id']."' and xiaoqu_id='".$forget['xiaoqu_id']."'")->group('loudong,loudong_id')->select();
    	$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$list));
    }
    
}