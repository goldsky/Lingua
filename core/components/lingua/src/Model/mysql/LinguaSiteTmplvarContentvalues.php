<?php
namespace Lingua\Model\mysql;

use xPDO\xPDO;

class LinguaSiteTmplvarContentvalues extends \Lingua\Model\LinguaSiteTmplvarContentvalues
{

    public static $metaMap = array (
        'package' => 'Lingua\\Model',
        'version' => '1.1',
        'table' => 'lingua_site_tmplvar_contentvalues',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'MyISAM',
        ),
        'fields' => 
        array (
            'lang_id' => 0,
            'tmplvarid' => 0,
            'contentid' => 0,
            'value' => NULL,
        ),
        'fieldMeta' => 
        array (
            'lang_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'tmplvarid' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'contentid' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'value' => 
            array (
                'dbtype' => 'mediumtext',
                'phptype' => 'string',
                'null' => false,
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
            'contentid' => 
            array (
                'alias' => 'contentid',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'contentid' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'tv_cnt' => 
            array (
                'alias' => 'tv_cnt',
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
                    'contentid' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'lang_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'lang_id' => 
            array (
                'alias' => 'lang_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'lang_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'Tmplvars' => 
            array (
                'class' => 'LinguaSiteTmplvars',
                'local' => 'tmplvarid',
                'foreign' => 'tmplvarid',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Lang' => 
            array (
                'class' => 'LinguaLangs',
                'local' => 'lang_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
