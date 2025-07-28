<?php
switch ($modx->event->name) {
    case 'OnWebPagePrerender':
        if ($modx->getOption('cookieconsent.enabled', null, 0)) {
            $css_path = $modx->getOption('cookieconsent.css_path');
            $js_path = $modx->getOption('cookieconsent.js_path');
            $loader_js_path = $modx->getOption('cookieconsent.loader_js_path');

            $styles = '';
            if (!empty($css_path)) {
                $styles = '<link rel="stylesheet" href="' . $css_path . '">';
            }

            $scripts = '';
            if (!empty($js_path)) {
                $scripts .= '<script src="' . $js_path . '"></script>';
            }
            if (!empty($loader_js_path)) {
                $scripts .= '<script src="' . $loader_js_path . '"></script>';
            }

            $chunk = $modx->getChunk('cookieconsentMarkup');
            if ($chunk) {
                $output = $chunk . $styles . $scripts;
                $modx->resource->_output = str_replace('</body>', $output . '</body>', $modx->resource->_output);
            }
        }
        break;
}