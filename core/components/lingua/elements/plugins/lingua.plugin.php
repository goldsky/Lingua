<?php
if ($modx->context->key === 'mgr') {
    return;
}

$event = $modx->event->name;
switch ($event) {
    case 'OnHandleRequest':
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
        break;

    default:
        break;
}
return;