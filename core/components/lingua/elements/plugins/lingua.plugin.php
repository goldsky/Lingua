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
    case 'OnHandleRequest':
        if ($modx->context->key !== 'mgr') {
            $langKey = $modx->getOption('lingua.get.key', $scriptProperties, 'lang');
            $lang = $modx->cultureKey;
            if (isset($_GET[$langKey]) &&
                    $_GET[$langKey] !== '' &&
                    $_GET[$langKey] !== $lang
            ) {
                $lang = $_GET[$langKey];
                $_SESSION['cultureKey'] = $lang;
                $modx->cultureKey = $lang;
                $modx->setOption('cultureKey', $lang);
                setcookie('modx.lingua.switcher', $lang, time() + (1 * 24 * 60 * 60));
            } else if (isset($_COOKIE['modx.lingua.switcher']) &&
                    $_COOKIE['modx.lingua.switcher'] !== '' &&
                    $_COOKIE['modx.lingua.switcher'] !== $lang
            ) {
                $lang = $_COOKIE['modx.lingua.switcher'];
                $_SESSION['cultureKey'] = $lang;
                $modx->cultureKey = $lang;
                $modx->setOption('cultureKey', $lang);
            }

            $modx->setPlaceholder('lingua.cultureKey', $lang);
            $modx->setPlaceholder('lingua.language', $lang);
        }
        break;

    case 'OnDocFormPrerender':
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/');

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
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/');

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

    case 'OnLoadWebDocument':
        $lingua = $modx->getService('lingua', 'Lingua', MODX_CORE_PATH . 'components/lingua/model/');

        if (!($lingua instanceof Lingua)) {
            return '';
        }
        
        if ($modx->getOption('cultureKey') === $modx->cultureKey) {
            return;
        }
        $linguaLangs = $modx->getObject('linguaLangs', array('lang_code' => $modx->cultureKey));
        $linguaSiteContent = $modx->getObject('linguaSiteContent', array(
            'resource_id' => $modx->resource->get('id'),
            'lang_id' => $linguaLangs->get('id'),

        ));
        if (!$linguaSiteContent) {
            return;
        }
        $linguaSiteContentArray = $linguaSiteContent->toArray();
        unset($linguaSiteContentArray['id']);
        foreach ($linguaSiteContentArray as $k => $v) {
            $modx->resource->set($k, $v);
        }
        $modx->resource->_processed = false;
        $modx->resource->_content = false;
        $modx->resource->process();
        
        break;
    default:
        break;
}
return;
