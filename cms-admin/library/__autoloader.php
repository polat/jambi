<?php

/*
 * An autoloader file for Composer
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';


/*
 * An autoloader file for ReCaptcha library
 */
require_once dirname(__FILE__) . '/vendor/ReCaptcha/autoload.php';


/*
 * An autoloader for library/system/Foo or library/handle/Foo classes. This should be required()
 * by the user before attempting to instantiate any of the library classes.
 */
function autoloadSystemLibrary($class)
{
    $systemDefault = dirname(__FILE__) . '/system/' . $class . '.php';

    if (is_readable($systemDefault)) {
        require_once $systemDefault;
    }
}

spl_autoload_register("autoloadSystemLibrary");