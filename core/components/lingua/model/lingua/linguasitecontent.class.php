<?php

/**
 * Lingua
 *
 * Copyright 2013-2014 by goldsky <goldsky@virtudraft.com>
 *
 * This file is part of Lingua, a MODX's Lexicon switcher for front-end interface
 *
 * Lingua is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation version 3.
 *
 * Lingua is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Lingua; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package lingua
 * @subpackage lingua_site_content
 */
class linguaSiteContent extends xPDOSimpleObject {

    /**
     * Refresh Resource URI fields for children of the specified parent.
     *
     * @static
     * @param modX &$modx A reference to a valid modX instance.
     * @param int $parent The id of a Resource parent to start from (default is 0, the root)
     * @param array $options An array of various options for the method:
     *      - resetOverrides: if true, Resources with uri_override set to true will be included
     *      - contexts: an optional array of context keys to limit the refresh scope
     * @return void
     */
    public static function refreshURIs(modX &$modx, $parent = 0, array $options = array()) {
        $resetOverrides = array_key_exists('resetOverrides', $options) ? (boolean) $options['resetOverrides'] : false;
        $contexts = array_key_exists('contexts', $options) ? explode(',', $options['contexts']) : null;
        $criteria = $modx->newQuery('linguaSiteContent');
        $criteria->where(array(
            'lang_id' => $options['lang_id'],
            'parent' => $parent,
        ));
        if (!$resetOverrides) {
            $criteria->where(array('uri_override' => false));
        }
        if (!empty($contexts)) {
            $criteria->where(array('context_key:IN' => $contexts));
        }
        /** @var Resource $resource */
        $resources = $modx->getIterator('linguaSiteContent', $criteria);
        foreach ($resources as $resource) {
            $resource->set('refreshURIs', true);
            if ($resetOverrides) {
                $resource->set('uri_override', false);
            }
            if (!$resource->get('uri_override')) {
                $resource->set('uri', '');
            }
            $resource->save();
        }
    }

    /**
     * Set a field value by the field key or name.
     *
     * {@inheritdoc}
     * 
     * Additional logic added for the following fields:
     * 	-alias: Applies {@link modResource::cleanAlias()}
     *  -contentType: Calls {@link modResource::addOne()} to sync contentType
     *  -content_type: Sets the contentType field appropriately
     */
    public function set($k, $v = null, $vType = '') {
        switch ($k) {
            case 'alias' :
                $v = $this->cleanAlias($v);
                break;
        }
        return parent :: set($k, $v, $vType);
    }

    /**
     * Persist new or changed modResource instances to the database container.
     *
     * If the modResource is new, the createdon and createdby fields will be set
     * using the current time and user authenticated in the context.
     *
     * If uri is empty or uri_overridden is not set and something has been changed which
     * might affect the Resource's uri, it is (re-)calculated using getAliasPath(). This
     * can be forced recursively by setting refreshURIs to true before calling save().
     *
     * @param boolean $cacheFlag
     * @return boolean
     */
    public function save($cacheFlag = null) {
        $refreshChildURIs = false;
        if ($this->xpdo instanceof modX && $this->xpdo->getOption('friendly_urls')) {
            $refreshChildURIs = ($this->get('refreshURIs') || $this->isDirty('alias') || $this->isDirty('parent') || $this->isDirty('context_key'));
            if ($this->get('uri') == '' || (!$this->get('uri_override') && ($this->isDirty('uri_override') || $this->isDirty('content_type') || $this->isDirty('isfolder') || $refreshChildURIs))) {
                $this->set('uri', $this->getAliasPath($this->get('alias')));
            }
        }

        $rt = parent :: save($cacheFlag);
        if ($rt && $refreshChildURIs) {
            $this->xpdo->call('linguaSiteContent', 'refreshURIs', array(
                &$this->xpdo,
                $this->get('resource_id'),
                array(
                    'lang_id' => $this->get('lang_id')
                )
            ));
        }
        return $rt;
    }

    /**
     * Get the Resource's full alias path.
     *
     * @param string $alias Optional. The alias to check. If not set, will
     * then build it from the pagetitle if automatic_alias is set to true.
     * @param array $fields Optional. An array of field values to use instead of
     * using the current modResource fields.
     * @return string
     */
    public function getAliasPath($alias = '', array $fields = array()) {
        if (empty($fields)) {
            $fields = $this->toArray();
        }
        $workingContext = $this->xpdo->getContext($fields['context_key']);
        if (empty($fields['uri_override']) || empty($fields['uri'])) {
            /* auto assign alias if using automatic_alias */
            if (empty($alias) && $workingContext->getOption('automatic_alias', false)) {
                $alias = $this->cleanAlias($fields['pagetitle']);
            } elseif (empty($alias) && isset($fields['resource_id']) && !empty($fields['resource_id'])) {
                $alias = $this->cleanAlias($fields['resource_id']);
            } else {
                $alias = $this->cleanAlias($alias);
            }

            $fullAlias = $alias;
            $isHtml = true;
            $extension = '';
            $containerSuffix = $workingContext->getOption('container_suffix', '');
            /* @var modContentType $contentType process content type */
            if (!empty($fields['content_type']) && $contentType = $this->xpdo->getObject('modContentType', $fields['content_type'])) {
                $extension = $contentType->getExtension();
                $isHtml = (strpos($contentType->get('mime_type'), 'html') !== false);
            }
            /* set extension to container suffix if Resource is a folder, HTML content type, and the container suffix is set */
            if (!empty($fields['isfolder']) && $isHtml && !empty($containerSuffix)) {
                $extension = $containerSuffix;
            }
            $aliasPath = '';
            /* if using full alias paths, calculate here */
            if ($workingContext->getOption('use_alias_path', false)) {
                $pathParentId = $fields['parent'];
                $parentResources = array();
                $queryLingua = $this->xpdo->newQuery('linguaSiteContent');
                $queryLingua->select($this->xpdo->getSelectColumns('linguaSiteContent', '', '', array('parent', 'alias')));
                $queryLingua->where("{$this->xpdo->escape('resource_id')} = ? AND {$this->xpdo->escape('lang_id')} = {$this->get('lang_id')}");
                $queryLingua->prepare();
                $queryLingua->stmt->execute(array($pathParentId));
                $currResource = $queryLingua->stmt->fetch(PDO::FETCH_ASSOC);

                // if empty, try default resource
                $query = $this->xpdo->newQuery('modResource');
                $query->select($this->xpdo->getSelectColumns('modResource', '', '', array('parent', 'alias')));
                $query->where("{$this->xpdo->escape('id')} = ?");
                $query->prepare();
                if (empty($currResource)) {
                    $query->stmt->execute(array($pathParentId));
                    $currResource = $query->stmt->fetch(PDO::FETCH_ASSOC);
                }

                while ($currResource) {
                    $parentAlias = $currResource['alias'];
                    if (empty($parentAlias)) {
                        $parentAlias = "{$currResource['resource_alias']}";
                        if (empty($parentAlias)) {
                            $parentAlias = "{$pathParentId}";
                        }
                    }
                    $parentResources[] = "{$parentAlias}";
                    $pathParentId = $currResource['parent'];
                    $queryLingua->stmt->execute(array($pathParentId));
                    $currResource = $queryLingua->stmt->fetch(PDO::FETCH_ASSOC);

                    if (empty($currResource)) {
                        $query->stmt->execute(array($pathParentId));
                        $currResource = $query->stmt->fetch(PDO::FETCH_ASSOC);
                    }
                }
                $aliasPath = !empty($parentResources) ? implode('/', array_reverse($parentResources)) : '';
                if (strlen($aliasPath) > 0 && $aliasPath[strlen($aliasPath) - 1] !== '/') {
                    $aliasPath .= '/';
                }
            }

            $fullAlias = $aliasPath . $fullAlias . $extension;
        } else {
            $fullAlias = $fields['uri'];
        }
        return $fullAlias;
    }

    protected function cleanAlias($alias, array $options = array()) {
        $resource = $this->xpdo->getService('modResource');
        return $resource->cleanAlias($alias, $options);
    }

}
