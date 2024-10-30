er<?php
/*
Plugin Name: Bing Bot Notifier
Plugin URI: http://wordpress.org/plugins/bing-bot-notifier/
Description: The Bing Bot Notifier Plugin is designed to help website owners know when Bing boot index their site.
Author: Ivan Lopez
Author URI: http://www.macrostudio.com.mx/
Version: 1.0
*/

/**
 * Bing Bot Notifier core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/bing-bot-notifier/
 *
 * @package 		Bing Bot Notifier
 * @copyright		Copyright (c) 2013, Ivan Lopez
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		Bing Bot Notifier 1.0
 */

add_action( 'init', 'thisismyurl_bing_bot_notifier_init_code' );
add_action( 'admin_menu', 'thisismyurl_bing_bot_notifier' );
add_filter( 'plugin_action_links', 'thisismyurl_bing_bot_notifier_plugin_actions', 10, 2 );

register_activation_hook( __FILE__, 'thisismyurl_bing_bot_notifier_activate' );

function thisismyurl_bing_bot_notifier( ) {
  add_options_page( 'Bing Bot Notifier', 'Bing Bot Notifier', 10,'thisismyurl_bing_bot_notifier.php', 'thisismyurl_bing_bot_notifier_options' );
}

function thisismyurl_bing_bot_notifier_activate( ) {

	if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) ) == 0 && strlen( get_option( 'cr_bing_bot_notifier_file' ) ) > 0 )
	  update_option( 'thisismyurl_bing_bot_notifier_log_file_name', get_option( 'cr_bing_bot_notifier_file' ) );

	if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_time' ) ) == 0 && strlen( get_option( 'cr_bing_bot_notifier_time' ) ) > 0 )
	  update_option( 'thisismyurl_bing_bot_notifier_time', get_option( 'cr_bing_bot_notifier_time' ) );

	if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_email' ) ) == 0 && strlen( get_option( 'cr_bing_bot_notifier_email' ) ) > 0 )
	  update_option( 'thisismyurl_bing_bot_notifier_email', get_option( 'cr_bing_bot_notifier_email' ) );

	if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_email' ) ) == 0 )
	  update_option( 'thisismyurl_bing_bot_notifier_email', get_bloginfo( 'admin_email' ) );

	if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) ) == 0 )
	  update_option( 'thisismyurl_bing_bot_notifier_log_file_name', rand( 11111111,99999999 ).'.txt' );

	if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_time' ) ) == 0 )
	  update_option( 'thisismyurl_bing_bot_notifier_time', '3600' );

}

function thisismyurl_bing_bot_notifier_init_code( $options='' ) {

	if( eregi( "bing",$_SERVER['HTTP_USER_AGENT'] ) ||
		$_GET['thisismyurl_bing_bot_notifier_test'] == get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) ||
		strpos( $_SERVER['REQUEST_URI'], get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) ) > 0
	  ) {


		if ( $QUERY_STRING != "" )
			$current_url = "http://".$SERVER_NAME.$PHP_SELF.'?'.$QUERY_STRING;
		else
			$current_url = "http://".$SERVER_NAME.$PHP_SELF;

		$date_today = date( "F j, Y, g:i a" );

		if( eregi( "bing",$_SERVER['HTTP_USER_AGENT'] ) ) {
		  $log_message = thisismyurl_bing_bot_notifier_getPageURL() . ' was requested on ' . date( "F jS, Y - h:i:s A" ) . "\n";
		  $log_message = get_option( 'thisismyurl_bing_bot_notifier_log_items' ) . $log_message;
		  update_option( 'thisismyurl_bing_bot_notifier_log_items', $log_message );
		  unset( $log_message );
		}

		if( $_GET['thisismyurl_bing_bot_notifier_test'] == get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) &&
			strlen( get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) )  > 0 )
		  $show_log = true;

		if( strpos( $_SERVER['REQUEST_URI'], get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) ) > 0
		  )
		  $show_log = true;

		if ( $show_log )
		{
		  echo get_option( 'thisismyurl_bing_bot_notifier_log_items' );
		  die;
		}

		$email = trim( get_option( 'thisismyurl_bing_bot_notifier_email' ) );

		if ( !empty( $email ) ) {

			if ( get_option( 'thisismyurl_bing_bot_notifier_time' ) > 1 ) {

				if ( date( 'U' ) > ( get_option( 'thisismyurl_bing_bot_notifier_time' ) + get_option( 'thisismyurl_bing_bot_notifier_time_last' ) ) ) {

					$log_message = get_option( 'thisismyurl_bing_bot_notifier_log_items' );

					$email = get_option( 'thisismyurl_bing_bot_notifier_email' );
					$message = "Bing bot was detected on ".get_bloginfo( 'url' )."\r\n\r\n";
					$message .= "Bing had crawled the following pages on your website:\r\n\r\n";

					$message .= $log_message."\r\n\r\n";

					$message .= "If you find this plugin helpful, please subscribe to my website!";
					$message .= "\r\n\r\nIvan Lopez\r\nhttp://www.macrostudio.com.mx/";

					$headers = 'From: Bing Bot Notifier <' . $email . ' >' . "\r\n";

					wp_mail( $email, 'Bing Bot detected ( '.get_bloginfo( 'url' ).' )', $message, $headers );

					update_option( 'thisismyurl_bing_bot_notifier_time_last',date( 'U' ) );
					update_option( 'thisismyurl_bing_bot_notifier_log_items', '' );
				}

			} else {

				$email= get_option( 'thisismyurl_bing_bot_notifier_email' );
				$message = 'Bing Bot  detected on ' . get_bloginfo( 'url' ) . '\r\n\r\n';
				$message .= 'just crawl page:  ' . thisismyurl_bing_bot_notifier_getPageURL( ) . '\r\n\r\n';
				$message .= 'If you find this plugin helpful, please visit my site online at http://www.macrostudio.com.mx/';
				$message .= '\r\n\r\nIvan Lopez\r\nhttp://www.macrostudio.com.mx';

				$headers = 'From: Bing Bot Notifier <' . $email . '>' . '\r\n';
				wp_mail( $email, 'Bing Bot detected ( '.get_bloginfo( 'url' ) . ' )',$message, $headers );

			}
		}
	}
}


/**
 * Add links to the plugin menu item
 *
 */
function thisismyurl_bing_bot_notifier_plugin_actions( $links, $file ){
	static $this_plugin;

	if( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );

	if( $file == $this_plugin ){
		$links [] = '<a href="options-general.php?page=thisismyurl_bing_bot_notifier.php">' . __( 'Options' ) . '</a>';
		$links [] = "<a href='http://wordpress.org/plugins/bing-bot-notifier/'>thisismyurl.com</a>";
	}
	return $links;
}


function thisismyurl_bing_bot_notifier_options( $options='' ) {
?>

    <div class="wrap">
	<div class="thisismyurl icon32"><br /></div>
    <h2><?php _e( 'Bing Bot Notifier by Ivan Lopez', 'thisismyurl_bing_bot_notifier' ) ?></h2>

	<p>The Bing Bot Notifier Plugin is designed to help website owners know when Bing has indexed their website.</p>

    <form method="post" action="options.php">
    <?php wp_nonce_field( 'update-options' ); ?>


    <h3>Settings</h3>

    <table class="form-table">

        <tr valign="top">
        <th scope="row">Email Address</th>
        <td>
        <input class='regular-text code' name="thisismyurl_bing_bot_notifier_email" type="text" id="thisismyurl_bing_bot_notifier_email" value="<?php echo get_option( 'thisismyurl_bing_bot_notifier_email' ); ?>" />
        <p>Input the email address you would like Bing Bot Notifier to send emails to.</p>
		</td>
        </tr>

			<SCRIPT TYPE="text/javascript">
            <!--
            function numbersonly( myfield, e, dec )
            {
            var key;
            var keychar;

            if ( window.event )
               key = window.event.keyCode;
            else if ( e )
               key = e.which;
            else
               return true;
            keychar = String.fromCharCode( key );

            // control keys
            if ( ( key==null ) || ( key==0 ) || ( key==8 ) ||
                ( key==9 ) || ( key==13 ) || ( key==27 ) )
               return true;

            // numbers
            else if ( ( ( "0123456789" ).indexOf( keychar ) > -1 ) )
               return true;

            // decimal point jump
            else if ( dec && ( keychar == "." ) )
               {
               myfield.form.elements[dec].focus( );
               return false;
               }
            else
               return false;
            }

            //-->
            </SCRIPT>


        <tr valign="top">
        <th scope="row">Email Interval</th>
        <td>
        <input class='regular-text code' onKeyPress="return numbersonly( this, event )" name="thisismyurl_bing_bot_notifier_time" type="text" id="thisismyurl_bing_bot_notifier_time" value="<?php echo get_option( 'thisismyurl_bing_bot_notifier_time' ); ?>" />
        <p>How often would you like an email sent ( in <abbr title='3600 = one hour, 86400 = one day, 604800 = one week'>seconds</abbr>? ). By default, it will send everytime Bing scans your site.</p>
		</td>
        </tr>

       <tr valign="top">
        <th scope="row">Secret File Name</th>
        <td>
        <input class='regular-text code' name="thisismyurl_bing_bot_notifier_log_file_name" type="text" id="thisismyurl_bing_bot_notifier_log_file_name" value="<?php
		echo get_option( 'thisismyurl_bing_bot_notifier_log_file_name' );

		?>" />
        <?php if ( strlen( get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ) )>1 ) {?>
        <p>Your file will be downloadable from <a href='<?php echo get_bloginfo( 'url' )."/".get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ); ?>'><?php echo get_bloginfo( 'url' )."/".get_option( 'thisismyurl_bing_bot_notifier_log_file_name' ); ?></a></p>
        <?php } else { ?>
        <p>If you would like Bing Bot Notifier to write a text file with your latest blings, give it a name ( <em><?php

		$rand = rand( 111111, 999999 );
		$rand = $rand.".txt";
		echo $rand; ?></em> for example ) here. The file will appear at <?php bloginfo( 'url' );?>/<?php echo $rand; ?></p>
        <?php } ?>
		</td>
        </tr>

    </table>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="thisismyurl_bing_bot_notifier_email,thisismyurl_bing_bot_notifier_log_file_name,thisismyurl_bing_bot_notifier_time" />


    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
    </p>
	</form>

    </div>

<?php
}


function thisismyurl_bing_bot_notifier_getPageURL( ) {
 $pageURL = 'http';
 if ( $_SERVER["HTTPS"] == "on" )
  $pageURL .= "s";

 $pageURL .= "://";

 if ( $_SERVER["SERVER_PORT"] != "80" )
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 else
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

 return $pageURL;
}

/**
 * Adds CSS to the WordPress admin for this plugin
 * @return object  Description
 */
function thisismyurl_bing_bot_notifier_wordpress_scripts() {
	?>
	<style>
	.thisismyurl { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAFKSURBVFhHY/wPBAwDCJig9ICBUQeMOmDUAQPuAJIKoqPdZxmq90E5WIBbvTFDlQWUQySgagjsajzL4JD9nOERlE8MIMsBqonaDAe2G6PgVieo5L1nDEtOQNlEAKqFgHWpNkO6EoS96/B7CIMIQMUo4GCQU4AySQBUdMAPhkcPICxVeU4IgwhANQcc7b7KMPMekKEkxVAbxgERJAJQNRsyOCkxHCgVhHKIA1SMAiDYd4/BwfMaw/InUD4xABQCxIIjXWf+23uc+Z+y8jtUBBm8+98KlAPJ23tc/b/sMVSYAKBiCAgyVG1XYnADs78z7Dv2A8wiBKgbBUBH2EMLpNsPv0MYBACVHcDAIEtCFgQBKjvgB8PRgxCfE1sWUNEBPxiWZ0PLAgZOBicr4soC6pYDUEBKtUzdKAAVRMCakZQ2wWjPaNQBI90BDAwAnUnOGR7tr7gAAAAASUVORK5CYII=) no-repeat; }
	</style>
	<?php

}
add_action( 'admin_head', 'thisismyurl_bing_bot_notifier_wordpress_scripts' );
