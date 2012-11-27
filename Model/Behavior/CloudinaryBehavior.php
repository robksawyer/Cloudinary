<?php
/**
 *  Yee Old Cloudinary Behavior for auto-uploadz
 */
class CloudinaryBehavior extends ModelBehavior {

	protected $_defaults = array();

/**
 * Initiate behaviour
 *
 * @param object $Model
 * @param array $settings
 */
	public function setup(Model $Model, $settings = array()) {
    App::uses('CakeCloudinary', 'Cloudinary.Lib');
		$this->CakeCloudinary = new CakeCloudinary();
		$this->Model->CakeCloudinary = $this->CakeCloudinary->initialize();

		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
		$this->Model = $Model;
		$this->alias = $Model->alias;
	}


	public function afterSave(Model $Model, $created = null) {
		foreach ($this->settings as $model_alias => $settings) {
			if (!empty($Model->data[$model_alias])) {

				$model = $Model->data[$model_alias];
				if (!empty($settings['field_name'])) {
					$image_path = $model[$settings['field_name']];
					$publicId = $model['slug'].'-'.strtolower($model_alias);
					$options['public_id'] = $publicId;
					$this->syncLink($image_path, $Model, $publicId);

				}
			}
		}

	}

	private function syncLink($image_path, $model, $publicId) {
		App::import('Model', 'Cloudinary.CloudinaryLink');
		$cloudinaryLink = new CloudinaryLink;

		$count = $cloudinaryLink->find('count', array(
			'ref_id' => $model->id,
			'public_id' => $publicId,
		));

		if ($count < 1) {
			$options['public_id'] = $publicId;
			$link = $this->CakeCloudinary->upload($image_path, $options);
			$link['ref_id'] = $model->id;
			$link['ref_type'] = $model->alias;
			$result = $cloudinaryLink->save($link);
		}
	}

}