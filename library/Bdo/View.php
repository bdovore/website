<?php

class Bdo_View
{

    public $html = '';

    protected $type = '';

    public $title = CFG_NAME;

    public $titlePage = '';

    public $onLoad = '';

    public $a_header = array();

    public $a_cssFile = array();

    public $a_jsFile = array();

    public $a_phtmlFile = array();

    public $a_var = array();

    public $a_infoPage = array();

    public $a_alertPage = array();

    public $addAriane = null;

    public $a_htmlEndFile = array(); // tableau contenant le code html à
                                     // afficher en fin de fichier
    public $vars = array();

    public $layout = "default";

    // initialisation
    public function __construct ()
    {
        $this->title .= (CFG_READONLY ? ' (' . LANG_READONLY . ')' : '');
    }

    public function setType ($type)
    {
        $this->type = $type;
    }

    public function getType ()
    {
        return $this->type;
    }

    public function addJavascriptFile ($filename)
    {
        if (! in_array($filename, $this->a_jsFile)) {
            $this->a_jsFile[] = $filename;
        }
    }

    public function addTitle ($title)
    {
        $this->title .= ' - ' . TransConstante($title);
    }

    public function addTitlePage ($title, $addToTitle = true)
    {
        $this->titlePage .= (empty($this->titlePage) ? '' : ' - ') . TransConstante($title);
        if ($addToTitle) {
            $this->title .= ' - ' . TransConstante($title);
        }
    }

    public function addSubTitlePage ($title, $addToTitle = true)
    {
        $this->subTitlePage .= (empty($this->subTitlePage) ? '' : ' - ') . TransConstante($title);
        if ($addToTitle) {
            $this->title .= ' - ' . TransConstante($title);
        }
    }

    public function addOnLoadPage ($js)
    {
        $this->onLoad .= $js;
    }

    public function addHeader ($txt)
    {
        if (! in_array($txt, $this->a_header)) {
            $this->a_header[] = $txt;
        }
    }

    public function addCssFile ($filename)
    {
        if (! in_array($filename, $this->a_cssFile)) {
            $this->a_cssFile[] = $filename;
        }
    }

    public function addPhtmlFile ($filename, $mask, $unshift = false)
    {
        if ($unshift) {
            $this->a_phtmlFile = array_reverse($this->a_phtmlFile, true);
            $this->a_phtmlFile[$mask] = $filename;
            $this->a_phtmlFile = array_reverse($this->a_phtmlFile, true);
            // array_unshift($this->a_phtmlFile, $filename);
        }
        else {
            $this->a_phtmlFile[$mask] = $filename;
        }
    }

    public function addVar ($name, $value)
    {
        $this->a_var[$name] = $value;
    }

    public function addInfoPage ($value)
    {
        if (is_array($value)) {
            $this->a_infoPage = array_merge($this->a_infoPage, $value);
        }
        else {
            $this->a_infoPage[] = $value;
        }
    }

    public function addAlertPage ($value)
    {
        if (is_array($value)) {
            $this->a_alertPage = array_merge($this->a_alertPage, $value);
        }
        else {
            $this->a_alertPage[] = $value;
        }
    }

    public function addAriane ($value = null, $baseAriane = null)
    {
        if ($value) {
            $this->addAriane = $value;
        }

        if ($baseAriane) {
            Bdo_Cfg::setVar('baseAriane', $baseAriane);
        }
    }


    public function bugAcces ($msg = null)
    {
        $this->addVar('acl', Bdo_Cfg::getVar('acl'));
        $this->addVar('user', Bdo_Cfg::getVar('user'));

        // $this->a_alertPage = array();

        if ($msg) {
            $this->addAlertPage($msg);
        }
        else {
            if (Bdo_User::minLevel(0)) {
                $this->addAlertPage("<font size=4>" . LANG_BUGACCESNO . "</font><br />" . LANG_BUGACCESPAGE2);
            }
            else {
                $this->addAlertPage("<font size=4>" . LANG_BUGACCESNO . "</font><br />" . LANG_BUGACCESPAGE1);
            }
        }

        $this->flush();
        Bdo_Cfg::quit();
    }

    public function flush ()
    {
        foreach ($this->a_var as $key => $value) {
            $$key = $value;
        }

        $this->addHeader('Content-Type: text/html; charset=UTF-8');

        if ($this->type != 'xhr') {
            $this->addPhtmlFile(BDO_DIR . "design/header.php", true);
            $this->addPhtmlFile(BDO_DIR . "design/footer.php");
        }
        else {
            $this->addHeader('cache-control: no-cache');
            if (! empty($this->onLoad)) {
                $this->a_htmlEndFile[] = '
				<script language="javascript">
				' . $this->onLoad . '
				</script>
				';
            }
        }

        $view = $this;

        foreach ($this->a_header as $header) {
            header($header);
        }

        foreach ($this->a_phtmlFile as $filename) {
            include ($filename);
        }

        foreach ($this->a_htmlEndFile as $code_html) {
            echo $code_html . "\n";
        }
        if ($this->type != 'xhr') {
            echo "
		</body>
		</html>
		";
        }
    }

    public function set_var ($d, $var = null)
    {
        if (! is_array($d)) {
            $d = array(
                    $d => $var
            );
        }
        $this->a_var = array_merge($this->a_var, $d);
    }

    public function render ()
    {
        $user = Bdo_Cfg::user();
        foreach ($this->a_var as $key => $value) {
            $this->$key = $value;
        }
        $view = $this;
        foreach ($this->a_phtmlFile as $var => $filename) {
            ob_start();
            require BDO_DIR . 'mvc' . DS . 'views' . DS . 'views_controllers' . DS . strtolower($filename) . '.phtml';
            $view->sview[$var] = ob_get_clean();
            $view->$var = $view->sview[$var];
        }

        if ($this->layout) {
            require BDO_DIR . 'mvc' . DS . 'views' . DS . 'layout' . DS . $this->layout . '.phtml';
        }
        else {
            foreach ($view->sview as $content) {
                echo $content;
            }
        }
    }

    public function getHelper ($helperName)
    {
         require BDO_DIR . 'mvc' . DS . 'views' . DS . 'helpers' . DS . strtolower($helperName) . '.php';
         $classhelperName = ucfirst($helperName);
         return new $classhelperName;
    }
}
