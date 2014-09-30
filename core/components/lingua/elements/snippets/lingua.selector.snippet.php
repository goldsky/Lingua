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
 * @subpackage lingua_selector
 */
$tplWrapper = $modx->getOption('tplWrapper', $scriptProperties, 'lingua.selector.wrapper');
$tplItem = $modx->getOption('tplItem', $scriptProperties, 'lingua.selector.item');
$langKey = $modx->getOption('getKey', $scriptProperties, $modx->getOption('lingua.get_key', null, 'lang'));
$sortby = $modx->getOption('sortby', $scriptProperties, 'id');
$sortdir = $modx->getOption('sortdir', $scriptProperties, 'asc');
$phsPrefix = $modx->getOption('phsPrefix', $scriptProperties, 'lingua.');
$codeField = $modx->getOption('codeField', $scriptProperties, 'lang_code');

$defaultLinguaCorePath = $modx->getOption('core_path') . 'components/lingua/';
$linguaCorePath = $modx->getOption('lingua.core_path', null, $defaultLinguaCorePath);
$lingua = $modx->getService('lingua', 'Lingua', $linguaCorePath . 'model/lingua/', $scriptProperties);

if (!($lingua instanceof Lingua)) {
    return;
}

$allowedContexts = $modx->getOption('lingua.contexts');
$allowedContexts = array_map('trim', @explode(',', $allowedContexts));
$currentContext = $modx->context->get('key');
if (!in_array($currentContext, $allowedContexts)) {
    return;
}

$c = $modx->newQuery('linguaLangs');
$c->where('active=1');
$linguaLangs = $modx->context->config['lingua.langs'];
if (!empty($linguaLangs)) {
    $linguaLangs = array_map('trim', @explode(',', $linguaLangs));
    $c->where(array(
        'lang_code:IN' => $linguaLangs
    ));
}
$linguaLcids = $modx->context->config['lingua.lcids'];
if (!empty($linguaLcids)) {
    $linguaLcids = array_map('trim', @explode(',', $linguaLcids));
    $c->where(array(
        'lcid_string:IN' => $linguaLcids
    ));
}
$c->sortby($sortby, $sortdir);
$collection = $modx->getCollection('linguaLangs', $c);
$output = '';
if (!$collection) {
    return;
}
$pageURL = 'http';
if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
    $pageURL .= "s";
}
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] !== "80") {
    $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
} else {
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
}
$parseUrl = parse_url($pageURL);
if (!empty($parseUrl['query'])) {
    /**
     * http://stackoverflow.com/a/7753154/1246646
     */
    if (!function_exists('http_build_url')) {
        define('HTTP_URL_REPLACE', 1);              // Replace every part of the first URL when there's one of the second URL
        define('HTTP_URL_JOIN_PATH', 2);            // Join relative paths
        define('HTTP_URL_JOIN_QUERY', 4);           // Join query strings
        define('HTTP_URL_STRIP_USER', 8);           // Strip any user authentication information
        define('HTTP_URL_STRIP_PASS', 16);          // Strip any password authentication information
        define('HTTP_URL_STRIP_AUTH', 32);          // Strip any authentication information
        define('HTTP_URL_STRIP_PORT', 64);          // Strip explicit port numbers
        define('HTTP_URL_STRIP_PATH', 128);         // Strip complete path
        define('HTTP_URL_STRIP_QUERY', 256);        // Strip query string
        define('HTTP_URL_STRIP_FRAGMENT', 512);     // Strip any fragments (#identifier)
        define('HTTP_URL_STRIP_ALL', 1024);         // Strip anything but scheme and host

        /**
         * Build an URL<br>
         * The parts of the second URL will be merged into the first according to the flags argument.<br><br>
         *
         * @param	mixed	$url	(Part(s) of) an URL in form of a string or associative array like parse_url() returns
         * @param	mixed	$parts	Same as the first argument
         * @param	int		$flags	A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
         * @param	array	$newUrl	If set, it will be filled with the parts of the composed url like parse_url() would return
         * @return	string			Built URL
         */
        function http_build_url($url, $parts = array(), $flags = HTTP_URL_REPLACE, &$newUrl = false) {
            $keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

            // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
            if ($flags & HTTP_URL_STRIP_ALL) {
                $flags |= HTTP_URL_STRIP_USER;
                $flags |= HTTP_URL_STRIP_PASS;
                $flags |= HTTP_URL_STRIP_PORT;
                $flags |= HTTP_URL_STRIP_PATH;
                $flags |= HTTP_URL_STRIP_QUERY;
                $flags |= HTTP_URL_STRIP_FRAGMENT;
            }
            // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
            else if ($flags & HTTP_URL_STRIP_AUTH) {
                $flags |= HTTP_URL_STRIP_USER;
                $flags |= HTTP_URL_STRIP_PASS;
            }

            // Parse the original URL
            $parseUrl = parse_url($url);

            // Scheme and Host are always replaced
            if (isset($parts['scheme']))
                $parseUrl['scheme'] = $parts['scheme'];
            if (isset($parts['host']))
                $parseUrl['host'] = $parts['host'];

            // (If applicable) Replace the original URL with it's new parts
            if ($flags & HTTP_URL_REPLACE) {
                foreach ($keys as $key) {
                    if (isset($parts[$key]))
                        $parseUrl[$key] = $parts[$key];
                }
            }
            else {
                // Join the original URL path with the new path
                if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
                    if (isset($parseUrl['path']))
                        $parseUrl['path'] = rtrim(str_replace(basename($parseUrl['path']), '', $parseUrl['path']), '/') . '/' . ltrim($parts['path'], '/');
                    else
                        $parseUrl['path'] = $parts['path'];
                }

                // Join the original query string with the new query string
                if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
                    if (isset($parseUrl['query']))
                        $parseUrl['query'] .= '&' . $parts['query'];
                    else
                        $parseUrl['query'] = $parts['query'];
                }
            }

            // Strips all the applicable sections of the URL
            // Note: Scheme and Host are never stripped
            foreach ($keys as $key) {
                if ($flags & (int) constant('HTTP_URL_STRIP_' . strtoupper($key)))
                    unset($parseUrl[$key]);
            }

            $newUrl = $parseUrl;

            return
                    ((isset($parseUrl['scheme'])) ? $parseUrl['scheme'] . '://' : '')
                    . ((isset($parseUrl['user'])) ? $parseUrl['user'] . ((isset($parseUrl['pass'])) ? ':' . $parseUrl['pass'] : '') . '@' : '')
                    . ((isset($parseUrl['host'])) ? $parseUrl['host'] : '')
                    . ((isset($parseUrl['port'])) ? ':' . $parseUrl['port'] : '')
                    . ((isset($parseUrl['path'])) ? $parseUrl['path'] : '')
                    . ((isset($parseUrl['query'])) ? '?' . $parseUrl['query'] : '')
                    . ((isset($parseUrl['fragment'])) ? '#' . $parseUrl['fragment'] : '')
            ;
        }

    }

    parse_str($parseUrl['query'], $queries);
    unset($queries[$langKey]);
    $parseUrl['query'] = http_build_query($queries);

    $pageURL = http_build_url($pageURL, $parseUrl);
    $pageURL = urldecode($pageURL);
    // replace: &queryarray[0]=foo&queryarray[1]=bar
    // to:		&queryarray[]=foo&queryarray[]=bar
    $pageURL = preg_replace('/\[+(\d)+\]+/', '[]', $pageURL);
}

$pageURL = rtrim($pageURL, '?');
$hasQuery = strstr($pageURL, '?');

$languages = array();
$originPageUrl = $pageURL;
$requestUri = str_replace(MODX_BASE_URL, '', $parseUrl['path']);
// $modx->getOption('cultureKey') is overriden by plugin!
$modCultureKey = $modx->getObject('modSystemSetting', array('key' => 'cultureKey'));
$cultureKey = $modCultureKey->get('value');

$baseUrl = $modx->getOption('base_url', $scriptProperties);
$baseUrl = str_replace(MODX_BASE_URL, '', $baseUrl);
$baseUrl = trim($baseUrl, '/');
$originResource = $modx->getObject('modResource', $modx->resource->get('id'));

foreach ($collection as $item) {
    if ($item->get('lang_code') === $modx->cultureKey) {
        continue;
    }
    $itemArray = $item->toArray($phsPrefix);
    $cloneSite = $modx->getObject('linguaSiteContent', array(
        'resource_id' => $modx->resource->get('id'),
        'lang_id' => $item->get('id'),
    ));
    if ($modx->getOption('friendly_urls')) {
        $itemUri = '';
        if ($itemArray[$phsPrefix . 'lang_code'] === $cultureKey) {
            $itemUri = $originResource->get('uri');
        } elseif ($cloneSite) {
            $itemUri = $cloneSite->get('uri');
        }
        
        if (!empty($itemUri)) {
            $matches = null;
            preg_match('/(\/)*$/', $itemUri, $matches);
            $search = $requestUri . (!empty($matches[0]) ? $matches[1] : '');
            $replace = (!empty($baseUrl) ? $baseUrl . '/' : '') . $itemUri;
            $pageURL = str_replace($search, $replace, $originPageUrl);
        }
    }

    $itemArray[$phsPrefix . 'url'] = $pageURL . (!empty($hasQuery) ? '&' : '?') . $langKey . '=' . $itemArray[$phsPrefix . $codeField];
//    $itemArray[$phsPrefix . 'url'] = $pageURL;

    if (!empty($toArray)) {
        $languages[] = $itemArray;
    } else {
        $languages[] = $lingua->parseTpl($tplItem, $itemArray);
    }
}

if (!empty($toArray)) {
    $wrapper = array(
        $phsPrefix . 'languages' => $languages
    );
    $output = '<pre>' . print_r($wrapper, TRUE) . '</pre>';
} else {
    $selection = @implode("\n", $languages);
    $wrapper = array($phsPrefix . 'languages' => $selection);
    $output = $lingua->parseTpl($tplWrapper, $wrapper);
}
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return;
}
return $output;
