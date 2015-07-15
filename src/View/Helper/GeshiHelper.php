<?php
namespace Geshi\View\Helper;

use Cake\View\Helper;
use \Cake\View\View;
use Geshi\View\Helper\GeSHi;

/**
 * GeshiHelper wraps GeSHi, providing source code highlighting and output for over 100 languages. 
 * Visit http://www.qbnz.com/highlighter/ for documentation of GeSHi 
 * 
 * Under normal conditions, there is only one $config value to set; `language`. 
 * This property will default to 'php' if you don't set it.
 * 
 * The Helper sets the path to GeSHi's language parsing files. If for some reason you have different 
 * language files you need to link to, you can set $config['path'] to the full path to your files 
 * which will then be used instead of GeSHi's files.
 * 
 * This helper provides several ways to use GeSHi and GeSHi objects.
 *    1. get a new GeSHi object so you can use all the normal GeSHi calls
 *    2. get the parsed and highlighted code, but not the object that generated it (which gets destroyed)
 *    3. create named GeSHi objects to serve as templates for later use
 *        - you can use all the GeSHi calls to configure the templates
 *    4. get clones of template objects configured with source code
 * 
 * All examples assume you have the helper at $this->Geshi in your View
 * 
 * Technique #1 - Get a GeSHi object
 *	In your .ctp code:
 * 
 *		$geshi_php = $this->Geshi->make('$myArray = [1, 2, 3, 4];');
 *		$geshi_javascript = $this->Geshi->make('var myArray = [1, 2, 3, 4];', 'javascript');
 *		$default = $this->Geshi->make();
 * 
 *	The first version will prepare the GeSHi object with the provided source and will 
 *	be set to parse that source using the language currently set in the Helper. The second 
 *	version will prepare the GeSHi object with the source and set it to use javascript as 
 *	the language. The third version makes an object with no source code and using the 
 *  default language. At this point you can use any of the normal GeSHi methods on your objects.
 *	
 *		$geshi_php->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
 *		$geshi_javascript->enable_classes();
 *		
 *		echo $geshi_php->parse_code();
 *		echo $geshi_javascript->parse_code();
 * 
 * Technique #2 - Get the parsed code, disposing of the object that created it
 *	In your .ctp code:
 * 
 *		echo $this->Geshi->parse('$myArray = [1, 2, 3, 4];');
 *		echo $this->Geshi->parse('var myArray = [1, 2, 3, 4];', 'javascript');
 * 
 *	The first version will output your highlighed source code using the language 
 *	currently set in the Helper. The second version will output the source code 
 *	using javascript as the language.
 * 
 * Technique #3 - Create templates of GeSHi objects that can later be cloned 
 *	From your .ctp code or another Helper:
 * 
 *		// make a new template named Jquery that parses jquery source code
 *		$this->Geshi->template('Jquery', 'jquery');
 *		$this->Geshi->template('Jquery')->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
 *		$this->Geshi->template('Jquery')->set_line_style('background: #fcfcdd;', 'background: #f4f4cc;');
 *		$footer = 'Jquery example. Copyright 2015, Jane Javasmith, http://janejavsmith.com/javascript_license.html';
 *		$this->Geshi->template('Jquery')->set_footer_content($footer);
 * 
 * Technique #4 - Get template clones configured with source code
 *	Assuming you've implemented the code in Technique #3 (above):
 * 
 *		// GeshiHelper allows dynamic method naming for template cloning 
 *      // Append your template name to 'make' and pass the source code for the new object
 *		$sample1 = $this->Geshi->makeJquery("// ID selector example\nvar main = \$('#main');");
 *		$sample2 = $this->Geshi->makeJquery("// Tag selector expample\nvar allNumberedListItems = \$('ol > li');");
 * 
 *		echo $sample1->parse_code();
 *		echo $sample2->parse_code();
 * 
 *		// You can also clone and output in one step, but you will lose the clone object
 *		$code = "function notify(bool) {\n  if (bool) {\n    alert('true');\n  } else {\n    alert('false');\n  }\n}";
 *		echo $this->Geshi->makeJquery($code)->parse_code();
 * 
 *		// or you can make the template and the clone in one step
 *		// though this technique isn't all that useful because later changes to 
 *		// the template won't be reflected in the first clone that gets made
 *		$clone = $this->Geshi->makePHP('array_flip([1, 2, 3, 4]);'); // creates 'PHP' template using the default language
 *		$another = $this->Geshi->makeRare('(+ 1 2 3 4)', 'lisp'); // creates 'Rare' template using lisp as the language
 *	
 * The templates() method will return an array of template names or tell you if a template exists
 * 
 * @author dondrake
 */
class GeshiHelper extends Helper {
	
	protected $templates = [];
	
	protected $language = 'php';
	
	private $path;

	private $properties;

	/**
	 * 
	 * @param View $View
	 * @param array $config
	 */
	public function __construct(View $View, array $config = array()) {
		$this->path = ROOT . '/plugins/Geshi/src/View/Helper/geshi/';
		parent::__construct($View, $config);

		// This is a suspect technique. Possibly unnecessary, possibly not generalizable
		$this->properties = array_diff(
				array_keys(get_class_vars(get_class($this))), 
				array_keys(get_class_vars('Cake\View\Helper')), 
				['properties']
		);
		foreach (array_keys($this->_config) as $property) {
			if (in_array($property, $this->properties)) {
				$this->$property = $this->_config[$property];
			}
		}
	}
	
	/**
	 * Handles dynamic template cloning
	 * 
	 * You can get template clones by calling 
	 * ->makePhpBlock($source) where the template 
	 * name is 'PhpBlock'
	 * 
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public function __call($method, $params = NULL) {
		$match = preg_split('/make/', $method);
		if (count($match) === 2 && $match[0] === '') {
			return $this->makeNamed($match[1], $params);
		}
		parent::__call($method, $params);
	}
	
	/**
	 * Create and return a Geshi object
	 * 
	 * @param string $source
	 * @param string $lang
	 */
	public function make($source = '', $lang = NULL) {
		$language = is_null($lang) ? $this->language : $lang;
		$g = new GeSHi();
//		$g = $g->GeSHi($source, $language, $this->path); // this returns an array of the args not an object
//		debug($g);die;
		$g->set_language_path($this->path);
		$g->set_language($language);
		$g->set_source($source);
		return $g;
	}
	
	/**
	 * Directly create the object, then parse the code
	 * 
	 * @param string $source
	 * @param string $lang
	 * @return string The output
	 */
	public function parse($source, $lang = NULL) {
		return $this->make($source, $lang)->parse_code();
	}
	
	/**
	 * Create and store a Geshi object to use as a template
	 * 
	 * @param string $name
	 * @param string $lang
	 */
	public function template($name, $lang = NULL) {
		if (!isset($this->templates[$name]) || !is_object($this->templates[$name]) || !stristr(get_class($this->templates[$name]), 'GeSHi')) {
			$language = is_null($lang) ? $this->language : $lang;
			$this->templates[$name] = new GeSHi('', '', $this->path); // the first two args don't do anything
			$this->templates[$name]->set_language($language);
		}		
		return $this->templates[$name];
//		debug($this->templates[$name]);die;
//		return $this->templates[$name];
	}
	
	/**
	 * Clone the named template, add the source, and return the Geshi object
	 * 
	 * @param string $source
	 */
	public function makeNamed($name, $args) {
		$source = isset($args[0]) ? $args[0] : '';
		$language = isset($args[1]) ? $args[1] : $this->language;
		
		//where Named is a previously made and named template to clone with new source.
		if (!isset($this->templates[$name]) || !is_object($this->templates[$name]) || get_class($this->templates[$name]) !== 'GeSHi') {
			$this->template($name, $language);
		}
		$obj = clone $this->templates[$name];
		$obj->set_source($source);
		return $obj;
	}
	
	/**
	 * Get a list of template names or determine if a template exists
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function templates($name = NULL) {
		if (is_null($name)) {
			return array_keys($this->templates);
		}
		return isset($this->templates[$name]);
	}

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     *
     * @return array
     */
    public function __debugInfo()
    {
		$parent = ['Parent Properties' => parent::__debugInfo()];
		$properties = [];
		foreach ($this->properties as $property) {
			$properties[$property] = $this->$property;
		}
		
		return $properties + $parent;
    }
}
