<?php

$baseDir = __DIR__ . '/../../';

$finder = PhpCsFixer\Finder::create()
    ->in([
        $baseDir . 'src',
        $baseDir . 'tests',
    ])
    ->name('*.php');

$config = new PhpCsFixer\Config();
return $config
    ->setRules(
        require_once __DIR__ . '/php-cs-fixer-rules.php'
    )
    ->setFinder($finder);
