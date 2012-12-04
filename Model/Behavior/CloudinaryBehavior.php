<?php
/**
 *  Yee Old Cloudinary Behavior for auto-uploadz
 */
class CloudinaryBehavior extends ModelBehavior {

	protected $_defaults = array(
		'publicPattern' => array('slug', 'alias'),
		'publicSeparator' => '-',
		);

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
	}

	/**
	 * Main entry point for models to sync their image
	 * @param  Model  $Model      [description]
	 * @param  string $image_path relative path to le image to upload
	 * @return bool   true/false on success of the image
	 */
	public function syncCloudImage(Model $Model, $image_path) {
		if (empty($image_path) || !trim($image_path)) {
			return array('statusCode' => -1);
		}

		App::import('Model', 'Cloudinary.CloudinaryLink');
		$cloudinaryLink = new CloudinaryLink;
		$result = array();
		$publicId = $this->buildPublicId($Model);

		$link = $this->CakeCloudinary->upload($image_path, array('public_id' =>  $publicId));

		$count = $cloudinaryLink->find('count', array(
			'conditions' => array(
				'ref_id' => $Model->id,
				'public_id' => $publicId,
			)
		));
		
		if ($count < 1) {
			$link['ref_id'] = $Model->id;
			$link['ref_type'] = $Model->alias;
			if ($cloudinaryLink->save($link)) {
				$result = array('statusCode' => 200);
			} else {
				$result = array('statusCode' => -1);
			}
		} else {
			$result = array('statusCode' => 200);
		}

		return $result;
	}

/**
	 * Build the publicId for this image/model
	 * 
	 * @param  Model  $Model
	 * @return string $publicId
	 */
	private function buildPublicId(Model $Model) {
		$settings = $this->settings[$Model->alias];
		foreach ($settings['publicPattern'] as $fieldname) {

			if ($Model->$fieldname) {
				$publicParts[] = strtolower($Model->$fieldname);
			} else if ($Model->field($fieldname)) {
				$publicParts[] = strtolower($Model->field($fieldname));
			}
		}

		$publicId = implode($settings['publicSeparator'], $publicParts);
		return $publicId;
	}

	public function afterSave(Model $Model, $created = null) {
		foreach ($this->settings as $model_alias => $settings) {
			if (!empty($Model->data[$model_alias])) {
				if (!empty($settings['field_name'])) {
					$image_path = $Model->field($settings['field_name']);
					$this->syncCloudImage($Model, $image_path);
				}
			}
		}
	}

	public function afterFind(Model $Model, $results = array(), $primary = false) {

		if (!empty($results) and is_array($results) and count($results)) {
			App::import('Model', 'Cloudinary.CloudinaryLink');
			$cloudinaryLink = new CloudinaryLink;
			$cloudinary = new Cloudinary;
			foreach ($results as $i => $row) {
				
				foreach ($this->settings as $alias => $settings) {
					if (!empty($row[$alias])) {
						if (!empty($settings['thumbs']) and !empty($row[$alias]['id'])) {
							$params = array(
								'conditions' => array(
										'ref_id' => $row[$alias]['id'],
										'ref_type' => $alias
									)
								);
							if ($linkedCloud = $cloudinaryLink->find('first', $params)) {
								$cloud_name = $linkedCloud['CloudinaryLink']['name'];

								foreach ($settings['thumbs'] as $style => $dims) {
									$thumbname = $style . '_thumb_path';

									$options = array(
										"width" => $dims['w'],
										"height" => $dims['h'],
										"crop" => "fill");

									$thumbpath = $cloudinary->cloudinary_url($cloud_name, $options);
									$row[$alias][$thumbname] = $thumbpath;
								}
							}
						}

					}
				}
				$results[$i] = $row;
			}
		}
		return $results;
	}


}