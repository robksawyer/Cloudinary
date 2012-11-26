<?php
/**
 *  Yee Old Cloudinary Behavior for auto-uploadz
 */
class CloudinaryBehavior extends ModelBehavior {

/**
 * Initiate behaviour
 *
 * @param object $Model
 * @param array $settings
 */
	public function setup(Model $Model, $settings = array()) {
    App::uses('CakePhpThumb', 'PhpThumb.Lib');
    $this->thumbs_path = Configure::read('PhpThumb.thumbs_path');
		$this->PhpThumb = new CakePhpThumb();
    
		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
		$this->Model = $Model;
		$this->alias = $Model->alias;
	}

}