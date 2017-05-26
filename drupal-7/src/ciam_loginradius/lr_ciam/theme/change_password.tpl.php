<?php

/**
 * @file
 * Change Password Template file.
 */
$ciam_api_key = trim(variable_get('lr_ciam_apikey'));
if (!empty($ciam_api_key)):
  ?>
  <script>
    jQuery(document).ready(function () {
      initializeChangePasswordCiamForms();
    });
  </script>
  <div class="messages" style="display:none">
    <h2 class="element-invisible">Error message</h2>
    <ul>
      <li class="messageinfo">

      </li>
      <div class="clear"></div>
    </ul>
  </div>
  <div class="my-form-wrapper">
    <div id="changepassword-container"></div>  
  </div>
<?php
endif;
