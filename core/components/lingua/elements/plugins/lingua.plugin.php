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
    case 'OnPageNotFound':
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');

        if ($lingua instanceof Lingua) {
            $modx->lexicon->load('lingua:default');

            $parseUrl = parse_url($_SERVER['REQUEST_URI']);
            $search = $parseUrl['path'];
            $baseUrl = $modx->getOption('base_url', null, MODX_BASE_URL);
            if (!empty($baseUrl) &&
                    $baseUrl !== '/' &&
                    $baseUrl !== ' ' &&
                    $baseUrl !== '/' . $modx->context->get('key') . '/'
            ) {
                $search = str_replace($baseUrl, '', $search);
            }

            $search = ltrim($search, '/');
            if (!empty($search)) {
                $c = $modx->newQuery('linguaSiteContent');
                $c->leftJoin('modResource', 'Resource', 'Resource.id = linguaSiteContent.resource_id');
                $c->where(array(
                    'uri:LIKE' => $search,
                    'Resource.published:=' => 1,
                    'Resource.deleted:!=' => 1,
                ));
                
                $c->leftJoin('linguaLangs', 'Lang', 'Lang.id = linguaSiteContent.lang_id');
                $c->where(array(
                    'Lang.lang_code:=' => $modx->cultureKey,
                ));
                
                $clone = $modx->getObject('linguaSiteContent', $c);
                if ($clone) {
                    $resource = $modx->getObject('modResource', $clone->get('resource_id'));
                    if ($resource) {
                        $modx->sendForward($resource->get('id'));
                    }
                }
            }
        }

        break;

    case 'OnHandleRequest': // for global
    case 'OnInitCulture':   // for request class
        if ($modx->context->key !== 'mgr') {
            $langGetKey = $modx->getOption('lingua.request_key', $scriptProperties, 'lang');
            $langGetKeyValue = filter_input(INPUT_GET, $langGetKey, FILTER_SANITIZE_STRING);
            $langGetKeyValue = strtolower($langGetKeyValue);
            $langCookieValue = filter_input(INPUT_COOKIE, 'modx_lingua_switcher', FILTER_SANITIZE_STRING);
            $langCookieValue = strtolower($langCookieValue);
            if (!empty($langGetKeyValue) &&
                    $langGetKeyValue !== $modx->cultureKey &&
                    strlen($langGetKeyValue) === 2
            ) {
                $_SESSION['cultureKey'] = $langGetKeyValue;
                $modx->cultureKey = $langGetKeyValue;
//                $modx->setOption('cultureKey', $langGetKeyValue);
                setcookie('modx_lingua_switcher', $langGetKeyValue, time() + (1 * 24 * 60 * 60));
            } else if (!empty($langCookieValue) &&
                    $langCookieValue !== $modx->cultureKey &&
                    strlen($langCookieValue) === 2
            ) {
                $_SESSION['cultureKey'] = $langCookieValue;
                $modx->cultureKey = $langCookieValue;
//                $modx->setOption('cultureKey', $langCookieValue);
            }

            if ($modx->cultureKey !== $modx->getOption('cultureKey')) {
//                $modx->setOption('cultureKey', $modx->cultureKey);
                $modx->context->config['cultureKey'] = $modx->cultureKey;
            }

            $modx->setPlaceholder('lingua.cultureKey', $modx->cultureKey);
            $modx->setPlaceholder('lingua.language', $modx->cultureKey);
        }
        break;

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
            return '';
        }
        $modx->lexicon->load('lingua:default');
        $languages = $lingua->getLanguages();
        if (!empty($languages)) {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/components/lingua/css/mgr.css');
            $modx->controller->addJavascript(MODX_BASE_URL . 'assets/components/lingua/js/mgr/resource.js');
            // $modx->getOption('cultureKey') doesn't work!
            $modCultureKey = $modx->getObject('modSystemSetting', array('key' => 'cultureKey'));
            $cultureKey = $modCultureKey->get('value');
            //------------------------------------------------------------------
            $jsHTML = '
    window.lingua = new Lingua({
        defaultLang: "' . $cultureKey . '"
    });';

            //------------------------------------------------------------------
            $storeData = array();
            $storeDefaultData = array();
            $linguaSiteContentArray = array();
            $createHiddenFields = array();
            foreach ($languages as $language) {
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
            $jsHTML .= '
    lingua.config.siteContent = ' . json_encode($linguaSiteContentArray) . ';
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
        } // if (!empty($languages))
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
            return '';
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
                switch ($tv->get('type')) {
                    case 'tag':
                    case 'autotag':
                        $cloneInputForm = preg_replace('/("|\'){1}tv\-tags\-' . $tvId . '("|\'){1}/', '${1}tv-tags-' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/fld' . $tvId . '/', 'fld' . $tvCloneId, $cloneInputForm);
                        $cloneInputForm = preg_replace('/tv\-' . $tvId . '\-tag\-list/', 'tv-' . $tvCloneId . '-tag-list', $cloneInputForm);
                        $cloneInputForm = preg_replace('/o.id != \'' . $tvId . '\'/', 'o.id != \'' . $tvCloneId . '\'', $cloneInputForm);
                        $cloneInputForm = preg_replace('/("|\'){1}tvdef' . $tvId . '("|\'){1}/', '${1}tvdef' . $tvCloneId . '${2}', $cloneInputForm);
                        break;
                    case 'radio':
                    case 'option':
                        $cloneInputForm = preg_replace('/("|\'){1}tv' . $tvId . '\-/', '${1}tv' . $tvCloneId . '-', $cloneInputForm);
                        break;
                    case 'checkbox':
                        $cloneInputForm = preg_replace('/("|\'){1}tv' . $tvId . '\-/', '${1}tv' . $tvCloneId . '-', $cloneInputForm);
                        $cloneInputForm = preg_replace('/("|\'){1}tv\-' . $tvId . '("|\'){1}/', '${1}tv-' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/("|\'){1}tvdef' . $tvId . '("|\'){1}/', '${1}tvdef' . $tvCloneId . '${2}', $cloneInputForm);
                        break;
                    case 'file':
                        $cloneInputForm = preg_replace('/("|\'){1}tvbrowser' . $tvId . '("|\'){1}/', '${1}tvbrowser' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/("|\'){1}tvpanel' . $tvId . '("|\'){1}/', '${1}tvpanel' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/fld' . $tvId . '/', 'fld' . $tvCloneId, $cloneInputForm);
                        $cloneInputForm = preg_replace('/tv: ("|\'){1}' . $tvId . '("|\'){1}/', 'tv: ${1}' . $tvCloneId . '${2}', $cloneInputForm);
                        break;
                    case 'image':
                        $cloneInputForm = preg_replace('/("|\'){1}tvbrowser' . $tvId . '("|\'){1}/', '${1}tvbrowser' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/("|\'){1}tv\-image\-' . $tvId . '("|\'){1}/', '${1}tv-image-' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/("|\'){1}tv\-image\-preview\-' . $tvId . '("|\'){1}/', '${1}tv-image-preview-' . $tvCloneId . '${2}', $cloneInputForm);
                        $cloneInputForm = preg_replace('/fld' . $tvId . '/', 'fld' . $tvCloneId, $cloneInputForm);
                        $cloneInputForm = preg_replace('/tv: ("|\'){1}' . $tvId . '("|\'){1}/', 'tv: ${1}' . $tvCloneId . '${2}', $cloneInputForm);
                        break;
                    case 'url':
                        $cloneInputForm = preg_replace('/("|\'){1}tv' . $tvId . '_prefix("|\'){1}/', '${1}tv' . $tvId . '_prefix' . '_' . $language['lang_code'] . '_lingua_tv${2}', $cloneInputForm);

                        break;
                    default:
                        break;
                }
                $count++;
                $phs = $tvArray;
                $phs['tv.id'] = $tvCloneId;
                $phs['tv.formElement'] = $cloneInputForm;
                $phs['tv.showCheckbox'] = $showCheckbox;
                $cloneTVFields[] = $lingua->processElementTags($lingua->parseTpl('lingua.resourcetv.row', $phs));
            }
        }

        $jsHTML = "<script>\nExt.onReady(function() {\n";
        $jsHTML .= '    lingua.config.tmplvars = ' . json_encode($tmplvars) . ';' . "\n";
        $jsHTML .= '    lingua.initAllClonedTVFields(' . json_encode($initAllClonedTVFields) . ');' . "\n";
        $jsHTML .= "});\n</script>";
        $modx->event->output($jsHTML);
        $modx->event->output(@implode("\n", $cloneTVFields));

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
            return '';
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
            $linguaSiteContent->set('pagetitle', $v['pagetitle']);
            $linguaSiteContent->set('longtitle', $v['longtitle']);
            $linguaSiteContent->set('description', $v['description']);
            $linguaSiteContent->set('content', (isset($v['content']) && !empty($v['content']) ? $v['content'] : $v['ta']));
            if (empty($v['alias'])) {
                $v['alias'] = $resource->get('alias');
                $linguaSiteContent->setDirty('alias');
            }
            $linguaSiteContent->set('introtext', $v['introtext']);
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
         * TV's procces does this outside of reverting's loops
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
                    $linguaSiteTmplvarContentvalues->fromArray($params);
                    $linguaSiteTmplvarContentvalues->save();
                }
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

    case 'OnWebPageInit':
        $modx->setOption('cache_resource_key', 'lingua/resource/' . $modx->cultureKey);
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
        if (!empty($ids)) {
            $modx->removeCollection('linguaSiteContent', array(
                'resource_id:IN' => $ids
            ));
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
