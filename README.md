Cloudinary Component for CakePHP 2.x
====================================
A Cakephp component for Cloudinary cloud image service.

# Installation
1. Download and extract Cloudinary PHP library from https://github.com/cloudinary/cloudinary_php to app/Vendor/Cloudinary
This is easy if you just add it to your composer.json and run `composer.phar update`
```
{
  "require": {
    "cloudinary/cloudinary_php": "dev-master"
  }
}
```

2. Rename the file app/Config/bootstrap.php.example to bootstrap.php and update the variables. You should be able to find these after you sign up with Cloudinary.

			// Cloudinary.env - sign up and get this from http://cloudinary.com
			// Cloudinary.path - tmp path for images
			Configure::write(
				'Cloudinary', 
				array(
					'env' =>  '',
					'path' => APP . 'webroot' . DS . 'img' . DS . 'photos'
				)
			);

3. Load the plugin via app/Config/bootstrap.php
```
CakePlugin::load(array('Cloudinary');
```

4. Add either the behavior to your model `$actsAs = array('Cloudinary')` and use the plugin that way, or use the component and by adding it to the `$components` variable in your controller.

5. Done!

# Usage

Call the component in controller

			class ExampleController extends AppController {
				public $name = 'Example';

				public $components = array(
					'Cloudinary.CloudinaryComponent'
				);

				public function index() {
					// Assume you have already uploaded images to tmp locations
					$this->Cloudinary->upload($file);
				}
			}			
# Limitation
	Currently, it only supports uploading and deleteing images. 

# Need help or want to contribute?
	Please feel free to drop me a note.