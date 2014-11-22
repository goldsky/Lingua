<?php

header('Content-Type: text/html; charset=utf-8');
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
 * @subpackage lingua_plugin
 */
$event = $modx->event->name;
switch ($event) {
    case 'OnHandleRequest': // for global
        break;

    case 'OnInitCulture':   // for request class
        if ($modx->context->key === 'mgr') {
            return;
        }
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');
        if (!($lingua instanceof Lingua)) {
            return;
        }
        $modx->lexicon->load('lingua:default');
        $langGetKey = $modx->getOption('lingua.request_key', $scriptProperties, 'lang');
        $langGetKeyValue = filter_input(INPUT_GET, $langGetKey, FILTER_SANITIZE_STRING);
        $langGetKeyValue = strtolower($langGetKeyValue);
        $langCookieValue = filter_input(INPUT_COOKIE, 'modx_lingua_switcher', FILTER_SANITIZE_STRING);
        $langCookieValue = strtolower($langCookieValue);
        if (!empty($langGetKeyValue) &&
                $langGetKeyValue !== $modx->cultureKey &&
                strlen($langGetKeyValue) === 2
        ) {
            $lingua->setCultureKey($langGetKeyValue);
        } else if (!empty($langCookieValue) &&
                $langCookieValue !== $modx->cultureKey &&
                strlen($langCookieValue) === 2
        ) {
            $lingua->setCultureKey($langCookieValue);
        } else if(empty($langGetKeyValue) &&
                empty($langCookieValue)
        ){
            $detectBrowser = $modx->getOption('lingua.detect_browser');
            if ($detectBrowser === '1') {
                $languages = explode(',', filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING));
                $sortedLangs = array();
                foreach ($languages as $language) {
                    $language = strtolower($language);
                    $parts = @explode(';', $language);
                    if (!isset($parts[1])) {
                        $sort = 1.0;
                    } else {
                        $x = @explode('=', $parts[1]);
                        $sort = $x[1] - 0;
                    }
                    $sortedLangs[$parts[0]] = $sort;
                }
                arsort($sortedLangs);
                $langs = array_keys($sortedLangs);
                $linguaLangs = $modx->getCollection('linguaLangs', array(
                    'active' => 1
                ));
                $c = $modx->newQuery('linguaLangs');
                $c->where('active=1');
                $contextLangs = $modx->context->config['lingua.langs'];
                if (!empty($contextLangs)) {
                    $contextLangs = array_map('trim', @explode(',', $contextLangs));
                    $c->where(array(
                        'lang_code:IN' => $contextLangs
                    ));
                }
                $linguaLangs = $modx->getCollection('linguaLangs', $c);
                $existingLangs = array();
                if ($linguaLangs) {
                    foreach ($linguaLangs as $linguaLang) {
                        $existingLangs[] = $linguaLang->get('lang_code');
                    }
                }

                $acceptedLangs = array_intersect($existingLangs, $langs);
                $acceptedLangs = array_values($acceptedLangs); // reset index

                if (!empty($acceptedLangs) && is_array($acceptedLangs)) {
                    $lingua->setCultureKey($acceptedLangs[0]);
                }
            }
        }
        $modx->cultureKey = $lingua->getCultureKey();
        if ($modx->cultureKey !== $modx->getOption('cultureKey')) {
            $modx->setOption('cultureKey', $modx->cultureKey);
            $modx->context->config['cultureKey'] = $modx->cultureKey;
        }
        $modx->setPlaceholder('lingua.cultureKey', $modx->cultureKey);
        $modx->setPlaceholder('lingua.language', $modx->cultureKey);
        
        break;

    /**
     * /////////////////// MANAGER SIDE ///////////////////
     */
    case 'OnDocFormPrerender':
        $contexts = $modx->getOption('lingua.contexts', $scriptProperties, 'web');
        if (!empty($contexts)) {
            $contexts = array_map('trim', @explode(',', $contexts));
            if ($resource) {
                $currentContext = $resource->get('context_key');
            } else {
                $currentContext = filter_input(INPUT_GET, 'context_key', FILTER_SANITIZE_STRING);
            }
            if (!in_array($currentContext, $contexts)) {
                return;
            }
        }
        $parents = $modx->getOption('lingua.parents', $scriptProperties);
        if (!empty($parents)) {
            $parents = array_map('trim', @explode(',', $parents));
            if ($resource) {
                $currentParent = $resource->get('parent');
            } else {
                $currentParent = filter_input(INPUT_GET, 'parent', FILTER_SANITIZE_NUMBER_INT);
            }
            if (!in_array($currentParent, $parents)) {
                return;
            }
        }
        if (is_object($resource)) {
            $ids = $modx->getOption('lingua.ids', $scriptProperties);
            if (!empty($ids)) {
                $ids = array_map('trim', @explode(',', $ids));
                $currentId = $resource->get('id');
                if (!in_array($currentId, $ids)) {
                    return;
                }
            }
        }

        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');
        if (!($lingua instanceof Lingua)) {
            return;
        }
        
        $modx->lexicon->load('lingua:default');
        $languages = $lingua->getLanguages();
        if (empty($languages)) {
            return;
        }
        $modx->regClientCSS(MODX_BASE_URL . 'assets/components/lingua/css/mgr.css');
        $modx->controller->addJavascript(MODX_BASE_URL . 'assets/components/lingua/js/mgr/resource.js');
        // $modx->getOption('cultureKey') doesn't work!
        $modCultureKey = $modx->getObject('modSystemSetting', array('key' => 'cultureKey'));
        $cultureKey = $modCultureKey->get('value');
        $storeData = array();
        $storeDefaultData = array();
        $configLang = array();
        $linguaSiteContentArray = array();
        $createHiddenFields = array();
        foreach ($languages as $language) {
            $configLang[$language['lang_code']] = array(
                'lang_code' => $language['lang_code'],
                'local_name' => $language['local_name'],
                'flag' => $language['flag'],
            );
            if ($language['lang_code'] === $cultureKey) {
                $storeDefaultData[] = array(
                    $language['lang_code'],
                    $language['local_name'],
                    $language['flag'],
                );
                continue;
            }
            $storeData[] = array(
                $language['lang_code'],
                $language['local_name'],
                $language['flag'],
            );
            if ($mode === modSystemEvent::MODE_UPD) {
                $linguaSiteContent = $modx->getObject('linguaSiteContent', array(
                    'resource_id' => $resource->get('id'),
                    'lang_id' => $language['id']
                ));
                if ($linguaSiteContent) {
                    $linguaSiteContentArray[$language['lang_code']] = $linguaSiteContent->toArray();
                } else {
                    $linguaSiteContentArray[$language['lang_code']] = array();
                }
            } else {
                $linguaSiteContentArray[$language['lang_code']] = array();
            }
            $modx->regClientStartupHTMLBlock('<style>.icon-lingua-flag-' . $language['lcid_string'] . ' {background-image: url(\'../' . $language['flag'] . '\'); background-repeat: no-repeat;}</style>');
            $createHiddenFields[] = $language;
        } // foreach ($languages as $language)
        //------------------------------------------------------------------
        $jsHTML = '
    window.lingua = new Lingua({
        defaultLang: "' . $cultureKey . '",
        langs: ' . json_encode($configLang) . '
    });
    lingua.config.siteContent = ' . json_encode($linguaSiteContentArray) . ';
    lingua.flagDefaultFields();
    lingua.createHiddenFields(' . json_encode($createHiddenFields) . ');
    var actionButtons = Ext.getCmp("modx-action-buttons");
    if (actionButtons) {
        var languageBtn = new Ext.form.ComboBox({
            id: "lingua-languageBtn",
            tpl: \'<tpl for="."><div class="x-combo-list-item"><img src="../{flag}" class="icon"/> {local_name}</div></tpl>\',
            store: new Ext.data.ArrayStore({
                id: 0,
                fields: [
                    "lang_code",
                    "local_name",
                    "flag"
                ],
                data: ' . json_encode(array_merge($storeDefaultData, $storeData)) . '
            }),
            valueField: "lang_code",
            displayField: "local_name",
            typeAhead: false,
            forceSelection: true,
            editable: false,
            mode: "local",
            triggerAction: "all",
            //emptyText: "' . $languages[$cultureKey]['local_name'] . '",
            selectOnFocus: true,
            width: 150,
            listeners: {
                select: {
                    fn: function(combo, record, index) {
                        lingua.switchLanguage(record.get("lang_code"));
                    },
                    scope: this
                },
                render: {
                    fn: function(comboBox) {
                        var store = comboBox.store;
                        var valueField = comboBox.valueField;
                        var displayField = comboBox.displayField;
                        var recordNumber = store.findExact(valueField, "' . $cultureKey . '", 0);
                        if (recordNumber !== -1) {
                            var displayValue = store.getAt(recordNumber).data[displayField];
                            comboBox.setValue("' . $cultureKey . '");
                            comboBox.setRawValue(displayValue);
                            comboBox.selectedIndex = recordNumber;
                        }
                    },
                    scope: this
                }
            }
        });
        actionButtons.insertButton(0, [languageBtn, "-"]);
        actionButtons.doLayout();
    }';
        $modx->controller->addHtml('
<script type="text/javascript">
Ext.onReady(function() {
    ' . $jsHTML . '
});
</script>');
        //------------------------------------------------------------------
        break;

    case 'OnResourceTVFormRender':
        if (!is_object($resource) && is_numeric($resource)) {
            $resourceId = $resource;
            $resource = $modx->getObject('modResource', $resource);
        }
        $contexts = $modx->getOption('lingua.contexts', $scriptProperties, 'web');
        if (!empty($contexts)) {
            $contexts = array_map('trim', @explode(',', $contexts));
            if ($resource) {
                $currentContext = $resource->get('context_key');
            } else {
                $currentContext = filter_input(INPUT_GET, 'context_key', FILTER_SANITIZE_STRING);
            }
            if (!in_array($currentContext, $contexts)) {
                return;
            }
        }
        $parents = $modx->getOption('lingua.parents', $scriptProperties);
        if (!empty($parents)) {
            $parents = array_map('trim', @explode(',', $parents));
            if ($resource) {
                $currentParent = $resource->get('parent');
            } else {
                $currentParent = filter_input(INPUT_GET, 'parent', FILTER_SANITIZE_NUMBER_INT);
            }
            if (!in_array($currentParent, $parents)) {
                return;
            }
        }
        if (is_object($resource)) {
            $ids = $modx->getOption('lingua.ids', $scriptProperties);
            if (!empty($ids)) {
                $ids = array_map('trim', @explode(',', $ids));
                $currentId = $resource->get('id');
                if (!in_array($currentId, $ids)) {
                    return;
                }
            }
        }

        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');
        if (!($lingua instanceof Lingua)) {
            return;
        }
        $languages = $lingua->getLanguages(1, false);
        if (empty($languages)) {
            return;
        }
        $initAllClonedTVFields = array();
        foreach ($languages as $language) {
            $initAllClonedTVFields[] = $language;
        }

        if ($resource) {
            $tvs = $resource->getTemplateVars();
        } else {
            $templateId = $template;
            $template = $modx->getObject('modTemplate', $templateId);
            $tvs = $template->getTemplateVars();
        }
        if (!$tvs) {
            return;
        }

        $tvIds = array();
        $tvOutputs = array();
        foreach ($tvs as $tv) {
            $tvIds[] = $tv->get('id');
        }
        $c = $modx->newQuery('linguaSiteTmplvars');
        $c->where(array(
            'tmplvarid:IN' => $tvIds
        ));
        $linguaSiteTmplvars = $modx->getCollection('linguaSiteTmplvars', $c);
        if (!$linguaSiteTmplvars) {
            return;
        }

        $formElements = array();
        foreach ($scriptProperties['categories'] as $category) {
            foreach ($category['tvs'] as $tv) {
                $formElements[$tv->get('id')] = $tv;
            }
        }

        if (!empty($modx->controller->scriptProperties['showCheckbox'])) {
            $showCheckbox = 1;
        }

        $tmplvars = array();
        $cloneTVFields = array();
        $count = 0;
        // $modx->getOption('cultureKey') doesn't work!
        $modCultureKey = $modx->getObject('modSystemSetting', array('key' => 'cultureKey'));
        $cultureKey = $modCultureKey->get('value');
        foreach ($linguaSiteTmplvars as $linguaTv) {
            $tvId = $linguaTv->get('tmplvarid');
            if (!isset($formElements[$tvId])) {
                continue;
            }
            $tv = $formElements[$tvId];
            $tmplvars[] = array(
                'id' => $tvId,
                'type' => $tv->get('type'),
            );
            $tvArray = $tv->toArray('tv.');
            foreach ($languages as $language) {
                if ($language['lang_code'] === $cultureKey) {
                    continue;
                }

                $linguaTVContent = $modx->getObject('linguaSiteTmplvarContentvalues', array(
                    'tmplvarid' => $tvId,
                    'contentid' => $resourceId,
                    'lang_id' => $language['id']
                ));

                /**
                 * Start to manipulate the ID to parse hidden TVs
                 */
                $content = '';
                if ($linguaTVContent) {
                    $content = $linguaTVContent->get('value');
                }
                $inputForm = $tv->renderInput($resource, array(
                    'value' => $content
                ));
                if (empty($inputForm)) {
                    continue;
                }

                $tvCloneId = $tvId . '_' . $language['lang_code'] . '_lingua_tv';
                // basic replacements
                $cloneInputForm = $inputForm;
                $cloneInputForm = preg_replace('/("|\'){1}tv' . $tvId . '("|\'){1}/', '${1}tv' . $tvCloneId . '${2}', $cloneInputForm);
                $cloneInputForm = preg_replace('/("|\'){1}tv' . $tvId . '\[\]("|\'){1}/', '${1}tv' . $tvCloneId . '[]${2}', $cloneInputForm);
                // advanced replacements
                $linguaSiteTmplvarsPatterns = $modx->getCollection('linguaSiteTmplvarsPatterns', array(
                    'type' => $tv->get('type')
                ));
                if ($linguaSiteTmplvarsPatterns) {
                    foreach ($linguaSiteTmplvarsPatterns as $pattern) {
                        $search = $pattern->get('search');
                        $search = str_replace('{{tvId}}', $tvId, $search);
                        $replacement = $pattern->get('replacement');
                        $replacement = str_replace('{{tvCloneId}}', $tvCloneId, $replacement);
                        $cloneInputForm = preg_replace($search, $replacement, $cloneInputForm);
                    }
                }
                $count++;
                $phs = $tvArray;
                $phs['tv.id'] = $tvCloneId;
                $phs['tv.formElement'] = $cloneInputForm;
                $phs['tv.showCheckbox'] = $showCheckbox;
                $cloneTVFields[] = $lingua->processElementTags($lingua->parseTpl('lingua.resourcetv.row', $phs));
            }
        }
        
        // reset any left out output after rendering TV forms above
        if ($modx->event->name === 'OnTVInputRenderList') {
            $modx->event->_output = '';
        }

        $modx->event->output(@implode("\n", $cloneTVFields));    
        $jsHTML = "
<script>
    Ext.onReady(function() {
        lingua.config.tmplvars = " . json_encode($tmplvars) . ";
        lingua.initAllClonedTVFields(" . json_encode($initAllClonedTVFields) . ");
        lingua.flagDefaultTVFields();
    });
</script>";
        $modx->event->output($jsHTML);

        break;

    case 'OnDocFormSave':
        $contexts = $modx->getOption('lingua.contexts', $scriptProperties, 'web');
        if (!empty($contexts)) {
            $contexts = array_map('trim', @explode(',', $contexts));
            $currentContext = $resource->get('context_key');
            if (!in_array($currentContext, $contexts)) {
                return;
            }
        }
        $parents = $modx->getOption('lingua.parents', $scriptProperties);
        if (!empty($parents)) {
            $parents = array_map('trim', @explode(',', $parents));
            $currentParent = $resource->get('parent');
            if (!in_array($currentParent, $parents)) {
                return;
            }
        }
        $ids = $modx->getOption('lingua.ids', $scriptProperties);
        if (!empty($ids)) {
            $ids = array_map('trim', @explode(',', $ids));
            $currentId = $resource->get('id');
            if (!in_array($currentId, $ids)) {
                return;
            }
        }

        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');
        if (!($lingua instanceof Lingua)) {
            return;
        }

        // update linguaSiteContent
        $reverting = array();
        $clearKeys = array();
        // $modx->getOption('cultureKey') doesn't work!
        $modCultureKey = $modx->getObject('modSystemSetting', array('key' => 'cultureKey'));
        $cultureKey = $modCultureKey->get('value');
        foreach ($resource->_fields as $k => $v) {
            if (!preg_match('/_lingua$/', $k)) {
                continue;
            }
            foreach ($v as $a => $b) {
                if ($a === $cultureKey) {
                    continue;
                }
                $reverting[$a][preg_replace('/_lingua$/', '', $k)] = $b;
            }
            $clearKeys[] = $k;
        }

        $resourceId = $resource->get('id');
        foreach ($reverting as $k => $v) {
            $linguaLangs = $modx->getObject('linguaLangs', array('lang_code' => $k));
            $params = array(
                'resource_id' => $resourceId,
                'lang_id' => $linguaLangs->get('id'),
            );
            $linguaSiteContent = $modx->getObject('linguaSiteContent', $params);
            if (!$linguaSiteContent) {
                $linguaSiteContent = $modx->newObject('linguaSiteContent');
                $linguaSiteContent->fromArray($params);
                $linguaSiteContent->save();
            }
            if (empty($v['pagetitle'])) {
                $v['pagetitle'] = $resource->get('pagetitle');
            }
            $linguaSiteContent->set('pagetitle', $v['pagetitle']);
            $linguaSiteContent->set('longtitle', $v['longtitle']);
            $linguaSiteContent->set('description', $v['description']);
            $linguaSiteContent->set('content', (isset($v['content']) && !empty($v['content']) ? $v['content'] : $v['ta']));
            $linguaSiteContent->set('introtext', $v['introtext']);
            if (empty($v['alias'])) {
                $v['alias'] = $v['pagetitle'];
                $linguaSiteContent->setDirty('alias');
            }
            $linguaSiteContent->set('alias', $v['alias']);
            $linguaSiteContent->set('menutitle', $v['menutitle']);
            $linguaSiteContent->set('link_attributes', $v['link_attributes']);
            $linguaSiteContent->set('uri_override', $v['uri_override']);
            $linguaSiteContent->set('uri', $v['uri']);
            $linguaSiteContent->set('parent', $resource->get('parent'));
            $linguaSiteContent->set('isfolder', $resource->get('isfolder'));
            $linguaSiteContent->set('context_key', $resource->get('context_key'));
            $linguaSiteContent->set('content_type', $resource->get('content_type'));
            if ($resource->get('refreshURIs')) {
                $linguaSiteContent->set('refreshURIs', true);
            }
            $linguaSiteContent->save();
        }

        // update linguaSiteTmplvarContentvalues
        $reverting = array();
        foreach ($resource->_fields as $k => $value) {
            if (!preg_match('/_lingua_tv$/', $k)) {
                continue;
            }
            $tvKey = preg_replace('/_lingua_tv$/', '', $k);
            $tvKeys = @explode('_', $tvKey);
            $tvId = str_replace('tv', '', $tvKeys[0]);
            if (!is_numeric($tvId)) {
                continue;
            }
            $reverse = array_reverse($tvKeys);
            $lang = $reverse[0];
            if ($lang === $cultureKey) {
                continue;
            }
            $tv = $modx->getObject('modTemplateVar', $tvId);
            $tvKey = $tvKeys[0];
            /* validation for different types */
            switch ($tv->get('type')) {
                case 'url':
                    // tv16_prefix_id_lingua_tv
                    $prefix = $resource->_fields[$tvKey . '_prefix_' . $lang . '_lingua_tv'];
                    if ($prefix != '--') {
                        $value = str_replace(array('ftp://', 'http://', 'https://', 'ftp://', 'mailto:'), '', $value);
                        $value = $prefix . $value;
                    }
                    $reverting[$lang][$tvId] = $value;

                    break;
                case 'date':
                    $value = empty($value) ? '' : strftime('%Y-%m-%d %H:%M:%S', strtotime($value));

                    break;
                /* ensure tag types trim whitespace from tags */
                case 'tag':
                case 'autotag':
                    $tags = explode(',', $value);
                    $newTags = array();
                    foreach ($tags as $tag) {
                        $newTags[] = trim($tag);
                    }
                    $value = implode(',', $newTags);

                    break;
                default:
                    /* handles checkboxes & multiple selects elements */
                    if (is_array($value)) {
                        $featureInsert = array();
                        while (list($featureValue, $featureItem) = each($value)) {
                            if (empty($featureItem)) {
                                continue;
                            }
                            $featureInsert[count($featureInsert)] = $featureItem;
                        }
                        $value = implode('||', $featureInsert);
                    }

                    break;
            }
            $reverting[$lang][$tvId] = $value;
            $clearKeys[] = $k;
        }

        /**
         * json seems to have number of characters limit;
         * that makes saving success report truncated and output modal hangs,
         * TV's proccessing does this outside of reverting's loops
         */
        if (!empty($clearKeys)) {
            foreach ($clearKeys as $k) {
                $resource->set($k, '');
            }
        }

        foreach ($reverting as $k => $tmplvars) {
            $linguaLangs = $modx->getObject('linguaLangs', array('lang_code' => $k));
            $langId = $linguaLangs->get('id');
            foreach ($tmplvars as $key => $val) {
                if (empty($val)) {
                    continue;
                }
                $params = array(
                    'lang_id' => $langId,
                    'tmplvarid' => $key,
                    'contentid' => $resourceId,
                );
                $linguaSiteTmplvarContentvalues = $modx->getObject('linguaSiteTmplvarContentvalues', $params);
                if (!$linguaSiteTmplvarContentvalues) {
                    $linguaSiteTmplvarContentvalues = $modx->newObject('linguaSiteTmplvarContentvalues');
                }
                $linguaSiteTmplvarContentvalues->set('lang_id', $langId);
                $linguaSiteTmplvarContentvalues->set('tmplvarid', $key);
                $linguaSiteTmplvarContentvalues->set('contentid', $resourceId);
                $linguaSiteTmplvarContentvalues->set('value', $val);
                $linguaSiteTmplvarContentvalues->save();
            }
        }

        // clear cache
        $contexts = array($resource->get('context_key'));
        $cacheManager = $modx->getCacheManager();
        $cacheManager->refresh(array(
            'lingua/resource' => array('contexts' => $contexts),
        ));
        break;

    case 'OnResourceDuplicate':
        $contexts = $modx->getOption('lingua.contexts', $scriptProperties, 'web');
        if (!empty($contexts)) {
            $contexts = array_map('trim', @explode(',', $contexts));
            $currentContext = $oldResource->get('context_key');
            if (!in_array($currentContext, $contexts)) {
                return;
            }
        }
        $parents = $modx->getOption('lingua.parents', $scriptProperties);
        if (!empty($parents)) {
            $parents = array_map('trim', @explode(',', $parents));
            $currentParent = $oldResource->get('parent');
            if (!in_array($currentParent, $parents)) {
                return;
            }
        }
        $ids = $modx->getOption('lingua.ids', $scriptProperties);
        if (!empty($ids)) {
            $ids = array_map('trim', @explode(',', $ids));
            $currentId = $oldResource->get('id');
            if (!in_array($currentId, $ids)) {
                return;
            }
        }

        $linguaSiteContents = $modx->getCollection('linguaSiteContent', array(
            'resource_id' => $oldResource->get('id')
        ));
        if ($linguaSiteContents) {
            foreach ($linguaSiteContents as $linguaSiteContent) {
                $params = $linguaSiteContent->toArray();
                unset($params['id']);
                $params['resource_id'] = $newResource->get('id');
                $newLinguaSiteContent = $modx->newObject('linguaSiteContent');
                $newLinguaSiteContent->fromArray($params);
                $newLinguaSiteContent->save();
            }
        }
        break;

    case 'OnEmptyTrash':
        if (!empty($ids) && is_array($ids)) {
            $collection = $modx->getCollection('linguaSiteContent', array(
                'resource_id:IN' => $ids
            ));
            if ($collection) {
                foreach ($collection as $item) {
                    $item->remove();
                }
            }
        }
        break;

    case 'OnTemplateSave':
    case 'OnTempFormSave':
    case 'OnTVFormSave':
    case 'OnSnipFormSave':
    case 'OnPluginFormSave':
    case 'OnMediaSourceFormSave':
    case 'OnChunkFormSave':
    case 'OnSiteRefresh':
        $cacheManager = $modx->getCacheManager();
        $cacheManager->refresh(array(
            'lingua/resource' => array(),
        ));
        break;

    default:
        break;
}
return;
