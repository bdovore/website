<?php

class Jquery
{

    public function alert ($a_var)
    {
        if (! is_array($a_var)) {
            $a_var = array(
                    $a_var
            );
        }
        
        $xhtml = '
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
		<strong>Alerte ! </strong>' . implode('<br />', $a_var) . '</p>
	</div>
</div>';
        return $xhtml;
    }

    public function info ($a_var)
    {
        if (! is_array($a_var)) {
            $a_var = array(
                    $a_var
            );
        }
        $xhtml = '
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Info ! </strong>' . implode('<br />', $a_var) . '</p>
	</div>
</div>';
        return $xhtml;
    }

    public function idea ($a_var)
    {
        if (! is_array($a_var)) {
            $a_var = array(
                    $a_var
            );
        }
        $xhtml = '
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class=".ui-state-focus ui-icon ui-icon-lightbulb" style="float: left; margin-right: .3em;"></span>
		<strong>Idée !</strong><div class="idea">' . implode('<br />', $a_var) . '</div></p>
	</div>
</div>';
        return $xhtml;
    }
}