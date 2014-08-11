<?php
function htmltag_textbox($box_name, $box_lenght, $box_value) {
	$tag = '<input name="' . $box_name . '"';
	if ($box_lenght != '')
		$tag .= ' size="' . $box_lenght . '"';
	if ($box_value != '')
		$tag .= ' value="' . $box_value . '"';
	$tag .= ' class="flat" type="text" />';
	return $tag;
}
function htmltag_textarea($box_name, $box_lenght, $box_height, $box_value) {
	$tag = '<textarea name="' . $box_name . '"';
	if ($box_lenght != '')
		$tag .= ' cols="' . $box_lenght . '"';
	if ($box_height != '')
		$tag .= ' rows="' . $box_height . '"';
	$tag .= ">";
	if ($box_value != '')
		$tag .= $box_value;
	$tag .= '</textarea>';
	return $tag;
}
function htmltag_checkbox($box_name, $box_value, $box_checked) {
	$tag = '<input name="' . $box_name . '"';
	if ($box_value != '')
		$tag .= ' value="' . $box_value . '"';
	$tag .= ' type="checkbox"';
	if ($box_checked)
		$tag .= " checked";
	$tag .= ' />';
	return $tag;
}
function htmltag_listbox($box_name, $box_values, $box_default) {
	$tag = '<select name="' . $box_name . '" size="1">';
	$tag .= GetOptionValue ( $box_values, $box_default );
	$tag .= '</select>';
	return $tag;
}
