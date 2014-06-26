<?php

/**
 * Lingua
 *
 * Copyright 2013 by goldsky <goldsky@virtudraft.com>
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

            $modx->setPlaceholder('lingua.cultureKey', $modx->cultureKey);
            $modx->setPlaceholder('lingua.language', $modx->cultureKey);
        }
        break;

    case 'OnDocFormPrerender':
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');

        if (!($lingua instanceof Lingua)) {
            return '';
        }
        $modx->lexicon->load('lingua:default');
        $languages = $lingua->getLanguages();
        if (!empty($languages)) {
            $modx->regClientCSS(MODX_BASE_URL . 'assets/components/lingua/css/mgr.css');
            $modx->controller->addJavascript(MODX_BASE_URL . 'assets/components/lingua/js/mgr/resource.js');

            //------------------------------------------------------------------
            $jsHTML = '
    var lingua = new Lingua({
        defaultLang: "' . $modx->getOption('cultureKey') . '"
    });';

            //------------------------------------------------------------------
            $storeData = array();
            $linguaSiteContentArray = array();
            $createHiddenFields = '';
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
                $createHiddenFields .= 'lingua.createHiddenFields("' . $language['lang_code'] . '");' . "\n";
            } // foreach ($languages as $language)
            //------------------------------------------------------------------
            $jsHTML .= '
    lingua.config.langs = ' . json_encode($languages) . ';
    lingua.config.siteContent = ' . json_encode($linguaSiteContentArray) . ';
    ' . $createHiddenFields . '
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

    case 'OnDocFormSave':
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/lingua/');

        if (!($lingua instanceof Lingua)) {
            return '';
        }
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
        }

        foreach ($reverting as $k => $v) {
            $linguaLangs = $modx->getObject('linguaLangs', array('lang_code' => $k));
            $params = array(
                'resource_id' => $resource->get('id'),
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
        break;

    case 'OnWebPageInit':
        $modx->setOption('cache_resource_key', 'lingua/' . $modx->cultureKey);
        break;

    default:
        break;
}
return;
