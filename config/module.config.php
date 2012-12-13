<?php

return array(
    'view_helpers' => array(
        'factories' => array(
            'Dojo' => 'Dojo\View\Helper\DojoFactory',
        ),
        'invokables' => array(
            'JsonRestStore' => 'Dojo\View\Helper\JsonRestStore'
        )
    ),
);