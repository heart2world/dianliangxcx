<?php
ini_set('max_execution_time', '600000'); 
header("Content-type:text/html;charset=utf-8");
$serverName = "39.104.172.9"; //数据库服务器地址
$uid = "sa"; //数据库用户名
$pwd = "Test1234"; //数据库密码
$connectionInfo = array("UID"=>$uid, "PWD"=>$pwd, "Database"=>"KDDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn == false)
{
echo "连接失败！";
die( print_r( sqlsrv_errors(), true));
}
$query = sqlsrv_query($conn, "select * from kd_room where isdo=0");
$option = sqlsrv_query($conn, "select * from options");
$daytime ='';
while($roww = sqlsrv_fetch_array($option))
{
	if($roww['option_name'] ='dl_settings')
	{
		$op =json_decode($roww['option_value'],true);
		$daytime =$op['lessdianliang'];
	}
}
			
while($row = sqlsrv_fetch_array($query))
{
	
	if($row['allAmp'] - $row['usedAmp'] <= $daytime)
	{
		// 推送消息
		$mquery = sqlsrv_query($conn, "select * from member where sid='".$row['sid']."' and xiaoqu_id='".$row['xiaoqu_id']."' and loudong_id='".$row['loudong_id']."' and room_id='".$row['room_id']."'");
		
		while($v = sqlsrv_fetch_array($mquery))
		{
			if($v['openid'])
			{
				file_put_contents('aaa.txt',$v['openid']);
				$rquery = sqlsrv_query($conn,"select * from kd_room where sid='".$v['sid']."' and xiaoqu_id='".$v['xiaoqu_id']."' and loudong_id='".$v['loudong_id']."' and room_id='".$v['room_id']."'");
				while($room = sqlsrv_fetch_array($rquery))
				{
					$less = round($row['allAmp'] - $row['usedAmp'],2);
					$data_arr = array(
					  'keyword1' => array( "value" => trim($room['xiaoqu']).trim($room['loudong']).trim($room['room']), "color" => '' ) ,
					  'keyword2' => array( "value" => $less, "color" => '' ),
					  'keyword3' => array( "value" => "您的电量快要使用完啦，请尽快进行充值", "color" => '' )
					  //这里根据你的模板对应的关键字建立数组，color 属性是可选项目，用来改变对应字段的颜色
					);
					$post_data = array (
					  "touser"           => $v['openid'],
					  //用户的 openID，可用过 wx.getUserInfo 获取
					  "template_id"      => '6j1YRKPfiqdHt82hhSGn0xxvOakviWb4Krmy37aLtnA',
					  //小程序后台申请到的模板编号
					  "page"             => "/pages/index/index?id=".$v['sid'],
					  //点击模板消息后跳转到的页面，可以传递参数
					  "form_id"          => 26,
					  //第一步里获取到的 formID
					  "data"             => $data_arr,
					  "emphasis_keyword" => "keyword2.DATA"
					  //需要强调的关键字，会加大居中显示
					);
					file_put_contents('a11110.txt',var_export($post_data,true));
					put_send($post_data);
				}
			}
		}
	}
}

function put_send($post_data)
{
	file_put_contents('a11112.txt',var_export($post_data,true));
	$appid ='wxbffcacc44840a18f';
	$appSecret ='f1a9a2c162b7a2e7b7587108e105d3dd';
	// 这里替换为你的 appID 和 appSecret
	$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".getAccessToken ($appid, $appSecret);  
	// 将数组编码为 JSON
	$data = json_encode($post_data);   
	// 这里的返回值是一个 JSON，可通过 json_decode() 解码成数组
	file_put_contents('a1111.txt',var_export($data,true));
	$return = send_post($url, $data);
	print_r($return);
}
 
function send_post($url, $post_data) {
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
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
 
  return $result;
}
function getAccessToken ($appid, $appsecret) {                  
  $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
  $html = file_get_contents($url);
  $output = json_decode($html, true);
  $access_token = $output['access_token'];

  return $access_token;
}


?>