<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.software-kunze.de
 * @since             1.0.0
 * @package           player-leaderboard
 *
 * @wordpress-plugin
 * Plugin Name:       Player Leaderboard
 * Plugin URI:        https://www.software-kunze.de/plugin-player-leaderboard/
 * Description:       Management of a player leaderboard
 * Version:           1.0.2
 * Author:            Alexander Kunze Software Consulting
 * Author URI:        https://www.software-kunze.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       player-leaderboard
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.4 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('Player_Leaderboard', '1.0.2');

global $player_leaderboard_db_version;
$player_leaderboard_db_version = '1.0.1';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-player-leaderboard.php
 */
function activate_player_leaderboard()
{
    global $player_leaderboard_db_version;

	// create tables
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	if (!function_exists('dbDelta'))
	{
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	// competition table
	$table_competition = $wpdb->prefix . 'player_leaderboard_competition';

	$sql = "CREATE TABLE $table_competition (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
            kindofsport varchar(64) NULL,
			type tinyint NOT NULL,
			gender tinyint NULL,
			description varchar(255) NOT NULL,
			bestof tinyint NOT NULL,
			victory tinyint NOT NULL,
			defeat tinyint NOT NULL,
			draw tinyint NOT NULL,
			rating tinyint NOT NULL,
            noduelpoints tinyint NOT NULL,
            bonuspoints tinyint NOT NULL,
            gamefactor tinyint NULL,
            setfactor tinyint NULL,
            ratings smallint NULL,
            deltarating bit(1) NULL,
            deltapercent tinyint NULL,
            headercolor varchar(8) NOT NULL,
            bordercolor varchar(8) NOT NULL,
            textcolor varchar(8) NOT NULL,
            zerocolor varchar(8) NOT NULL,
            maxcolor varchar(8) NOT NULL,
            lowcolor varchar(8) NOT NULL,
            midcolor varchar(8) NOT NULL,
            highcolor varchar(8) NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
	dbDelta($sql);

	// player table
	$table_player = $wpdb->prefix . 'player_leaderboard_player';

    if ($wpdb->get_var($wpdb->prepare( "SHOW TABLES LIKE %s", $table_player)) === $table_player)
    {
    	$sql = "CREATE TABLE $table_player (
    			id mediumint(9) NOT NULL AUTO_INCREMENT,
    			competitionid mediumint(9) NOT NULL,
    			name varchar(256) NOT NULL,
    			givenname varchar(256) NULL,
                comment varchar(256) NULL,
                playerpass varchar(32) NULL,
                birthday date NULL,
    			gender tinyint NULL,
                address1 varchar(256) NULL,
                address2 varchar(256) NULL,
                phone varchar(256) NULL,
                email varchar(256) NULL,
    			rating tinyint NULL,
    			duels int NULL,
                points DECIMAL(8,2) NULL,
                ratingpoints DECIMAL(8,2) NULL,
                quotient DECIMAL(8,2) NULL,
                ratings smallint NULL,
                ranking smallint NULL,
    			UNIQUE KEY id (id)
    		) $charset_collate;";
    }
    else
    {
    	$sql = "CREATE TABLE $table_player (
    			id mediumint(9) NOT NULL AUTO_INCREMENT,
    			competitionid mediumint(9) NOT NULL,
    			name varchar(256) NOT NULL,
    			givenname varchar(256) NULL,
                comment varchar(256) NULL,
                playerpass varchar(32) NULL,
                birthday date NULL,
    			gender tinyint NULL,
                address1 varchar(256) NULL,
                address2 varchar(256) NULL,
                phone varchar(256) NULL,
                email varchar(256) NULL,
    			rating tinyint NULL,
    			duels int NULL,
                points DECIMAL(8,2) NULL,
                ratingpoints DECIMAL(8,2) NULL,
                quotient DECIMAL(8,2) NULL,
                ratings smallint NULL,
                ranking smallint NULL,
                FOREIGN KEY (competitionid) REFERENCES {$table_competition}(id) ON DELETE CASCADE,
    			UNIQUE KEY id (id)
    		) $charset_collate;";
    }
	dbDelta($sql);

	// result table
	$table_result = $wpdb->prefix . 'player_leaderboard_result';

    if ($wpdb->get_var($wpdb->prepare( "SHOW TABLES LIKE %s", $table_result)) === $table_result)
    {
    	$sql = "CREATE TABLE $table_result (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			date date NOT NULL,
            comment varchar(255) NULL,
			competitionid mediumint(9) NOT NULL,
			player1id mediumint(9) NOT NULL,
			player2id mediumint(9) NOT NULL,
			partner1id mediumint(9) NULL,
			partner2id mediumint(9) NULL,
			player1set1 tinyint NULL,
			player1set2 tinyint NULL,
			player1set3 tinyint NULL,
			player1set4 tinyint NULL,
			player1set5 tinyint NULL,
			player1set6 tinyint NULL,
			player1set7 tinyint NULL,
			player2set1 tinyint NULL,
			player2set2 tinyint NULL,
			player2set3 tinyint NULL,
			player2set4 tinyint NULL,
			player2set5 tinyint NULL,
			player2set6 tinyint NULL,
			player2set7 tinyint NULL,
			player1points smallint NULL,
			player2points smallint NULL,
			player1sets tinyint NULL,
			player2sets tinyint NULL,
			player1games tinyint NULL,
			player2games tinyint NULL,
            ratingflag bit(1) NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
    }
    else
    {
    	$sql = "CREATE TABLE $table_result (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			date date NOT NULL,
            comment varchar(255) NULL,
			competitionid mediumint(9) NOT NULL,
			player1id mediumint(9) NOT NULL,
			player2id mediumint(9) NOT NULL,
			partner1id mediumint(9) NULL,
			partner2id mediumint(9) NULL,
			player1set1 tinyint NULL,
			player1set2 tinyint NULL,
			player1set3 tinyint NULL,
			player1set4 tinyint NULL,
			player1set5 tinyint NULL,
			player1set6 tinyint NULL,
			player1set7 tinyint NULL,
			player2set1 tinyint NULL,
			player2set2 tinyint NULL,
			player2set3 tinyint NULL,
			player2set4 tinyint NULL,
			player2set5 tinyint NULL,
			player2set6 tinyint NULL,
			player2set7 tinyint NULL,
			player1points smallint NULL,
			player2points smallint NULL,
			player1sets tinyint NULL,
			player2sets tinyint NULL,
			player1games tinyint NULL,
			player2games tinyint NULL,
            ratingflag bit(1) NULL,
			FOREIGN KEY (competitionid) REFERENCES {$table_competition}(id) ON DELETE CASCADE,
			FOREIGN KEY (player1id) REFERENCES {$table_player}(id),
			FOREIGN KEY (player2id) REFERENCES {$table_player}(id),
			FOREIGN KEY (partner1id) REFERENCES {$table_player}(id),
			FOREIGN KEY (partner2id) REFERENCES {$table_player}(id),
			UNIQUE KEY id (id)
		) $charset_collate;";
    }
	dbDelta($sql);

    update_option('player_leaderboard_db_version', $player_leaderboard_db_version);
}

/**
 * Check for db version and update db if new version avaiable
 *
 * @since    1.0.1
 *
 */
function update_db_check_player_leaderboard()
{
    global $player_leaderboard_db_version;
    if (get_site_option('player_leaderboard_db_version') != $player_leaderboard_db_version)
    {
        activate_player_leaderboard();
    }
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-player-leaderboard.php
 */
function deactivate_player_leaderboard()
{
	// Remove everything only by uninstall
}

/**
 * The code that runs during plugin unistall.
 * This action is documented in includes/class-player-leaderboard.php
 */
function uninstall_player_leaderboard()
{
	// remove tables
	global $wpdb;

	// result table
	$table_result = $wpdb->prefix . 'player_leaderboard_result';
	$sql = "DROP TABLE IF EXISTS $table_result";
	$wpdb->query($sql);

	// player table
	$table_player = $wpdb->prefix . 'player_leaderboard_player';
	$sql = "DROP TABLE IF EXISTS $table_player";
	$wpdb->query($sql);

	// competition table
	$table_competition = $wpdb->prefix . 'player_leaderboard_competition';
	$sql = "DROP TABLE IF EXISTS $table_competition";
	$wpdb->query($sql);
}

register_activation_hook(__FILE__, 'activate_player_leaderboard');
register_deactivation_hook(__FILE__, 'deactivate_player_leaderboard');
register_uninstall_hook(__FILE__, 'uninstall_player_leaderboard'); 

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-player-leaderboard.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_player_leaderboard()
{
    $description = __('Management of a player leaderboard', 'player-leaderboard');
    $plugin = new Player_Leaderboard();
    $plugin->run();
}

run_player_leaderboard();
