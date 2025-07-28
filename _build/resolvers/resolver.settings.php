<?php
/**
 * resolver.settings.php
 *
 * Резолвер для создания и удаления системных настроек.
 *
 * @package cookieconsent
 * @subpackage build
 */

$success = true;

if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {

        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Запуск резолвера системных настроек...');

            // Создаем пространство имен, если его нет
            if (!$namespace = $modx->getObject('modNamespace', 'cookieconsent')) {
                $namespace = $modx->newObject('modNamespace');
                $namespace->set('name', 'cookieconsent');
                $namespace->set('path', '{core_path}components/cookieconsent/');
                $namespace->set('assets_path', '{assets_path}components/cookieconsent/');
                if (!$namespace->save()) {
                    $modx->log(modX::LOG_LEVEL_ERROR, '[CookieConsent] Не удалось создать пространство имен "cookieconsent".');
                }
            }

            $assetsUrl = $modx->getOption('assets_url') . 'components/cookieconsent/';

            // Массив системных настроек для создания
            $settings = [
                'enabled' => [
                    'xtype' => 'combo-boolean',
                    'value' => false,
                    'area' => 'cookieconsent_main',
                ],
                'css_path' => [
                    'xtype' => 'textfield',
                    'value' => $assetsUrl . 'css/cookieconsent.css',
                    'area' => 'cookieconsent_main',
                ],
                'js_path' => [
                    'xtype' => 'textfield',
                    'value' => $assetsUrl . 'js/cookieconsent.min.js',
                    'area' => 'cookieconsent_main',
                ],
                'loader_js_path' => [
                    'xtype' => 'textfield',
                    'value' => $assetsUrl . 'js/cookieconsent.loader.js',
                    'area' => 'cookieconsent_main',
                ],
            ];

            // Проходим по массиву и создаем настройки, если их нет
            foreach ($settings as $key => $settingOptions) {
                $settingKey = 'cookieconsent.' . $key;
                if (!$setting = $modx->getObject('modSystemSetting', ['key' => $settingKey])) {
                    $setting = $modx->newObject('modSystemSetting');
                    $setting->fromArray(array_merge(
                        [
                            'key' => $settingKey,
                            'namespace' => 'cookieconsent',
                        ],
                        $settingOptions
                    ), '', true, true);

                    if ($setting->save()) {
                        $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Создана системная настройка: ' . $settingKey);
                    } else {
                        $modx->log(modX::LOG_LEVEL_ERROR, '[CookieConsent] Не удалось создать системную настройку: ' . $settingKey);
                        $success = false;
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Запуск удаления системных настроек...');

            if ($modx->removeCollection('modSystemSetting', ['namespace' => 'cookieconsent'])) {
                $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Системные настройки удалены.');
            }

            if ($namespace = $modx->getObject('modNamespace', 'cookieconsent')) {
                if ($namespace->remove()) {
                    $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Пространство имен "cookieconsent" удалено.');
                }
            }
            break;
    }
} else {
    $success = false;
}

return $success;