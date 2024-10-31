<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.software-kunze.de
 * @since      1.0.0
 *
 * @package    Player-Leaderboard
 * @subpackage player-leaderboard/includes
 * @author     Alexander Kunze
 */

class Player_Leaderboard
{
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('Player_Leaderboard')) 
		{
            $this->version = Player_Leaderboard;
        } 
		else 
		{
            $this->version = '1.0.0';
        }
        
		$this->plugin_name = 'player-leaderboard';
		$this->load_dependencies();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Player_Leaderboard_Admin. Defines all hooks for the admin area.
     * - Player_Leaderboard_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-player-leaderboard-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-player-leaderboard-public.php';
    }

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() 
	{
		load_plugin_textdomain(
			'player-leaderboard',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Player_Leaderboard_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Player_Leaderboard_Admin($this->get_plugin_name(), $this->get_version());

        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

        add_action('admin_post_pl_action_competition', array($plugin_admin, 'action_competition'));
        add_action('admin_post_pl_action_player', array($plugin_admin, 'action_player'));
        add_action('admin_post_pl_action_result', array($plugin_admin, 'action_result'));
        add_action('admin_post_pl_action_results', array($plugin_admin, 'action_results'));

        add_action('admin_menu', array($plugin_admin,'add_admin_page'));

        add_action('plugins_loaded', array($plugin_admin, 'db_check'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Player_Leaderboard_Public($this->get_plugin_name(), $this->get_version());

        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));

        // Add the shortcode which is used in the public area to show the standings
        add_shortcode('player_leaderboard', array($plugin_public, 'public_shortcode'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
