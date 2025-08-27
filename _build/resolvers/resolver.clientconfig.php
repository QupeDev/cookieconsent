<?php
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx =& $object->xpdo;
            
        
            if (!($modx instanceof modX)) {
                return false;
            }
            
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Запуск резолвера ClientConfig...');
            
            if (!class_exists('ClientConfig')) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[CookieConsent] ClientConfig не установлен!');
                return false;
            }
            
            $modx->addPackage('clientconfig', MODX_CORE_PATH . 'components/clientconfig/model/');
            
            // Создаём группу настроек
            $groupName = 'Политика конфиденциальности';
            $group = $modx->getObject('cgGroup', ['label' => $groupName]);
            if (!$group) {
                $group = $modx->newObject('cgGroup');
                $group->set('label', $groupName);
                $group->set('description', '');
                if ($group->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[CookieConsent] Создана группа настроек: {$groupName}");
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR, "[CookieConsent] Ошибка при создании группы: {$groupName}");
                    return false;
                }
            }
            $groupId = $group->get('id');
            
            // Настройки для ClientConfig
            $settings = [
                [
                    'key' => 'private_policy_header', 
                    'value' => 'Управление cookie', 
                    'label' => 'Заголовок', 
                    'xtype' => 'textfield'
                ],
                [
                    'key' => 'private_policy_text', 
                    'value' => '<p>Наш сайт обрабатывает файлы cookie (в том числе, файлы cookie, используемые «Яндекс-метрикой»). Нажимая на кнопку «Соглашаюсь», вы даете свое согласие на обработку файлов cookie вашего браузера в соответствии с нашей <a target="_blank" href="[[++private_policy_href]]">политикой конфиденциальности</a>.</p>',
                    'label' => 'Сообщение', 
                    'xtype' => 'code'
                ],
                [
                    'key' => 'private_policy_href', 
                    'value' => '[[~1]]', 
                    'label' => 'Ссылка на политику', 
                    'xtype' => 'textfield'
                ],
                [
                    'key' => 'private_policy_popup_bg', 
                    'value' => '2e425a', 
                    'label' => 'Фон окна', 
                    'xtype' => 'colorpickerfield'
                ],
                [
                    'key' => 'private_policy_popup_btn', 
                    'value' => 'd4d6da', 
                    'label' => 'Цвет кнопки', 
                    'xtype' => 'colorpickerfield'
                ],
                [
                    'key' => 'private_policy_popup_txt', 
                    'value' => '2e425a', 
                    'label' => 'Цвет текста кнопки', 
                    'xtype' => 'colorpickerfield'
                ],
                [
                    'key' => 'private_policy_popup_txt_color',
                    'value' => 'ffffff',
                    'label' => 'Цвет текста',
                    'xtype' => 'colorpickerfield'
                ],
                [
                    'key' => 'private_policy_popup_title_color',
                    'value' => '000000',
                    'label' => 'Цвет текста заголовка',
                    'xtype' => 'colorpickerfield'
                ],
                [
                    'key' => 'private_policy_popup_padding',
                    'value' => '1em',
                    'label' => 'Отступ окна',
                    'xtype' => 'textfield'
                ],
                [
                    'key' => 'private_policy_popup_border',
                    'value' => '0',
                    'label' => 'Рамка окна',
                    'xtype' => 'textfield'
                ],
                [
                    'key' => 'private_policy_popup_border_radius',
                    'value' => '0',
                    'label' => 'Скругление углов окна',
                    'xtype' => 'textfield'
                ],
            ];
            
            // Сохраняем настройки ClientConfig
            foreach ($settings as $s) {
                $setting = $modx->getObject('cgSetting', ['key' => $s['key']]);
                if (!$setting) {
                    $setting = $modx->newObject('cgSetting');
                    $setting->fromArray([
                        'key'         => $s['key'],
                        'value'       => $s['value'],
                        'label'       => $s['label'],
                        'description' => '',
                        'xtype'       => $s['xtype'],
                        'is_required' => 0,
                        'sortorder'   => 0,
                        'group'       => $groupId,
                    ]);
                    if ($setting->save()) {
                        $modx->log(modX::LOG_LEVEL_INFO, "[CookieConsent] Добавлена настройка: {$s['key']}");
                    } else {
                        $modx->log(modX::LOG_LEVEL_ERROR, "[CookieConsent] Ошибка при сохранении настройки: {$s['key']}");
                    }
                } else {
                    $modx->log(modX::LOG_LEVEL_INFO, "[CookieConsent] Настройка уже существует: {$s['key']}");
                }
            }
            
            
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Резолвер ClientConfig завершён.');
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Обновление кэша ClientConfig...');
            $modx->getCacheManager()->delete('clientconfig', [xPDO::OPT_CACHE_KEY => 'system_settings']);
            if ($modx->getOption('clientconfig.clear_cache', null, true)) {
                $modx->getCacheManager()->delete('', [xPDO::OPT_CACHE_KEY => 'resource']);
            }

            $modx->invokeEvent('ClientConfig_ConfigChange');
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Кэш ClientConfig успешно обновлен.');

            
            break;
            
        case xPDOTransport::ACTION_UNINSTALL:
            // Код для удаления
            $modx =& $object->xpdo;
            
            // Дополнительная проверка MODX
            if (!($modx instanceof modX)) {
                return false;
            }
            
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Запуск удаления настроек ClientConfig...');
            
            // Проверяем наличие ClientConfig
            if (class_exists('ClientConfig')) {
                // Подключаем модель ClientConfig
                $modx->addPackage('clientconfig', MODX_CORE_PATH . 'components/clientconfig/model/');
                
                // Удаляем настройки ClientConfig
                $settingKeys = [
                    'private_policy_popup_bg',
                    'private_policy_popup_btn', 
                    'private_policy_popup_txt',
                    'private_policy_header',
                    'private_policy_text',
                    'private_policy_href',
                    'private_policy_popup_txt_color',
                    'private_policy_popup_title_color'
                ];
                
                foreach ($settingKeys as $key) {
                    $setting = $modx->getObject('cgSetting', ['key' => $key]);
                    if ($setting) {
                        if ($setting->remove()) {
                            $modx->log(modX::LOG_LEVEL_INFO, "[CookieConsent] Удалена настройка ClientConfig: {$key}");
                        }
                    }
                }
                
                
                $groupName = 'Политика конфиденциальности';
                $group = $modx->getObject('cgGroup', ['label' => $groupName]);
                if ($group) {
                    $settingsCount = $modx->getCount('cgSetting', ['group' => $group->get('id')]);
                    if ($settingsCount == 0) {
                        if ($group->remove()) {
                            $modx->log(modX::LOG_LEVEL_INFO, "[CookieConsent] Удалена пустая группа: {$groupName}");
                        }
                    }
                }
            }
            
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Удаление настроек ClientConfig завершено.');

            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Обновление кэша ClientConfig...');
            $modx->getCacheManager()->delete('clientconfig', [xPDO::OPT_CACHE_KEY => 'system_settings']);
            if ($modx->getOption('clientconfig.clear_cache', null, true)) {
                $modx->getCacheManager()->delete('', [xPDO::OPT_CACHE_KEY => 'resource']);
            }

            $modx->invokeEvent('ClientConfig_ConfigChange');
            $modx->log(modX::LOG_LEVEL_INFO, '[CookieConsent] Кэш ClientConfig успешно обновлен.');



            break;
    }
}

return true;
?>