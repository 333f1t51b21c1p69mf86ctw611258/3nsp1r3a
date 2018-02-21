<?php
// uncomment at mainteannce
Router::parseExtensions('json');

//Router::connect('/', array('controller'=>'AppGenerics', 'action'=>'display', 'index'));
Router::connect('*', array('controller' => 'offlines', 'action' => 'index'));
Router::connect('/User/:action/*', array('controller' => 'User' ));

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();
        

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
