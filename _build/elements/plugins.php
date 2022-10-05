<?php

return [
    'Lingua' => [
        'file' => 'lingua.plugin',
        'description' => '',
        'events' => [
            'OnHandleRequest' => [],
            'OnInitCulture' => [],
            /////////////////// MANAGER SIDE ///////////////////
            'OnDocFormPrerender' => [],
            'OnResourceTVFormRender' => [],
            'OnDocFormSave' => [],
            'OnResourceDuplicate' => [],
            'OnEmptyTrash' => [],
            'OnTemplateSave' => [],
            'OnTempFormSave' => [],
            'OnTVFormSave' => [],
            'OnSnipFormSave' => [],
            'OnPluginFormSave' => [],
            'OnMediaSourceFormSave' => [],
            'OnChunkFormSave' => [],
            'OnSiteRefresh' => [],
        ],
    ],
];
