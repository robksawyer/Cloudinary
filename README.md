Cloudinary Component for CakePHP 2.x
====================================
A Cakephp component for Cloudinary cloud image service.

# Installation
1. Download and extract Cloudinary PHP library from https://github.com/cloudinary/cloudinary_php to app/Vendor/Cloudinary

2. Download from https://github.com/ryanicle/cakephp-cloudinary-component

3. Set variables in app/Config/bootstrap.php

			// Cloudinary.env - sign up and get this from http://cloudinary.com
			// Cloudinary.path - tmp path for images
			Configure::write(
				'Cloudinary', 
				array(
					'env' =>  '',
					'path' => APP . 'webroot' . DS . 'img' . DS . 'photos'
				)
			);

4. Place CloudinaryComponent.php in app/Controller/Component/

5. Done!

# Usage

Call the component in controller

			class ExampleController extends AppController {
				public $name = 'Example';

				public $components = array(
					'CloudinaryComponent'
				);

				public function index() {
					// Assume you have already uploaded images to tmp locations
					$this->Cloudinary($upload);
				}
			}			
# Limitation
	Currently, it only supports uploading and deleteing images. 

# Need help or want to contribute?
	Please feel free to drop me a note.