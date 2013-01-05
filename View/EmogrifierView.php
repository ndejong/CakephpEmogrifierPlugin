<?php

/* ***************************************************************************
 * Cakephp EmogrifierPlugin
 * Nicholas de Jong - http://nicholasdejong.com - https://github.com/ndejong
 * 19 December 2011
 * 
 * @author Nicholas de Jong
 * @copyright Nicholas de Jong
 * ***************************************************************************/

App::uses('View', 'View');

/**
 * All <style> tags found in the content will be moved inline: <tag style="...">
 * 
 * To prevent this for individual style tags (such as when using media queries, targeting mobiles or 
 * specific email clients), use <style class="notInline">...</style>.  These tags will be left as-is.
 * 
 * ### Usage 
 * 
 * class ExampleController extends AppController {
 * 
 * 		public function index () {
 * 			// This will send the view output through the Emogrifier parser, moving all styles inline
 *			$this->viewClass = 'Emogrifier.Emogrifier';
 * 		}
 * 
 * 		public function sendEmail () {
 * 			App::uses('CakeEmail', 'Network/Email');
 *			$email = new CakeEmail();
 *			$email->to(...)
 *				  ->from(...)
 *				  ->subject(...)
 *				  ->viewVars(...)
 *				  ->template('myTemplate', 'myLayout')
 *				  ->viewRender('Emogrifier.Emogrifier')
 *				  ->emailFormat('html')
 *				  ->send();
 *		}
 * }
 * 
 */
class EmogrifierView extends View {
	
	/**
	 * $media_types - if a <link> element has a media attribute, we only 
	 * slurp up the CSS those that match these
	 * 
	 * @var array
	 */
	public $media_types = array('all','screen');
	
	/**
	 * $remove_css - remove the original css from HTML, includes the removal 
	 * of <link> and <style> elements
	 * 
	 * @var bool
	 */
	public $remove_css = true;
	
	/**
	 * $import_external_css - determines if we attempt to pull down external @import css
	 * 
	 * @var bool
	 */
	public $import_external_css = false;

	/**
	 * Constructor
	 *
	 * @param Controller $controller The controller with viewVars
	 */
	public function __construct($controller = null) {
		parent::__construct($controller);
	}

	/**
	 * Render with Emogrification
	 *
	 * @param string $view
	 * @param string $layout
	 * @return mixed
	 * @throws NotFoundException
	 */
	public function render($view = null, $layout = null) {

		// If it is the text/both version of an email: ->emailFormat('both')
		if(strpos($this->layoutPath,'Emails/text') !== false && strpos($this->viewPath,'Emails/text') !== false)
			return parent::render($view, $layout);

		// Let the parent do it's rendering thing first
		parent::render($view, $layout);
		
		// Parse out the CSS into a string and remove any CSS from the output
		$css = $this->_extractAndRemoveCss();

		// Import the Emogrifier class
		App::import('Vendor', 'Emogrifier.emogrifier');
		$Emogrifier = new Emogrifier($this->output, $css);

		// Emogrification!
		$this->output = @$Emogrifier->emogrify();
		
		//echo $this->output;exit;
		return $this->output;
	}
	
	/**
	 * _extractAndRemoveCss - extracts any CSS from the rendered view and 
	 * removes it from the $this->output
	 * 
	 * @return string
	 */
	protected function _extractAndRemoveCss() {
		
		$html = $this->output;
		$css = null;
		
		$DOM = new DOMDocument;
		$DOM->loadHTML($html);
		
		// DOM removal queue
		$remove_doms = array();
		
		// catch <link> style sheet content
		$links = $DOM->getElementsByTagName('link');
		
		foreach($links as $link) {
			if($link->hasAttribute('href') && preg_match("/\.css$/i",$link->getAttribute('href'))) {
				
				// find the css file and load contents
				if($link->hasAttribute('media')) {
					foreach($this->media_types as $css_link_media) {
						if(strstr($link->getAttribute('media'),$css_link_media)) {
							$css .= $this->_findAndLoadCssFile($link->getAttribute('href'))."\n\n";
							$remove_doms[] = $link;
						}
					}
				}
				else {
					$css .= $this->_findAndLoadCssFile($link->getAttribute('href'))."\n\n";
					$remove_doms[] = $link;
				}
			}
		}
		
		// Catch embeded <style> and @import CSS content
		$styles = $DOM->getElementsByTagName('style');
		
		// Style
		foreach($styles as $style) {
			if($style->getAttribute('class') == 'notInline') continue;
			if($style->hasAttribute('media')) {
				foreach($this->media_types as $css_link_media) {
					if(strstr($style->getAttribute('media'),$css_link_media)) {
						$css .= $this->_parseInlineCssAndLoadImports($style->nodeValue);
						$remove_doms[] = $style;
					}
				}
			}
			else {
				$css .= $this->_parseInlineCssAndLoadImports($style->nodeValue);
				$remove_doms[] = $style;
			}
		}
		
		// Remove
		if($this->remove_css) {
			foreach($remove_doms as $remove_dom) {
				try {
					$remove_dom->parentNode->removeChild($remove_dom);
				} catch (DOMException $e) {}
			}
			// Throw the new output back onto $this->output
			$this->output = $DOM->saveHTML();
		}
		
		//debug($css);exit;
		return $css;
	}
	
	/**
	 * _findAndLoadCssFile - finds the appropriate css file within the CSS path
	 * 
	 * @param string $css_href 
	 */
	protected function _findAndLoadCssFile($css_href) {

		$css_filenames = array_merge($this->_globRecursive(CSS.'*.Css'),$this->_globRecursive(CSS.'*.CSS'),$this->_globRecursive(CSS.'*.css'));
		
		// Build an array of the ever more path specific $css_href location
		$css_hrefs = explode('/',$css_href);
		$css_href_paths = array();
		for($i=count($css_hrefs)-1;$i>0;$i--) {
			if(isset($css_href_paths[count($css_href_paths)-1])) {
				$css_href_paths[] = $css_hrefs[$i].DS.$css_href_paths[count($css_href_paths)-1];
			}
			else {
				$css_href_paths[] = $css_hrefs[$i];
			}
		}
		
		// the longest string match will be the match we are looking for
		$best_css_filename = null;
		$best_css_match_length = 0;
		foreach($css_filenames as $css_filename) {
			foreach($css_href_paths as $css_href_path) {
				$regex = "/".str_replace('/','\/',str_replace('.','\.',$css_href_path))."/";
				if(preg_match($regex,$css_filename,$match)) {
					if(strlen($match[0]) > $best_css_match_length) {
						$best_css_match_length = strlen($match[0]);
						$best_css_filename = $css_filename;
					}
				}
			}
		}
		
		$css = null;
		if(!empty($best_css_filename) && is_file($best_css_filename)) {
			$css = file_get_contents($best_css_filename);
		}
		
		return $css;
	}
	
	/**
	 * _globRecursive
	 * 
	 * @param string $pattern
	 * @param int $flags
	 * @return array 
	 */
	protected function _globRecursive($pattern, $flags = 0) {

		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->_globRecursive($dir . '/' . basename($pattern), $flags));
		}

		return $files;
	}
	
	/**
	 * _parseInlineCssAndLoadImports
	 */
	protected function _parseInlineCssAndLoadImports($css) {
		
		// Remove any <!-- --> comment tags - they are valid in HTML but we probably 
		// don't want to be commenting out CSS
		$css = str_replace('-->','',str_replace('<!--','',$css))."\n\n";
		
		// Load up the @import CSS if any exists
		preg_match_all("/\@import.*?url\((.*?)\)/i",$css,$matches);
		
		if(isset($matches[1]) && is_array($matches[1])) {
			
			// First remove the @imports
			$css = preg_replace("/\@import.*?url\(.*?\).*?;/i",'', $css);
			
			foreach($matches[1] as $url) {
				if(preg_match("/^http/i",$url)) {
					if($this->import_external_css) {
						$css .= file_get_contents($url);
					}
				}
				else {
					$css .= $this->_findAndLoadCssFile($url);
				}
			}
		}
		
		return $css;
	}

}
