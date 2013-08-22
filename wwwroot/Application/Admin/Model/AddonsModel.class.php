<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

/**
 * 插件模型
 * @author yangweijie <yangweijiester@gmail.com>
 */

class AddonsModel extends Model {

	/**
	 * 查找后置操作
	 */
	protected function _after_find(&$result,$options) {
		// $result['status_text_arr'] = array(-1=>'损坏', 0=>'禁用', 1=>'启用');
		// $result['status_text'] = $result['status_text_arr'][$result['status']];
		$addons = addons($result['name']);
		if($addons && $addons->config_file){
			$data = include $addons->config_file;
			if($data && $result['config']){
				if(is_string($result['config']))
					$result['config'] = json_decode($result['config'], TRUE);
				foreach ($result['config'] as $key => $value) {
					$data[$key]['value'] = $value;
				}
			}
			$result['config'] = $data;
		}
	}

	protected function _after_select(&$result,$options){
		intToString($result, array('status'=>array(-1=>'损坏', 0=>'禁用', 1=>'启用')));
		foreach($result as &$record){
			$this->_after_find($record,$options);
		}
	}
    /**
	 * 文件模型自动完成
	 * @var array
	 */
    protected $_auto = array(
    	array('create_time', NOW_TIME, self::MODEL_INSERT),
	);

	/**
	 * 获取插件列表
	 * @param string $addon_dir
	 */
	public function getList($addon_dir = ''){
		if(!$addon_dir)
			$addon_dir = C('EXTEND_MODULE.Addons');
		$addons_names = glob($addon_dir.'*', GLOB_ONLYDIR);
		if($addons_names === FALSE || !file_exists($addon_dir)){
			$this->error = '插件目录不可读或者不存在';
			return FALSE;
		}
		$addons = array();
		foreach ($addons_names as $value) {
			$addons[] = $this->getAddonsInfo(basename($value));
		}
		intToString($addons, array('status'=>array(-1=>'损坏', 0=>'禁用', 1=>'启用')));
		return $addons;
	}

	/**
	 * 获取插件信息
	 */
	public function getAddonsInfo($name){
		$info = $this->where("name='{$name}'")->find();
		if(!$info){
			$addons = addons($name);
			$info = $addons->info;
			if($info)
				$info['uninstall'] = 1;
		}
		return $info;
	}
}