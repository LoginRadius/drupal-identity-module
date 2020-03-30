<script>
    jQuery(document).ready(function () {
      initializePhoneUpdate("<?php echo $phone_id?>");
    });
  </script>

 
  <div class="ciam-lr-form my-form-wrapper">
  <?php if($phone_id != '--' && $phone_id != '') {?>
      <div id="lr_phoneid" style="display: none;"><span class="lr_phone_no_label">Phone Number</span><span class="lr_user_phoneno"><?php echo $phone_id;?></span></div>
  <?php }?>
      <div id="updatephone-container" style="display: none;"></div>
  </div>
