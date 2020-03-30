<?php
drupal_add_css((drupal_get_path('module', 'lr_ciam') .'/css/lr_loading.min.css'));
$loading_image = $GLOBALS['base_url'] . '/' . drupal_get_path('module', 'lr_ciam') . '/images/loading-white.png';
?>
<div class="overlay" id="lr-loading" style="display: none;">
  <div class="circle">
    <div id="imganimation">
        <img src="<?php print $loading_image; ?>" alt="LoginRadius Processing" class="lr_loading_screen_spinner">
    </div>
  </div>
  <div></div>
</div>
