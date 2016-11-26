<?php

return array(
    'view_helpers' => array(
        'factories' => array(
            'dojo' => 'Dojo\View\Helper\DojoFactory',
        ),
        'invokables' => array(
            'JsonRestStore' => 'Dojo\View\Helper\JsonRestStore'
        )
    ),
);