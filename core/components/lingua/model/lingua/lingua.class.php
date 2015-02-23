<?php

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
 * @package lingua
 * @subpackage lingua
 */
class Lingua {

    const VERSION = '2.1.0';
    const RELEASE = 'beta4';

    /**
     * modX object
     * @var object
     */
    public $modx;

    /**
     * $scriptProperties
     * @var array
     */
    public $config;

    /**
     * To hold error message
     * @var string
     */
    private $_error = '';

    /**
     * To hold output message
     * @var string
     */
    private $_output = '';

    /**
     * To hold placeholder array, flatten array with prefixable
     * @var array
     */
    private $_placeholders = array();

    /**
     * store the chunk's HTML to property to save memory of loop rendering
     * @var array
     */
    private $_chunks = array();

    /**
     * store array of ancestors
     * @var array
     */
    private $_ancestors = array();

    /**
     * constructor
     * @param   modX    $modx
     * @param   array   $config     parameters
     */
    public function __construct(modX $modx, $config = array()) {
        $this->modx = & $modx;
        $config = is_array($config) ? $config : array();
        $basePath = $this->modx->getOption('lingua.core_path', $config, $this->modx->getOption('core_path') . 'components/lingua/');
        $assetsUrl = $this->modx->getOption('lingua.assets_url', $config, $this->modx->getOption('assets_url') . 'components/lingua/');
        $this->config = array_merge(array(
            'version' => self::VERSION . '-' . self::RELEASE,
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath . 'model/',
            'processorsPath' => $basePath . 'processors/',
            'chunksPath' => $basePath . 'elements/chunks/',
            'templatesPath' => $basePath . 'templates/',
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'conn/mgr.php',
                ), $config);

        $this->modx->lexicon->load('lingua:default');
        $tablePrefix = $this->modx->getOption('lingua.table_prefix', null, $this->modx->config[modX::OPT_TABLE_PREFIX] . 'lingua_');
        $this->modx->addPackage('lingua', $this->config['modelPath'], $tablePrefix);
    }

    /**
     * Set class configuration exclusively for multiple snippet calls
     * @param   array   $config     snippet's parameters
     */
    public function setConfigs(array $config = array()) {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Define individual config for the class
     * @param   string  $key    array's key
     * @param   string  $val    array's value
     */
    public function setConfig($key, $val) {
        $this->config[$key] = $val;
    }

    /**
     * Set string error for boolean returned methods
     * @return  void
     */
    public function setError($msg) {
        $this->_error = $msg;
    }

    /**
     * Get string error for boolean returned methods
     * @return  string  output
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * Set string output for boolean returned methods
     * @return  void
     */
    public function setOutput($msg) {
        $this->_output = $msg;
    }

    /**
     * Get string output for boolean returned methods
     * @return  string  output
     */
    public function getOutput() {
        return $this->_output;
    }

    /**
     * Set internal placeholder
     * @param   string  $key    key
     * @param   string  $value  value
     * @param   string  $prefix add prefix if it's required
     */
    public function setPlaceholder($key, $value, $prefix = '') {
        $prefix = !empty($prefix) ? $prefix : (isset($this->config['phsPrefix']) ? $this->config['phsPrefix'] : '');
        $this->_placeholders[$prefix . $key] = $this->trimString($value);
    }

    /**
     * Get an internal placeholder
     * @param   string  $key    key
     * @return  string  value
     */
    public function getPlaceholder($key) {
        return $this->_placeholders[$key];
    }

    /**
     * Set internal placeholders
     * @param   array   $placeholders   placeholders in an associative array
     * @param   string  $prefix         add prefix if it's required
     * @param   boolean $merge          define whether the output will be merge to global properties or not
     * @param   string  $delimiter      define placeholder's delimiter
     * @return  mixed   boolean|array of placeholders
     */
    public function setPlaceholders($placeholders, $prefix = '', $merge = true, $delimiter = '.') {
        if (empty($placeholders)) {
            return FALSE;
        }
        $prefix = !empty($prefix) ? $prefix : (isset($this->config['phsPrefix']) ? $this->config['phsPrefix'] : '');
        $placeholders = $this->trimArray($placeholders);
        $placeholders = $this->implodePhs($placeholders, rtrim($prefix, $delimiter));
        // enclosed private scope
        if ($merge) {
            $this->_placeholders = array_merge($this->_placeholders, $placeholders);
        }
        // return only for this scope
        return $placeholders;
    }

    /**
     * Get internal placeholders in an associative array
     * @return array
     */
    public function getPlaceholders() {
        return $this->_placeholders;
    }

    /**
     * Merge multi dimensional associative arrays with separator
     * @param   array   $array      raw associative array
     * @param   string  $keyName    parent key of this array
     * @param   string  $separator  separator between the merged keys
     * @param   array   $holder     to hold temporary array results
     * @return  array   one level array
     */
    public function implodePhs(array $array, $keyName = null, $separator = '.', array $holder = array()) {
        $phs = !empty($holder) ? $holder : array();
        foreach ($array as $k => $v) {
            $key = !empty($keyName) ? $keyName . $separator . $k : $k;
            if (is_array($v)) {
                $phs = $this->implodePhs($v, $key, $separator, $phs);
            } else {
                $phs[$key] = $v;
            }
        }
        return $phs;
    }

    /**
     * Trim string value
     * @param   string  $string     source text
     * @param   string  $charlist   defined characters to be trimmed
     * @link http://php.net/manual/en/function.trim.php
     * @return  string  trimmed text
     */
    public function trimString($string, $charlist = null) {
        if (empty($string) && !is_numeric($string)) {
            return '';
        }
        $string = htmlentities($string);
        // blame TinyMCE!
        $string = preg_replace('/(&Acirc;|&nbsp;)+/i', '', $string);
        $string = trim($string, $charlist);
        $string = trim(preg_replace('/\s+^(\r|\n|\r\n)/', ' ', $string));
        $string = html_entity_decode($string);
        return $string;
    }

    /**
     * Trim array values
     * @param   array   $array          array contents
     * @param   string  $charlist       [default: null] defined characters to be trimmed
     * @link http://php.net/manual/en/function.trim.php
     * @return  array   trimmed array
     */
    public function trimArray($input, $charlist = null) {
        if (is_array($input)) {
            $output = array_map(array($this, 'trimArray'), $input);
        } else {
            $output = $this->trimString($input, $charlist);
        }

        return $output;
    }

    /**
     * Parsing template
     * @param   string  $tpl    @BINDINGs options
     * @param   array   $phs    placeholders
     * @return  string  parsed output
     * @link    http://forums.modx.com/thread/74071/help-with-getchunk-and-modx-speed-please?page=2#dis-post-413789
     */
    public function parseTpl($tpl, array $phs = array()) {
        $output = '';

        if (isset($this->_chunks[$tpl]) && !empty($this->_chunks[$tpl])) {
            return $this->parseTplCode($this->_chunks[$tpl], $phs);
        }

        if (preg_match('/^(@CODE|@INLINE)/i', $tpl)) {
            $tplString = preg_replace('/^(@CODE|@INLINE)/i', '', $tpl);
            // tricks @CODE: / @INLINE:
            $tplString = ltrim($tplString, ':');
            $tplString = trim($tplString);
            $this->_chunks[$tpl] = $tplString;
            $output = $this->parseTplCode($tplString, $phs);
        } elseif (preg_match('/^@FILE/i', $tpl)) {
            $tplFile = preg_replace('/^@FILE/i', '', $tpl);
            // tricks @FILE:
            $tplFile = ltrim($tplFile, ':');
            $tplFile = trim($tplFile);
            $tplFile = $this->replacePropPhs($tplFile);
            try {
                $output = $this->parseTplFile($tplFile, $phs);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
        // ignore @CHUNK / @CHUNK: / empty @BINDING
        else {
            $tplChunk = preg_replace('/^@CHUNK/i', '', $tpl);
            // tricks @CHUNK:
            $tplChunk = ltrim($tpl, ':');
            $tplChunk = trim($tpl);

            $chunk = $this->modx->getObject('modChunk', array('name' => $tplChunk), true);
            if (empty($chunk)) {
                // try to use @splittingred's fallback
                $f = $this->config['chunksPath'] . strtolower($tplChunk) . '.chunk.tpl';
                try {
                    $output = $this->parseTplFile($f, $phs);
                } catch (Exception $e) {
                    $output = $e->getMessage();
                    return 'Chunk: ' . $tplChunk . ' is not found, neither the file ' . $output;
                }
            } else {
//                $output = $this->modx->getChunk($tplChunk, $phs);
                /**
                 * @link    http://forums.modx.com/thread/74071/help-with-getchunk-and-modx-speed-please?page=4#dis-post-464137
                 */
                $chunk = $this->modx->getParser()->getElement('modChunk', $tplChunk);
                $this->_chunks[$tpl] = $chunk->get('content');
                $chunk->setCacheable(false);
                $chunk->_processed = false;
                $output = $chunk->process($phs);
            }
        }

        return $output;
    }

    /**
     * Parsing inline template code
     * @param   string  $code   HTML with tags
     * @param   array   $phs    placeholders
     * @return  string  parsed output
     */
    public function parseTplCode($code, array $phs = array()) {
        $chunk = $this->modx->newObject('modChunk');
        $chunk->setContent($code);
        $chunk->setCacheable(false);
        $phs = $this->replacePropPhs($phs);
        $chunk->_processed = false;
        return $chunk->process($phs);
    }

    /**
     * Parsing file based template
     * @param   string  $file   file path
     * @param   array   $phs    placeholders
     * @return  string  parsed output
     * @throws  Exception if file is not found
     */
    public function parseTplFile($file, array $phs = array()) {
        if (!file_exists($file)) {
            throw new Exception('File: ' . $file . ' is not found.');
        }
        $o = file_get_contents($file);
        $this->_chunks[$file] = $o;
        $chunk = $this->modx->newObject('modChunk');

        // just to create a name for the modChunk object.
        $name = strtolower(basename($file));
        $name = rtrim($name, '.tpl');
        $name = rtrim($name, '.chunk');
        $chunk->set('name', $name);

        $chunk->setCacheable(false);
        $chunk->setContent($o);
        $chunk->_processed = false;
        $output = $chunk->process($phs);

        return $output;
    }

    /**
     * If the chunk is called by AJAX processor, it needs to be parsed for the
     * other elements to work, like snippet and output filters.
     *
     * Example:
     * <pre><code>
     * <?php
     * $content = $myObject->parseTpl('tplName', $placeholders);
     * $content = $myObject->processElementTags($content);
     * </code></pre>
     *
     * @param   string  $content    the chunk output
     * @param   array   $options    option for iteration
     * @return  string  parsed content
     */
    public function processElementTags($content, array $options = array()) {
        $maxIterations = intval($this->modx->getOption('parser_max_iterations', $options, 10));
        if (!$this->modx->parser) {
            $this->modx->getParser();
        }
        $this->modx->parser->processElementTags('', $content, true, false, '[[', ']]', array(), $maxIterations);
        $this->modx->parser->processElementTags('', $content, true, true, '[[', ']]', array(), $maxIterations);
        return $content;
    }

    /**
     * Replace the property's placeholders
     * @param   string|array    $subject    Property
     * @return  array           The replaced results
     */
    public function replacePropPhs($subject) {
        $pattern = array(
            '/\{core_path\}/',
            '/\{base_path\}/',
            '/\{assets_url\}/',
            '/\{filemanager_path\}/',
            '/\[\[\+\+core_path\]\]/',
            '/\[\[\+\+base_path\]\]/'
        );
        $replacement = array(
            $this->modx->getOption('core_path'),
            $this->modx->getOption('base_path'),
            $this->modx->getOption('assets_url'),
            $this->modx->getOption('filemanager_path'),
            $this->modx->getOption('core_path'),
            $this->modx->getOption('base_path')
        );
        if (is_array($subject)) {
            $parsedString = array();
            foreach ($subject as $k => $s) {
                if (is_array($s)) {
                    $s = $this->replacePropPhs($s);
                }
                $parsedString[$k] = preg_replace($pattern, $replacement, $s);
            }
            return $parsedString;
        } else {
            return preg_replace($pattern, $replacement, $subject);
        }
    }

    /**
     * Replacing MODX's getCount(), because it has bug on counting SQL with function.<br>
     * Retrieves a count of xPDOObjects by the specified xPDOCriteria.
     *
     * @param string $className Class of xPDOObject to count instances of.
     * @param mixed $criteria Any valid xPDOCriteria object or expression.
     * @return integer The number of instances found by the criteria.
     * @see xPDO::getCount()
     * @link http://forums.modx.com/thread/88619/getcount-fails-if-the-query-has-aggregate-leaving-having-039-s-field-undefined The discussion for this
     */
    public function getQueryCount($className, $criteria = null) {
        $count = 0;
        if ($query = $this->modx->newQuery($className, $criteria)) {
            $expr = '*';
            if ($pk = $this->modx->getPK($className)) {
                if (!is_array($pk)) {
                    $pk = array($pk);
                }
                $expr = $this->modx->getSelectColumns($className, 'alias', '', $pk);
            }
            $query->prepare();
            $sql = $query->toSQL();
            $stmt = $this->modx->query("SELECT COUNT($expr) FROM ($sql) alias");
            if ($stmt) {
                $tstart = microtime(true);
                if ($stmt->execute()) {
                    $this->modx->queryTime += microtime(true) - $tstart;
                    $this->modx->executedQueries++;
                    if ($results = $stmt->fetchAll(PDO::FETCH_COLUMN)) {
                        $count = reset($results);
                        $count = intval($count);
                    }
                } else {
                    $this->modx->queryTime += microtime(true) - $tstart;
                    $this->modx->executedQueries++;
                    $this->modx->log(modX::LOG_LEVEL_ERROR, "[" . __CLASS__ . "] Error " . $stmt->errorCode() . " executing statement: \n" . print_r($stmt->errorInfo(), true), '', __METHOD__, __FILE__, __LINE__);
                }
            }
        }
        return $count;
    }

    /**
     * Returns select statement for easy reading
     *
     * @access public
     * @param xPDOQuery $query The query to print
     * @return string The select statement
     * @author Coroico <coroico@wangba.fr>
     */
    public function niceQuery(xPDOQuery $query = null) {
        $searched = array("SELECT", "GROUP_CONCAT", "LEFT JOIN", "INNER JOIN", "EXISTS", "LIMIT", "FROM",
            "WHERE", "GROUP BY", "HAVING", "ORDER BY", "OR", "AND", "IFNULL", "ON", "MATCH", "AGAINST",
            "COUNT");
        $replace = array(" \r\nSELECT", " \r\nGROUP_CONCAT", " \r\nLEFT JOIN", " \r\nINNER JOIN", " \r\nEXISTS", " \r\nLIMIT", " \r\nFROM",
            " \r\nWHERE", " \r\nGROUP BY", " \r\nHAVING", " ORDER BY", " \r\nOR", " \r\nAND", " \r\nIFNULL", " \r\nON", " \r\nMATCH", " \r\nAGAINST",
            " \r\nCOUNT");
        $output = '';
        if (isset($query)) {
            $query->prepare();
            $output = str_replace($searched, $replace, " " . $query->toSQL());
        }
        return $output;
    }

    /**
     * Get list of the languages. Default cultureKey comes first.
     * @param   boolean $activeOnly only return active languages (default: true)
     * @param   boolean $assoc      returned as associative array
     * @param   boolean $persist    keep the output in placeholder or not?
     * @return  array   languages
     */
    public function getLanguages($activeOnly = true, $assoc = true, $persist = true) {
        if ($persist) {
            if ($assoc) {
                if (isset($this->_placeholders['languages_assoc_array']) && !empty($this->_placeholders['languages_assoc_array'])) {
                    return $this->_placeholders['languages_assoc_array'];
                }
            } else {
                if (isset($this->_placeholders['languages_array']) && !empty($this->_placeholders['languages_array'])) {
                    return $this->_placeholders['languages_array'];
                }
            }
        }
        $this->_placeholders['languages_assoc_array'] = array();
        $this->_placeholders['languages_array'] = array();
        // $modx->getOption('cultureKey') doesn't work!
        $modCultureKey = $this->modx->getObject('modSystemSetting', array('key' => 'cultureKey'));
        $cultureKey = $modCultureKey->get('value');
        $defaultLang = $this->modx->getObject('linguaLangs', array(
            'lang_code' => $cultureKey
        ));
        if ($defaultLang) {
            if ($assoc) {
                $this->_placeholders['languages_assoc_array'][$defaultLang->get('lang_code')] = $defaultLang->toArray();
            } else {
                $this->_placeholders['languages_array'][] = $defaultLang->toArray();
            }
        }
        $c = $this->modx->newQuery('linguaLangs');

        $definedLanguages = $this->getOption('lingua.langs');
        if (!empty($definedLanguages)) {
            $definedLanguages = array_map('trim', @explode(',', $definedLanguages));
            $c->where(array(
                'lang_code:IN' => $definedLanguages
            ));
        } else {
            if ($activeOnly) {
                $c->where(array(
                    'active' => 1
                ));
            }
        }

        if ($defaultLang) {
            $c->where(array(
                'id:!=' => $defaultLang->get('id')
            ));
        }
        $collection = $this->modx->getCollection('linguaLangs', $c);
        if ($collection) {
            foreach ($collection as $item) {
                if ($assoc) {
                    $this->_placeholders['languages_assoc_array'][$item->get('lang_code')] = $item->toArray();
                } else {
                    $this->_placeholders['languages_array'][] = $item->toArray();
                }
            }
        }
        if ($assoc) {
            return $this->_placeholders['languages_assoc_array'];
        } else {
            return $this->_placeholders['languages_array'];
        }
    }

    /**
     * Get system's option
     * @param   string  $key    option's key
     * @return  string  value
     */
    public function getOption($key) {
        // Scope's setting overrides CMP's setting of defining active languages
        $config = array();
        // system wide
        $config = array_merge($config, $this->modx->config);
        // context wide
        if ($this->modx->resource) {
            if ($this->modx->context->get('key') === 'mgr') {
                $docId = intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
                if ($docId === 0) {
                    $ctxKey = filter_input(INPUT_GET, 'context_key', FILTER_SANITIZE_STRING);
                } else {
                    $ctxKey = $this->modx->resource->get('context_key');
                }
                $contextSettings = $this->modx->getCollection('modContextSetting', array(
                    'context_key' => $ctxKey,
                ));
                if ($contextSettings) {
                    foreach ($contextSettings as $setting) {
                        $config[$setting->get('key')] = $setting->get('value');
                    }
                }
            }
            /**
             * resource scopes
             */
            $propKey = preg_replace('/^lingua\./', '', $key);
            // check resource's ancestors scope
            $ancestors = $this->getAncestors($this->modx->resource->get('id'));
            if (!empty($ancestors)) {
                $countResourceAncestorsScopes = $this->modx->getCount('linguaResourceScopes', array(
                    'resource_id:IN' => $ancestors,
                    'as_ancestor' => 1,
                ));
                if ($countResourceAncestorsScopes > 0) {
                    /**
                     * loop the ancestors array instead, because ancestors are
                     * sorted from the closest ones, and we want to get the config
                     * from the closest ancestor
                     */
                    foreach ($ancestors as $ancestor) {
                        $scope = $this->modx->getObject('linguaResourceScopes', array(
                            'resource_id' => $ancestor,
                            'as_ancestor' => 1,
                        ));
                        if ($scope) {
                            $props = $scope->get('properties');
                            $props = json_decode($props, true);
                            if (isset($props[$propKey]) && !empty($props[$propKey])) {
                                $config[$key] = $props[$propKey];
                                break; // quit, bring up the value from the closest ancestor
                            }
                        }
                    }
                }
            }
            // check resource's parent scope
            $parentId = $this->modx->resource->get('parent');
            if ($parentId > 0) {
                $linguaResourceParentScope = $this->modx->getObject('linguaResourceScopes', array(
                    'resource_id' => $parentId,
                    'as_parent' => 1,
                ));
                if ($linguaResourceParentScope) {
                    $props = $linguaResourceScope->get('properties');
                    $props = json_decode($props, true);
                    if (isset($props[$propKey]) && !empty($props[$propKey])) {
                        $config[$key] = $props[$propKey];
                    }
                }
            }
            // check resource's scope
            $linguaResourceScope = $this->modx->getObject('linguaResourceScopes', array('resource_id' => $this->modx->resource->get('id')));
            if ($linguaResourceScope) {
                $props = $linguaResourceScope->get('properties');
                $props = json_decode($props, true);
                if (isset($props[$propKey])) {
                    $excludeSelf = $linguaResourceScope->get('exclude_self');
                    if (empty($excludeSelf)) {
                        $config[$key] = $props[$propKey];
                    }
                }
            }
        }

        // user's defined properties
        if ($this->modx->context->get('key') !== 'mgr') {
            if ($this->modx->user->get('id') !== 0) {
                $userSettings = $this->modx->getCollection('modUserSetting', array(
                    'user' => $this->modx->user->get('id'),
                ));
                if ($userSettings) {
                    foreach ($userSettings as $setting) {
                        $config[$setting->get('key')] = $setting->get('value');
                    }
                }
            }
        }
        // element's properties
        $config = array_merge($config, $this->config);

        return $this->modx->getOption($key, $config);
    }

    /**
     * Get sorted array of ancestors from a resource
     * @param int $id   resource's ID
     * @return array list of ancestors
     */
    public function getAncestors($id) {
        $self = $this->modx->getObject('modResource', $id);
        if ($self) {
            $parent = $self->get('parent');
            if ($parent > 0) {
                if (!in_array($parent, $this->_ancestors)) {
                    $this->_ancestors = array_merge($this->_ancestors, (array) $parent);
                }
                return $this->getAncestors($parent);
            }
        }
        return $this->_ancestors;
    }

    /**
     * Get culture key down the stream from all overridings probabilities
     * @return string   cultureKey
     */
    public function getCultureKey() {
        $langGetKey = $this->modx->getOption('lingua.request_key', $this->config, 'lang');
        $langGetKeyValue = filter_input(INPUT_GET, $langGetKey, FILTER_SANITIZE_STRING);
        if (!empty($langGetKeyValue)) {
            return strtolower($langGetKeyValue);
        }

        $langCookieValue = filter_input(INPUT_COOKIE, 'modx_lingua_switcher', FILTER_SANITIZE_STRING);
        if (!empty($langCookieValue)) {
            return strtolower($langCookieValue);
        }

        $langSessionValue = $_SESSION['cultureKey'];
        if (!empty($langSessionValue)) {
            return strtolower($langSessionValue);
        }

        return $this->modx->cultureKey;
    }

    /**
     * Override cultureKeys
     * @param void all environments
     */
    public function setCultureKey($cultureKey) {
        $_SESSION['cultureKey'] = $cultureKey;
        $this->modx->cultureKey = $cultureKey;
        $this->modx->setOption('cultureKey', $cultureKey);
        setcookie('modx_lingua_switcher', $cultureKey, time() + (1 * 24 * 60 * 60), '/');
    }

    /**
     * Create/Update translation of a resource
     * @param int       $id         resource's ID
     * @param string    $langCode   language's 2-letters ISO code
     * @param array     $values     array of values
     * @param boolean   $update     allow update or not?
     * @return boolean
     */
    public function setContentTranslation($id, $langCode, array $values = array(), $update = true) {
        $resource = $this->modx->getObject('modResource', $id);
        if (!$resource) {
            return false;
        }
        $this->modx->context = $this->modx->getObject('modContext', array('key' => $resource->get('context_key')));
        $defaultCultureKey = $this->modx->context->getOption('cultureKey');
        if ($langCode === $defaultCultureKey) {
            return false;
        }
        $linguaLangs = $this->modx->getObject('linguaLangs', array('lang_code' => $langCode));
        if (!$linguaLangs) {
            return false;
        }
        $params = array(
            'resource_id' => $id,
            'lang_id' => $linguaLangs->get('id'),
        );
        $linguaSiteContent = $this->modx->getObject('linguaSiteContent', $params);
        if (!$linguaSiteContent) {
            $linguaSiteContent = $this->modx->newObject('linguaSiteContent');
            $linguaSiteContent->fromArray($params);
            $linguaSiteContent->save();
        } else {
            if (!$update) {
                return false;
            }
        }
        if (!isset($values['pagetitle']) || empty($values['pagetitle'])) {
            $values['pagetitle'] = $resource->get('pagetitle');
        }
        $linguaSiteContent->set('pagetitle', $values['pagetitle']);
        if (isset($values['longtitle'])) {
            $linguaSiteContent->set('longtitle', $values['longtitle']);
        }
        if (isset($values['description'])) {
            $linguaSiteContent->set('description', $values['description']);
        }
        if (isset($values['content']) || isset($values['ta'])) {
            $linguaSiteContent->set('content', (isset($values['content']) && !empty($values['content']) ? $values['content'] : $values['ta']));
        }
        if (isset($values['introtext'])) {
            $linguaSiteContent->set('introtext', $values['introtext']);
        }
        if (empty($values['alias'])) {
            $values['alias'] = $values['pagetitle'];
            $linguaSiteContent->setDirty('alias');
        }
        $linguaSiteContent->set('alias', $values['alias']);
        if (isset($values['menutitle'])) {
            $linguaSiteContent->set('menutitle', $values['menutitle']);
        }
        if (isset($values['link_attributes'])) {
            $linguaSiteContent->set('link_attributes', $values['link_attributes']);
        }
        if (isset($values['uri_override'])) {
            $linguaSiteContent->set('uri_override', $values['uri_override']);
        }
        if (isset($values['uri'])) {
            $linguaSiteContent->set('uri', $values['uri']);
        }
        $linguaSiteContent->set('parent', $resource->get('parent'));
        $linguaSiteContent->set('isfolder', $resource->get('isfolder'));
        $linguaSiteContent->set('context_key', $resource->get('context_key'));
        $linguaSiteContent->set('content_type', $resource->get('content_type'));
        if ($resource->get('refreshURIs')) {
            $linguaSiteContent->set('refreshURIs', true);
        }
        return $linguaSiteContent->save();
    }

    /**
     * Create/Update translation of a Template Variable
     * @param int       $resourceId resource's ID
     * @param string    $langCode   language's 2-letters ISO code
     * @param int       $tvId       TV's ID
     * @param string    $val        TV's value
     * @param boolean   $update     allow update or not?
     * @return boolean
     */
    public function setTVTranslation($resourceId, $langCode, $tvId, $val = '', $update = true) {
        $resource = $this->modx->getObject('modResource', $resourceId);
        if (!$resource) {
            return false;
        }
        $this->modx->context = $this->modx->getObject('modContext', array('key' => $resource->get('context_key')));
        $defaultCultureKey = $this->modx->context->getOption('cultureKey');
        if ($langCode === $defaultCultureKey) {
            return false;
        }
        $linguaLangs = $this->modx->getObject('linguaLangs', array('lang_code' => $langCode));
        if (!$linguaLangs) {
            return false;
        }
        $params = array(
            'lang_id' => $linguaLangs->get('id'),
            'tmplvarid' => $tvId,
            'contentid' => $resourceId,
        );
        $linguaSiteTmplvarContentvalues = $this->modx->getObject('linguaSiteTmplvarContentvalues', $params);
        if (!$linguaSiteTmplvarContentvalues) {
            $linguaSiteTmplvarContentvalues = $this->modx->newObject('linguaSiteTmplvarContentvalues');
            $linguaSiteTmplvarContentvalues->set('lang_id', $linguaLangs->get('id'));
            $linguaSiteTmplvarContentvalues->set('tmplvarid', $tvId);
            $linguaSiteTmplvarContentvalues->set('contentid', $resourceId);
        } else {
            if (!$update) {
                return false;
            }
        }
        $linguaSiteTmplvarContentvalues->set('value', $val);
        return $linguaSiteTmplvarContentvalues->save();
    }

    /**
     * Synchronize Resource and its TVs to the translations of the defined languages
     * @param modResource $resource
     * @return boolean
     */
    public function synchronize(modResource $resource) {
        $resourceArray = $resource->toArray();
        $this->modx->resource = $resource;
        $languages = $this->getLanguages(true, true, false);
        if ($languages) {
            $langCodes = array();
            foreach ($languages as $language) {
                $langCodes[] = $language['lang_code'];
            }
            // first, delete unused translation
            $c = $this->modx->newQuery('linguaSiteContent');
            $c->innerJoin('linguaLangs', 'Lang');
            $c->where(array(
                'linguaSiteContent.resource_id:=' => $resourceArray['id'],
                'Lang.lang_code:NOT IN' => $langCodes,
            ));
            $unusedContents = $this->modx->getCollection('linguaSiteContent', $c);
            if ($unusedContents) {
                foreach ($unusedContents as $item) {
                    $item->remove();
                }
            }
            foreach ($languages as $language) {
                $this->setContentTranslation($resourceArray['id'], $language['lang_code'], $resourceArray, false);
            }
            $tvs = $resource->getTemplateVars();
            $translatedTvs = $this->modx->getCollection('linguaSiteTmplvars');
            if ($translatedTvs && $tvs) {
                $translatedTvsArray = array();
                foreach ($translatedTvs as $translatedTv) {
                    $translatedTvsArray[] = $translatedTv->get('tmplvarid');
                }
                foreach ($tvs as $tv) {
                    // first, delete unused translation
                    $c = $this->modx->newQuery('linguaSiteTmplvarContentvalues');
                    $c->innerJoin('linguaLangs', 'Lang');
                    $c->where(array(
                        'linguaSiteTmplvarContentvalues.contentid:=' => $resourceArray['id'],
                        'linguaSiteTmplvarContentvalues.tmplvarid:=' => $tv->get('id'),
                        'Lang.lang_code:NOT IN' => $langCodes,
                    ));
                    $unusedContents = $this->modx->getCollection('linguaSiteTmplvarContentvalues', $c);
                    if ($unusedContents) {
                        foreach ($unusedContents as $item) {
                            $item->remove();
                        }
                    }
                    if (!in_array($tv->get('id'), $translatedTvsArray)) {
                        continue;
                    }
                    $this->setTVTranslation($resourceArray['id'], $language['lang_code'], $tv->get('id'), $tv->get('value'), false);
                }
            }
        }

        return true;
    }

}
