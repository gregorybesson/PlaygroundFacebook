<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'playgroundfacebook_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundFacebook/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundFacebook\Entity' => 'playgroundfacebook_entity'
                )
            )
        )
    ),

    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundfacebook'
            )
        )
    ),

    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view/frontend',
            __DIR__ . '/../view/admin'
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'playgroundfacebook_admin_app' => 'PlaygroundFacebook\Controller\Admin\AppController',
            'playgroundfacebook_admin_page' => 'PlaygroundFacebook\Controller\Admin\PageController',
            'playgroundfacebook' => 'PlaygroundFacebook\Controller\IndexController'
        )
    ),

    'router' => array(
        'routes' => array(
            'frontend' => array(
                'child_routes' => array(
                    'facebook' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/face-book',
                            'defaults' => array(
                                'controller' => 'playgroundfacebook',
                                'action' => 'index'
                            )
                        )
                    )
                )
            ),
            'admin' => array(
                'child_routes' => array(
                    'facebook' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/facebook',
                            'defaults' => array(
                                'controller' => 'playgroundfacebook_admin_app',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'app' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/app',
                                    'defaults' => array(
                                        'controller' => 'playgroundfacebook_admin_app',
                                        'action' => 'index'
                                    )
                                ),
                                'child_routes' => array(
                                    'list' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/list[/:p]',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'list'
                                            )
                                        )
                                    ),
                                    'create' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/create/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'create',
                                                'appId' => 0
                                            )
                                        )
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'edit',
                                                'appId' => 0
                                            )
                                        )
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'remove',
                                                'appId' => 0
                                            )
                                        )
                                    ),
                                    'preinstall' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/preinstall/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'preinstall',
                                                'appId' => 0
                                            )
                                        )
                                    ),
                                    'install' => array(
                                            'type' => 'Segment',
                                            'options' => array(
                                                    'route' => '/install/:appId',
                                                    'defaults' => array(
                                                            'controller' => 'playgroundfacebook_admin_app',
                                                            'action' => 'install',
                                                            'appId' => 0
                                                    )
                                            )
                                    ),

                                    'uninstall' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/uninstall/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'uninstall',
                                                'appId' => 0
                                            )
                                        )
                                    )
                                )
                            ),
                            'page' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/page',
                                    'defaults' => array(
                                        'controller' => 'playgroundfacebook_admin_page',
                                        'action' => 'index'
                                    )
                                ),
                                'child_routes' => array(
                                    'list' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/list[/:p]',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_page',
                                                'action' => 'list'
                                            )
                                        )
                                    ),
                                    'create' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/create/:pageId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_page',
                                                'action' => 'create',
                                                'pageId' => 0
                                            )
                                        )
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:pageId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_page',
                                                'action' => 'edit',
                                                'pageId' => 0
                                            )
                                        )
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:pageId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_page',
                                                'action' => 'remove',
                                                'pageId' => 0
                                            )
                                        )
                                    ),

                                    'install' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/install/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'install',
                                                'appId' => 0
                                            )
                                        )
                                    ),

                                    'uninstall' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/uninstall/:appId',
                                            'defaults' => array(
                                                'controller' => 'playgroundfacebook_admin_app',
                                                'action' => 'uninstall',
                                                'appId' => 0
                                            )
                                        )
                                    )
                                )
                            )
                        ),
                    ),
                )
            )
        )
    ),

    'core_layout' => array(
        'PlaygroundFacebook' => array(
            'default_layout' => 'layout/2columns-left',
            'children_views' => array(
                'col_left' => 'playground-user/user/col-user.phtml'
            ),
            'controllers' => array(
                'playgroundfacebook_admin_app' => array(
                    'default_layout' => 'layout/admin'
                ),
                'playgroundfacebook_admin_page' => array(
                    'default_layout' => 'layout/admin'
                )
            )
        )
    ),

    'navigation' => array(
        'admin' => array(
            'playgroundfacebookadmin' => array(
                'order' => 70,
                'label' => 'Facebook',
                'route' => 'admin/facebook/app/list',
                'resource' => 'facebook',
                'privilege' => 'list',
                'pages' => array(
                    'list' => array(
                        'label' => 'Apps list',
                        'route' => 'admin/facebook/app/list',
                        'resource' => 'facebook',
                        'privilege' => 'list'
                    ),
                    'create' => array(
                        'label' => 'New appli',
                        'route' => 'admin/facebook/app/create',
                        'resource' => 'facebook',
                        'privilege' => 'add'
                    ),
                    'list_pages' => array(
                        'label' => 'Pages list',
                        'route' => 'admin/facebook/page/list',
                        'resource' => 'facebook',
                        'privilege' => 'list'
                    ),
                    'create_page' => array(
                        'label' => 'New page',
                        'route' => 'admin/facebook/page/create',
                        'resource' => 'facebook',
                        'privilege' => 'add'
                    )
                )
            )
        )
    )
);
