<?php
$xpdo_meta_map['extResource']= array (
  'package' => 'lingua',
  'version' => '1.1',
  'extends' => 'modDocument',
  'fields' => 
  array (
  ),
  'fieldMeta' => 
  array (
  ),
  'composites' => 
  array (
    'SiteContent' => 
    array (
      'class' => 'linguaSiteContent',
      'local' => 'id',
      'foreign' => 'resource_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
