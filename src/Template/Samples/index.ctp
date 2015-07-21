<style>
	pre {
		padding: 2.5em;
		background-color: #eee;
	}
	div.implementation > pre {
		padding: 1.5em;
		margin: 0 5em;
	}
</style>
<h3>Overview</h3>
<p>GeSHi generally returns an object that can then be modified through a series 
	of calls to its methods. The calls determine the language assumed when 
	parsing the code, set formatting for code blocks and determine the use of 
	css (inline or style sheets). Once the objects are configured, other call 
	will output the highlighted code.</p>
<p>The Geshi Plugin give you methods to create GeSHi objects. You can work with the 
	objects exactly as you would GeSHi object (beacuse that's what they are). 
	You can also create and output the objects in a single step. You might assume 
	this would limit the formatting options for your code. But the Geshi Plugin 
	also provides tools to make templates which can carry all your formatting 
	and configuration, so it's possible to create and output very complex GeSHi 
	objects in a single step.</p>
<?= $this->element('Geshi.implementation', ['code' => array_shift($implementation), 'number' => 1]); ?>

<?php
// the $samples array is orgainized:
// $samples = [[language, code], [language, code]];
// where `language` and `code` are strings

// This illustrates the direct output of parsed code 
// using the default GeSHi settings and ignoring the GeSHi objects
?>
<h3>Example #1 - Direct output of the parsed code.</h3>
<p>This is the implementation code</p>
<?= $this->element('Geshi.implementation', ['code' => array_shift($implementation), 'number' => 26]); ?>
<p>This is the result</p>
<?php
// Example #1 - Direct output of the parsed code.
foreach ($samples as $sample) {
	list($language, $source) = $sample;
	echo $this->Geshi->parse($source, $language);
}

// This illustrates the capture of geshi objects so their 
// settings can be changed before output.
?>
<h3>Example #2 - Capture and manipulation of the object before output.</h3>
<p>This is the implementation code</p>
<?= $this->element('Geshi.implementation', ['code' => array_shift($implementation), 'number' => 40]); ?>
<p>This is the result</p>
<?php
// Example #2 - Capture and manipulation of the object before output.
foreach ($samples as $index => $sample) {
	$index++;
	list($language, $source) = $sample;
	$geshi = $this->Geshi->make($source, $language);
	// now we can configure the object using any GeSHi method
	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
	$geshi->set_header_content("Example code #$index: $language");
	echo $geshi->parse_code();
}

// This illustrates the capture of geshi objects so their 
// settings can be changed before output.
?>
<h3>Example #3 - Using GeSHi object templates</h3>
<p>This is the implementation code</p>
<?= $this->element('Geshi.implementation', ['code' => array_shift($implementation), 'number' => 59]); ?>
<p>This is the result</p>
<?php
// Example #3 - Using GeSHi object templates

// Now I'll make the templates. This wouldn't normally be done in a .ctp like 
// this. But I'm trying to keep the extra files in the example down to a minimum. 
// You might set these up in a Helper that used the GeshiHelper.
// 
// I'll use the language name for the template name though you could use anything you want
$templates = ['html5', 'php', 'jquery'];

foreach ($templates as $name) {
	$language = $name;
	$this->Geshi->template($name, $language);
	// now we can configure the template using any GeSHi method
	$footer = ucfirst($name) . ' example. Copyright 2015, Jane Javasmith';
	$this->Geshi->template($name)->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
	$this->Geshi->template($name)->set_line_style('background: #fcfcdd;', 'background: #f4f4cc;');
	$this->Geshi->template($name)->set_footer_content($footer);
}
// Now with the templates built, we'll clone fully configured objects

foreach ($samples as $sample) {
	list($template, $source) = $sample;
	// the cloning method is named dynamically 'make' . {template name}
	$method = "make$template";
	// here we'll clone and output in one step
	echo $this->Geshi->$method($source)->parse_code();
	// you could capture the clone if you wanted to do additional 
	// configuration or needed all the objects for some other process
	// $geshi[] = $this->Geshi->$method($source);
}
?>
<p>You can also use the ->templates($name) method to detect whether a template exists.</p>
<?= $this->element('Geshi.implementation', ['code' => array_shift($implementation), 'number' => 1]); ?>
<p>Omitting the argument to ->templates() will return an array of all template names.</p>
<p>It's worth noting that most GeSHi methods DO NOT return the object so they can't be chained. The GeshiHelper::template() and GeshiHelper::make{name} methods do return objects. So you can chain ONE GeSHi call on to template(). And you can chain ->parse_code() onto ->make{name}. But if you try to chain any configuration method onto ->make{name} YOU WON'T GET AN OBJECT BACK. Take a look at lines 9-11 in the implementation code above for an example.</p>
