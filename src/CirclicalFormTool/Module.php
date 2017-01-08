<?php

namespace CirclicalFormTool;

use Zend\Console\Adapter\AdapterInterface;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }


    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'formtool create [--doctrine|-d] <module> <form>' => 'Create a form, input filter, and all related elements',

            ['module'           => 'The ZF2 module in which the form set should be created'],
            ['form'             => 'The name of the form you are creating, e.g. User'],
            ['--doctrine|-d'    => 'Wire it out as a Doctrine form'],
            ['object'           => 'The object that is hydrated by this form (you code this separately)'],
        ];
    }
}