<?php
if (!$this->Geshi->templates('Imp')) {
	$this->Geshi->template('Imp', 'php')->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
	$this->Geshi->template('Imp')->set_overall_style('background: #eefcfc;');
	$this->Geshi->template('Imp')->set_line_style('background: #eefcfc;');
}
?>
<div class="implementation">
<?php
	$g = $this->Geshi->makeImp($code);
	$g->start_line_numbers_at($number);
	echo $g->parse_code();
?>
</div>
