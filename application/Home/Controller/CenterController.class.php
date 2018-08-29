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
class CenterController extends Controller {

	// 绑定宿舍
	public function index()
	{
		$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	if(intval($forget['id'])==0 || intval($forget['xiaoqu_id'])==0 || intval($forget['loudong_id'])==0 || intval($forget['room_id']) ==0 || empty($forget['openid']))
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'参数有误','data'=>null));
    	}
    	// 绑定
    	if(intval($forget['atype']) == 0)
    	{
    		$count =M('member')->where("openid='".$forget['openid']."'")->count();
	    	if($count > 0)
	    	{
	    		$this->ajaxReturn(array('status'=>'204','msg'=>'已绑定宿舍','data'=>null));
	    	}else
	    	{
	    		$data['sid'] =$forget['id'];
		    	$data['xiaoqu_id'] =$forget['xiaoqu_id'];
		    	$data['loudong_id'] =$forget['loudong_id'];
		    	$data['room_id']    =$forget['room_id'];
		    	$data['openid']    =$forget['openid'];
		    	$data['createtime'] =time();
		    	$lastid=M('member')->add($data);
		    	if($lastid)
		    	{
		    		$this->ajaxReturn(array('status'=>'200','msg'=>'绑定成功','data'=>$lastid));
		    	}else
		    	{
		    		$this->ajaxReturn(array('status'=>'203','msg'=>'绑定失败','data'=>null));
		    	}
	    	}
    	}else  // 改绑
    	{
    		$data['sid'] =$forget['id'];
	    	$data['xiaoqu_id'] =$forget['xiaoqu_id'];
	    	$data['loudong_id'] =$forget['loudong_id'];
	    	$data['room_id']    =$forget['room_id'];
	    	$data['createtime'] =time();
	    	$lastid=M('member')->where("openid='".$forget['openid']."'")->save($data);
	    	if($lastid)
	    	{
	    		$this->ajaxReturn(array('status'=>'200','msg'=>'改绑成功','data'=>$lastid));
	    	}else
	    	{
	    		$this->ajaxReturn(array('status'=>'203','msg'=>'改绑失败','data'=>null));
	    	}
    	} 
	}
	// 搜索房间号查询
	public function searchrooms()
	{
		$forget =I('get.');		
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	if(intval($forget['id'])==0 || intval($forget['xiaoqu_id'])==0 || intval($forget['loudong_id'])==0 || trim($forget['room']) == '')
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'参数有误','data'=>null));
    	}

    	$roomlist=M('kd_room')->field('sid,xiaoqu_id,loudong_id,room,room_id')->where("sid='".$forget['id']."' and xiaoqu_id='".$forget['xiaoqu_id']."' and loudong_id='".$forget['loudong_id']."' and room like '%".trim($forget['room'])."%'")->select();
    	if($roomlist)
		{
			foreach($roomlist as $k=>$v)
			{
				$roomlist[$k]['room'] =trim($v['room']);
			}
			$this->ajaxReturn(array('status'=>'200','msg'=>'查询成功','data'=>$roomlist));
		}else
		{
			$this->ajaxReturn(array('status'=>'203','msg'=>'查询失败','data'=>null));
		}
    	
	}
	
	// 电量查询接口
	public function getdlinfo()
	{
		$forget =I('get.');
		
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	if(intval($forget['id'])==0 || intval($forget['xiaoqu_id'])==0 || intval($forget['loudong_id'])==0 || intval($forget['room_id']) ==0)
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'参数有误','data'=>null));
    	}

    	$info=M('kd_room')->where("sid='".$forget['id']."' and xiaoqu_id='".$forget['xiaoqu_id']."' and loudong_id='".$forget['loudong_id']."' and room_id='".$forget['room_id']."'")->find();
    	$schoolname =M('school')->where("id='".$info['sid']."'")->getField('schoolname');
    	$address =trim($info['xiaoqu']).'/'.trim($info['loudong']).'/'.trim($info['room']);
    	$data['address'] =$address;
    	$data['lessamp'] =round($info['allamp']-$info['usedamp'],2);
		$ddd = M('kd_elec')->field('ElecDate')->where("sid='".$forget['id']."' and XiaoQu_id='".$forget['xiaoqu_id']."' and Room_Id='".$forget['room_id']."'")->order('ElecDate desc')->find();
		$data['lasttime'] =substr($ddd['elecdate'],0,-7);
    	$this->ajaxReturn(array('status'=>'200','msg'=>'改绑成功','data'=>$data));
	}
	// 历史电量
	public function hisdl()
	{
		$forget =I('get.');
		
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	if(intval($forget['id'])==0 || intval($forget['xiaoqu_id'])==0 || intval($forget['loudong_id'])==0 || intval($forget['room_id']) ==0)
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'参数有误','data'=>null));
    	}
    	if(empty($forget['monthtime']))
    	{    		
    		$startday=date('Y-m-01', strtotime(date("Y-m-d"))); 		
    		$endday = date('Y-m-d', strtotime("$startday +1 month"));
    		$where =" and elecdate between '".$startday."' and '".$endday."'";
    		$whereday = $startday;
			file_put_contents('a9012.txt',$where);
    	}else
    	{
    		$startday=$forget['monthtime'].'-01'; 	
    		$sday1 =	date('Y-m-01', strtotime($forget['monthtime']));
    		$endday = date('Y-m-d', strtotime("$sday1 +1 month"));
    		$where =" and elecdate between '".$startday."' and '".$endday."'";
    		$whereday = $startday;
			
    	}
    	$list=M('kd_elec')->field('elecdate,usedamp')->where("sid='".$forget['id']."' and xiaoqu_id='".$forget['xiaoqu_id']."' and room_id='".$forget['room_id']."'".$where)->order('elecdate desc')->select();
		
    	$mtotal =0;
    	foreach ($list as $k => $v) {
    		$list[$k]['elecdate'] = date('m月d日',strtotime($v['elecdate']));
    		if(empty($list[$k+1]['usedamp']))
    		{
    			$endtime =M('kd_elec')->field('elecdate,usedamp')->where("sid='".$forget['id']."' and xiaoqu_id='".$forget['xiaoqu_id']."' and room_id='".$forget['room_id']."' and elecdate <'".$whereday."'")->order('elecdate desc')->find();
    			$list[$k]['syamp'] = round($list[$k]['usedamp']-$endtime['usedamp'],2);
    		}else
    		{
    			$list[$k]['syamp'] = round($list[$k]['usedamp']-$list[$k+1]['usedamp'],2);
    		}
    		
    		$list[$k]['usedamp'] = round($v['usedamp'],2);
    		$mtotal+=$list[$k]['syamp'];
    	}
    	$data['list'] =$list;
    	$data['mtotal'] =round($mtotal,2);
		
    	$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$data));
	}

	// 缴费记录
	public function jflist()
	{
		$forget =I('get.');
	
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	if(intval($forget['id'])==0 || intval($forget['xiaoqu_id'])==0 || intval($forget['loudong_id'])==0 || intval($forget['room_id']) ==0)
    	{
    		$this->ajaxReturn(array('status'=>'202','msg'=>'参数有误','data'=>null));
    	}
    	if(empty($forget['yeartime']))
    	{
    		
    		$startday=date('Y-01-01', strtotime(date("Y-m-d"))); 		
    		$endday = date('Y-01-01', strtotime("$startday +1 year"));
    		$where =" and endatatime between '".$startday."' and '".$endday."'";    		
    	}else
    	{
			if(strlen($forget['yeartime'])==4)
			{
				$startday=$forget['yeartime'].'-01-01';	
				$endday = ($forget['yeartime']+1).'-01-01'; 
			}else{
				$startday=$forget['yeartime'].'-01';	
				$endday = date('Y-m-01', strtotime("$startday +1 month"));
			}
    		
    		$where =" and endatatime between '".$startday."' and '".$endday."'";    		
    	}
		
    	$list=M('kd_his')->field('endatatime,tranamt')->where("sid='".$forget['id']."' and xiaoqu_id='".$forget['xiaoqu_id']."' and room_id='".$forget['room_id']."'".$where)->order('endatatime desc')->select();
    	
    	foreach ($list as $k => $v) {
    		$list[$k]['endatatime'] = substr($v['endatatime'],0,-7);
    		$list[$k]['endatatime'] = date('Y年m月d日 H:i',strtotime($list[$k]['endatatime']));
    	}
    	$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$list));
	}

	// 查看当前用户是否绑定
	public function  getuserbangding()
	{
		$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	$info =M('member')->where("openid='".$forget['openid']."'")->find();
    	if($info)
    	{
    		$info['isbangding'] =1;
    		$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$info));
    	}else
    	{
    		$info['isbangding'] =0;
    		$this->ajaxReturn(array('status'=>'200','msg'=>'正常返回','data'=>$info));
    	}
	}

	public function tuiosong()
	{
		$forget =I('get.');
    	if($forget['token'] != C('Jsontoken'))
    	{
    		$this->ajaxReturn(array('msg'=>'token验证失败','status'=>'201','data'=>null));
    	}
    	$access_token = $this->getAccessToken('wxbffcacc44840a18f','f1a9a2c162b7a2e7b7587108e105d3dd');
    	$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;  
    	$data=array(
            "touser"=>$forget['openid'],
            "page"=>"index", 
            "emphasis_keyword"=>"",
            "form_id" =>$forget['form_id'],
            "template_id"=>"6j1YRKPfiqdHt82hhSGn0xxvOakviWb4Krmy37aLtnA-VM",
            'data'=>array(
                    'keyword1'=>array('value'=>urlencode($forget['address']),'color'=>"#151516"),
                    'keyword2'=>array('value'=>urlencode($forget['lessamp']),'color'=>"#151516"),
                    'keyword3'=>array('value'=>urlencode('您的电量快要使用完啦，请尽快进行充值'),'color'=>"#151516"),
                ));
    	$datastr = json_encode($data, true);   
		// 这里的返回值是一个 JSON，可通过 json_decode() 解码成数组
		file_put_contents('a1111.txt',var_export($datastr,true));
		$return = $this->send_post($url, $datastr);
	}
	function send_post( $url, $post_data ) {
		  $options = array(
		    'http' => array(
		      'method'  => 'POST',
		      'header'  => 'Content-type:application/json',
		      //header 需要设置为 JSON
		      'content' => $post_data,
		      'timeout' => 60
		      //超时时间
		    )
		  ); 
		  $context = stream_context_create( $options );
		  $result = file_get_contents( $url, false, $context );		 
		  return $result;
	}
	function getAccessToken ($appid, $appsecret) {                  
	  $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
	  $html = file_get_contents($url);
	  $output = json_decode($html, true);
	  $access_token = $output['access_token'];
	  return $access_token;
	}
}