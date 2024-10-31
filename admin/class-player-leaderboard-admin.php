<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.software-kunze.de
 * @since      1.0.0
 *
 * @package    Player-Leaderboard
 * @subpackage player-leaderboard/admin
 * @author     Alexander Kunze
 */

 if (!class_exists('Player_Leaderboard_Results'))
{
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-player-leaderboard-results.php';
}

 if (!class_exists('Player_Leaderboard_Players'))
{
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-player-leaderboard-players.php';
}

if (!class_exists('Player_Leaderboard_Calulator'))
{
    require_once( plugin_dir_path(__FILE__) . '../includes/class-player-leaderboard-calculator.php' );
}

if (!class_exists('Player_Leaderboard_Export'))
{
    require_once( plugin_dir_path(__FILE__) . '../includes/class-player-leaderboard-export.php' );
}

class Player_Leaderboard_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/player-leaderboard-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('wp-color-picker');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($page)
    {
        if (strpos($page, 'player-leaderboard') !== false)
        {
            wp_enqueue_script($this->plugin_name . "-admin", plugin_dir_url(__FILE__) . 'js/player-leaderboard-admin.js',
                array('jquery', 'wp-color-picker'), $this->version, false);

            // Localize the script with new data
            $translation_array = array(
                'yes' => __( 'Yes', 'player-leaderboard' ),
                'no' => __( 'No', 'player-leaderboard' )
            );

            wp_localize_script($this->plugin_name . "-admin", "adminobject", $translation_array);
        }
    }

    /**
     * Add the admin menu pages
     *
     * @since    1.0.0
     */
    public function add_admin_page()
    {
        add_menu_page(
            __('Player Leaderboard', 'player-leaderboard'),
            __('Player Leaderboard', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '',
            array($this, 'load_admin_competitions'),
            'dashicons-list-view',
            7
        );

        add_submenu_page(
            $this->plugin_name,
            __('Player Leaderboard', 'player-leaderboard'),
            __('Competitions', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '',
            array($this, 'load_admin_competitions')
        );

        add_submenu_page(
            null,
            'New Competition',
            __('New Competition', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '-competition',
            array($this, 'load_admin_competition')
        );

        add_submenu_page(
            null,
            __('Player Leaderboard', 'player-leaderboard'),
            __('Players', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '-players',
            array($this, 'load_admin_players')
        );

        add_submenu_page(
            null,
            'New Player',
            __('New Player', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '-player',
            array($this, 'load_admin_player')
        );

        add_submenu_page(
            null,
            __('Player Leaderboard', 'player-leaderboard'),
            __('Results', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '-results',
            array($this, 'load_admin_results')
        );

        add_submenu_page(
            null,
            'New Result',
            __('New Result', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '-result',
            array($this, 'load_admin_result')
        );

        add_submenu_page(
            null,
            'New Results',
            __('New Results', 'player-leaderboard'),
            'manage_options',
            $this->plugin_name . '-results-multiple',
            array($this, 'load_admin_results_multiple')
        );
    }

    public function load_admin_competitions()
    {
        $tab = (!empty($_GET['tab'])) ? esc_attr($_GET['tab']) : '0';
        if ($tab == '0')
        {
            require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-competitions.php';
        }
        else if ($tab == '1')
        {
            require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-players.php';
        }
        else
        {
           require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-results.php';
        }
    }

    public function load_admin_competition()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-competition.php';
    }

    public function load_admin_players()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-players.php';
    }

    public function load_admin_player()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-player.php';
    }

    public function load_admin_results()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-results.php';
    }

    public function load_admin_result()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-result.php';
    }

    public function load_admin_results_multiple()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/player-leaderboard-results-multiple.php';
    }

    /**
     * Import results from a CSV file
     *
     * @since    1.0.0
     *
     * 1.0.1 - Ignore competition, if a competition is selected
     */
    public function csv_import($competitionID, $competitiontype, $resetratings, $importresults)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_result = "{$wpdb->prefix}player_leaderboard_result";

        $filename = $_FILES['csvimport']['name'];
        $tempname = $_FILES['csvimport']['tmp_name'];

        if (isset($competitionID))
        {
            $message = __('Competition', 'player-leaderboard') . ' ' . $this->get_competition($competitionID)->name. '<br/>';
        }
        else
        {
            $message = __('No competition selected', 'player-leaderboard') . '<br/>';
        }

        if (empty($filename) == true)
        {
            return $message . __('Missing file name', 'player-leaderboard');
        }
        else
        {
            $message .= __('Import', 'player-leaderboard') . ' ' . $filename . '<br/>';
        }

        // File extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // If file extension is 'csv'
        if ($extension != 'csv')
        {
            return $message . __('Invalid Extension', 'player-leaderboard');
        }

        $players = 0;
        $results = 0;
        $ignoredresults = 0;
        $row = 0;

        // Open file in read mode
        $csvfile = fopen($tempname, 'r');

        if (!isset($resetratings))
        {
            $resetratings = 0;
        }

        if (!isset($importresults))
        {
            $importresults = 0;
        }

        // Lopping thru the rows
        while (($csvdata = fgetcsv($csvfile)) !== false)
        {
            $row++;
            $datalen = count($csvdata);
            $tag = $csvdata[0];

            switch ($tag)
            {
                case "COMPETITIONS":
                    break;

                case "COMPETITION":
                    if ($datalen < 2)
                    {
                        $message .= __('Row', 'player-leaderboard') . $row . ': '
                            . __('Competition contains not enough elements.', 'player-leaderboard') .'<br/>';
                        break;
                    }

                    $competitionname = sanitize_text_field($csvdata[1]);
                    $competitiontype = ($datalen > 3)? intval($csvdata[3]) : 3;

                    if (isset($competitionID))
                    {
                        $message .= __('Competiton', 'player-leaderboard') . ' ' . $competitionname . ' '
                            . __('ignored.', 'player-leaderboard') . '<br/>';
                    }
                    else
                    {
                        $competitiondata = array(
                            'name' => $competitionname,
            			    'kindofsport' => ($datalen > 2)? sanitize_text_field($csvdata[2]) : '',
                            'type' => $competitiontype,
            			    'gender' => ($datalen > 4)? intval($csvdata[4]) : 3,
                            'description' => ($datalen > 5)? sanitize_text_field($csvdata[5]) : '',
                            'bestof' => ($datalen > 6)? intval($csvdata[6]) : 3,
                            'victory' => ($datalen > 7)? intval($csvdata[7]) : 1,
                            'defeat' => ($datalen > 8)? intval($csvdata[8]) : 0,
                            'draw' => ($datalen > 9)? intval($csvdata[9]) : 0,
                            'rating' => ($datalen > 10)? intval($csvdata[10]) : 50,
                            'noduelpoints' => ($datalen > 11)? intval($csvdata[11]) : 0,
                            'bonuspoints' => ($datalen > 12)? intval($csvdata[12]) : 1,
                            'gamefactor' => ($datalen > 13)? intval($csvdata[13]) : 1,
                            'setfactor' => ($datalen > 14)? intval($csvdata[14]) : 0,
                            'ratings' => (($datalen > 15) && ($resetratings == 0))? intval($csvdata[15]) : 0,
                            'deltarating' => ($datalen > 16)? intval($csvdata[16]) : 1,
                            'deltapercent' => ($datalen > 17)? intval($csvdata[17]) : 100,
                            'headercolor' => ($datalen > 18)? sanitize_text_field($csvdata[18]) : '#38a5ff',
                            'bordercolor' => ($datalen > 19)? sanitize_text_field($csvdata[19]) : '#f2f2f2',
                            'textcolor' => ($datalen > 20)? sanitize_text_field($csvdata[20]) : '#ffffff',
                            'zerocolor' => ($datalen > 21)? sanitize_text_field($csvdata[21]) : '#ffffff',
                            'maxcolor' => ($datalen > 22)? sanitize_text_field($csvdata[22]) : '#90ee90',
                            'lowcolor' => ($datalen > 23)? sanitize_text_field($csvdata[23]) : '#ffffbb',
                            'midcolor' => ($datalen > 24)? sanitize_text_field($csvdata[24]) : '#ffff77',
                            'highcolor' => ($datalen > 25)? sanitize_text_field($csvdata[25]) : '#ffff33'
                        );
                        $format = array('%s','%s','%d','%d','%s',
                            '%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
                            '%s','%s','%s','%s','%s','%s','%s','%s');
                        $wpdb->insert($table_competition, $competitiondata, $format);
                        $competitionID = $wpdb->insert_id;

                        $message .= __('New competiton', 'player-leaderboard') . ' ' . $competitionname . ' '
                            . __('created.', 'player-leaderboard') . '<br/>';
                    }
                    break;

                case 'PLAYERS':
                    break;

                case 'PLAYER':
                    if (!isset($competitionID))
                    {
                        $message .= __('Row', 'player-leaderboard') . $row . ': '
                            . __('Missing competition, player skipped.', 'player-leaderboard') . '<br/>';
                        break;
                    }

                    if ($datalen < 2)
                    {
                        $message .= __('Row', 'player-leaderboard') . $row . ': '
                            . __('Player contains not enough elements.', 'player-leaderboard') . '<br/>';
                        break;
                    }

                    $playerdata = array(
        			    'competitionid' => $competitionID,
                        'name' => sanitize_text_field($csvdata[1]),
        			    'givenname' => ($datalen > 2)? sanitize_text_field($csvdata[2]) : '',
                        'comment' => ($datalen > 3)? sanitize_text_field($csvdata[3]) : '',
                        'playerpass' => ($datalen > 4)? sanitize_text_field($csvdata[4]) : '',
                        'birthday' => ($datalen > 5)? sanitize_text_field($csvdata[5]) : '0000-00-00',
        			    'gender' => ($datalen > 6)? intval($csvdata[6]) : '',
                        'address1' => ($datalen > 7)? sanitize_text_field($csvdata[7]) : '',
                        'address2' => ($datalen > 8)? sanitize_text_field($csvdata[8]) : '',
                        'phone' => ($datalen > 9)? sanitize_text_field($csvdata[9]) : '',
                        'email' => ($datalen > 10)? sanitize_text_field($csvdata[10]) : '',
                        'rating' => (($datalen > 11) && ($resetratings == 0))? intval($csvdata[11]) : 50,
                        'duels' => (($datalen > 12) && ($resetratings == 0))? intval($csvdata[12]) : 0,
                        'points' => (($datalen > 13) && ($resetratings == 0))? intval($csvdata[13]) : 0,
                        'ratingpoints' => (($datalen > 14) && ($resetratings == 0))? intval($csvdata[14]) : 0,
                        'quotient' => (($datalen > 15) && ($resetratings == 0))? intval($csvdata[15]) : 0,
                        'ratings' => (($datalen > 16) && ($resetratings == 0))? intval($csvdata[16]) : 0,
                        'ranking' => (($datalen > 17) && ($resetratings == 0))? intval($csvdata[17]) : 0

                    );
                    $format = array('%d','%s','%s','%s','%s','%s','%d','%s','%s','%s','%s',
                        '%d','%d','%d','%d','%d','%d','%d');
                    $wpdb->insert($table_player, $playerdata, $format);
                    if ($wpdb->insert_id > 0)
                    {
                        $players++;
                    }
                    break;

                case "RESULTS":
                    break;

                case "RESULT":
                    if ($importresults == 1)
                    {
                        if (!isset($competitionID))
                        {
                            $message .= __('Row', 'player-leaderboard') . $row . ': '
                                . __('Missing competition, result skipped.', 'player-leaderboard') . '<br/>';
                            break;
                        }

                        if ($datalen < 5)
                        {
                            $message .= __('Row', 'player-leaderboard') . $row . ': '
                                . __('Result contains not enough elements.', 'player-leaderboard') .'<br/>';
                            break;
                        }

                        $column = 1; // Skip header

                        // Assign value to variables
                        $date = sanitize_text_field($csvdata[$column++]);
                        $player1name = sanitize_text_field($csvdata[$column++]);
                        $partner1name = sanitize_text_field($csvdata[$column++]);
                        $player2name = sanitize_text_field($csvdata[$column++]);
                        $partner2name = sanitize_text_field($csvdata[$column++]);

                        $player1set1 = intval($csvdata[$column++]);
                        $player2set1 = intval($csvdata[$column++]);
                        $player1set2 = 0;
                        $player2set2 = 0;
                        $player1set3 = 0;
                        $player2set3 = 0;
                        $player1set4 = 0;
                        $player2set4 = 0;
                        $player1set5 = 0;
                        $player2set5 = 0;
                        $player1set6 = 0;
                        $player2set6 = 0;
                        $player1set7 = 0;
                        $player2set7 = 0;

                        $player1points = $player1set1;
                        $player2points = $player2set1;
                        $player1sets = ($player1set1 > $player2set1)? 1:0;
                        $player2sets = ($player2set1 > $player1set1)? 1:0;

                        if ($datalen > 8)
                        {
                            $player1set2 = intval($csvdata[$column++]);
                            $player2set2 = intval($csvdata[$column++]);
                            $player1points += $player1set2;
                            $player2points += $player2set2;
                            $player1sets += ($player1set2 > $player2set2)? 1:0;
                            $player2sets += ($player2set2 > $player1set2)? 1:0;

                            if ($datalen > 10)
                            {
                                $player1set3 = intval($csvdata[$column++]);
                                $player2set3 = intval($csvdata[$column++]);
                                $player1points += $player1set3;
                                $player2points += $player2set3;
                                $player1sets += ($player1set3 > $player2set3)? 1:0;
                                $player2sets += ($player2set3 > $player1set3)? 1:0;

                                if ($datalen > 12)
                                {
                                    $player1set4 = intval($csvdata[$column++]);
                                    $player2set4 = intval($csvdata[$column++]);
                                    $player1points += $player1set4;
                                    $player2points += $player2set4;
                                    $player1sets += ($player1set4 > $player2set4)? 1:0;
                                    $player2sets += ($player2set4 > $player1set4)? 1:0;

                                    if ($datalen > 14)
                                    {
                                        $player1set5 = intval($csvdata[$column++]);
                                        $player2set5 = intval($csvdata[$column++]);
                                        $player1points += $player1set5;
                                        $player2points += $player2set5;
                                        $player1sets += ($player1set5 > $player2set5)? 1:0;
                                        $player2sets += ($player2set5 > $player1set5)? 1:0;

                                        if ($datalen > 16)
                                        {
                                            $player1set6 = intval($csvdata[$column++]);
                                            $player2set6 = intval($csvdata[$column++]);
                                            $player1points += $player1set6;
                                            $player2points += $player2set6;
                                            $player1sets += ($player1set6 > $player2set6)? 1:0;
                                            $player2sets += ($player2set6 > $player1set6)? 1:0;

                                            if ($datalen > 18)
                                            {
                                                $player1set7 = intval($csvdata[$column++]);
                                                $player2set7 = intval($csvdata[$column++]);
                                                $player1points += $player1set7;
                                                $player2points += $player2set7;
                                                $player1sets += ($player1set7 > $player2set7)? 1:0;
                                                $player2sets += ($player2set7 > $player1set7)? 1:0;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (($datalen > 20) && ($resetratings == 0))
                        {
                            $ratingflag = intval($csvdata[$column++]);
                        }
                        else
                        {
                            $ratingflag = 0;
                        }

                        $player1id = $wpdb->get_var("SELECT id FROM {$table_player}
                            WHERE name = '" . esc_sql($player1name) ."' AND competitionid = {$competitionID}");
                        if (!isset($player1id))
                        {
                            $message .= "Row " . $row . " Player '" . $player1name . "' not found." . '<br/>';
                            break;
                        }
                        $player2id = $wpdb->get_var("SELECT id FROM {$table_player}
                            WHERE name = '" . esc_sql($player2name) ."' AND competitionid = {$competitionID}");
                        if (!isset($player2id))
                        {
                            $message .= "Row " . $row . " Player '" . $player2name . "' not found." . '<br/>';
                            break;
                        }

                        if ($competitiontype == 2)
                        {
                            $partner1id = $wpdb->get_var("SELECT id FROM {$table_player}
                                WHERE name = '" . esc_sql($partner1name) . "' AND competitionid = {$competitionID}");
                            if (!isset($player1id))
                            {
                                $message .= "Row " . $row . " Partner '" . $partner1name . "' not found." . '<br/>';
                                break;
                            }
                            $partner2id = $wpdb->get_var("SELECT id FROM {$table_player}
                                WHERE name = '" . esc_sql($partner2name) . "' AND competitionid = {$competitionID}");
                            if (!isset($player2id))
                            {
                                $message .= "Row " . $row . " Partner '" . $partner2name . "' not found." . '<br/>';
                                break;
                            }
                        }
                        else
                        {
                            $partner1id = null;
                            $partner2id = null;
                        }

                        $player1games = ($player1sets > $player2sets)? 1:0;
                        $player2games = ($player2sets > $player1sets)? 1:0;

                        $wpdb->insert($table_result,
                            array(
                				'date' => $date,
                				'competitionid' => $competitionID,
                				'player1id' => $player1id,
                				'partner1id' => $partner1id,
                				'player2id' => $player2id,
                				'partner2id' => $partner2id,
                				'player1set1' => $player1set1,
                				'player1set2' => $player1set2,
                				'player1set3' => $player1set3,
                				'player1set4' => $player1set4,
                				'player1set5' => $player1set5,
                				'player1set6' => $player1set6,
                				'player1set7' => $player1set7,
                				'player2set1' => $player2set1,
                				'player2set2' => $player2set2,
                				'player2set3' => $player2set3,
                				'player2set4' => $player2set4,
                				'player2set5' => $player2set5,
                				'player2set6' => $player2set6,
                				'player2set7' => $player2set7,
                				'player1points' => $player1points,
                				'player2points' => $player2points,
                				'player1sets' => $player1sets,
                				'player2sets' => $player2sets,
                                'player1games' => $player1games,
                                'player2games' => $player2games,
                                'ratingflag' => $ratingflag
                            ),
                            array(
                                '%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d',
                                '%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
                                '%d','%d','%d','%d','%d','%d'
                			));

                        if ($wpdb->insert_id > 0)
                        {
                            $results++;
                        }
                    }
                    else
                    {
                        $ignoredresults++;
                    }
                    break;

                default:
                    $message .= __('Row', 'player-leaderboard') . $row . ': '
                            . __('Contains invalid header: ', 'player-leaderboard') . $tag . '<br/>';
                    break;

            } // switch
        } // while

        $message .= __('Total players inserted', 'player-leaderboard') . ': ' . $players . '<br/>';
        $message .= __('Total results inserted', 'player-leaderboard') . ': ' . $results . '<br/>';
        if ($importresults == 0)
        {
            $message .= __('Total results ignored', 'player-leaderboard') . ': ' . $ignoredresults . '<br/>';
        }

        return $message;
    }

    /**
     * Create new competitions with default values
     *
     * @since    1.0.0
     */
    private function new_competition()
    {
        $competition = new stdClass();
        $competition->id = 0;
        $competition->name = '';
        $competition->gender = 3;
        $competition->description = '';
        $competition->kindofsport = 'Badminton';
        $competition->type = 1;
        $competition->bestof = 3;
        $competition->victory = 1;
        $competition->defeat = 0;
        $competition->draw = 0;
        $competition->rating = 50;
        $competition->gamefactor = 1;
        $competition->setfactor = 0;
        $competition->noduelpoints = 0;
        $competition->bonuspoints = 1;
        $competition->ratings = 0;
        $competition->deltarating = 1;
        $competition->deltapercent = 100;
        $competition->headercolor = '#38a5ff';
        $competition->textcolor = '#ffffff';
        $competition->bordercolor = '#f2f2f2';
        $competition->zerocolor = '#ffffff';
        $competition->maxcolor = '#90ee90';
        $competition->lowcolor = '#ffffbb';
        $competition->midcolor = '#ffff77';
        $competition->highcolor = '#ffff33';
        return $competition;
    }

    /**
     * get competitions from database
     *
     * @since    1.0.0
     */
    private function get_competitions()
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        return $wpdb->get_results("SELECT * FROM $table_competition ORDER BY name");
    }

    /**
     * get competitions from database
     *
     * @since    1.0.0
     */
    private function get_competition($competitionID)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        return $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");
    }

    /**
     * get competition name from database
     *
     * @since    1.0.0
     */
    private function get_competition_name($competitionID)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        return $wpdb->get_var("SELECT name FROM $table_competition WHERE id = $competitionID");
    }

     /**
     * Handle competition action
     *
     * @since    1.0.0
     */
    public function action_competition()
    {
        if (isset($_POST['player-leaderboard-save']))
        {
            $this->save_competition();
        }
        else
        {
            $this->delete_competition();
        }
    }

    /**
     * Insert or update competition to database
     *
     * @since    1.0.0
     */
    private function save_competition()
    {
        if (isset($_POST['id']))
        {
            $competitionID = intval($_POST['id']);
        }

        $type = intval($_POST['type']);
        if ($type == 3)
        {
            $kindofsport = sanitize_text_field($_POST['kindofsportteam']);
        }
        else
        {
            $kindofsport = sanitize_text_field($_POST['kindofsport']);
        }

        $competitiondata = array(
            'name' => sanitize_text_field($_POST['name']),
            'type' => $type,
            'description' => sanitize_textarea_field($_POST['description']),
            'kindofsport' => $kindofsport,
            'gender' => intval($_POST['gender']),
            'bestof' => intval($_POST['bestof']),
            'victory' => intval($_POST['victory']),
            'defeat' => intval($_POST['defeat']),
            'draw' => intval($_POST['draw']),
            'rating' => ($type == 1)? intval($_POST['singlerating']) : (($type == 2)? intval($_POST['doublerating']) : 0),
            'gamefactor' => intval($_POST['gamefactor']),
            'setfactor' => intval($_POST['setfactor']),
            'noduelpoints' => intval($_POST['noduelpoints']),
            'bonuspoints' => intval($_POST['bonuspoints']),
            'deltarating' => isset($_POST['deltarating'])? intval($_POST['deltarating']) : 0,
            'deltapercent' => isset($_POST['deltapercent'])? intval($_POST['deltapercent']):100,
            'headercolor' => sanitize_text_field($_POST['headercolor']),
            'textcolor' => sanitize_text_field($_POST['textcolor']),
            'bordercolor' => sanitize_text_field($_POST['bordercolor']),
            'zerocolor' => sanitize_text_field($_POST['zerocolor']),
            'maxcolor' => sanitize_text_field($_POST['maxcolor']),
            'lowcolor' => sanitize_text_field($_POST['lowcolor']),
            'midcolor' => sanitize_text_field($_POST['midcolor']),
            'highcolor' => sanitize_text_field($_POST['highcolor']),
            );

        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $format = array('%s','%d','%s','%s',
            '%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
            '%s','%s','%s','%s','%s','%s','%s','%s');

        if ((isset($competitionID)) && ((int) $competitionID > 0))
    	{
    		// edit
            $result = $wpdb->update($table_competition, $competitiondata,
                array('id' => (int) $competitionID), $format);
            if (false === $result)
            {
                return $wpdb->last_error;
            }
            else if($result == true)
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard&msg=2"));
            }
            else
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard"));
            }
        }
    	else
    	{
    		// create
            $result = $wpdb->insert($table_competition, $competitiondata, $format);
            if (false === $result)
            {
                return $wpdb->last_error;
            }
            else if($result == true)
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard&msg=3"));
            }
            else
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard"));
            }
        }
    }

    /**
     * delete competition from database
     *
     * @since    1.0.0
     */
    private function delete_competition()
    {
        if (isset($_POST['id']))
        {
            $competitionID = intval($_POST['id']);

            global $wpdb;

            $table_result = "{$wpdb->prefix}player_leaderboard_result";
            $result = $wpdb->delete($table_result, array('competitionid' => (int) $competitionID));

            $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
            $result = $wpdb->delete($table_competition, array('id' => (int) $competitionID));
            if (false === $result)
            {
                return $wpdb->last_error;
            }
        }
    }

    /**
     * Recalc the rating of the competition players
     *
     * @since    1.0.0
     */
    private function recalc_competition($competitionID)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        if ($competition->type == 2)
        {
            $rankingresult = Player_Leaderboard_Calulator::calcRankingDouble($competitionID);

            foreach ($rankingresult as $ranking)
            {
                // Update the player rating in the database
                $wpdb->update($table_player,
                    array(
                        'rating' => $ranking->rating,
                        'duels' => $ranking->duels,
                        'points' => $ranking->points,
                        'quotient' => $ranking->quotient,
                        'ratings' => $ranking->ratings,
                        'ratingpoints' => $ranking->ratingpoints
                    ),
                    array('id' => $ranking->id),
                    array(
                        '%d',
                        '%d',
                        '%f',
                        '%f',
                        '%d',
                        '%f'
                    ));
            }

            // Update the ratings counter for the competition
            $wpdb->update($table_competition,
                array('ratings' => ($competition->ratings + 1)),
                array('id' => $competition->id),
                array('%d'));

            $results = $wpdb->get_results("SELECT * FROM $table_result WHERE competitionid= $competitionID"); // AND ratingflag <> 1");
            foreach ($results as $result)
            {
                // Set the rating flag for all competition results
                $wpdb->update($table_result,
                    array('ratingflag' => 1),
                    array('id' => $result->id),
                    array('%d'));
            }
        }
        else
        {
            // todo
        }
    }

    /**
     * Preview the rating of the competition players
     *
     * @since    1.0.0
     */
    private function recalc_preview($competitionID)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        if ($competition->type == 2)
        {
            return Player_Leaderboard_Calulator::calcRankingDouble($competitionID);
        }
        else
        {
            // Todo !!
            //return Player_Leaderboard_Calulator::calcRankingSingle($competitionID);
        }
    }

    /**
     * Compare ranking based on the calculated quotient
     *
     * @since    1.0.0
     */
    function compare_ranking_quotient($ranking1 , $ranking2)
    {
        $quotient1 = ($ranking1->duels > 0)? ($ranking1->quotient / $ranking1->duels) : 0;
        $quotient2 = ($ranking2->duels > 0)? ($ranking2->quotient / $ranking2->duels) : 0;
        if ($quotient1 > $quotient2)
        {
               return -1;
        }

        if ($quotient1 < $quotient2)
        {
               return 1;
        }

        return 0;
    }

	/*
    public function getEventByID($competitionID)
    {
        global $wpdb;
        $table_competitions = "{$wpdb->prefix}competition_participants_competitions";
        return $wpdb->get_row("SELECT * FROM $table_competitions WHERE id = $competitionID");
    }
	*/

    /**
     * New player with some default values
     *
     * @since    1.0.0
     */
    private function new_player()
    {
        $player = new stdClass();
        $player->id = 0;
        $player->name = '';
        $player->comment = '';
        $player->playerpass = '';
        $player->birthday= null;
        $player->gender = 0;
        $player->address1 = '';
        $player->address2 = '';
        $player->phone = '';
        $player->email = '';
        $player->rating = 0;
        $player->competitionid = 0;
        $player->points = 0;
        $player->quotient = 0;
        $player->ratings = 0;
        $player->ratingpoints = 0;
        $player->ranking = 1;
        return $player;
    }

    /**
     * Get players from database for a competition
     *
     * @since    1.0.0
     */
    private function get_players($competitionID)
    {
        global $wpdb;
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        if (isset($competitionID))
        {
            // Get players for one competition
            return $wpdb->get_results("SELECT * FROM $table_player WHERE competitionid = $competitionID ORDER BY name");
        }
        else
        {
            // Get all players
            return $wpdb->get_results("SELECT * FROM $table_player ORDER BY name");
        }
    }

    /**
     * Get a single player from database
     *
     * @since    1.0.0
     */
    private function get_player($playerID)
    {
        global $wpdb;
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        return $wpdb->get_row("SELECT * FROM $table_player WHERE id = $playerID");
    }

    /**
     * Get a player name from database
     *
     * @since    1.0.0
     */
    private function get_player_name($playerID)
    {
        global $wpdb;
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        return $wpdb->get_var("SELECT name FROM $table_player WHERE id = $playerID");
    }

     /**
     * Handle player action
     *
     * @since    1.0.1
     */
    public function action_player()
    {
        // error_log(print_r($_POST, true));
        if (isset($_POST['player-leaderboard-save']))
        {
            $this->save_player();
        }
        else
        {
            $this->delete_player();
        }
    }

    /**
     * Save player to database
     *
     * @since    1.0.0
     */
    private function save_player()
    {
        if ((isset($_POST['id'])) && isset($_POST['competitionid']))
        {
            $playerID = intval($_POST['id']);
            $competitionID = intval($_POST['competitionid']);

            $url = admin_url("admin.php?page=player-leaderboard-players&competitionID={$competitionID}");

            $playerdata = array(
                    'name' => sanitize_text_field($_POST['name']),
                    'comment' => sanitize_textarea_field($_POST['comment']),
                    'playerpass' => sanitize_text_field($_POST['playerpass']),
                    'birthday' => isset($_POST['birthday'])? sanitize_text_field($_POST['birthday']) : null,
                    'gender' => isset($_POST['gender'])? intval($_POST['gender']) : 0,
                    'address1' => sanitize_text_field($_POST['address1']),
                    'address2' => sanitize_text_field($_POST['address2']),
                    'phone' => sanitize_text_field($_POST['phone']),
                    'email' => sanitize_text_field($_POST['email']),
                    'rating' => intval($_POST['rating']),
                    'ranking' => intval($_POST['ranking']),
                    'competitionid' => $competitionID
                    );

            global $wpdb;
            $table_player = "{$wpdb->prefix}player_leaderboard_player";
            $format = array('%s','%s','%s','%s','%d','%s','%s','%s','%s','%d','%d','%d');

            if ((isset($playerID)) && ((int) $playerID > 0))
        	{
        		// edit
                $result = $wpdb->update($table_player, $playerdata,
                    array('id' => (int) $playerID), $format);
                if (false === $result)
                {
                    return $wpdb->last_error;
                }
                else if($result == true)
                {
                    wp_redirect(admin_url("admin.php?page=player-leaderboard-players&msg=2&competitionID=" . intval($playerdata['competitionid'])));
                }
                else
                {
                    wp_redirect(admin_url("admin.php?page=player-leaderboard-players&competitionID=" . intval($playerdata['competitionid'])));
                }
            }
        	else
        	{
        		// create
                $result = $wpdb->insert($table_player, $playerdata,$format);
                if (false === $result)
                {
                    return $wpdb->last_error;
                }
                else if($result == true)
                {
                    wp_redirect(admin_url("admin.php?page=player-leaderboard-players&msg=3&competitionID=" . intval($playerdata['competitionid'])));
                }
                else
                {
                    wp_redirect(admin_url("admin.php?page=player-leaderboard-players&competitionID=" . intval($playerdata['competitionid'])));
                }
            }
        }
    }

     /**
     * Delete player from database
     *
     * @since    1.0.0
     */
    private function delete_player()
    {
        if (isset($_POST['id']))
        {
            $playerID = intval($_POST['id']);

            global $wpdb;
            $table_player = "{$wpdb->prefix}player_leaderboard_player";
            $result= $wpdb->delete($table_player, array('id' => (int) $playerID));

            if (false === $result)
            {
                return $wpdb->last_error;
            }
        }
    }

    /**
     * Get the results for one player
     *
     * @since    1.0.0
     */
    public function get_player_results($playerID)
    {
        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        return $wpdb->get_results("SELECT result.*,
                 p1.name AS player1name,
                 p2.name AS player2name,
                 p3.name AS partner1name,
                 p4.name AS partner2name
             FROM $table_result AS result
                LEFT JOIN $table_player AS p1 ON p1.id = result.player1id
                LEFT JOIN $table_player AS p2 ON p2.id = result.player2id
                LEFT JOIN $table_player AS p3 ON p3.id = result.partner1id
                LEFT JOIN $table_player AS p4 ON p4.id = result.partner2id
             WHERE
                result.player1id = $playerID OR result.player2id = $playerID OR
                result.partner1id = $playerID OR result.partner2id = $playerID");
    }

    /**
     * Get a single result from the database
     *
     * @since    1.0.0
     */
    public function get_result($resultID)
    {
        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        return $wpdb->get_row("SELECT * FROM $table_result WHERE id = $resultID");
    }

    /**
     * Get latest result date from the results table
     * This is used for a pre selected value for a new result
     *
     * @since    1.0.0
     */
    public function get_last_result_date($competitionID)
    {
        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        return $wpdb->get_var("SELECT MAX(date) FROM $table_result WHERE competitionid = $competitionID");
    }

    /**
     * Create a new result with default values
     *
     * @since    1.0.0
     */
    public function new_result($competitionID)
    {
        $result = new stdClass();
        $result->id = 0;
        $result->comment = '';

        if (isset($competitionID))
        {
    		$result->competitionid = $competitionID;
        }
        else
        {
    		$result->competitionid = 0;
            $result->competitionname = __('No Competition Selected', 'player-leaderboard');
        }

        $result->date = $this->get_last_result_date($result->competitionid);
        if (!isset($result->date))
        {
            $result->date = date("Y-m-d");
        }

        $result->player1id = 0;
        $result->player2id = 0;
        $result->partner1id = 0;
        $result->partner2id = 0;
        $result->player1set1 = 0;
        $result->player1set2 = 0;
        $result->player1set3 = 0;
        $result->player1set4 = 0;
        $result->player1set5 = 0;
        $result->player1set6 = 0;
        $result->player1set7 = 0;
        $result->player2set1 = 0;
        $result->player2set2 = 0;
        $result->player2set3 = 0;
        $result->player2set4 = 0;
        $result->player2set5 = 0;
        $result->player2set6 = 0;
        $result->player2set7 = 0;
        $result->player1points = 0;
        $result->player2points = 0;
        $result->player1sets = 0;
        $result->player2sets = 0;
        $result->player1games = 0;
        $result->player2games = 0;
        $result->ratingflag = 0;
        return $result;
    }

     /**
     * Handle result action
     *
     * @since    1.0.1
     */
    public function action_result()
    {
        if (isset($_POST['player-leaderboard-save']))
        {
            $this->save_result();
        }
        else
        {
            $this->delete_result();
        }
    }

     /**
     * Handle result multiple action
     *
     * @since    1.0.1
     */
    public function action_results()
    {
        // error_log(print_r($_POST, true));
        if (isset($_POST['player-leaderboard-save']))
        {
            $this->save_results();
        }
    }

    /**
     * Get latest result date from the results table
     * This is used for a pre selected value for a new result
     *
     * @since    1.0.0
     */
    private function save_result()
    {
        $resultID = intval($_POST['id']);

        $player1points = 0;
        $player2points = 0;
        $player1sets = 0;
        $player2sets = 0;
        $player1games = 0;
        $player2games = 0;

        if (isset($_POST['player1points']))
        {
            $player1points = intval($_POST['player1points']);
            $player2points = intval($_POST['player2points']);
            $player1sets = intval($_POST['player1sets']);
            $player2sets = intval($_POST['player2sets']);
            $player1games = intval($_POST['player1games']);
            $player2games = intval($_POST['player2games']);
        }
        else
        {
            for ($i=1; ($i<8); $i++)
            {
                $player1set = intval($_POST['player1set' . $i]);
                $player2set = intval($_POST['player2set' . $i]);
                $player1points += $player1set;
                $player2points += $player2set;

                if ($player1set > $player2set)
                {
                	$player1sets++;
                }
                elseif ($player1set < $player2set)
                {
                	$player2sets++;
                }
            }

            if ($player1sets > $player2sets)
            {
                $player1games++;
            }
            else if ($player1sets < $player2sets)
            {
                $player2games++;
            }
        }

        $resultdata = array(
            'date' => sanitize_text_field($_POST['date']),
            'comment' => sanitize_textarea_field($_POST['comment']),
            'competitionid' => intval($_POST['competitionid']),
            'player1id' => intval($_POST['player1id']),
            'player2id' => intval($_POST['player2id']),
            'partner1id' => (isset($_POST['partner1id']))? intval($_POST['partner1id']) : null,
            'partner2id' => (isset($_POST['partner2id']))? intval($_POST['partner2id']) : null,
            'player1set1' => (isset($_POST['player1set1']))? intval($_POST['player1set1']) : 0,
            'player1set2' => (isset($_POST['player1set2']))? intval($_POST['player1set2']) : 0,
            'player1set3' => (isset($_POST['player1set3']))? intval($_POST['player1set3']) : 0,
            'player1set4' => (isset($_POST['player1set4']))? intval($_POST['player1set4']) : 0,
            'player1set5' => (isset($_POST['player1set5']))? intval($_POST['player1set5']) : 0,
            'player1set6' => (isset($_POST['player1set6']))? intval($_POST['player1set6']) : 0,
            'player1set7' => (isset($_POST['player1set7']))? intval($_POST['player1set7']) : 0,
            'player2set1' => (isset($_POST['player2set1']))? intval($_POST['player2set1']) : 0,
            'player2set2' => (isset($_POST['player2set2']))? intval($_POST['player2set2']) : 0,
            'player2set3' => (isset($_POST['player2set3']))? intval($_POST['player2set3']) : 0,
            'player2set4' => (isset($_POST['player2set4']))? intval($_POST['player2set4']) : 0,
            'player2set5' => (isset($_POST['player2set5']))? intval($_POST['player2set5']) : 0,
            'player2set6' => (isset($_POST['player2set6']))? intval($_POST['player2set6']) : 0,
            'player2set7' => (isset($_POST['player2set7']))? intval($_POST['player2set7']) : 0,
            'player1points' => $player1points,
            'player2points' => $player2points,
            'player1sets' => $player1sets,
            'player2sets' => $player2sets,
            'player1games' => $player1games,
            'player2games' => $player2games,
            'ratingflag' => (isset($_POST['ratingflag']))? intval($_POST['ratingflag']) : 0);

        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        $format = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
            '%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d');

        if ((isset($resultID)) && ((int) $resultID > 0))
    	{
    		// edit
            $result = $wpdb->update($table_result, $resultdata,
                array('id' => (int) $resultID), $format);
            if (false === $result)
            {
                return $wpdb->last_error;
            }
            else if($result == true)
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard-results&msg=2&competitionID="
                    . intval($resultdata['competitionid'])));
            }
            else
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard-results&competitionID="
                    . intval($resultdata['competitionid'])));
            }
        }
    	else
    	{
    		// create
            $result = $wpdb->insert($table_result, $resultdata, $format);
            if (false === $result)
            {
                return $wpdb->last_error;
            }
            else if($result == true)
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard-results&msg=3&competitionID="
                    . intval($resultdata['competitionid'])));
            }
            else
            {
                wp_redirect(admin_url("admin.php?page=player-leaderboard-results&competitionID="
                    . intval($resultdata['competitionid'])));
            }
        }
    }

    /**
     * Get latest result date from the results table
     * This is used for a pre selected value for a new result
     *
     * @since    1.0.0
     */
    private function save_results()
    {
        $results = intval($_POST['results']);
        $competitionID = intval($_POST['competitionid']);

        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        $format = array('%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d',
            '%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d');

        for ($r = 1; ($r <= $results); $r++)
        {
            $player1id = intval($_POST['player1id-' . $r]);
            $player2id = intval($_POST['player2id-' . $r]);

            if (($player1id > 0) && ($player2id > 0))
            {
                $player1points = 0;
                $player2points = 0;
                $player1sets = 0;
                $player2sets = 0;
                $player1games = 0;
                $player2games = 0;

                if (isset($_POST['player1points']))
                {
                    $player1points = intval($_POST['player1points-' . $r]);
                    $player2points = intval($_POST['player2points-' . $r]);
                    $player1sets = intval($_POST['player1sets-' . $r]);
                    $player2sets = intval($_POST['player2sets-' . $r]);
                    $player1games = intval($_POST['player1games-' . $r]);
                    $player2games = intval($_POST['player2games-' . $r]);
                }
                else
                {
                    for ($i=1; ($i<8); $i++)
                    {
                        if ((isset($_POST['player1set' . $i . '-' . $r])) &&
                            (isset($_POST['player1set' . $i . '-' . $r])))
                        {
                            $player1set = intval($_POST['player1set' . $i . '-' . $r]);
                            $player2set = intval($_POST['player2set' . $i . '-' . $r]);
                            $player1points += $player1set;
                            $player2points += $player2set;

                            if ($player1set > $player2set)
                            {
                            	$player1sets++;
                            }
                            elseif ($player1set < $player2set)
                            {
                            	$player2sets++;
                            }
                        }
                    }

                    if ($player1sets > $player2sets)
                    {
                        $player1games++;
                    }
                    else if ($player1sets < $player2sets)
                    {
                        $player2games++;
                    }
                }

                $resultdata = array(
                    'date' => sanitize_text_field($_POST['date']),
                    'comment' => sanitize_textarea_field($_POST['comment-' . $r]),
                    'competitionid' => $competitionID,
                    'player1id' => intval($_POST['player1id-' . $r]),
                    'player2id' => intval($_POST['player2id-' . $r]),
                    'partner1id' => (isset($_POST['partner1id-' . $r]))? intval($_POST['partner1id-' . $r]) : null,
                    'partner2id' => (isset($_POST['partner2id-' . $r]))? intval($_POST['partner2id-' . $r]) : null,
                    'player1set1' => (isset($_POST['player1set1-' . $r]))? intval($_POST['player1set1-' . $r]) : 0,
                    'player1set2' => (isset($_POST['player1set2-' . $r]))? intval($_POST['player1set2-' . $r]) : 0,
                    'player1set3' => (isset($_POST['player1set3-' . $r]))? intval($_POST['player1set3-' . $r]) : 0,
                    'player1set4' => (isset($_POST['player1set4-' . $r]))? intval($_POST['player1set4-' . $r]) : 0,
                    'player1set5' => (isset($_POST['player1set5-' . $r]))? intval($_POST['player1set5-' . $r]) : 0,
                    'player1set6' => (isset($_POST['player1set6-' . $r]))? intval($_POST['player1set6-' . $r]) : 0,
                    'player1set7' => (isset($_POST['player1set7-' . $r]))? intval($_POST['player1set7-' . $r]) : 0,
                    'player2set1' => (isset($_POST['player2set1-' . $r]))? intval($_POST['player2set1-' . $r]) : 0,
                    'player2set2' => (isset($_POST['player2set2-' . $r]))? intval($_POST['player2set2-' . $r]) : 0,
                    'player2set3' => (isset($_POST['player2set3-' . $r]))? intval($_POST['player2set3-' . $r]) : 0,
                    'player2set4' => (isset($_POST['player2set4-' . $r]))? intval($_POST['player2set4-' . $r]) : 0,
                    'player2set5' => (isset($_POST['player2set5-' . $r]))? intval($_POST['player2set5-' . $r]) : 0,
                    'player2set6' => (isset($_POST['player2set6-' . $r]))? intval($_POST['player2set6-' . $r]) : 0,
                    'player2set7' => (isset($_POST['player2set7-' . $r]))? intval($_POST['player2set7-' . $r]) : 0,
                    'player1points' => $player1points,
                    'player2points' => $player2points,
                    'player1sets' => $player1sets,
                    'player2sets' => $player2sets,
                    'player1games' => $player1games,
                    'player2games' => $player2games,
                    'ratingflag' => (isset($_POST['ratingflag']))? intval($_POST['ratingflag']) : 0);

                $wpdb->insert($table_result, $resultdata, $format);
            }
        }

        wp_redirect(admin_url("admin.php?page=player-leaderboard-results&msg=4&competitionID={$competitionID}"));
    }

    /**
     * delete result from database
     *
     * @since    1.0.0
     */
    private function delete_result()
    {
        if (isset($_POST['id']))
        {
            $resultID = intval($_POST['id']);

            global $wpdb;
            $table_result = "{$wpdb->prefix}player_leaderboard_result";
            $result = $wpdb->delete($table_result, array('id' => (int) $resultID));

            if (false === $result)
            {
                return $wpdb->last_error;
            }
        }
    }

    /**
     * Create the block styles => Set the background color
     *
     * @since    1.0.0
     */
    private function create_styles($competitionID)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        echo "<style>\n";
        echo "table.pl-preview tr:nth-child(even) {";
        echo " background-color: " . $competition->bordercolor . "; }\n";

        echo "table.pl-preview {";
        echo " border: 1px solid " . $competition->bordercolor . "; }\n";

        echo "table.pl-preview thead th {";
        echo " color: " . $competition->textcolor . ";";
        echo " background: " . $competition->headercolor . "; }\n";
        echo "</style>";
    }

    /**
     * Export data as CSV
     *
     * @since    1.0.0
     */
    private function generate_csv()
    {
        $file = fopen('php://output', 'w');
        Player_Leaderboard_Export::competitions_csv($file, true, true);
        fclose($file);
    }

    /**
     * Perform check of database version
     *
     * @since    1.0.1
     */
    public function db_check()
    {
        update_db_check_player_leaderboard();
    }
}
