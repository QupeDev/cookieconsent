<?php
$plugins = array();

$tmp = array(
    'InjectCookieMarkup' => array(
        'file' => 'inject_cookie_markup',
        'description' => 'Injects cookie markup into footer',
        'events' => array(
            'OnWebPagePrerender' => array(), // <- Убедись, что здесь есть имя события
        ),
    ),
);

if (!function_exists('getSnippetContent')) {
    function getSnippetContent($filename) {
        $o = file_get_contents($filename);
        if (preg_match('#\<\?php(.*)#is', $o, $matches)) {
            $o = trim($matches[1]);
        }
        return $o;
    }
}

foreach ($tmp as $pluginName => $data) {
    /** @var modPlugin $plugin */
    $plugin = $modx->newObject('modPlugin');
    $plugin->fromArray(array(
        'name' => $pluginName,
        'description' => @$data['description'],
        'plugincode' => getSnippetContent($sources['source_core'].'/elements/plugins/plugin.'.$data['file'].'.php'),
        'static' => false,
        'source' => 1,
    ), '', true, true);

    $events = array();
    if (!empty($data['events'])) {
        foreach ($data['events'] as $eventName => $eventProps) {
            /** @var modPluginEvent $event */
            $event = $modx->newObject('modPluginEvent');
            $event->fromArray(array_merge(array(
                'event' => $eventName,
                'priority' => 0,
                'propertyset' => 0,
            ), $eventProps), '', true, true);
            $events[] = $event;
        }
    }

    $plugin->addMany($events);
    $plugins[] = $plugin;
}

return $plugins;