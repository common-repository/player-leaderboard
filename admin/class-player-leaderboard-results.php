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

class Player_Leaderboard_Results extends WP_List_Table
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
            'date' => __('Date', 'player-leaderboard'),
            'player1' => __('Player1', 'player-leaderboard'),
            'player2' => __('Player2', 'player-leaderboard'),
            'competitionname' => __('Competition', 'player-leaderboard'),
            'set1' => __('Set1', 'player-leaderboard'),
            'set2' => __('Set2', 'player-leaderboard'),
            'set3' => __('Set3', 'player-leaderboard'),
            'points' => __('Points', 'player-leaderboard'),
            'sets' => __('Sets', 'player-leaderboard'),
            'ratingflag' => __('Rating Flag', 'player-leaderboard'),
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
            'date' => array('date',false),
            // 'player1' => array('player1',false),
            // 'player2' => array('player2',false),
            'competitionname' => array('competitionname',false)
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

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = $this->get_numberof_results();

        $pagination = array(
            'total_items' => $total_items,    // total number of items
            'per_page'    => $per_page,       // items to show on a page
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
          );
        $this->set_pagination_args($pagination);

        $this->items = $this->get_results($current_page, $per_page);
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
                // See function column_cb()
                break;

            case 'date':
                return mysql2date(__('Y/m/d', 'player-leaderboard'), $item['date']);

            case 'competitionname':
                return $item[$column_name];

            case 'player1':
                if ($item['competitiontype'] == 2)
                {
                    return $item['player1name'] . "/" . $item['partner1name'];
                }
                else
                {
                    return $item['player1name'];
                }

            case 'player2':
                if ($item['competitiontype'] == 2)
                {
                    return $item['player2name'] . "/" . $item['partner2name'];
                }
                else
                {
                    return $item['player2name'];
                }

            case 'set1':
                return $item['player1set1'] . ":" . $item['player2set1'];
            case 'set2':
                return $item['player1set2'] . ":" . $item['player2set2'];
            case 'set3':
                if ($item['player1set3'] != $item['player2set3'])
                {
                    return $item['player1set3'] . ":" . $item['player2set3'];
                }
                else
                {
                    return '';
                }
            case 'points':
                return $item['player1points'] . ":" . $item['player2points'];
            case 'sets':
                return $item['player1sets'] . ":" . $item['player2sets'];

            case "ratingflag":
                return $item[$column_name];

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
            $competitionID = $_GET['competitionID'];
            return "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-result&resultID=" .
                $item['id']) . "&competitionID=$competitionID'>" . __('Details', 'player-leaderboard') . "</a>" .
                " | " .
                "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-result&mode=edit&resultID=" .
                $item['id']) . "&competitionID=$competitionID'>" . __('Edit', 'player-leaderboard') . "</a>";
        }
        else
        {
            return "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-result&resultID=" .
                $item['id']) . "'>" . __('Details', 'player-leaderboard') . "</a>" .
                " | " .
                "<a class='page-action' href='" .
                admin_url("admin.php?page=player-leaderboard-result$mode=edit&resultID=" .
                $item['id']) . "'>" . __('Edit', 'player-leaderboard') . "</a>";
        }
    }

    /**
     * Render the bulk action check box
     *
     * @since    1.0.0
     * @access   public
     */
	public function column_cb($item)
    {
		return sprintf( '<input type="checkbox" class="bulk-item-selection" name="bulk-item-selection[]" value="%s" />', $item['id'] );
	}

    /**
     * Load the number of events from the wordpress database
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_numberof_results()
    {
        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_result");
    }

    /**
     * Load the events from the wordpress database
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_results($current_page, $per_page)
    {
        global $wpdb;

        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";

        $sql = "SELECT  result.*, competition.name AS competitionname, competition.bestof AS competitionbestof,
                        competition.type AS competitiontype,
                        p1.name AS player1name,
                        p2.name AS player2name,
                        p3.name AS partner1name,
                        p4.name AS partner2name
                FROM $table_result AS result
                JOIN  $table_competition AS competition ON competition.id = result.competitionid
                LEFT JOIN $table_player AS p1 ON p1.id = result.player1id
                LEFT JOIN $table_player AS p2 ON p2.id = result.player2id
                LEFT JOIN $table_player AS p3 ON p3.id = result.partner1id
                LEFT JOIN $table_player AS p4 ON p4.id = result.partner2id";

        if (isset($_POST['competitionID']))
        {
            $sql .= " WHERE (competition.id LIKE '%" . esc_sql($_POST['competitionID']) . "%')";
        }
        else if (isset($_GET['competitionID']))
        {
            $sql .= " WHERE (competition.id LIKE '%" . esc_sql($_GET['competitionID']) . "%')";
        }

        if (isset($_POST['s']))
        {
            $searchstring = sanitize_text_field($_POST['s']);
        }

        if (isset($_GET['s']))
        {
            $searchstring = sanitize_text_field($_POST['s']);
        }

        if (isset($searchstring) == true)
        {
            if (strpos($sql, 'WHERE') == false)
            {
                $sql .= " WHERE (";
            }
            else
            {
                $sql .= " AND (";
            }
            $sql .= " (p1.name LIKE '%" . esc_sql($searchstring) . "%')";
            $sql .= " OR (p2.name LIKE '%" . esc_sql($searchstring) . "%')";
            $sql .= " OR (p3.name LIKE '%" . esc_sql($searchstring) . "%')";
            $sql .= " OR (p4.name LIKE '%" . esc_sql($searchstring) . "%'))";
        }

        if (isset($_POST['competitionday']))
        {
            $competitionday = sanitize_text_field($_POST['competitionday']);
        }

        if (isset($_GET['competitionday']))
        {
            $competitionday = sanitize_text_field($_GET['competitionday']);
        }

        if (isset($competitionday))
        {
            if (strpos($sql, 'WHERE') == false)
            {
                $sql .= " WHERE (";
            }
            else
            {
                $sql .= " AND (";
            }

            $sql .= " result.date = '" . esc_sql($competitionday) . "')";
        }

        if (!empty( $_REQUEST['orderby']))
        {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty( $_REQUEST['order'])? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($current_page - 1) * $per_page;

        $wpdb->show_errors();
        $results = $wpdb->get_results($sql, ARRAY_A);
        $wpdb->hide_errors();

        if ((sizeof($results) == 0) && (isset($competitionday)))
        {
            unset($_POST['competitionday']);
            return $this->get_results($current_page, $per_page);
        }
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
     * Load the competitions days from the wordpress database
     *
     * @since    1.0.0
     * @access   public
     */
    public function get_competition_days($competitionID)
    {
        global $wpdb;
        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        return $wpdb->get_results("SELECT DISTINCT date FROM $table_result
            WHERE competitionid = $competitionID ORDER BY date");
    }

    /**
     * Create a CSV for the currently shown results
     *
     * @since    1.0.0
     * @access   public
     */
    public function generate_csv()
    {
        $file = fopen('php://output', 'w');
        Player_Leaderboard_Export::results_csv($file);
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
                        $table_result = "{$wpdb->prefix}player_leaderboard_result";
                        $wpdb->query("DELETE FROM $table_result WHERE id IN ($selecteditems)");
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
