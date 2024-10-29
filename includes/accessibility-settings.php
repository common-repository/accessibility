<?php
/* 
 * The accessibility plugin settings page.
 */
?>
<div class="wrap">
        <h2><?php _e('Accessibility Options', $this->plugin_slug) ?></h2>

      <?php 
      $magixiteLicense = get_option('oc-accessibility', "");
      $license_data = $this->_get_license_data($magixiteLicense);
      ?>
        <form class="oc-accessibilty-form" method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
          <h2 class="oc-accessibilty-main-title"><?php _e('General Options', $this->plugin_slug) ?></h2>
          <input type="hidden" name="action" value="save_accessibility_settings">
          <a class="oc-accessibilty-tabletop" href="<?php echo esc_attr(OCACCESSIBILITY_PLUGINURL); ?>" alt="MagiXite" title="MagiXite"><img src="<?php echo esc_attr(OCACCESSIBILITY_ASSETS_URL); ?>/images/logo.png" /></a>
          <table class="form-table oc-accessibilty-style">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('Magixite License', $this->plugin_slug); ?></th>
                <td>
                  <input type="text" name="magixite_license" placeholder="<?php _e('Paste your license key', $this->plugin_slug) ?>" value="<?php echo esc_html($magixiteLicense); ?>" />
      <?php if ($magixiteLicense == ""): ?>
                    <p>
                        * Using FREE community license, obtain personal license at <a href="https://acc.magixite.com" target="_blank">https://acc.magixite.com</a>
                    </p>
      <?php else: ?>
                    <p><?php echo $this->_get_license_message($license_data); ?></p>
      <?php endif; ?>
                </td>
              </tr>

              <tr valign="top">
                <th scope="row"><?php _e('Select Preferred Language', $this->plugin_slug); ?></th>
      <?php $language_options = $this->get_language_options(); ?>
                <td>
                  <select name="ac_language">
                  <?php foreach ($language_options as $key => $l): ?>
                      <option <?php
                    if ($key == get_option('oc-accessibility-language', "")): echo "selected=\"selected\"";
                    endif;
                    ?> value="<?php echo esc_attr($key); ?>"><?php echo esc_html($l->name); ?></option>
      <?php endforeach; ?>
                  </select>
                  <p>* <strong>Auto Detect</strong> - <?php _e('Automatically choose language by user browser language setting.', $this->plugin_slug) ?></p>
                </td>
              </tr>

              <tr class="creditRow"><th colspan="2">Brought To You By <a href="<?php echo esc_attr(OCACCESSIBILITY_PLUGINURL); ?>" alt="MagiXite"><img src="<?php echo OCACCESSIBILITY_ASSETS_URL; ?>/images/text_logo.png" /></a></th></tr>
            </tbody>
          </table>
          <input name="form_nonce" type="hidden" value="<?=wp_create_nonce('oc-accessibility')?>" />
          <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes', $this->plugin_slug) ?>" />
          </p>

        </form>
      </div>