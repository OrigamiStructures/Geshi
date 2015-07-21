<?php
namespace Geshi\Controller;
use App\Controller\AppController;

/**
 * CakePHP Sample
 * @author dondrake
 */
class SamplesController extends AppController {
	
	public $helpers = ['Geshi.Geshi'];
	
	public $samples = 
	[
		['html5', "<!DOCTYPE HTML>
<html>
 <head>
  <title>My Precious</title>
 </head>
 <body>
  <header><h1>My precious</h1> <p>Summer 2012</p></header>
  <p>Recently I managed to dispose of a red gem that had been
  bothering me. I now have a much nicer blue sapphire.</p>
  <p>The red gem had been found in a bauxite stone while I was digging
  out the office level, but nobody was willing to haul it away. The
  same red gem stayed there for literally years.</p>
  <footer>
   Tags: <a rel=tag href=\"http://en.wikipedia.org/wiki/Gemstone\">Gemstone</a>
  </footer>
 </body>
</html>"],
		['php', "array_flip([1, 2, 3, 4]);"],
		['jquery', "// ID selector example\nvar main = \$('#main');"],
		['jquery', "// Tag selector expample\nvar allNumberedListItems = \$('ol > li');"]
	];
	
	public $implementation = [
		'// the $samples array is orgainized:
// $samples = [[language, code], [language, code]];
// where `language` and `code` are strings',
		'// Example #1 - Direct output of the parsed code.
foreach ($samples as $sample) {
	list($language, $source) = $sample;
	echo $this->Geshi->parse($source, $language);
}',
		'// Example #2 - Capture and manipulation of the object before output.
foreach ($samples as $index => $sample) {
	$index++;
	list($language, $source) = $sample;
	$geshi = $this->Geshi->make($source, $language);
	// now we can configure the object using any GeSHi method
	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
	$geshi->set_header_content("Example code #$index: $language");
	echo $geshi->parse_code();
}',
		'// Example #3 - Using GeSHi object templates

// Now I\'ll make the templates. This wouldn\'t normally be done in a .ctp like 
// this. But I\'m trying to keep the extra files in the example down to a minimum. 
// You might set these up in a Helper that uses the GeshiHelper.
// 
// I\'ll use the language name for the template name though you could use anything you want
$templates = [\'html5\', \'php\', \'jquery\'];

foreach ($templates as $name) {
	$language = $name;
	$this->Geshi->template($name, $language);
	// now we can configure the template using any GeSHi method
	$footer = ucfirst($name) . \' example. Copyright 2015, Jane Javasmith\';
	$this->Geshi->template($name)->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
	$this->Geshi->template($name)->set_line_style(\'background: #fcfcdd;\', \'background: #f4f4cc;\');
	$this->Geshi->template($name)->set_footer_content($footer);
}
// Now with the templates built, we\'ll clone fully configured objects

foreach ($samples as $sample) {
	list($template, $source) = $sample;
	// the cloning method is named dynamically \'make\' . {template name}
	$method = "make$template";
	// here we\'ll clone and output in one step
	echo $this->Geshi->$method($source)->parse_code();
	// you could capture the clone if you wanted to do additional 
	// configuration or needed all the objects for some other process
	// $geshi[] = $this->Geshi->$method($source);
}',
		'<?php
if (!$this->Geshi->templates(\'Imp\')) {
	$this->Geshi->template(\'Imp\', \'php\')->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
	$this->Geshi->template(\'Imp\')->set_overall_style(\'background: #eefcfc;\');
	$this->Geshi->template(\'Imp\')->set_line_style(\'background: #eefcfc;\');
}
?>
<div class="implementation">
<?php
	$g = $this->Geshi->makeImp($code);
	$g->start_line_numbers_at($number);
	echo $g->parse_code();
?>
</div>',
		"",
		""
	];
	
	public function index() {
		$this->set('samples', $this->samples);
		$this->set('implementation', $this->implementation);
		$this->set('imp', 0);
	}
	
}
