<?php
namespace Lingua\Model\mysql;

use xPDO\xPDO;

class LinguaSiteTmplvarsPatterns extends \Lingua\Model\LinguaSiteTmplvarsPatterns
{

    public static $metaMap = array (
        'package' => 'Lingua\\Model',
        'version' => '1.1',
        'table' => 'lingua_site_tmplvars_patterns',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'MyISAM',
        ),
        'fields' => 
        array (
            'type' => NULL,
            'search' => NULL,
            'replacement' => NULL,
        ),
        'fieldMeta' => 
        array (
            'type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => true,
            ),
            'search' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => true,
            ),
            'replacement' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => true,
            ),
        ),
    );

}
