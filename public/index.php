<?php



include_once ('..' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'conf.inc.php');

 

if (stristr(BDO_DIR_REDIRECT, '.php')) {

    if (file_exists(BDO_DIR_REDIRECT)) {

        include (BDO_DIR . 'inc' . DS . 'template.php');

        include (BDO_DIR_REDIRECT);

    }

    else {

        $_GET['error'] = '404';

        include 'erreur.php';

    }

}

else {

    include_once (BDO_DIR . 'config' . DS . 'bootstrap.php');

    if (DEBUG) Bdo_Debug::execTime("chargement page");

    

    $fileController = BDO_DIR . 'mvc' . DS . 'controllers' . DS . $controller . '.php';
	//echo $fileController;
	
    if (file_exists($fileController)) {

        require_once $fileController;



        $o_controller = new $controller();
		
        if (method_exists($o_controller, $action)) {

            

            unset($params[0]);

            unset($params[1]);
			

            call_user_func_array(array(

                    $o_controller,

                    $action

            ), $params);

        }

        else {

            new Bdo_Error(404);

        }

    }

    else {

        new Bdo_Error(404);

    }

    

    // ---------------------------------------------------------------

    if (DEBUG) {

        echo Bdo_Debug::saveInfoVar($_SESSION, '_SESSION', '_SESSION en sortie');

        echo Bdo_Debug::affExecTime();

        echo Bdo_Debug::viewInclude();

        echo Bdo_Debug::viewInfoVar();

        echo Bdo_Debug::bilanQuery();

    }

    // echo '<pre>';

    // print_r(cfg::schema());

    

}
