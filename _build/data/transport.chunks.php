<?php
$chunks = [];

$chunk = $modx->newObject('modChunk');
$chunk->fromArray([
    'id' => 0,
    'name' => 'cookieconsentMarkup',
    'description' => 'Разметка для окна согласия на cookies',
    'snippet' => <<<HTML
<div id="cookie-config"
     data-popup-bg="#[[++private_policy_popup_bg]]"
     data-btn-bg="#[[++private_policy_popup_btn]]"
     data-btn-text="#[[++private_policy_popup_txt]]"
     data-text-color="#[[++private_policy_popup_txt_color]]"
     data-padding="[[++private_policy_popup_padding]]"
     data-border="[[++private_policy_popup_border]]"
     data-border-radius="[[++private_policy_popup_border_radius]]"
     data-header="[[++private_policy_header]]"
     data-href="[[++private_policy_href]]"
     data-site-url="[[++site_url]]">
</div>
<div id="cookie-message" style="display: none;">
    [[++private_policy_text]]
</div>
HTML,
], '', true, true);

$chunks[] = $chunk;
return $chunks;