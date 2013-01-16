<?php
return array(
    'router' => array(
        'routes' => array(
            'generic-client-exception' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/generic-client-exception',
                    'defaults' => array(
                        'controller' => 'ControllerModule\Controller\Exception',
                        'action'     => 'generic-client-exception',
                    ),
                ),
            ),

            'generic-server-exception' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/generic-server-exception',
                    'defaults' => array(
                        'controller' => 'ControllerModule\Controller\Exception',
                        'action'     => 'generic-server-exception',
                    ),
                ),
            ),

            'unauthorized-exception' => array(
                'type' => 'literal',
                'options' => array(
                    'route'    => '/unauthorized-exception',
                    'defaults' => array(
                        'controller' => 'ControllerModule\Controller\Exception',
                        'action'     => 'unauthorized-exception',
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'ControllerModule\Controller\Exception' => 'ControllerModule\Controller\ExceptionController',
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
