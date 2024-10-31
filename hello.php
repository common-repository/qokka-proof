<?php
/**
 * @package Credible
 * @version 0.1.3
 */
/*
Plugin Name: Credible
Plugin URI: http://usecredible.com/
Description: Customers are always in doubt when they visit new businesses online. Customer reviews and actions from other customers, such as recent purchases, are the two most effective ways to address these doubts. Credible is a tool that enables these businesses to collect and present personalized customer reviews and actions as social proof to get more sales, collect more reviews, and become more credible. Customers can easily install Credible on Shopify, WordPress, or custom websites in just 1 minute. See https://usecredible.com for more features and offerings.
Author: Qokka Inc
Version: 0.1.3
Author URI: http://qokka.ai/
*/
$credible_api_root = 'https://usecredible.com';
function credible_init()
{
  wp_register_script('credible-widget', plugins_url('/widget.js', __FILE__), array());
  wp_enqueue_script('credible-widget');
  wp_localize_script('credible-widget', 'credible_settings', array(
    'apiKey' => get_option('credible_api_key')
  ));
}


function credible_add_settings_section_callback()
{
  echo '';
}

function credible_add_settings_field_callback()
{
  $val = get_option('credible_api_key');
  echo "<input id='credible_api_key' name='credible_api_key' value='$val' type='text'/>";
  echo '<p>If you already have a key, you can set it up directly here. For more information, please visit <a href="https://usecredible.com/dashboard" target="_blank">Credible dashboard</a>. If you need help, please contact <a href="mailto:credible@qokka.ai" target="_blank">Credible team</a>.</p>';
  echo '<p>More features and configurations are coming soon! We will update you by email.</p>';
}

function credible_activate_signup_callback()
{
  ?>
  <script>
    jQuery(document).ready(function () {
      jQuery('#credible-signup-section').show()
      jQuery('#credible-login-section').hide()
    })
  </script>
  <?php
}

function credible_activate_login_callback()
{
  ?>
  <script>
    jQuery(document).ready(function () {
      jQuery('#credible-signup-section').hide()
      jQuery('#credible-login-section').show()
    })
  </script>
  <?php
}


function credible_signup_form_callback()
{
  global $credible_api_root;
  $admin_email = get_option('admin_email');
  $site_url = get_site_url();
//  $site_url = (empty($_site_url) ? '' : parse_url($_site_url)['host']);
  $name = get_bloginfo('name');
  echo "<div id='credible-signup-section'>"
    . "<h3>Sign up for Credible account</h3>"
    . "<form action='#'><table><tbody>"
    . "<tr><td><label>Email</label></td><td><input id='credible-signup-email' name='email'  value='$admin_email' type='text'></td></tr>"
    . "<tr><td><label>Password</label></td><td><input id='credible-signup-password' name='password'  type='password'></td></tr>"
    . "<tr><td><label>Confirm Password</label></td><td><input id='credible-signup-password2' name='confirm-password'  type='password'></td></tr>"
    . "<tr><td><label>Use default configurations</label></td><td><input id='credible-signup-use-advanced-config' name='advanced'  type='checkbox' checked></td></tr>"
    . "<tr class='advanced-config'><td><label>Name of new project</label></td><td><input id='credible-signup-site-name' name='name'  value='$name' type='text'><span><small>(how your website's name should appear in social proofs)</small></span></td></tr>"
    . "<tr class='advanced-config'><td><label>URL of new project</label></td><td><input id='credible-signup-site-url' name='url'  value='$site_url' type='text'><span><small>(the domain name of the site, to help you manage configurations)</small></span></td></tr>"
    . "</tbody></table><input type='submit'  id='credible-signup-submit' name='submit' class='button button-primary' value='Sign up'></form>"
    . "<div>Already have an account? <a href='#' id='credible-signup-login'>Back to Login</a></div>"
    . "<p id='credible-signup-response'></p>"
    . "</div>";
  ?>
  <script>
    (function () {
      var checkbox = jQuery('#credible-signup-use-advanced-config')
      checkbox.on('change', function () {
        if (checkbox.is(':checked')) {
          jQuery('#credible-signup-section .advanced-config').hide()
        } else {
          jQuery('#credible-signup-section .advanced-config').show()
        }
      })
      var apiRoot = '<?php echo $credible_api_root ?>'
      var submitButton = jQuery('#credible-signup-submit')
      var response = jQuery('#credible-signup-response')
      submitButton.click(function () {
        if (jQuery('#credible-signup-password').val() !== jQuery('#credible-signup-password2').val()) {
          response.text('Passwords are not the same. Please check again.')
          return false
        }
        jQuery.post({
          url: apiRoot + '/auth/signup',
          data: {
            email: jQuery('#credible-signup-email').val().trim(),
            password: jQuery('#credible-signup-password').val(),
            name: jQuery('#credible-signup-site-name').val().trim(),
            url: jQuery('#credible-signup-site-url').val().trim(),
            genBy: 'wordpress'
          },
          json: true
        }).done(function (res) {
          if (res.apiKey) {
            response.text('Account and project created. Saving project key...')
            jQuery('#credible_api_key').val(res.apiKey)
            jQuery('#credible-save-settings').trigger('click')
          } else {
            response.text('Account created, but creating new project failed. Please contact team@qokka.ai')
          }
        }).fail(function (err) {
          console.log(err)
          var error = (err.responseJSON && err.responseJSON.message) || err.responseText
          response.text('Login failed. Error: ' + error)
        })
        return false
      })
      jQuery('#credible-signup-login').click(function () {
        jQuery('#credible-signup-section').hide()
        jQuery('#credible-login-section').show()
      })
    }())
  </script>
  <?php
}

function credible_login_form_callback()
{
  global $credible_api_root;
  $site_url = get_site_url();
//  $site_url = (empty($_site_url) ? '' : parse_url($_site_url)['host']);
  $name = get_bloginfo('name');
  $admin_email = get_option('admin_email');
  echo "<div id='credible-login-section'>"
    . "<h3>Login to Credible, and create a project</h3>"
    . "<form action='#'><table><tbody>"
    . "<tr><td><label>Email</label></td><td><input id='credible-login-email' name='email'  value='$admin_email' type='text'></td></tr>"
    . "<tr><td><label>Password</label></td><td><input id='credible-login-password' name='password'  type='password'></td></tr>"
    . "<tr><td><label>Use default configurations</label></td><td><input id='credible-login-use-advanced-config' name='advanced'  type='checkbox' checked></td></tr>"
    . "<tr class='advanced-config'><td><label>Name of new project</label></td><td><input id='credible-login-site-name' name='name'  value='$name' type='text'><span><small>(how your website's name should appear in pop-ups)</small></span></td></tr>"
    . "<tr class='advanced-config'><td><label>URL of new project</label></td><td><input id='credible-login-site-url' name='url'  value='$site_url' type='text'><span><small>(the domain name of the site, to help you manage configurations)</small></span></td></tr>"
    . "</tbody></table><input type='submit' id='credible-login-submit' name='submit' class='button button-primary' value='Login'></form>"
    . "<div><a href='#' id='credible-login-reset'>Reset password</a></div>"
    . "<div>Don't have an account? <a href='#' id='credible-login-signup'>Signup for FREE</a></div>"
    . "<p id='credible-login-response'></p>"
    . "<div><a href='#' id='credible-login-resend-verify'>Resend email verification</a></div>"
    . "</div>";
  ?>
  <script>
    (function () {
      var checkbox = jQuery('#credible-login-use-advanced-config')
      checkbox.on('change', function () {
        if (checkbox.is(':checked')) {
          jQuery('#credible-login-section .advanced-config').hide()
        } else {
          jQuery('#credible-login-section .advanced-config').show()
        }
      })
      var apiRoot = '<?php echo $credible_api_root ?>'
      var submitButton = jQuery('#credible-login-submit')
      var response = jQuery('#credible-login-response')
      submitButton.click(function () {
        jQuery.post({
          url: apiRoot + '/auth/login',
          data: {
            email: jQuery('#credible-login-email').val().trim(),
            password: jQuery('#credible-login-password').val(),
            name: jQuery('#credible-login-site-name').val().trim(),
            url: jQuery('#credible-login-site-url').val().trim(),
            createProject: true,
            genBy: 'wordpress'
          },
          json: true
        }).done(function (res) {
          if (res.apiKey) {
            response.text('Logged in. Creating new project and retrieving key...')
            jQuery('#credible_api_key').val(res.apiKey)
            jQuery('#credible-save-settings').trigger('click')
          } else {
            response.text('Login success, but creating new project failed. Please contact team@qokka.ai')
          }
        }).fail(function (err) {
          console.log(err)
          var error = (err.responseJSON && err.responseJSON.message) || err.responseText
          response.text('Login failed. Error: ' + error)
          if (error.indexOf('has not verified email') > 0) {
            jQuery('#credible-login-resend-verify').show()
          }
        })
        return false
      })
      jQuery('#credible-login-reset').click(function () {
        jQuery.post({
          url: apiRoot + '/auth/reset',
          data: {
            email: jQuery('#credible-login-email').val()
          },
          json: true
        }).done(function (res) {
          response.text('Please check your email. We just sent you some instructions for you to get a new password. ')
        }).fail(function (err) {
          console.log(err)
          var error = (err.responseJSON && err.responseJSON.message) || err.responseText

          response.text('Reset failed. Error: ' + error)
        })
        return false
      })
      jQuery('#credible-login-resend-verify').click(function () {
        jQuery.post({
          url: apiRoot + '/auth/resend-verify',
          data: {
            email: jQuery('#credible-login-email').val()
          },
          json: true
        }).done(function (res) {
          response.text('✅ Verification email was sent.')
        }).fail(function (err) {
          console.log(err)
          var error = (err.responseJSON && err.responseJSON.message) || err.responseText
          response.text('❌ Failed to send verification email: ' + error)
        })
      })
      jQuery('#credible-login-signup').click(function () {
        jQuery('#credible-signup-section').show()
        jQuery('#credible-login-section').hide()
      })
    }())
  </script>
  <?php

}

function credible_render_welcome()
{
  echo <<<STR
<div class="credible-welcome">
    <h2>👏 Credible activated</h2>
    <p>Thanks for installing Credible. Just one more thing before we are ready to go...</p>
</div>
STR;

}

function credible_settings_page()
{
  global $credible_api_root, $credible_debugging;
  $api_key = get_option('credible_api_key');
  if ($api_key === NULL or $api_key === '') {
    credible_render_welcome();
    $admin_email = get_option('admin_email');
    $available = true;
    if (empty($admin_email)) {
      echo "<p>Your WordPress admin email is empty, so we assume you don't have a Credible account.</p>";
    } else {
      $user_exist_response = wp_remote_get("${credible_api_root}/auth/check-email/${admin_email}", ($credible_debugging ? array('sslverify' => false) : array()));
      if (!is_wp_error($user_exist_response)) {
        $available = json_decode($user_exist_response['body'], true)['available'];
      }
    }
    if (empty($available)) {
      echo "<p>Looks like we already have a Credible account for you (${admin_email}). Please login.</p>";
      credible_activate_login_callback();
    } else {
      echo "<p>We are setting up Credible for the first time, so we need to create an account for you.</p>";
      credible_activate_signup_callback();
    }
    credible_login_form_callback();
    credible_signup_form_callback();

  } else {
    echo '<h2>✅ Credible is ready to go</h2>';
    echo '<p>Show your visitors actions of customers and authentic word-of-mouth. You have full control of what, when, and how the social proofs are presented. </p>';
    echo '<p>To change settings, please use Credible dashboard. </p>';
    echo '<br/><a href="https://usecredible.com/dashboard" target="_blank"><button class="big-button">Go to Credible Dashboard</button></a><br/><br/><br/>';
  }
  ?>
  <br/>
  <form action="options.php" method="post">
    <?php
    settings_fields('credible-settings');
    do_settings_sections('credible-settings');
    submit_button('Save Settings', 'primary', 'submit', true, array('id' => 'credible-save-settings'));
    echo "";
    ?>
    <div id='credible-api-key-check-response'></div>
    <div class="credible-danger-zone">
      <h2>⚠️ Danger Zone</h2>
      <div><a href="#" id="credible-reset">Reset Credible</a> - <span>use this if something is wrong and you want to reconfigure everything. It will reset the Key and let you set up everything again. It won't delete any data you accumulated on Credible. You can still find them in your <a
            href="https://usecredible.com/dashboard" target="_blank">Credible dashboard</a></span></div>
    </div>
    <script>
      (function () {
        var apiRoot = '<?php echo $credible_api_root ?>'
        var saveSettingsButton = jQuery('#credible-save-settings')
        var input = jQuery('#credible_api_key')
        var apiKeyResponse = jQuery('#credible-api-key-check-response')
        input.on('input', function () {
          jQuery.get({
            url: apiRoot + '/keys/check',
            data: {
              apiKey: input.val()
            },
            json: true
          }).done(function (res) {
            if (!res.found) {
              saveSettingsButton.prop('disabled', true)
              return apiKeyResponse.text('❌ Invalid Key').removeClass().addClass('error')
            }
            saveSettingsButton.prop('disabled', false)
            return apiKeyResponse.text('✅ Key is valid, belongs to ' + res.email).removeClass().addClass('ok')
          }).fail(function (err) {
            console.log(err)
            apiKeyResponse.text(err.responseText)
            saveSettingsButton.prop('disabled', false)
          })
        })
        jQuery('#credible-reset').click(function () {
          input.val('')
          saveSettingsButton.trigger('click')
        })
      }())
    </script>
  </form>
  <?php
}

function credible_register_admin_page()
{
  wp_enqueue_script('jquery');
  wp_enqueue_style('credible_admin_styles', plugins_url('/admin.css', __FILE__), array());
  add_menu_page('Credible Settings', 'Credible', 'manage_options', 'credible-settings', 'credible_settings_page', plugins_url('/favicon-small.png', __FILE__));
}

function credible_settings_init()
{
  $args = array('type' => 'string', 'description' => 'The API Key you get from Qokka Team, or your project page at https://usecredible.com/', 'default' => NULL);
  register_setting('credible-settings', 'credible_api_key', $args);

  add_settings_section('credible_general', 'Advanced Settings', 'credible_add_settings_section_callback', 'credible-settings');
  add_settings_field('credible_api_key', 'Key', 'credible_add_settings_field_callback', 'credible-settings', 'credible_general');
}

add_action('admin_menu', 'credible_register_admin_page');
add_action('admin_init', 'credible_settings_init');
add_action('init', 'credible_init');

function credible_activation_redirect($plugin)
{
  if ($plugin == plugin_basename(__FILE__)) {
    exit(wp_redirect(admin_url('admin.php?page=credible-settings')));
  }
}

add_action('activated_plugin', 'credible_activation_redirect');
