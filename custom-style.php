<?php
/*
Plugin Name: Custom Style
Plugin URI: http://workbench.haefelinger.it
Description: Add CSS style rules to customize current theme to your liking.
Version: 1.0
Author: Wolfgang Häfelinger
Author URI: http://www.haefelinger.it
*/

// Add a function to the admin_menu hook.
add_action('admin_menu', 'custom_style_menu');

// Add an action to the wp_head hook.
add_action('wp_head', 'custom_style_wp_head', 100);


// This functions returns the (unique) key used to store this plugin's option.
function custom_style_key() {
  return 'custom_style_key_wp_head';
}


// This function queries the WP database for my value.
function custom_style_query() {
  return get_option(custom_style_key());
}

// This function saves given text (value) in my WP's database.
function custom_style_update($text) {
  $text = stripslashes($text);
  update_option(custom_style_key(),$text);
}


function mycallback ($match) {
  $key = $match[1];
  $val = get_bloginfo($key);
  return $val;
}

// This is the function hooked into WP's wp_head trigger. The main goal of this 
// function is to render the admin's custom style into a <style /> element. We
// also take care on substituting {bloginfo:keyword} elements.
function custom_style_wp_head() {
  $text = custom_style_query();
  $text = preg_replace_callback(
                                '/{\s*bloginfo:([^\s}]*)\s*}/',
                                mycallback,
                                $text
                                );
  echo "\n";
	echo '<style type="text/css">';	
  echo "\n";
  echo $text;
  echo "\n";
	echo '</style>';
}


// This function is plugged into the 'admin_menu' hook. Essentially it will allow the admin (
// or any other user with enough permissions) to enter text into a textfield which will be 
// saved as an option in the WP database. That text is then rendered whenever the wp_head hook
// gets triggered.
function custom_style_menu() {
  // page_title, menu_title, capability?, file?, function
  // switch_themes is the capability the current user must posses in order to carry
  // out this plugin. Here we require that user must have permission to change the
  // theme. That's a pretty advanced capability ususally only the site admin posesses.
  // No clue why __FILE__ needs to be taken here.
	add_theme_page(
                 'Custom Style Options', 
                 'Custom Style', 
                 'switch_themes', 
                 __FILE__, 
                 'custom_style_options'
                 );
}

// This function is represents a option in the blog's dashboard. It does two things, namely it
// renders a textfield which allows a dashboard user to enter CSS rules on the client side. On
// the server side, it receives the user input and stores it persisentantly in the database.
function custom_style_options() {
  // just a status flag ..
	$updated = false;
  
  // handle a HTTP post action ..
  if( $_POST['action'] == 'submit' ) {
		$val = $_POST[ 'customstyle' ];
		custom_style_update($val);
		$updated = true;
	}

  // Fetch current value of my option from database. Do this in any case, so users will see
  // the persistent value.
  $val = custom_style_query();

  if ($updated) {
    echo sprintf("<div class='updated'><p><strong>%s</strong></p></div>",
                 __('Options saved.', 'mt_trans_domain' ));
  }

  echo "<div class='wrap'>";
  echo "<h2>Custom Style</h2>";
  echo sprintf("<form method='post' action='%s'>",$_SERVER['REQUEST_URI']);

  // wp_nonce_field:
  // -> http://codex.wordpress.org/Function_Reference/wp_nonce_field
  // The nonce field is used to validate that the contents of the form came
  // from the location on the current site and not somewhere else. The nonce 
  // does not offer absolute protection, but should protect against most cases. 
  // It is very important to use nonce field in forms.
  wp_nonce_field('update-options');

  /* hidden HTTP post options */
  //echo "<input type='hidden' name='action' value='update' />";

  /* User input */
  echo "<table class='form-table'>";
  echo "<tr valign='top'>";
  echo "<td>";
  echo sprintf("<textarea cols='80' rows='20' name='customstyle'>%s</textarea>",$val);
  echo "</td>";
  echo "<td><hr /><p>The text you add here to my left will be included as <code>&lt;style>..&lt;/style></code> into your HTML's header for each and every page. It allows you to modify the style of your current theme without touching any of your theme's CSS files.</p><p>Blog related information is available using the syntax <code>{<a href='http://codex.wordpress.org/Function_Reference/get_bloginfo'>bloginfo</a>:<em>keyword</em>}</code>.This information becomes particular useful when refering to images in your theme as shown in the following example:<pre>#header {\n  background-image: url( {<a href='http://codex.wordpress.org/Function_Reference/get_bloginfo'>bloginfo</a>:template_url}/images/bg.jpg );\n}</pre></p>";

  /* submit button */
  echo "<hr />";
  echo "<button name='action' value='submit' type='submit' >Save</button>";
  echo "<td>";
  echo "</tr>";
  echo "</table>";
  
  echo "</form>";
?>
<?php
  echo "</div>";
}

?>
