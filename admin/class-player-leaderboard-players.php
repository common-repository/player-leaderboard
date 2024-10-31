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

/**
 * we have to make sure that the necessary class is available since the WP_List_Table
 * isn’t loaded automatically
 */
if (!class_exists('WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if (!class_exists('Player_Leaderboard_Export'))
{
    require_once( plugin_dir_path(__FILE__) . '../includes/class-player-leaderboard-export.php' );
}

class Player_Leaderboard_Players extends WP_List_Table
{
    /**
     * Overloaed constructor
     *
     * @since    1.0.0
     * @access   public
     */
     public function __construct()
     {
        parent::__construct(
            array(
                'singular' => 'singular_form',
                'plural'   => 'plural_form',
                'ajax'     => false
            )
        );
    }

    /**
     * The method get_columns() is needed to label the columns on the top and bottom
     * of the table. The keys in the array have to be the same as in the data array
     * otherwise the respective columns aren’t displayed.
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />', // this is all you need for the bulk-action checkbox
            'id' => __('Id', 'player-leaderboard'),
            'name' => __('Name', 'player-leaderboard'),
            'competitionname' => __('Competition', 'player-leaderboard'),
            'rating' => __('Rating', 'player-leaderboard'),
            'duels' => __('Duels', 'player-leaderboard'),
            'points' => __('Points', 'player-leaderboard'),
            'average' => __('Average', 'player-leaderboard'),
            'quotient' => __('Quotient', 'player-leaderboard'),
            'ratings' => __('Ratings', 'player-leaderboard'),
            'ratingpoints' => __('Rating Points', 'player-leaderboard'),
            'action' => __('Action', 'player-leaderboard')
        );
    }

    /**
     * Get names of the hidde columns
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_hidden_columns()
    {
        return array('id');
    }

    /**
     * Get array of the sortable columns
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_sortable_columns()
    {
        return array(
            'name' => array('name',false),
            'competitionname' => array('competitionname',false),
            'rating' => array('rating',true),
            'duels' => array('duels',true),
            'points' => array('points',true),
            'average' => array('average',true),
            'quotient' => array('quotient',true),
            'ratings' => array('ratings',true),
            'ratingpoints' => array('ratingpoints',true)
          );
    }

    /**
     * Get array of bulk actions
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_bulk_actions()
    {
        return array(
            'delete' => __( 'Delete', 'player-leaderboard' )
        );
    }

    /**
     * prepare_items defines two arrays controlling the behaviour of the table:
     *
     * $hidden defines the hidden columns (see Screen Options),
     * $sortable defines if the table can be sorted by this column.
     *
     * Finally the method assigns the participants events to the class data
     * representation variable items.
     *
     * @since    1.0.0
     * @access   public / overridden
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $this->items = $this->get_results();
    }

    /**
     * Before actually displaying each column WordPress looks for methods called
     * column_{key_name}, e.g. function column_booktitle. There has to be such a method
     * for every defined column. To avoid the need to create a method for each column
     * there is column_default that will process any column for which no special method
     * is defined.
     *
     * @since    1.0.0
     * @access   public / overridden
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name)
        {
            case 'cb':
                return sprintf('<input type="checkbox" class="bulk-item-selection" name="bulk-item-selection[]" value="%s" />', $item['id']);

            case 'name':
            case 'competitionname':
            case 'rating':
            case 'duels':
            case 'points':
            case 'ratings':
            case 'ratingpoints':
                return $item[$column_name];
            case 'quotient':
                return round($item[$column_name],2);
            case 'average':
                return ($item['duels'] > 0)? round($item[$column_name] / $item['duels'], 2) : 0;
            case 'action':
                // See function column_action()
                return "";
            default:
                // Show the whole array for troubleshooting purposes
                return print_r( $item, true ) ;
        }
    }

    /**
     * Render the link to the event edit page
     *
     * @since    1.0.0
     * @access   public
     */
    public function column_action($item)
    {
        if (isset($_GET['competitionID']))
        {
            $competitionID = intval($_GET['competitionID']);
            return "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-player&playerID=" .
                $item['id']) . "&competitionID={$competitionID}'>" . __('Details', 'player-leaderboard') .
                " | " .
                "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-player&mode=edit&playerID=" .
                $item['id']) . "&competitionID={$competitionID}'>" . __('Edit', 'player-leaderboard');
        }
        else
        {
            return "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-player&playerID=" .
                $item['id']) . "'>" . __('Details', 'player-leaderboard') . "</a>" .
                " | " .
                "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-player&mode=edit&playerID=" .
                $item['id']) . "'>" . __('Edit', 'player-leaderboard') . "</a>";
        }
    }

    /**
     * Load the results from the wordpress database
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_results()
    {
        global $wpdb;
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";

        if (isset($_GET['competitionID']))
        {
            $competitionID = intval($_GET['competitionID']);
            $sql = "SELECT player.id AS id, player.name AS name, player.rating AS rating,
                player.duels AS duels, player.points AS points, player.quotient AS quotient,
                player.points AS average, player.ratings AS ratings, player.ratingpoints AS ratingpoints,
                competition.name as competitionname FROM $table_player AS player
            	JOIN $table_competition AS competition ON competition.id = player.competitionid
                WHERE competition.id = $competitionID";
        }
        else
        {
            $sql = "SELECT player.id AS id, player.name AS name, player.rating AS rating,
                player.duels AS duels, player.points AS points, player.quotient AS quotient,
                player.points AS average, player.ratings AS ratings, player.ratingpoints AS ratingpoints,
                competition.name as competitionname FROM $table_player AS player
            	JOIN $table_competition AS competition ON competition.id = player.competitionid";
        }

        if (!empty( $_REQUEST['orderby']))
        {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= !empty( $_REQUEST['order'])? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }
        else
        {
            $sql .= ' ORDER BY player.name ASC';
        }

        $wpdb->show_errors();
        $results = $wpdb->get_results($sql, ARRAY_A);
        $wpdb->hide_errors();

        return $results;
    }

    /**
     * Load the competitions from the wordpress database
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_competitions()
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        return $wpdb->get_results("SELECT * FROM $table_competition ORDER BY name");
    }

    /**
     * Create a CSV for the currently shown players
     *
     * @since    1.0.0
     * @access   public
     */
    public function generate_csv()
    {
        $file = fopen('php://output', 'w');
        Player_Leaderboard_Export::players_csv($file, true);
        fclose($file);
    }

    /**
     * Process the bulk action
     *
     * @since    1.0.0
     * @access   public
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();
        if (isset($_POST['bulk-item-selection']))
        {
            switch ($action)
            {
                case 'delete':
                    if (is_array($_POST['bulk-item-selection']))
                    {
                        $selecteditems = array_map('sanitize_text_field', $_POST['bulk-item-selection']);
                        $selecteditems = implode(',', $selecteditems);
                    }
                    else
                    {
                        $selecteditems = sanitize_text_field($_POST['bulk-item-selection']);
                    }

                    if (!empty($selecteditems))
                    {
                        global $wpdb;
                        $table_player = "{$wpdb->prefix}player_leaderboard_player";
                        $wpdb->query("DELETE FROM $table_player WHERE id IN ($selecteditems)");
                    }
                    break;

                default:
                    // do nothing or something else
                    return;
            }
        }

        return;
    }
}
