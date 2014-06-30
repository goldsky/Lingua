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
    case 'OnInitCulture':
        if ($modx->context->key !== 'mgr') {
            $langGetKey = $modx->getOption('lingua.request_key', $scriptProperties, 'lang');
            $langGetKeyValue = filter_input(INPUT_GET, $langGetKey, FILTER_SANITIZE_STRING);
            $langGetKeyValue = strtolower($langGetKeyValue);
            $langCookieValue = filter_input(INPUT_COOKIE, 'modx.lingua.switcher', FILTER_SANITIZE_STRING);
            $langCookieValue = strtolower($langCookieValue);
            if (!empty($langGetKeyValue) &&
                    $langGetKeyValue !== $modx->cultureKey &&
                    strlen($langGetKeyValue) === 2
            ) {
                $_SESSION['cultureKey'] = $langGetKeyValue;
                $modx->cultureKey = $langGetKeyValue;
                $modx->setOption('cultureKey', $langGetKeyValue);
                setcookie('modx.lingua.switcher', $langGetKeyValue, time() + (1 * 24 * 60 * 60));
            } else if (!empty($langCookieValue) &&
                    $langCookieValue !== $modx->cultureKey &&
                    strlen($langCookieValue) === 2
            ) {
                $_SESSION['cultureKey'] = $langCookieValue;
                $modx->cultureKey = $langCookieValue;
                $modx->setOption('cultureKey', $langCookieValue);
            }

            if ($modx->cultureKey !== $modx->getOption('cultureKey')) {
                $modx->setOption('cultureKey', $modx->cultureKey);
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
        $languages = $lingua->getLanguages(1);
        if (!empty($languages)) {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/components/lingua/css/mgr.css');
            $modx->controller->addJavascript(MODX_BASE_URL . 'assets/components/lingua/js/mgr/resource.js');

            //------------------------------------------------------------------
            $jsHTML = '
    window.lingua = new Lingua({
        defaultLang: "' . $modx->getOption('cultureKey') . '"
    });';

            //------------------------------------------------------------------
            $storeData = array();
            $linguaSiteContentArray = array();
            $createHiddenFields = array();
            foreach ($languages as $language) {
                $storeData[] = array(
                    $language['lang_code'],
                    $language['local_name'],
                    $language['flag'],
                );
                if ($language['lang_code'] === $modx->getOption('cultureKey')) {
                    continue;
                }
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
            tpl: \'<tpl for="."><div class="x-combo-list-item"><img src="../{flag}" class="icon"/> {local_name}</div></tpl>\',
            store: new Ext.data.ArrayStore({
                id: 0,
                fields: [
                    "lang_code",
                    "local_name",
                    "flag"
                ],
                data: ' . json_encode($storeData) . '
            }),
            displayField: "local_name",
            typeAhead: false,
            forceSelection: true,
            editable: false,
            mode: "local",
            triggerAction: "all",
            emptyText: "' . $languages[$modx->getOption('cultureKey')]['local_name'] . '",
            selectOnFocus: true,
            width: 150,
            listeners: {
                select: {
                    fn: function(combo, record, index) {
                        lingua.switchLanguage(record.get("lang_code"));
                    },
                    scope: this
                }
            }
        });
        actionButtons.insertButton(0, [languageBtn, "-"]);
        actionButtons.doLayout();
    }';
            $modx->controller->addHtml('<script type="text/javascript">
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
        if (!empty($languages)) {
            if ($resource) {
                $tvs = $resource->getTemplateVars();
            } else {
                $templateId = $template;
                $template = $modx->getObject('modTemplate', $templateId);
                $tvs = $template->getTemplateVars();
            }
            if ($tvs) {
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
                if ($linguaSiteTmplvars) {
                    $jsHTML = "<script>\nExt.onReady(function() {\n";             
                    $tmplvars = array();
                    $cloneTVFields = array();
                    foreach ($linguaSiteTmplvars as $linguaTv) {
                        $tvId = $linguaTv->get('tmplvarid');
                        $tv = $modx->getObject('modTemplateVar', $tvId);
                        if (!$tv) {
                            continue;
                        }
                        $tmplvars[] = array(
                            'id' => $tvId,
                            'type' => $tv->get('type')
                        );
                        foreach ($languages as $language) {
                            if ($language['lang_code'] === $modx->getOption('cultureKey')) {
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
                                'value'=> $content
                            ));
                            $tvCloneId = $tvId . '_' . $language['lang_code'] . '_lingua_tv';
                            // basic replacements
                            $inputForm = preg_replace('/("|\')+tv' . $tvId . '("|\')+/', '${1}tv' . $tvCloneId . '${2}', $inputForm);
                            $inputForm = preg_replace('/("|\')+tv' . $tvId . '\[\]("|\')+/', '${1}tv' . $tvCloneId . '[]${2}', $inputForm);
                            switch ($tv->get('type')) {
                                case 'tag':
                                case 'autotag':
                                    $inputForm = preg_replace('/("|\')+tv-tags-' . $tvId . '("|\')+/', '${1}tv-tags-' . $tvCloneId . '${2}', $inputForm);
                                    $inputForm = preg_replace('/fld' . $tvId . '/', 'fld' . $tvCloneId, $inputForm);
                                    $inputForm = preg_replace('/tv-' . $tvId . '-tag-list/', 'tv-' . $tvCloneId . '-tag-list', $inputForm);
                                    $inputForm = preg_replace('/o.id != \'' . $tvId . '\'/', 'o.id != \'' . $tvCloneId . '\'', $inputForm);
                                    break;
                                case 'radio':
                                case 'checkbox':
                                    $inputForm = preg_replace('/("|\')+tv' . $tvId . '-("|\')+/', '${1}tv' . $tvCloneId . '${2}-', $inputForm);
                                    break;
                                case 'file':
                                    $inputForm = preg_replace('/("|\')+tvbrowser' . $tvId . '("|\')+/', '${1}tvbrowser' . $tvCloneId . '${2}', $inputForm);
                                    $inputForm = preg_replace('/("|\')+tvpanel' . $tvId . '("|\')+/', '${1}tvpanel' . $tvCloneId . '${2}', $inputForm);
                                    break;
                                case 'image':
                                    $inputForm = preg_replace('/("|\')+tvbrowser' . $tvId . '("|\')+/', '${1}tvbrowser' . $tvCloneId . '${2}', $inputForm);
                                    $inputForm = preg_replace('/("|\')+tv-image-' . $tvId . '("|\')+/', '${1}tv-image-' . $tvCloneId . '${2}', $inputForm);
                                    $inputForm = preg_replace('/("|\')+tv-image-preview-' . $tvId . '("|\')+/', '${1}tv-image-preview-' . $tvCloneId . '${2}', $inputForm);
                                    $inputForm = preg_replace('/("|\')+tv-image-' . $tvId . '("|\')+/', '${1}tv-image-' . $tvCloneId . '${2}', $inputForm);
                                    $inputForm = preg_replace('/fld' . $tvId . '/', 'fld' . $tvCloneId, $inputForm);
                                    break;
                                default:
                                    break;
                            }
                            $cloneTVFields[] = $inputForm;
                        }
                    }
                    $jsHTML .= '    lingua.config.tmplvars = ' . json_encode($tmplvars) . ';' . "\n";
                    $jsHTML .= '    lingua.initAllClonedTVFields(' . json_encode($languages) . ');' . "\n";
                    $jsHTML .= "});\n</script>";
                    $modx->event->output($jsHTML);
                    $modx->event->output(@implode("\n", $cloneTVFields));
                }
            }
        }

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
        foreach ($resource->_fields as $k => $v) {
            if (!preg_match('/_lingua$/', $k)) {
                continue;
            }
            foreach ($v as $a => $b) {
                if ($a === $modx->getOption('cultureKey')) {
                    continue;
                }
                $reverting[$a][preg_replace('/_lingua$/', '', $k)] = $b;
            }
            /**
             * json seems has number of characters limit;
             * that makes saving success report truncated and output modal hangs
             */
            $resource->set($k, '');
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
            }
            $linguaSiteContent->set('introtext', $v['introtext']);
            $linguaSiteContent->set('alias', $v['alias']);
            $linguaSiteContent->set('menutitle', $v['menutitle']);
            $linguaSiteContent->set('link_attributes', $v['link_attributes']);
            $linguaSiteContent->set('uri_override', $v['uri_override']);
            $linguaSiteContent->set('uri', $v['uri']);
            $linguaSiteContent->save();
        }
        
        // update linguaSiteTmplvarContentvalues
        $reverting = array();
        foreach ($resource->_fields as $k => $v) {
            if (!preg_match('/_lingua_tv$/', $k)) {
                continue;
            }
            $tvKey = preg_replace('/_lingua_tv$/', '', $k);
            $tvKeys = @explode('_', $tvKey);
            $tvId = str_replace('tv', '', $tvKeys[0]);
            if ($tvKeys[1] === $modx->getOption('cultureKey')) {
                continue;
            }
            $reverting[$tvKeys[1]][$tvId] = $v;
            /**
             * json seems has number of characters limit;
             * that makes saving success report truncated and output modal hangs
             */
            $resource->set($k, '');
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
