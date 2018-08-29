<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class SchoolController extends AdminbaseController{

	// 列表
	public function index(){
		$where = array();
		/**搜索条件**/
		$user_login = I('request.schoolname');
		if($user_login){
			$where['schoolname'] = array('like',"%$user_login%");
		}
		if(session('ADMIN_ID') !=1)
		{
			$sid= M('users')->where("id='".session('ADMIN_ID')."'")->getField('sid');
			$where['id'] =$sid;
		}
		$count=M('school')->where($where)->count();
		$page = $this->page($count, 20);
        $list = M('school')
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->select();
		
		$this->assign("page", $page->show('Admin'));		
		$this->assign("list",$list);
		$this->display();
	}

	// 管理员添加
	public function add(){
		
		$this->display();
	}

	// 管理员添加提交
	public function add_post(){
		if(IS_POST){
			$pdata =I('post.');
			if(empty($pdata['schoolname']))
			{
				$this->ajaxReturn(array('status'=>1,'msg'=>'请输入学校名称'));
			}
			if(empty($pdata['avartar']))
			{
				$this->ajaxReturn(array('status'=>1,'msg'=>'请上传学校校徽'));
			}
			$count =M('school')->where("schoolname='".$pdata['schoolname']."'")->count();
			if($count > 0)
			{
				$this->ajaxReturn(array('status'=>1,'msg'=>'学校名称已存在'));
			}
			$pdata['createtime'] =time();
			$res=M('school')->add($pdata);
			if($res)
			{
				$path="pages/choseArae/choseArea?sid=".$res;    
				$width=800;    
				$post_data='{"path":"'.$path.'","width":'.$width.'}';  
				$access_token =$this->gettoken('wxbffcacc44840a18f','f1a9a2c162b7a2e7b7587108e105d3dd');	
				$url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;    
				$result=$this->api_notice_increment($url,$post_data);
				if($result)
				{
					 $imgurl = "./data/upload/images/".date('Ymd').rand(10000,99999).".jpg";					
					 $resource = fopen($imgurl, 'w+');  
					 //将图片内容写入上述新建的文件  
					 fwrite($resource, $result);  
					 //关闭资源  
					 fclose($resource);  					
					 M('school')->where("id='$res'")->save(array('codeimg'=>$imgurl));
				}				
				$this->ajaxReturn(array('status'=>0,'msg'=>'保存成功'));
			}else
			{
				$this->ajaxReturn(array('status'=>0,'msg'=>'保存失败'));
			}
		}
	}

	// 管理员编辑
	public function edit(){
	    $id = I('get.id',0,'intval');
		
		$user=M('school')->where(array("id"=>$id))->find();
		if($user['avartar'])
		{
			$user['avartar2'] ='http://'.$_SERVER['HTTP_HOST'].'/'.$user['avartar'];
		}
		//$xiaoqu=M('schoollinkxiaoqu')->field('xiaoqu,xiaoqu_id')->where("sid='".$id."'")->select();
		//if(empty($xiaoqu))
		//{			
			$xiaoqu =M('kd_room')->field('xiaoqu_id,xiaoqu')->where("sid='$id'")->group('xiaoqu_id,xiaoqu')->select();
		//}
		$this->assign('xiaoqu',$xiaoqu);
		$this->assign($user);
		$this->display();
	}

	// 管理员编辑提交
	public function edit_post(){
			$pdata =I('post.');
			
			if(empty($pdata['schoolname']))
			{
				$this->ajaxReturn(array('status'=>1,'msg'=>'请输入学校名称'));
			}
			if(empty($pdata['avartar']))
			{
				$this->ajaxReturn(array('status'=>1,'msg'=>'请上传学校校徽'));
			}
			if($pdata['oldschoolname'] != $pdata['schoolname'])
			{
				$count =M('school')->where("schoolname='".$pdata['schoolname']."'")->count();
				if($count > 0)
				{
					$this->ajaxReturn(array('status'=>1,'msg'=>'学校名称已存在'));
				}
			}
			$oldcodeimg =M('school')->where("id='".$pdata['id']."'")->getField('codeimg');
			$path="pages/choseArae/choseArea?sid=".$pdata['id'];    
			$width=800;    
			$post_data='{"path":"'.$path.'","width":'.$width.'}';  
			$access_token =$this->gettoken('wxbffcacc44840a18f','f1a9a2c162b7a2e7b7587108e105d3dd');	
			$url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;    
			$result=$this->api_notice_increment($url,$post_data);
			if($result)
			{
				 $imgurl = "./data/upload/images/".date('Ymd').rand(10000,99999).".jpg";					
				 $resource = fopen($imgurl, 'w+');  
				 //将图片内容写入上述新建的文件  
				 fwrite($resource, $result);  
				 //关闭资源  
				 fclose($resource);  					
				$pdata['codeimg']=$imgurl;
			}	
			$res=M('school')->save($pdata);
			if($res)
			{
				@unlink($oldcodeimg);
				foreach($pdata['xiaoquname'] as $k=>$v)
				{
					if($v !='')
					{
						M('schoollinkxiaoqu')->where("sid='".$pdata['id']."'")->delete();
						$data['sid']=$pdata['id'];
						$data['xiaoqu'] =$v;
						$data['xiaoqu_id'] =$pdata['xiaoqu_id'][$k];
						M('schoollinkxiaoqu')->add($data);
					}
					
				}
				
				
				$this->ajaxReturn(array('status'=>0,'msg'=>'保存成功'));
			}else
			{
				$this->ajaxReturn(array('status'=>0,'msg'=>'保存失败'));
			}
	}
	public function delete()
	{
		$id=I('id','','intval');
		if (!empty($id)) {
			$info=M('kd_room')->where("sid='$id'")->find();
			if($info)
			{
				$this->ajaxReturn(array('status'=>1,'msg'=>'该学校下有数据'));
			}else
			{
				$result = M('school')->where(array("id"=>$id))->delete();
				if ($result!==false){
					$this->ajaxReturn(array('status'=>0,'msg'=>'删除成功'));
				} else {
					$this->ajaxReturn(array('status'=>1,'msg'=>'删除失败'));
				}
			}
    		
    	}
	}
	// 下载二维码
	public function download()
	{
		$id=I('id','','intval');
		$info =M('school')->where("id='$id'")->find();
        $url_file = SITE_PATH . '/' . $info['codeimg'];
		file_put_contents('aa-1.txt',$url_file);
        if (file_exists($url_file))
        {
            header('Content-type: application/unknown');
            header('Content-Disposition: attachment; filename="' . $info['codeimg'] . '"');
            header("Content-Length: " . filesize($url_file) . "; ");
            readfile($url_file);
        }else{
        	$this->error('下载出错');
        }
	}
	function gettoken($appid,$secret)
	{
		$tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;    
		$getArr=array();    
		$tokenArr=json_decode($this->send_post($tokenUrl,$getArr,"GET"));    
		$access_token=$tokenArr->access_token;
		return $access_token;
	}
	function send_post($url, $post_data,$method='POST') {    
		$postdata = http_build_query($post_data);    
		$options = array(      
			'http' => array(        
			'method' => $method, 
			//or GET        
			'header' => 'Content-type:application/x-www-form-urlencoded',        
			'content' => $postdata,        
			'timeout' => 15 * 60 
			// 超时时间（单位:s）      
			)    
		);    
		$context = stream_context_create($options);    
		$result = file_get_contents($url, false, $context);    
		return $result;  
	}
	function api_notice_increment($url, $data){    
		$ch = curl_init();    
		$header = "Accept-Charset: utf-8";    
		curl_setopt($ch, CURLOPT_URL, $url);    
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);    
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');    
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);    
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
		$tmpInfo = curl_exec($ch); 
		if (curl_errno($ch)) {      
			return false;    
		}else{ 
			return $tmpInfo;    
		}  
	}
	
}