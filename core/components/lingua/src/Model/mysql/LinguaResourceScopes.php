<?php
namespace Lingua\Model\mysql;

use xPDO\xPDO;

class LinguaResourceScopes extends \Lingua\Model\LinguaResourceScopes
{

    public static $metaMap = array (
        'package' => 'Lingua\\Model',
        'version' => '1.1',
        'table' => 'lingua_resource_scopes',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'MyISAM',
        ),
        'fields' => 
        array (
            'resource_id' => NULL,
            'properties' => NULL,
            'as_parent' => 0,
            'as_ancestor' => 0,
            'exclude_self' => 0,
        ),
        'fieldMeta' => 
        array (
            'resource_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
            ),
            'properties' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
            ),
            'as_parent' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'as_ancestor' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'exclude_self' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
        ),
    );

}
