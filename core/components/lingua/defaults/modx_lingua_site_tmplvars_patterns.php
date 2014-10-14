<?php
/**
 * Export to PHP Array plugin for PHPMyAdmin
 * @version 0.2b
 */

return array(
  array('id' => '1','type' => 'tag','search' => '/("|\'){1}tv-tags-{{tvId}}("|\'){1}/','replacement' => '${1}tv-tags-{{tvCloneId}}${2}'),
  array('id' => '2','type' => 'tag','search' => '/fld{{tvId}}/','replacement' => 'fld{{tvCloneId}}'),
  array('id' => '3','type' => 'tag','search' => '/tv-{{tvId}}-tag-list/','replacement' => 'tv-{{tvCloneId}}-tag-list'),
  array('id' => '4','type' => 'tag','search' => '/o.id != \'{{tvId}}\'/','replacement' => 'o.id != \'{{tvCloneId}}\''),
  array('id' => '5','type' => 'tag','search' => '/("|\'){1}tvdef{{tvId}}("|\'){1}/','replacement' => '${1}tvdef{{tvCloneId}}${2}'),
  array('id' => '6','type' => 'autotag','search' => '/("|\'){1}tv-tags-{{tvId}}("|\'){1}/','replacement' => '${1}tv-tags-{{tvCloneId}}${2}'),
  array('id' => '7','type' => 'autotag','search' => '/fld{{tvId}}/','replacement' => 'fld{{tvCloneId}}'),
  array('id' => '8','type' => 'autotag','search' => '/tv-{{tvId}}-tag-list/','replacement' => 'tv-{{tvCloneId}}-tag-list'),
  array('id' => '9','type' => 'autotag','search' => '/o.id != \'{{tvId}}\'/','replacement' => 'o.id != \'{{tvCloneId}}\''),
  array('id' => '10','type' => 'autotag','search' => '/("|\'){1}tvdef{{tvId}}("|\'){1}/','replacement' => '${1}tvdef{{tvCloneId}}${2}'),
  array('id' => '11','type' => 'autotag','search' => '/("|\'){1}tvdef{{tvId}}("|\'){1}/','replacement' => '${1}tvdef{{tvCloneId}}${2}'),
  array('id' => '12','type' => 'option','search' => '/("|\'){1}tv{{tvId}}-/','replacement' => '${1}tv{{tvCloneId}}-'),
  array('id' => '13','type' => 'checkbox','search' => '/("|\'){1}tv{{tvId}}-/','replacement' => '${1}tv{{tvCloneId}}-'),
  array('id' => '14','type' => 'checkbox','search' => '/("|\'){1}tv-{{tvId}}("|\'){1}/','replacement' => '${1}tv-{{tvCloneId}}${2}'),
  array('id' => '15','type' => 'checkbox','search' => '/("|\'){1}tvdef{{tvId}}("|\'){1}/','replacement' => '${1}tvdef{{tvCloneId}}${2}'),
  array('id' => '16','type' => 'file','search' => '/("|\'){1}tvbrowser{{tvId}}("|\'){1}/','replacement' => '${1}tvbrowser{{tvCloneId}}${2}'),
  array('id' => '17','type' => 'file','search' => '/("|\'){1}tvpanel{{tvId}}("|\'){1}/','replacement' => '${1}tvpanel{{tvCloneId}}${2}'),
  array('id' => '18','type' => 'file','search' => '/fld{{tvId}}/','replacement' => 'fld{{tvCloneId}}'),
  array('id' => '19','type' => 'file','search' => '/tv: ("|\'){1}{{tvId}}("|\'){1}/','replacement' => 'tv: ${1}{{tvCloneId}}${2}'),
  array('id' => '20','type' => 'image','search' => '/("|\'){1}tvbrowser{{tvId}}("|\'){1}/','replacement' => '${1}tvbrowser{{tvCloneId}}${2}'),
  array('id' => '21','type' => 'image','search' => '/("|\'){1}tv-image-{{tvId}}("|\'){1}/','replacement' => '${1}tv-image-{{tvCloneId}}${2}'),
  array('id' => '22','type' => 'image','search' => '/("|\'){1}tv-image-preview-{{tvId}}("|\'){1}/','replacement' => '${1}tv-image-preview-{{tvCloneId}}${2}'),
  array('id' => '23','type' => 'image','search' => '/fld{{tvId}}/','replacement' => 'fld{{tvCloneId}}'),
  array('id' => '24','type' => 'image','search' => '/tv: ("|\'){1}{{tvId}}("|\'){1}/','replacement' => 'tv: ${1}{{tvCloneId}}${2}'),
  array('id' => '25','type' => 'url','search' => '/("|\'){1}tv{{tvId}}_prefix("|\'){1}/','replacement' => '${1}tv{{tvCloneId}}_prefix${2}')
);
