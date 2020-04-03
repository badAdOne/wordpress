<?php
/**
* @package badAd
*/
// Keys
$badad_status = get_option('badad_testlive');
$badad_live_pub = get_option('badad_live_pub');
$badad_live_sec = get_option('badad_live_sec');
$badad_test_pub = get_option('badad_test_pub');
$badad_test_sec = get_option('badad_test_sec');
$badad_call_key = get_option('badad_call_key');
$badad_siteslug = get_option('badad_siteslug');
if (($badad_live_pub == '') || ($badad_live_pub == null) || (!isset($badad_live_pub)) || (strpos($badad_live_pub, 'live_pub_') === false)
 || ($badad_live_sec == '') || ($badad_live_sec == null) || (!isset($badad_live_sec)) || (strpos($badad_live_sec, 'live_sec_') === false)
 || ($badad_test_pub == '') || ($badad_test_pub == null) || (!isset($badad_test_pub)) || (strpos($badad_test_pub, 'test_pub_') === false)
 || ($badad_test_sec == '') || ($badad_test_sec == null) || (!isset($badad_test_sec)) || (strpos($badad_test_sec, 'test_sec_') === false)) {
   $badad_plugin = 'notset';
 } else {
   $badad_plugin = 'set';
 }
if (($badad_call_key == 'delete') && ($badad_siteslug == 'delete')) {
  // Delete connection.php
  unlink(plugin_dir_path( __FILE__ ).'connection.php');
  // Reset the options
  update_option('badad_call_key', '');
  update_option('badad_siteslug', '');
  // Set our variables
  $badad_call_key = null;
  $badad_siteslug = null;
  $badad_connection = 'notset';
} elseif (($badad_call_key == '') || ($badad_call_key == null) || (!isset($badad_call_key)) || (strpos($badad_call_key, 'call_key_') === false)
 || ($badad_siteslug == '') || ($badad_siteslug == null) || (!isset($badad_siteslug)) || (!preg_match('/[A-Za-z]/', $badad_siteslug))) {
  $badad_connection = 'notset';
} else {
  $badad_connection = 'set';
}

if ($badad_status == 'live') {
  $write_dev_pub_key = $badad_live_pub;
  $write_dev_sec_key = $badad_live_sec;
} elseif ($badad_status == 'test') {
  $write_dev_pub_key = $badad_test_pub;
  $write_dev_sec_key = $badad_test_sec;
}
// Access
$badad_access = get_option('badad_access');
if ( $badad_access == 'admin' ) {
  $badAd_drole = 'administrator';
  $badAd_arole = 'administrator';
} elseif ( $badad_access == 'admineditor' ) {
  $badAd_drole = 'administrator';
  $badAd_arole = 'editor';
} elseif ( $badad_access == 'editor' ) {
  $badAd_drole = 'editor';
  $badAd_arole = 'editor';
}
// Set these per use (not standard style, but poetically brief)
if ($badAd_drole == 'administrator') {$badAd_dlevel = 'activate_plugins';}
elseif ($badAd_drole == 'editor') {$badAd_dlevel = 'edit_others_posts';}
if ($badAd_arole == 'administrator') {$badAd_alevel = 'activate_plugins';}
elseif ($badAd_arole == 'editor') {$badAd_alevel = 'edit_others_posts';}

/* Note to developers and WordPress.org reviewers

- For speed, keys for regular calls to the badAd API should utilize include(), rather than SQL queries
- The variable values for these files are stored in wp_options via the WordPress API; upon viewing the plugin dashboard, the plugin renders these files if the files are missing
- These four files are created when adding keys:
  - callback.php (created automatically by the badAd settings dashboard [this file, settings.php] after adding Dev Keys, used to talk to our API)
  - devkeys.php  (created automatically by the badAd settings dashboard from settings stored using the WP native settings-database calls)
  - connection.php (created when a user authorizes an API connection, used to store related connection "call" keys, these keys are added to the database from the file the first time it is created upon auto-redirect to the badAd settings dashboard)
- Only devkeys.php and connection.php serve as our framework, having variables developers need to build on for plugins and themes dependent on this plugin:
- What the framework files look like:
  - devkeys.php:
    ```
    <?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $my_developer_pub_key = 'some_pub_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0abcd';
    $my_developer_sec_key = 'some_sec_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0abcd';
    ```
  - connection.php:
    ```
    <?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $partner_call_key = 'some_pub_0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0abcd';
    $partner_resiteSLUG = '0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdfghijklmnopqruvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghij';
    ```

*/

// Write our include files //
// Initiate $wp_filesystem
global $wp_filesystem;
if (empty($wp_filesystem)) {
  require_once (ABSPATH . '/wp-admin/includes/file.php');
  WP_Filesystem();
  WP_Filesystem_Direct();
}
// Write callback.php
$callbackFile = plugin_dir_path( __FILE__ ).'callback.php';
$connectionKeyFile = plugin_dir_path( __FILE__ ).'connection.php';
$connectionDelFile = plugin_dir_path( __FILE__ ).'disconnect.php';
$badadSettingsPage = admin_url( 'options-general.php?page=badad-settings' );
if (( ! $wp_filesystem->exists($connectionKeyFile) )
  || (( $badad_connection == 'set' ) && ( ! strpos ( $wp_filesystem->get_contents($connectionKeyFile), $badad_call_key ) === true ))
  || (( $badad_connection == 'set' ) && ( ! strpos ( $wp_filesystem->get_contents($connectionKeyFile), $badad_siteslug ) === true ))) {
  $badad_connection_file = false;
} else {
  $badad_connection_file = true;
}
if ( ( ! $wp_filesystem->exists($callbackFile)) || ( ($wp_filesystem->exists($callbackFile) ) && ( $badad_plugin == 'set' ) && ( strpos ( $wp_filesystem->get_contents($callbackFile), $write_dev_pub_key ) === false ) ) ) {
  $callbackContentsPHP = <<<'EOP'
<?php
if ((isset($_POST['badad_connect_response']))
&& (isset($_POST['partner_app_key']))
&& (isset($_POST['partner_call_key']))
&& (isset($_POST['partner_refcred']))
&& (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['partner_app_key']))
&& (preg_match ('/[a-zA-Z0-9_]$/i', $_POST['partner_call_key']))
&& (preg_match ('/^call_key_(.*)/i', $_POST['partner_call_key']))
&& (preg_match ('/[a-zA-Z0-9]$/i', $_POST['partner_refcred']))) { // _POST all present and mild regex check
$partner_call_key = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['partner_call_key'] ); // Starts with: "call_key_" Keep this in your database for future API calls with this connected partner, it starts with: "call_key_"
$partner_refcred = preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['partner_refcred'] ); // The "resite.html" URL, acting as BOTH a badAd click for Partner shares AND as a referral link for ad credits uppon purchase of a new customer
$connectionKeyContents = <<<EKK
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
\$partner_call_key = '$partner_call_key';
\$partner_resiteSLUG = '$partner_refcred';
EKK;
EOP;
  $connectionKeyFileContents = <<<EOK
file_put_contents('$connectionKeyFile', \$connectionKeyContents);
header("Location: $badadSettingsPage");
exit();
}
?>
EOK;
  $callbackContentsHTML = <<<EOH
<!DOCTYPE html>
<html>
<head>
<meta name="badad.api.dev.key" content="$write_dev_pub_key" />
</head>
<body>
No script kiddies.
</body>
</html>
EOH;

  $callbackContents = $callbackContentsPHP."\n".$connectionKeyFileContents."\n".$callbackContentsHTML;
  $wp_filesystem->put_contents( $callbackFile, $callbackContents, FS_CHMOD_FILE ); // predefined mode settings for WP files
}
// end callback.php

// Check connection.php
if ((( ! $wp_filesystem->exists($connectionKeyFile) ) && ( $badad_connection == 'set' ))
   || (( $wp_filesystem->exists($connectionKeyFile) ) && ( $badad_connection == 'set' )
      && (( strpos ( $wp_filesystem->get_contents($connectionKeyFile), $badad_call_key ) === false )
       || ( strpos ( $wp_filesystem->get_contents($connectionKeyFile), $badad_siteslug ) === false )))) {

  // Write connection.php
  $connectionKeys = <<<CONN
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
\$partner_call_key = '$badad_call_key';
\$partner_resiteSLUG = '$badad_siteslug';
CONN;
  $wp_filesystem->put_contents( $connectionKeyFile, $connectionKeys, FS_CHMOD_FILE ); // predefined mode settings for WP files
  include $connectionKeyFile; // Make sure we get our variable one way or another

} elseif (( $wp_filesystem->exists($connectionKeyFile) ) && ( $badad_connection == 'notset' )) {

  // Enter the call_key into the WP settings database
  include $connectionKeyFile; // Make sure we get our variable one way or another
  update_option('badad_call_key', $partner_call_key);
  update_option('badad_siteslug', $partner_resiteSLUG);
}

// Write devkeys.php
if ( $badad_status == 'live' ) {
  $devKeysContents = <<< EDK
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
\$my_developer_pub_key = '$badad_live_pub';
\$my_developer_sec_key = '$badad_live_sec';
EDK;
} elseif ( $badad_status == 'test' ) {
  $devKeysContents = <<< EDK
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
\$my_developer_pub_key = '$badad_test_pub';
\$my_developer_sec_key = '$badad_test_sec';
EDK;
}
$devKeysFile = plugin_dir_path( __FILE__ ).'devkeys.php'; // a better way
$wp_filesystem->put_contents( $devKeysFile, $devKeysContents, FS_CHMOD_FILE ); // predefined mode settings for WP files
// end devkeys.php

// Fetch the settings from the files we just made
extract(badad_keys());
 ?>

<div class="wrap">
  <h1>badAd</h1>

<?php
// Check for a writable plugin directory
$path = plugin_dir_path( __FILE__ );
if (!wp_is_writable($path)) {
  echo "<h2>Your 'badad' plugin folder is not writable on the server!</h2>
  <p>If you are using Apache, you might need to run:</p>
  <pre>sudo chown -R www-data:www-data $path</pre>
  <p>We can't do anymore until this gets fixed.</p>";
  exit();
}

// Check keys
if ( ( current_user_can($badAd_dlevel) ) && ( $badad_plugin == 'notset' ) ) {
  // add Dev keys
  $callbackURL = plugin_dir_url('badad').'badad/callback.php'; // a better way
  echo '<h2>Add your badAd Developer API keys to get started!</h2>
  <p>These keys can be found or created in your badAd.one <i>Partner Center > Developer Center</i>. For help or to create an account, see the <a target="_blank" href="https://badad.one/444/site.html">help videos here</a>.</p>
  <p><pre>Dev Callback URL: <b>'.$callbackURL.'</b> <i>(for badAd Developer Center: Dev App settings)</i></pre></p>
  <form method="post" action="options.php">';
    settings_fields( 'devkeys' );
    echo '<h4>Keys</h4>
    <label for="badad_live_pub">Live Public Key:</label>
    <input name="badad_live_pub" type="text" style="width: 100%" ><br>
    <label for="badad_live_sec">Live Secret Key:</label>
    <input name="badad_live_sec" type="text" style="width: 100%" ><br>
    <label for="badad_test_pub">Test Public Key:</label>
    <input name="badad_test_pub" type="text" style="width: 100%" ><br>
    <label for="test_sec_key">Test Secret Key:</label>
    <input name="badad_test_sec" type="text" style="width: 100%" ><br>
    <br>
    <input type="checkbox" name="double_check_key_update" value="certain" required>
    <label for="double_check_delete"> I am sure I want to update the keys.</label>
    <input class="button button-secondary" type="submit" value="Update all keys as shown">
  </form>
  <br><hr>
  <h2>Need help?</h2>
  <p><a target="_blank" href="https://badad.one/help_videos.php">Learn more</a> or sign up to start monetizing today!</p>
  <p>You must be registered, have purchased one (ridiculously cheap) ad, and confirmed your email to be a <a target="_blank" href="https://badad.one/444/site.html">badAd.one</a> Dev Partner. It could take as little as $1 and 10 minutes to be up and running! <a target="_blank" href="https://badad.one/444/site.html">Learn more</a>.</p>
  <p><iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/VBTAknEAACKJ/"></iframe></p>';

} elseif ( ( current_user_can($badAd_alevel) ) && ( $badad_connection_file == false ) && ( $badad_connection == 'notset' ) ) {
  // Forms to connect

  // User app_key
  echo '
  <form id="connect_partner_app_id" class="connect_partner" action="https://badad.one/connect_app.php" method="post" accept-charset="utf-8">
  <p><b>Connect with a Partner App Key</b></p>

  <!-- DEV NEEDS THIS -->
  <input type="hidden" name="dev_key" value="'.$my_developer_sec_key.'" />

  <label for="partner_app_key">Your Partner App Key:</label>
  <br /><br />

  <!-- DEV NEEDS THIS: name="partner_app_key" -->
  <input type="text" name="partner_app_key" id="partner_app_key" size="32" required />

  <input class="button button-primary" type="submit" value="Connect" class="formbutton" />
  <br />
  </form>';

  // Be pretty
  echo "<br /><hr /><br />";

  // User login
  echo '
  <form id="connect_partner_app_id" class="connect_partner" action="https://badad.one/connect_app.php" method="post" accept-charset="utf-8">
  <p><b>Connect by login</b></p>

  <!-- DEV NEEDS THIS -->
  <input type="hidden" name="dev_key" value="'.$my_developer_sec_key.'" />

  <input class="button button-primary" type="submit" value="Login to Connect..." class="formbutton" />
  <br />
  </form>
  <br><hr>
  <h2>Need help?</h2>
  <p><a target="_blank" href="https://badad.one/help_videos.php">Learn more</a> or sign up to start monetizing today!</p>
  <p>You must be registered, have purchased one (ridiculously cheap) ad, and confirmed your email to be a <a target="_blank" href="https://badad.one/444/site.html">badAd.one</a> Partner. It could take as little as $1 and 10 minutes to be up and running! <a target="_blank" href="https://badad.one/444/site.html">Learn more</a>.</p>
  <p><iframe width="640" height="360" scrolling="no" frameborder="0" style="border: none;" src="https://www.bitchute.com/embed/mZSpkFWnCbxo/"></iframe></p>';

  // Be pretty
    echo "<br /><hr /><br />";

} elseif ( current_user_can('edit_posts') ) {
  // All Contributors
  // Shortcode help
  echo "<h2>Shortcodes:</h2>";
  echo "<h3><pre>[badad]</pre></h3>";
  echo "<p><pre><i>Retrieve ads from badAd, share count</i></pre></p>";
  echo "<p><pre><b>[badad num=2 balink=no valign=no hit=no]</b> <i>(Defaults, two ads side-by-side)</i></pre></p>";
  echo "<p><pre> <b>num=</b> <i>Number 1-20: how many ads to show (1 share per ad)</i></pre></p>";
  echo "<p><pre> <b>balink=</b> <i>yes/no: Count-shares-if-clicked referral link, text only (share count of 1 ad)</i></pre></p>";
  echo "<p><pre> <b>valign=</b> <i>yes/no: Align ads vertically? (no effect on share count)</i></pre></p>";
  echo "<p><pre> <b>hit=</b> <i>yes/no: Count as \"hit\" in Project Stats? (no effect on share count)</i><br><i> Tip: Set exactly ONE [badad] shortcode to 'hit=true' per page for accurate Stats</i></pre></p>";
  echo "<br>";
  echo "<h3><pre>[badadrefer]</pre></h3>";
  echo "<p><pre><i>Count-shares-if-clicked referral link, no view share or hit count (loads fast)</i></pre></p>";
  echo "<p><pre><b>[badadrefer type=refer]</b> <i>Text: <b>Claim your ad credit...</b> (Default)</i></pre></p>";
  echo "<p><pre> <b>type=domain</b> <i>Text: <b>badAd.one</b></i></pre></p>";
  echo "<p><pre> <b>type=pic</b> <i>Shows a small banner-ad that cycles badAd logos and slogans (may change when plugin is updated)</i></pre></p>";
  echo '<br><p><i>Watch the <a target="_blank" href="https://www.bitchute.com/video/BkIMAjWX4jii/">help video on badAd-WordPress shortcodes</a></i></p>';
  echo "<hr>";

}

// Plugin Settings
if ( current_user_can($badAd_alevel) ) {
  echo "<h2>Connection Status:</h2>";

  // App Connection
  if (( $badad_connection == 'set' ) && ( $badad_connection_file == true )) {
    echo "<p><i><b>Connected to App Project:</b></i></p>";
    extract(badad_meta()); // use extract because we will use the response variable later
  } elseif ((( $badad_connection == 'notset' ) && ( $badad_connection_file == true  ))
    ||      (( $badad_connection == 'set'    ) && ( $badad_connection_file == false ))) {
    echo "<p><i>Connection just established. Reload this page to see your app connection status.<br></i></p>";
  } elseif  (( $badad_connection == 'notset' ) && ( $badad_connection_file == false )) {
  echo "<p><b>Use the form above to connect.</b></p>";
  }

  echo "<hr>";

  // Dev keys & callback
  if ( current_user_can($badAd_dlevel) ) {
    // Important info
    echo "<h2>Reference:</h2>";
    $callbackURL = plugin_dir_url('badad').'badad/callback.php'; // a better way
    echo "<p><pre>WP Plugin Status: <b>$badad_status</b></pre></p>";
    echo "<p><pre>Dev Callback URL: <b>$callbackURL</b> <i>(for badAd Developer Center: Dev App settings)</i></pre></p>";
    echo "<p><pre>Current Public Key: <b>$my_developer_pub_key</b></pre></p>";
    echo "<hr>" ;
  }
}

// Settings
if ( current_user_can($badAd_alevel) ) {
  echo "<h3>Danger Zone: Make changes</h3>";

  // Change Dev keys/status
  if ( current_user_can($badAd_dlevel) ) {
    echo '
    <button class="button button-primary" onclick="showDevKeysStatus()">Dev keys & status <b>&darr;&darr;&darr;</b></button>
    <div id="devKeysStatus" style="display:none">

    <!-- Status radio form -->
    <form method="post" action="options.php">';
      settings_fields( 'status' );
      echo '<h4>Status</h4>
      <input type="radio" name="badad_testlive" value="live"';
      checked('live', $badad_status, true);
      echo '> Live<br>

      <input type="radio" name="badad_testlive" value="test"';
      checked('test', $badad_status, true);
      echo '> Test<br>
      <br>

      <input class="button button-secondary" type="submit" value="Save status">
    </form>
    <br><br>

    <!-- Update keys form -->
    <form method="post" action="options.php">';
      settings_fields( 'devkeys' );
      echo '<h4>Keys</h4>
      <label for="badad_live_pub">Live Public Key:</label>
      <input name="badad_live_pub" type="text" style="width: 100%" value="';
      echo $badad_live_pub;
      echo '" ><br>
      <label for="badad_live_sec">Live Secret Key:</label>
      <input name="badad_live_sec" type="text" style="width: 100%" value="';
      echo $badad_live_sec;
      echo '" ><br>
      <label for="badad_test_pub">Test Public Key:</label>
      <input name="badad_test_pub" type="text" style="width: 100%" value="';
      echo $badad_test_pub;
      echo '" ><br>
      <label for="test_sec_key">Test Secret Key:</label>
      <input name="badad_test_sec" type="text" style="width: 100%" value="';
      echo $badad_test_sec;
      echo '" ><br>
      <br>
      <input type="checkbox" name="double_check_key_update" value="certain" required>
      <label for="double_check_delete"> I am sure I want to update the keys.</label>
      <input class="button button-secondary" type="submit" value="Update all keys as shown">
    </form>
    <p>You can update these keys from the same Dev App and it will not disconnect your ads.</p>
    <hr>
    </div>
    <script>
    function showDevKeysStatus() {
      var x = document.getElementById("devKeysStatus");
      if (x.style.display === "block") {
        x.style.display = "none";
      } else {
        x.style.display = "block";
      }
    }
    </script>
    <br><br>
    ';
  }

  // Delete App Call keys
  if (( current_user_can($badAd_alevel) ) && ( isset($connection_meta_response) )) {
    echo '
    <button class="button button-primary" onclick="showAppConnection()">App connection <b>&darr;&darr;&darr;</b></button>
    <div id="appConnection" style="display:none">
    <h4>Delete current App connection?</h4>
    <p><i>Currently connected to badAd App Project:<br>'.$connection_meta_response.'</i></p>
    <form method="post" action="options.php">';
    settings_fields( 'connection' );
    echo '<input type="hidden" name="badad_call_key" value="delete">
    <input type="hidden" name="badad_siteslug" value="delete">
    <input type="checkbox" name="double_check_delete" value="certain" required>
    <label for="double_check_delete"> I am sure I want to delete this connection.</label>
    <input class="button button-secondary" type="submit" value="Disconnect and delete forever!">
    </form>
    <br>
    <hr>
    </div>
    <script>
    function showAppConnection() {
      var x = document.getElementById("appConnection");
      if (x.style.display === "block") {
        x.style.display = "none";
      } else {
        x.style.display = "block";
      }
    }
    </script>
    <br><br>
    ';
  }
}
// Who can change plugin keys and connection
if ( current_user_can('update_plugins') ) { // Only admins or super admins
  // js button "User level settings..."
  // Radio options: Administrator for all; Administrator for Dev keys, Editor for App connection; Editor for all
  echo '
  <button class="button button-primary" onclick="showPluginAccess()">Plugin access <b>&darr;&darr;&darr;</b></button>
  <div id="pluginAccess" style="display:none">
    <form method="post" action="options.php">';
      settings_fields( 'access' );
      echo '<h4>Who can change Dev keys and App connection?</h4>
      <input type="radio" name="badad_access" value="admin"';
      checked('admin', $badad_access, true);
      echo '> Administrator for all<br>
      <input type="radio" name="badad_access" value="admineditor"';
      checked('admineditor', $badad_access, true);
      echo '> Administrator for Dev keys, Editor for App connection
      <br>
      <input type="radio" name="badad_access" value="editor"';
      checked('editor', $badad_access, true);
      echo '> Editor for all<br>
      <br>
      <br><br>
      <input class="button button-secondary" type="submit" value="Save">
    </form>
    <br><hr>
  </div>
  <script>
  function showPluginAccess() {
    var x = document.getElementById("pluginAccess");
    if (x.style.display === "block") {
      x.style.display = "none";
    } else {
      x.style.display = "block";
    }
  }
  </script>
  ';
}

?>
</div>
