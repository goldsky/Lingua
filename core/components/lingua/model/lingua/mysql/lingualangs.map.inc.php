<?php
$xpdo_meta_map['linguaLangs']= array (
  'package' => 'lingua',
  'version' => '1.1',
  'table' => 'langs',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'active' => 0,
    'local_name' => NULL,
    'lang_code' => NULL,
    'lcid_string' => NULL,
    'lcid_dec' => NULL,
    'date_format_lite' => 'Y-m-d',
    'date_format_full' => 'Y-m-d H:i:s',
    'is_rtl' => 0,
    'flag' => NULL,
  ),
  'fieldMeta' => 
  array (
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '3',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'local_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
    'lang_code' => 
    array (
      'dbtype' => 'char',
      'precision' => '2',
      'phptype' => 'string',
      'null' => false,
      'index' => 'index',
    ),
    'lcid_string' => 
    array (
      'dbtype' => 'char',
      'precision' => '10',
      'phptype' => 'string',
      'null' => true,
    ),
    'lcid_dec' => 
    array (
      'dbtype' => 'int',
      'precision' => '6',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
    ),
    'date_format_lite' => 
    array (
      'dbtype' => 'char',
      'precision' => '32',
      'phptype' => 'string',
      'null' => true,
      'default' => 'Y-m-d',
    ),
    'date_format_full' => 
    array (
      'dbtype' => 'char',
      'precision' => '32',
      'phptype' => 'string',
      'null' => true,
      'default' => 'Y-m-d H:i:s',
    ),
    'is_rtl' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'flag' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'lang_code' => 
    array (
      'alias' => 'lang_code',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'lang_code' => 
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
    'SiteContent' => 
    array (
      'class' => 'linguaSiteContent',
      'local' => 'id',
      'foreign' => 'lang_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'TmplvarContentvalues' => 
    array (
      'class' => 'linguaSiteTmplvarContentvalues',
      'local' => 'id',
      'foreign' => 'lang_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
