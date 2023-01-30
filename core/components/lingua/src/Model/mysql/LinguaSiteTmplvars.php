<?php
namespace Lingua\Model\mysql;

use xPDO\xPDO;

class LinguaSiteTmplvars extends \Lingua\Model\LinguaSiteTmplvars
{

    public static $metaMap = array (
        'package' => 'Lingua\\Model',
        'version' => '1.1',
        'table' => 'lingua_site_tmplvars',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'MyISAM',
        ),
        'fields' => 
        array (
            'tmplvarid' => 0,
        ),
        'fieldMeta' => 
        array (
            'tmplvarid' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
        ),
        'indexes' => 
        array (
            'tmplvarid' => 
            array (
                'alias' => 'tmplvarid',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'tmplvarid' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'composites' => 
        array (
            'TmplvarContentvalues' => 
            array (
                'class' => 'LinguaSiteTmplvarContentvalues',
                'local' => 'tmplvarid',
                'foreign' => 'tmplvarid',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
    );

}
