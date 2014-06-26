<?php
$xpdo_meta_map['linguaSiteTmplvars']= array (
  'package' => 'lingua',
  'version' => '1.1',
  'table' => 'site_tmplvars',
  'extends' => 'xPDOSimpleObject',
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
      'class' => 'linguaSiteTmplvarContentvalues',
      'local' => 'tmplvarid',
      'foreign' => 'tmplvarid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
