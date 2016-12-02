<?php

return array(
    'view_helpers' => array(
        'factories' => array(
            'dojo' => \Dojo\View\Helper\DojoFactory::class,
        ),
        'invokables' => array(
            'jsonRestStore' => \Dojo\View\Helper\JsonRestStore::class
        )
    ),
);