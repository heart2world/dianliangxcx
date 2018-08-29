<?php
namespace Common\Model;
use Common\Model\CommonModel;
class UsersModel extends CommonModel
{
	
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('user_nicename', 'require', '姓名不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
		array('user_login', 'require', '手机号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
		array('user_pass', 'require', '初始密码不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
		array('user_pass', '6,20', '密码应6-20位字符！', 2,'length', CommonModel:: MODEL_BOTH ),
		array('sid', 'require', '请关联大学', 1, 'regex', CommonModel:: MODEL_INSERT  ),
		array('user_nicename', 'require', '姓名不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE  ),
		array('user_login', 'require', '手机号不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE ),
		array('sid', 'require', '请关联大学', 0, 'regex', CommonModel:: MODEL_UPDATE ),		
		array('user_login','','手机号已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_login字段是否唯一
	);
	
	protected $_auto = array(
	    array('create_time','mGetDate',CommonModel:: MODEL_INSERT,'callback'),
	    array('birthday','',CommonModel::MODEL_UPDATE,'ignore')
	);
	
	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function mGetDate() {
		return time();
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
		
		if(!empty($data['user_pass']) && strlen($data['user_pass'])<25){
			$data['user_pass']=sp_password($data['user_pass']);
		}
	}
	
}

