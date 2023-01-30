<?php

return [
    'lingua.selector' => [
        'file' => 'lingua.selector.snippet',
        'description' => 'Languages selector drop down.',
        'properties' => [
            'codeField' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'getKey' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'phsPrefix' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'sortby' => [
                'type' => 'list',
                'value' => 'id',
                'options' => [
                    [
                        'text' => 'ID',
                        'value' => 'id',
                        'name' => 'ID',
                    ],
                    [
                        'text' => 'iso_code',
                        'value' => 'iso_code',
                        'name' => 'iso_code',
                    ],
                ],
            ],
            'sortdir' => [
                'type' => 'list',
                'value' => 'asc',
                'options' => [
                    [
                        'text' => 'ASC',
                        'value' => 'asc',
                        'name' => 'ASC',
                    ],
                    [
                        'text' => 'DESC',
                        'value' => 'desc',
                        'name' => 'DESC',
                    ],
                ],
            ],
            'tplItem' => [
                'type' => 'textfield',
                'value' => 'lingua.selector.item',
            ],
            'tplWrapper' => [
                'type' => 'textfield',
                'value' => 'lingua.selector.wrapper',
            ],
        ],
    ],
    'lingua.cultureKey' => [
        'file' => 'lingua.culturekey.snippet',
        'description' => 'Helper snippet to get the run time cultureKey, which is set by lingua\'s plugin.',
        'properties' => [],
    ],
    'lingua.getField' => [
        'file' => 'lingua.getfield.snippet',
        'description' => 'Get the value of the given field for the run time culture key.',
        'properties' => [],
    ],
    'lingua.getValue' => [
        'file' => 'lingua.getvalue.snippet',
        'description' => 'Get the value of the clone\'s field for the run time culture key.',
        'properties' => [
            'emptyReturnsDefault' => [
                'type' => 'combo-boolean',
                'value' => false,
            ],
            'field' => [
                'type' => 'textfield',
                'value' => '',
            ]
        ],
    ],
];
