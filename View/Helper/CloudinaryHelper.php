<?php
/**
 * CloudinaryHelper 
 *  A Helper for Cloudinary images
 * 
 * @author Ken Garland <ken@ufn.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * To use in your view, load an image like you would with the HtmlHelper:
 * <?php echo $this->Cloudinary->image('sample.jpg', array('height' => '70', 'width' => '90', 'crop' => 'scale')); ?>
 * Just be sure to load the Cloudinary Plugin first and set your environment variable in Bootstrap.php
 *
 *
 */

App::uses('HtmlHelper', 'View/Helper');
App::import('Vendor', 'Cloudinary.Cloudinary', array('file' => 'cloudinary_php/src/Cloudinary.php'));

class CloudinaryHelper extends HtmlHelper {

	/**
	 * Store the Cloudinary URL
	 * @var string
	 */
	public $url = null;

	/**
	 * Cloudinary Class
	 */
	public $Cloudinary;

	/**
	 * Cloudinary Environment variable
	 * @var string
	 */
	public $env = null;


	public function __construct(View $View, $options = array()) {
		parent::__construct($View, $options);
		$this->env = Configure::read('Cloudinary.env');
		if ($this->env !== false) {
			putenv($this->env);
		}
		$this->Cloudinary = new Cloudinary($this);
	}

	/**
	 * Overwriting the html helper image function since we are loading images
	 * with the full URL instead of locally stored images
	 * @see HtmlHelper::image
	 * @param string $filename
	 * @param array $options
	 * @return string
	 */
	public function image($filename, $options = array()) {

		// $path = $this->url;

		if (!isset($options['alt'])) {
			$options['alt'] = '';
		}

		$url = false;
		if (!empty($options['url'])) {
			$url = $options['url'];
			unset($options['url']);
			// convert cake routing array to string href
			$u = $this->url($url);
		}
		// TODO: fix coupling here - $this->url is defined in cl_image_tag
		$extra = $this->cl_image_tag($filename, $options);

		$image = sprintf($this->_tags['image'], $this->url, $this->_parseAttributes($options, null, '', ' '));

		if ($url) {
			return sprintf($this->_tags['link'], $u, null, $image);
		}

		return $image;
	}


	/**
	 * Use Cloudinary to build a lazy loading image tag.
	 * Load a dummy gif file for lazy loading, and use 
	 * the data-src attribute for loading actual image
	 *
	 * @param string filename of the cloudinary asset
	 * @param array options array same as image() options array plus one elements: lazy-filename for the gif loader
	 * @return string - img tag with lazy loading gif in src and real image in data-src
	 * @author dan@ufn.com
	 * 
	 * @todo refactor to handle image dimensions better - allow separate img h and w for spinner and main image
	 * @todo get just the src tag from Cloudinary (image() method uses string replace to build tag as last step)
	 * 
	 **/
	public function lazy($filename, $options = array())
	{
		// get the real image src using normal cloudinary
		$product_image_tag = $this->image($filename, $options);

		// extract the src attribute
		// load the dummy img tag using cloudinary
		// and combine them into the lazy-loader by 
		// setting the data-src to the previous (extracted) Cloudinary img src
		$options['data-src'] = $this->get_src($product_image_tag);
		$image_tag = $this->image($options['data-lazy-filename'], $options);
		return $image_tag;
	}


	/**
	 * return just the src attribute from an img tag
	 * 
	 * @param string an img tag with an src attribute in standard format
	 * @return string
	 * @author dan@ufn.com
	 *
	 **/
	protected function get_src($img_tag)
	{
		// match (case-insensitive) src= string followed by single or double quote, 
		// then capture anything that is not a single or double quote
		preg_match('/src=["\']([^"\']+)/i', $img_tag, $matches);
		return $matches[1];
	}

	/**
	 * Determine if a secure Cloudinary URL is needed based.
	 * @param string $source
	 * @param array $options
	 * @return string
	 */
	private function cloudinary_url_internal($source, &$options = array()) {
		if (!isset($options["secure"])) $options["secure"] = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' );

		return $this->Cloudinary->cloudinary_url($source, $options);
	}

	/**
	 * Transform the image
	 * @param string $source
	 * @param array $options
	 * @return string
	 */
	private function cl_image_tag($source, $options = array()) {
		$this->url = $this->cloudinary_url_internal($source, $options);
    	if (isset($options["html_width"])) $options["width"] = $this->Cloudinary->option_consume($options, "html_width");
    	if (isset($options["html_height"])) $options["height"] = $this->Cloudinary->option_consume($options, "html_height");

    	return $this->Cloudinary->html_attrs($options);
	}
}