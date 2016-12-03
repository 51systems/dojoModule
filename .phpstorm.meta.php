<?php

namespace PHPSTORM_META {
    override(\Zend\View\Renderer\PhpRenderer::plugin(0), map([
        'dojo' => Dojo\View\Helper\Dojo::class,
        'jsonRestStore' => \Dojo\View\Helper\JsonRestStore::class
    ]));
}