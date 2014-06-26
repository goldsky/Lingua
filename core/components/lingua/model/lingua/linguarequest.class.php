<?php
/**
 * LinguaRequest
 *
 * @package modx
 */
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
/**
 * Encapsulates the interaction of MODX manager with an HTTP request.
 *
 * {@inheritdoc}
 *
 * @package modx
 */
class LinguaRequest extends modRequest {
    
    /**
     * Instantiates a LinguaRequest object.
     *
     * @param modX $modx
     * @return LinguaRequest
     */
    function __construct(modX & $modx) {
        parent :: __construct($modx);
$this->modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
$this->modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
    }

    /**
     * The primary MODX request handler (a.k.a. controller).
     *
     * @return boolean True if a request is handled without interruption.
     */
    public function handleRequest() {
$this->modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
$this->modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
        $this->loadErrorHandler();

        $this->sanitizeRequest();
        $this->modx->invokeEvent('OnHandleRequest');
        if (!$this->modx->checkSiteStatus()) {
            header('HTTP/1.1 503 Service Unavailable');
            if (!$this->modx->getOption('site_unavailable_page',null,1)) {
                $this->modx->resource = $this->modx->newObject('modDocument');
                $this->modx->resource->template = 0;
                $this->modx->resource->content = $this->modx->getOption('site_unavailable_message');
            } else {
                $this->modx->resourceMethod = "id";
                $this->modx->resourceIdentifier = $this->modx->getOption('site_unavailable_page',null,1);
            }
        } else {
            $this->checkPublishStatus();
            $this->modx->resourceMethod = $this->getResourceMethod();
            $this->modx->resourceIdentifier = $this->getResourceIdentifier($this->modx->resourceMethod);
            if ($this->modx->resourceMethod == 'id' && $this->modx->getOption('friendly_urls', null, false) && !$this->modx->getOption('request_method_strict', null, false)) {
                $uri = $this->modx->context->getResourceURI($this->modx->resourceIdentifier);
                if (!empty($uri)) {
                    if ((integer) $this->modx->resourceIdentifier === (integer) $this->modx->getOption('site_start', null, 1)) {
                        $url = $this->modx->getOption('site_url', null, MODX_SITE_URL);
                    } else {
                        $url = $this->modx->getOption('site_url', null, MODX_SITE_URL) . $uri;
                    }
                    $this->modx->sendRedirect($url, array('responseCode' => 'HTTP/1.1 301 Moved Permanently'));
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
            $found = $this->modx->findResource($this->modx->resourceIdentifier);
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
            if (!$this->modx->resource = $this->getResource($this->modx->resourceMethod, $this->modx->resourceIdentifier)) {
                $this->modx->sendErrorPage();
                return true;
            }
        }

$this->modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
$this->modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
$this->modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $this->modx->resourceMethod ' . $this->modx->resourceMethod);

        return $this->prepareResponse();
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
$this->modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
$this->modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
$this->modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $method ' . $method);
        $resource = null;
        if ($method == 'alias') {
            $resourceId = $this->modx->findResource($identifier);
        } else {
            $resourceId = $identifier;
        }

        if (!is_numeric($resourceId)) {
            $this->modx->sendErrorPage();
        }
        $isForward = array_key_exists('forward', $options) && !empty($options['forward']);
        $fromCache = false;
        $cacheKey = $this->modx->context->get('key') . "/resources/{$resourceId}";
        $cachedResource = $this->modx->cacheManager->get($cacheKey, array(
            xPDO::OPT_CACHE_KEY => $this->modx->getOption('cache_resource_key', null, 'resource'),
            xPDO::OPT_CACHE_HANDLER => $this->modx->getOption('cache_resource_handler', null, $this->modx->getOption(xPDO::OPT_CACHE_HANDLER)),
            xPDO::OPT_CACHE_FORMAT => (integer) $this->modx->getOption('cache_resource_format', null, $this->modx->getOption(xPDO::OPT_CACHE_FORMAT, null, xPDOCacheManager::CACHE_PHP)),
        ));
        if (is_array($cachedResource) && array_key_exists('resource', $cachedResource) && is_array($cachedResource['resource'])) {
            /** @var modResource $resource */
            $resource = $this->modx->newObject($cachedResource['resourceClass']);
            if ($resource) {
                $resource->fromArray($cachedResource['resource'], '', true, true, true);
                $resource->_content = $cachedResource['resource']['_content'];
                $resource->_isForward = $isForward;
                if (isset($cachedResource['contentType'])) {
                    $contentType = $this->modx->newObject('modContentType');
                    $contentType->fromArray($cachedResource['contentType'], '', true, true, true);
                    $resource->addOne($contentType, 'ContentType');
                }
                if (isset($cachedResource['resourceGroups'])) {
                    $rGroups = array();
                    foreach ($cachedResource['resourceGroups'] as $rGroupKey => $rGroup) {
                        $rGroups[$rGroupKey]= $this->modx->newObject('modResourceGroupResource', $rGroup);
                    }
                    $resource->addMany($rGroups);
                }
                if (isset($cachedResource['policyCache'])) $resource->setPolicies(array($this->modx->context->get('key') => $cachedResource['policyCache']));
                if (isset($cachedResource['elementCache'])) $this->modx->elementCache = $cachedResource['elementCache'];
                if (isset($cachedResource['sourceCache'])) $this->modx->sourceCache = $cachedResource['sourceCache'];
                if ($resource->get('_jscripts')) $this->modx->jscripts = $this->modx->jscripts + $resource->get('_jscripts');
                if ($resource->get('_sjscripts')) $this->modx->sjscripts = $this->modx->sjscripts + $resource->get('_sjscripts');
                if ($resource->get('_loadedjscripts')) $this->modx->loadedjscripts = array_merge($this->modx->loadedjscripts, $resource->get('_loadedjscripts'));
                $isForward= $resource->_isForward;
                $resource->setProcessed(true);
                $fromCache = true;
            }
        }
        if (!$fromCache || !is_object($resource)) {
            $criteria = $this->modx->newQuery('modResource');
            $criteria->select(array($this->modx->escape('modResource').'.*'));
            $criteria->where(array('id' => $resourceId, 'deleted' => '0'));
            if (!$this->modx->hasPermission('view_unpublished') || $this->modx->getSessionState() !== modX::SESSION_STATE_INITIALIZED) {
                $criteria->where(array('published' => 1));
            }
            if ($resource = $this->modx->getObject('modResource', $criteria)) {
                if ($resource instanceof modResource) {
                    if ($resource->get('context_key') !== $this->modx->context->get('key')) {
                        if (!$isForward || ($isForward && !$this->modx->getOption('allow_forward_across_contexts', $options, false))) {
                            if (!$this->modx->getCount('modContextResource', array($this->modx->context->get('key'), $resourceId))) {
                                return null;
                            }
                        }
                    }
                    $resource->_isForward= $isForward;
                    if (!$resource->checkPolicy('view')) {
                        $this->modx->sendUnauthorizedPage();
                    }
                    if ($tvs = $resource->getMany('TemplateVars', 'all')) {
                        /** @var modTemplateVar $tv */
                        foreach ($tvs as $tv) {
                            $resource->set($tv->get('name'), array(
                                $tv->get('name'),
                                $tv->getValue($resource->get('id')),
                                $tv->get('display'),
                                $tv->get('display_params'),
                                $tv->get('type'),
                            ));
                        }
                    }
                    $this->modx->resourceGenerated = true;
                }
            }
        } elseif ($fromCache && $resource instanceof modResource && !$resource->get('deleted')) {
            if ($resource->checkPolicy('load') && ($resource->get('published') || ($this->modx->getSessionState() === modX::SESSION_STATE_INITIALIZED && $this->modx->hasPermission('view_unpublished')))) {
                if ($resource->get('context_key') !== $this->modx->context->get('key')) {
                    if (!$isForward || ($isForward && !$this->modx->getOption('allow_forward_across_contexts', $options, false))) {
                        if (!$this->modx->getCount('modContextResource', array($this->modx->context->get('key'), $resourceId))) {
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
            $this->modx->invokeEvent('OnLoadWebPageCache');
        }
        return $resource;
    }

}
