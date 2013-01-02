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
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'ControllerModule\Controller\Exception' => 'ControllerModule\Controller\ExceptionController',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
