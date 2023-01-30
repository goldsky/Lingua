<?php
namespace Lingua\Model;

use MODX\Revolution\modX;
use MODX\Revolution\modRequest;
use MODX\Revolution\modDocument;
use MODX\Revolution\modResource;
use MODX\Revolution\modContentType;
use MODX\Revolution\modContextResource;
use MODX\Revolution\modResourceGroupResource;
use MODX\Revolution\Error\modError;
use MODX\Revolution\Registry\modRegistry;
use MODX\Revolution\Registry\modFileRegister;
use xPDO\Cache\xPDOCacheManager;
use xPDO\xPDO;

/**
 * Lingua
 *
 * Copyright 2013-2015 by goldsky <goldsky@virtudraft.com>
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
 * Encapsulates the interaction of MODX manager with an HTTP request.
 *
 * {@inheritdoc}
 *
 * @package lingua
 * @subpackage linguarequest
 */

class LinguaRequest extends modRequest
{
    /**
     *
     * @var object $lingua A reference to the Lingua object
     */
    public $lingua;

    /**
     * @param modX $modx A reference to the modX object
     */
    function __construct(modX &$modx) {
        parent::__construct($modx);
        $this->lingua = $this->modx->getService('lingua', 'Lingua');
    }


    /**
     * The primary MODX request handler (a.k.a. controller).
     *
     * @return boolean True if a request is handled without interruption.
     */
    public function handleRequest() {
        $this->loadErrorHandler();

        // If enabled, send the X-Powered-By header to identify this site as running MODX, per discussion in #12882
        if ($this->modx->getOption('send_poweredby_header', null, true)) {
            $version = $this->modx->getVersionData();
            header("X-Powered-By: MODX {$version['code_name']}");
        }

        $this->sanitizeRequest();
        $this->modx->invokeEvent('OnHandleRequest');
        if (!$this->modx->checkSiteStatus()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');
            if (!$this->modx->getOption('site_unavailable_page', null, 1)) {
                $this->modx->resource = $this->modx->newObject(modDocument::class);
                $this->modx->resource->template = 0;
                $this->modx->resource->content = $this->modx->getOption('site_unavailable_message');
            } else {
                $this->modx->resourceMethod = "id";
                $this->modx->resourceIdentifier = $this->modx->getOption('site_unavailable_page', null, 1);
            }
        } else {
            $this->checkPublishStatus();
            $this->modx->resourceMethod = $this->getResourceMethod();
            $this->modx->resourceIdentifier = $this->getResourceIdentifier($this->modx->resourceMethod);
            if ($this->modx->resourceMethod == 'id' && $this->modx->getOption('friendly_urls', null,
                    false) && $this->modx->getOption('request_method_strict', null, false)) {
                $uri = $this->modx->context->getResourceURI($this->modx->resourceIdentifier);
                if (!empty($uri)) {
                    if ((integer)$this->modx->resourceIdentifier === (integer)$this->modx->getOption('site_start', null,
                            1)) {
                        $url = $this->modx->getOption('site_url', null, MODX_SITE_URL);
                    } else {
                        $url = $this->modx->getOption('site_url', null, MODX_SITE_URL) . $uri;
                    }
                    $this->modx->sendRedirect($url,
                        ['responseCode' => $_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently']);
                }
            }
        }
        if (empty ($this->modx->resourceMethod)) {
            $this->modx->resourceMethod = "id";
        }
        if ($this->modx->resourceMethod == "alias") {
            $this->modx->resourceIdentifier = $this->_cleanResourceIdentifier($this->modx->resourceIdentifier);
        }
        if ($this->modx->resourceMethod == "alias") {
            $found = $this->findResource($this->modx->resourceIdentifier);
            if ($found) {
                $this->modx->resourceIdentifier = $found;
                $this->modx->resourceMethod = 'id';
            } else {
                $this->modx->sendErrorPage();
            }
        }
        $this->modx->beforeRequest();
        $this->modx->invokeEvent("OnWebPageInit");

        if (!is_object($this->modx->resource)) {
            if (!$this->modx->resource = $this->getResource($this->modx->resourceMethod,
                $this->modx->resourceIdentifier)) {
                $this->modx->sendErrorPage();
            }
        }

        $this->prepareResponse();
    }

    /**
     * Gets a requested resource and all required data.
     *
     * @param string $method The method, 'id', or 'alias', by which to perform
     * the resource lookup.
     * @param string|integer $identifier The identifier with which to search.
     * @param array $options An array of options for the resource fetching
     * @return modResource The requested modResource instance or request
     * is forwarded to the error page, or unauthorized page.
     */
    public function getResource($method, $identifier, array $options = array()) {
        $resource = null;
        if ($method == 'alias') {
            $resourceId = $this->findResource($identifier);
        } else {
            $resourceId = $identifier;
        }

        if (!is_numeric($resourceId)) {
            $this->modx->sendErrorPage();
        }

        $resource = parent::getResource($method, $identifier, $options);
        if ($resource) {
            $context = $resource->get('context_key');
            $allowedContexts = $this->modx->getOption('lingua.contexts');
            $allowedContexts = array_map('trim', @explode(',', $allowedContexts));
            if (empty($context) || empty($allowedContexts) || !in_array($context, $allowedContexts)) {
                return $resource;
            }
        }
        $cultureKey = !empty($this->modx->cultureKey) ? $this->modx->cultureKey : $this->modx->getOption('cultureKey', null, 'en');
        $this->modx->setOption('cache_resource_key', 'lingua/resource/' . $cultureKey);

        $isForward = array_key_exists('forward', $options) && !empty($options['forward']);
        $fromCache = false;
        $cacheKey = $this->modx->context->get('key') . "/resources/{$resourceId}";
        $cachedResource = $this->modx->cacheManager->get($cacheKey, [
            xPDO::OPT_CACHE_KEY => $this->modx->getOption('cache_resource_key', null, 'lingua/resource/' . $cultureKey),
            xPDO::OPT_CACHE_HANDLER => $this->modx->getOption('cache_resource_handler', null,
                $this->modx->getOption(xPDO::OPT_CACHE_HANDLER)),
            xPDO::OPT_CACHE_FORMAT => (integer)$this->modx->getOption('cache_resource_format', null,
                $this->modx->getOption(xPDO::OPT_CACHE_FORMAT, null, xPDOCacheManager::CACHE_PHP)),
        ]);
        if (is_array($cachedResource) && array_key_exists('resource',
                $cachedResource) && is_array($cachedResource['resource'])) {
            /** @var modResource $resource */
            $resource = $this->modx->newObject($cachedResource['resourceClass']);
            if ($resource) {
                $resource->fromArray($cachedResource['resource'], '', true, true, true);
                $resource->_content = $cachedResource['resource']['_content'];
                $resource->_isForward = $isForward;
                if (isset($cachedResource['contentType'])) {
                    $contentType = $this->modx->newObject(modContentType::class);
                    $contentType->fromArray($cachedResource['contentType'], '', true, true, true);
                    $resource->addOne($contentType, 'ContentType');
                }
                if (isset($cachedResource['resourceGroups'])) {
                    $rGroups = [];
                    foreach ($cachedResource['resourceGroups'] as $rGroupKey => $rGroup) {
                        $rGroups[$rGroupKey] = $this->modx->newObject(modResourceGroupResource::class, $rGroup);
                    }
                    $resource->addMany($rGroups);
                }
                if (isset($cachedResource['policyCache'])) {
                    $resource->setPolicies([$this->modx->context->get('key') => $cachedResource['policyCache']]);
                }
                if (isset($cachedResource['elementCache'])) {
                    $this->modx->elementCache = $cachedResource['elementCache'];
                }
                if (isset($cachedResource['sourceCache'])) {
                    $this->modx->sourceCache = $cachedResource['sourceCache'];
                }
                if ($resource->get('_jscripts')) {
                    $this->modx->jscripts = $this->modx->jscripts + $resource->get('_jscripts');
                }
                if ($resource->get('_sjscripts')) {
                    $this->modx->sjscripts = $this->modx->sjscripts + $resource->get('_sjscripts');
                }
                if ($resource->get('_loadedjscripts')) {
                    $this->modx->loadedjscripts = array_merge($this->modx->loadedjscripts,
                        $resource->get('_loadedjscripts'));
                }
                $isForward = $resource->_isForward;
                $resource->setProcessed(true);
                $fromCache = true;
            }
        }
        if (!$fromCache || !is_object($resource)) {
            $criteria = $this->modx->newQuery(modResource::class);
            $criteria->select([$this->modx->escape('modResource') . '.*']);
            $criteria->where(['id' => $resourceId, 'deleted' => '0']);
            if (!$this->modx->hasPermission('view_unpublished') || $this->modx->getSessionState() !== modX::SESSION_STATE_INITIALIZED) {
                $criteria->where(['published' => 1]);
            }
            if ($resource = $this->modx->getObject(modResource::class, $criteria)) {
                if ($resource instanceof modResource) {
                    if ($resource->get('context_key') !== $this->modx->context->get('key')) {
                        if (!$isForward || ($isForward && !$this->modx->getOption('allow_forward_across_contexts',
                                    $options, false))) {
                            if (!$this->modx->getCount(modContextResource::class,
                                [$this->modx->context->get('key'), $resourceId])) {
                                return null;
                            }
                        }
                    }
                    $resource->_isForward = $isForward;
                    if (!$resource->checkPolicy('view')) {
                        $this->modx->sendUnauthorizedPage();
                    }

                    // hack the resource's content in here -------------------->
                    $linguaLangs = $this->modx->getObject(LinguaLangs::class, array('lang_code' => $cultureKey));
                    $emptyReturnsDefault = $this->modx->getOption('lingua.empty_returns_default', null, false);
                    if (($this->lingua instanceof \Lingua) && $linguaLangs) {
                        $linguaSiteContent = $this->modx->getObject(LinguaSiteContent::class, array(
                            'resource_id' => $resource->get('id'),
                            'lang_id' => $linguaLangs->get('id'),
                        ));
                        if ($linguaSiteContent) {
                            $linguaSiteContentArray = $linguaSiteContent->toArray();
                            unset($linguaSiteContentArray['id']);
                            foreach ($linguaSiteContentArray as $k => $v) {
                                // exclude URI to reveal back the original URI later
                                if ($k === 'uri') {
                                    continue;
                                }
                                if (empty($v)) {
                                    if (!$emptyReturnsDefault) {
                                        $resource->set($k, $v); // return empty value
                                    }
                                } else {
                                    $resource->set($k, $v);
                                }
                            }
                        }
                    }
                    // hacking ends ------------------------------------------->

                    if ($tvs = $resource->getMany('TemplateVars', 'all')) {
                        /** @var modTemplateVar $tv */
                        foreach ($tvs as $tv) {
                            $value = $tv->getValue($resource->get('id'));
                            // hack the tv's content in here ------------------>
                            if (($this->lingua instanceof \Lingua) && $linguaLangs) {
                                $linguaTVContent = $this->modx->getObject(LinguaSiteTmplvarContentvalues::class, array(
                                    'tmplvarid' => $tv->get('id'),
                                    'contentid' => $resourceId,
                                    'lang_id' => $linguaLangs->get('id')
                                ));
                                if ($linguaTVContent) {
                                    $linguaTVContentValue = $linguaTVContent->get('value');
                                    if (empty($linguaTVContentValue)) {
                                        if (!$emptyReturnsDefault) {
                                            $value = $linguaTVContentValue; // return empty value
                                        }
                                    } else {
                                        $value = $linguaTVContentValue;
                                    }
                                }
                            }
                            // hacking ends ----------------------------------->

                            $resource->set($tv->get('name'), [
                                $tv->get('name'),
                                $value,
                                $tv->get('display'),
                                $tv->get('display_params'),
                                $tv->get('type'),
                            ]);
                        }
                    }
                    $this->modx->resourceGenerated = true;
                }
            }
        } elseif ($fromCache && $resource instanceof modResource && !$resource->get('deleted')) {
            if ($resource->checkPolicy('load') && ($resource->get('published') || ($this->modx->getSessionState() === modX::SESSION_STATE_INITIALIZED && $this->modx->hasPermission('view_unpublished')))) {
                if ($resource->get('context_key') !== $this->modx->context->get('key')) {
                    if (!$isForward || ($isForward && !$this->modx->getOption('allow_forward_across_contexts', $options,
                                false))) {
                        if (!$this->modx->getCount(modContextResource::class,
                            [$this->modx->context->get('key'), $resourceId])) {
                            return null;
                        }
                    }
                }
                if (!$resource->checkPolicy('view')) {
                    $this->modx->sendUnauthorizedPage();
                }
            } else {
                return null;
            }
            $this->modx->invokeEvent('OnLoadWebPageCache', [
                'resource' => &$resource,
            ]);
        }

        if ($this->modx->getOption('lingua.debug')) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
            $this->modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
            $this->modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $resourceArray: ' . print_r($resource->toArray(), 1));
        }

        return $resource;
    }

    /**
     * Cleans the resource identifier from the request params.
     *
     * @param string $identifier The raw identifier.
     * @return string|integer The cleansed identifier.
     */
    public function _cleanResourceIdentifier($identifier) {
        if (empty ($identifier)) {
            if ($this->modx->getOption('base_url', null, MODX_BASE_URL) !== strtok($_SERVER["REQUEST_URI"], '?')) {
                $this->modx->sendRedirect($this->modx->getOption('site_url', null, MODX_SITE_URL),
                    ['responseCode' => $_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently']);
            }
            $identifier = $this->modx->getOption('site_start', null, 1);
            $this->modx->resourceMethod = 'id';
        } elseif ($this->modx->getOption('friendly_urls', null, false) && $this->modx->resourceMethod == 'alias') {
            $containerSuffix = trim($this->modx->getOption('container_suffix', null, ''));
            $found = $this->findResource($identifier);
            if ($found === false && !empty ($containerSuffix)) {
                $suffixLen = strlen($containerSuffix);
                $identifierLen = strlen($identifier);
                if (substr($identifier, $identifierLen - $suffixLen) === $containerSuffix) {
                    $identifier = substr($identifier, 0, $identifierLen - $suffixLen);
                    $found = $this->findResource($identifier);
                } else {
                    $identifier = "{$identifier}{$containerSuffix}";
                    $found = $this->findResource("{$identifier}{$containerSuffix}");
                }
                if ($found) {
                    $parameters = $this->getParameters();
                    unset($parameters[$this->modx->getOption('request_param_alias')]);
                    $url = $this->modx->makeUrl($found, $this->modx->context->get('key'), $parameters, 'full');
                    $this->modx->sendRedirect($url,
                        ['responseCode' => $_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently']);
                }
                $this->modx->resourceMethod = 'alias';
            } elseif ((integer)$this->modx->getOption('site_start', null, 1) === $found) {
                $parameters = $this->getParameters();
                unset($parameters[$this->modx->getOption('request_param_alias')]);
                $url = $this->modx->makeUrl($this->modx->getOption('site_start', null, 1),
                    $this->modx->context->get('key'), $parameters, 'full');
                $this->modx->sendRedirect($url,
                    ['responseCode' => $_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently']);
            } else {
                if ($this->modx->getOption('friendly_urls_strict', null, false)) {
                    $requestUri = $_SERVER['REQUEST_URI'];
                    $qsPos = strpos($requestUri, '?');
                    if ($qsPos !== false) {
                        $requestUri = substr($requestUri, 0, $qsPos);
                    }
                    $fullId = $this->modx->getOption('base_url', null, MODX_BASE_URL) . $identifier;
                    $requestUri = urldecode($requestUri);
                    if ($fullId !== $requestUri && strpos($requestUri, $fullId) !== 0) {
                        $parameters = $this->getParameters();
                        unset($parameters[$this->modx->getOption('request_param_alias')]);
                        $url = $this->modx->makeUrl($found, $this->modx->context->get('key'), $parameters, 'full');
                        $this->modx->sendRedirect($url,
                            ['responseCode' => $_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently']);
                    }
                }
                $this->modx->resourceMethod = 'alias';
            }
        } else {
            $this->modx->resourceMethod = 'id';
        }

        return $identifier;
    }

    public function findResource($uri, $context = '') {
        $resourceId = $this->modx->findResource($uri, $context);
        if (!is_numeric($resourceId)) {
            $resourceId = $this->findCloneResource($uri, $context);
        }
        return $resourceId;
    }

    public function findCloneResource($uri, $context = '') {
        $resourceId = false;
        if (empty($context) && isset($this->modx->context)) $context = $this->modx->context->get('key');
        if (!empty($context) && (!empty($uri) || $uri === '0')) {
            $query = $this->modx->newQuery(LinguaSiteContent::class);
            $query->leftJoin('modResource', 'Resource', 'Resource.id = LinguaSiteContent.resource_id');
            $query->where(array(
                'context_key' => $context,
                'uri' => $uri,
                'Resource.deleted' => false
            ));
            $linguaContent = $this->modx->getObject(LinguaSiteContent::class, $query);
            if ($linguaContent) {
                $resourceId = $linguaContent->get('resource_id');
                // reset the culture key
                $lang = $linguaContent->getOne('Lang');
                if ($lang) {
                    $cultureKey = $lang->get('lang_code');
                    $this->lingua->setCultureKey($cultureKey);
                    $this->modx->setOption('cultureKey', $cultureKey);
                    $this->modx->context->config['cultureKey'] = $cultureKey;
                }
            }
        }
        return $resourceId;
    }
}
