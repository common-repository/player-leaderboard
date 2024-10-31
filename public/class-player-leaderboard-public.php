<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.software-kunze.de
 * @since      1.0.0
 *
 * @package    Player-Leaderboard
 * @subpackage player-leaderboard/public
 * @author     Alexander Kunze
 */

if (!class_exists('Player_Leaderboard_Calulator'))
{
    require_once( plugin_dir_path(__FILE__) . '../includes/class-player-leaderboard-calculator.php' );
}

class Player_Leaderboard_Public
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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles($page)
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/player-leaderboard-public.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-ui', plugin_dir_url(__FILE__) . 'css/jquery-ui.min.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-ui-theme', plugin_dir_url(__FILE__) . 'css/jquery-ui.theme.min.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-ui-struct', plugin_dir_url(__FILE__) . 'css/jquery-ui.structure.min.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($page)
    {
        wp_enqueue_script('jquery-ui-dialog', false, array('jquery'));

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/player-leaderboard-public.js', array('jquery'), $this->version, false);

        // Localize the script with new data
        $translation_array = array(
            'yes' => __( 'Yes', 'player-leaderboard' ),
            'no' => __( 'No', 'player-leaderboard' )
        );

        wp_localize_script($this->plugin_name, "translations", $translation_array);
    }

    /**
     * Load competition by ID
     *
     * @since    1.0.0
     */
    public function get_competition_by_id($competitionID)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        return $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");
    }

    /**
     * Load standing for an competition
     *
     * @since    1.0.0
     */
    public function get_standings($competitionID)
    {
        $standings = Player_Leaderboard_Calulator::getStandings($competitionID);
        usort($standings, array($this, "compare_standing"));
        return $standings;
    }

    /**
     * Load all games for an competition
     *
     * @since    1.0.0
     */
    public function get_games($competitionID)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
		$table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $duels = array();

        $players = $wpdb->get_results("SELECT id, name, rating
            FROM $table_player
            WHERE competitionid = $competitionID", OBJECT);

        $results =$wpdb->get_results("SELECT * FROM $table_result
            WHERE competitionid = $competitionID", OBJECT);

        foreach ($players as $player1)
        {
            $games = array();

            foreach ($players as $player2)
            {
                foreach ($results as $result)
                {
                    if (((($result->player1id == $player1->id) || ((isset($result->partner1id)) && ($result->partner1id == $player1->id))) &&
                         (($result->player2id == $player2->id) || ((isset($result->partner2id)) && ($result->partner2id == $player2->id)))) ||
                        ((($result->player2id == $player1->id) || ((isset($result->partner2id)) && ($result->partner2id == $player1->id))) &&
                         (($result->player1id == $player2->id) || ((isset($result->partner1id)) && ($result->partner1id == $player2->id)))))
                    {
                        if (!in_array($result, $games))
                        {
                            $games[] = $result;
                        }
                    }
                }
            }

            usort($games, array($this, "compare_result"));
            array_push($duels, array("name" => $player1->name, "id" => $player1->id, "rating" => $player1->rating, "data" => $games));
        }

        return $duels;
    }

    function compare_standing($standing1 , $standing2)
    {
        if ($standing1->gameswon > $standing2->gameswon)
        {
            return -1;
        }
        else if ($standing2->gameswon > $standing1->gameswon)
        {
            return 1;
        }
        else
        {
            if ($standing1->gameslost < $standing2->gameslost)
            {
                return -1;
            }
            else if ($standing2->gameslost < $standing1->gameslost)
            {
                return 1;
            }
            else
            {
                if ($standing1->setswon > $standing2->setswon)
                {
                    return -1;
                }
                else if ($standing2->setswon > $standing1->setswon)
                {
                    return 1;
                }
                else
                {
                    if ($standing1->pointswon > $standing2->pointswon)
                    {
                        return -1;
                    }
                    else if ($standing2->pointswon > $standing1->pointswon)
                    {
                        return 1;
                    }
                }
            }
        }

        return 0;
    }

    function compare_result($result1 , $result2)
    {
        if ($result1->date > $result2->date)
        {
            return -1;
        }
        else if ($result2->date > $result1->date)
        {
            return 1;
        }

        return 0;
    }

    /**
     * Load ranking for an competition
     *
     * @since    1.0.0
     */
    public function get_ranking($competitionID, $rankingview)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        if ($competition->type == 2)
        {
            $ranking = $wpdb->get_results("SELECT * FROM $table_player WHERE competitionid = $competitionID");
            switch ($rankingview)
            {
                case 'quotient':
                    usort($ranking, array($this, "compare_ranking_quotient"));
                    break;
                case 'rating':
                    usort($ranking, array($this, "compare_ranking_ratingpoints"));
                    break;
                case 'ratings':
                    usort($ranking, array($this, "compare_ranking_ratingpoints"));
                    break;
                case 'average':
                    usort($ranking, array($this, "compare_ranking_average"));
                    break;
                case 'points':
                default:
                    usort($ranking, array($this, "compare_ranking_points"));
                    break;
            }
        }
        else
        {
            $ranking = Player_Leaderboard_Calulator::getRankingSingle($competitionID);
            usort($ranking, array($this, "compare_ranking"));
        }
        return $ranking;
    }

    /**
     * Compare ranking based on the collected points
     *
     * @since    1.0.0
     */
    function compare_ranking($ranking1 , $ranking2)
    {
        if ($ranking1->points > $ranking2->points)
        {
               return -1;
        }

        if ($ranking1->points < $ranking2->points)
        {
               return 1;
        }

        return 0;
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

    /**
     * Compare ranking based on the calculated quotient
     *
     * @since    1.0.0
     */
    function compare_ranking_ratings($ranking1 , $ranking2)
    {
        $quotient1 = ($ranking1->ratings > 0)? ($ranking1->ratingpoints / $ranking1->ratings) : 0;
        $quotient2 = ($ranking2->ratings > 0)? ($ranking2->ratingpoints / $ranking2->ratings) : 0;
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

    /**
     * Compare ranking based on the calculated quotient
     *
     * @since    1.0.0
     */
    function compare_ranking_rating($ranking1 , $ranking2)
    {
        $quotient1 = ($ranking1->ratings > 0)? ($ranking1->ratingpoints / $ranking1->ratings) : 0;
        $quotient2 = ($ranking2->ratings > 0)? ($ranking2->ratingpoints / $ranking2->ratings) : 0;
        if ($ranking1->rating > $ranking2->rating)
        {
               return -1;
        }

        if ($ranking1->rating < $ranking2->rating)
        {
               return 1;
        }

        return 0;
    }

    /**
     * Compare ranking based on the collected points
     *
     * @since    1.0.0
     */
    function compare_ranking_ratingpoints($ranking1 , $ranking2)
    {
        if ($ranking1->ratingpoints > $ranking2->ratingpoints)
        {
               return -1;
        }

        if ($ranking1->ratingpoints < $ranking2->ratingpoints)
        {
               return 1;
        }

        return 0;
    }

    /**
     * Compare ranking based on the calculated quotient
     *
     * @since    1.0.0
     */
    function compare_ranking_average($ranking1 , $ranking2)
    {
        $quotient1 = ($ranking1->duels > 0)? ($ranking1->points / $ranking1->duels) : 0;
        $quotient2 = ($ranking2->duels > 0)? ($ranking2->points / $ranking2->duels) : 0;
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

    /**
     * Compare ranking based on the calculated quotient
     *
     * @since    1.0.0
     */
    function compare_ranking_points($ranking1 , $ranking2)
    {
        if ($ranking1->points > $ranking2->points)
        {
               return -1;
        }

        if ($ranking1->points < $ranking2->points)
        {
               return 1;
        }

        return 0;
    }

    /**
     * Load matrix for an competition
     *
     * @since    1.0.0
     */
    public function get_matrix($competitionID, $type = "games")
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
		$table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $matrix = array();

        $players = $wpdb->get_results("SELECT id, name
            FROM $table_player
            WHERE competitionid = $competitionID", OBJECT);

        $results =$wpdb->get_results("SELECT * FROM $table_result
            WHERE competitionid = $competitionID", OBJECT);

        foreach ($players as $player1)
        {
            $datalist = array();

            foreach ($players as $player2)
            {
                $data1 = 0;
                $data2 = 0;

                foreach ($results as $result)
                {
                    if ((($result->player1id == $player1->id) ||
                         ($result->partner1id == $player1->id)) &&
                        (($result->player2id == $player2->id) ||
                         ($result->partner2id == $player2->id)))
                    {
                         switch ($type)
                         {
                             case "games":
                                $data1++;
                                break;

                             case "sets":
                                $data1 += $result->player1sets;
                                break;

                             case "points":
                                $data1 += $result->player1points;
                                break;

                             case "rating":
                                if ($result->player1sets > $result->player2sets)
                                {
                                    $data1++;
                                }
                                else if ($result->player1sets < $result->player2sets)
                                {
                                    $data2++;
                                }
                                break;
                         }
                    }
                    else if ((($result->player2id == $player1->id) ||
                              ($result->partner2id == $player1->id)) &&
                             (($result->player1id == $player2->id) ||
                              ($result->partner1id == $player2->id)))
                    {
                         switch ($type)
                         {
                             case "games":
                                $data1++;
                                break;

                             case "sets":
                                $data1 += $result->player2sets;
                                break;

                             case "points":
                                $data1 += $result->player2points;
                                break;

                             case "rating":
                                if ($result->player2sets > $result->player1sets)
                                {
                                    $data1++;
                                }
                                else if ($result->player2sets < $result->player1sets)
                                {
                                    $data2++;
                                }
                                break;
                         }
                    }
                }

                if ($type == "rating")
                {
                     if (($data1 > 0) || ($data2 > 0))
                    {
                        $data1->points += (($data1  / ($data1 + $data2)) * $competition->rating);
                    }
                    else
                    {
                        // No game played
                        $data1 = 0.0;
                    }
                }
                
                array_push($datalist, $data1);
            }

            array_push($matrix, array(
                "name" => $player1->name,
                "id" => $player1->id,
                "data" => $datalist));
        }

        return $matrix;
    }

    /**
     * Get max matrix result.
     * This stuff is used the calculate the table cell class, which is used
     * to render the cell in a specific color.
     *
     * @since    1.0.0
     */
    public function get_matrix_max($results)
    {
        $max = 0;
        for ($i = 0; $i < sizeof($results); $i++)
        {
            $row = $results[$i];
            for ($j = 0; $j < sizeof($row['data']); $j++)
            {
                $data = $row['data'][$j] ;
                if ($data > $max)
                {
                    $max = $data;
                }
            }
        }
        return $max;
    }

    /**
     * Calculate the table cell class based on the value and the max value used
     * in the matrix.
     *
     * 0                => pl-matrix-zero
     * 1..max/3         => pl-matrix-low
     * max/3..2*max/3   => pl-matrix-mid
     * 2*max/3.. max    => pl-matrix-high
     * max              => pl-matrix-max
     *
     * @since    1.0.0
     */
    public function get_matrix_class($value, $max)
    {
        if ($value == 0)
        {
            return "zero";
        }
        else if ($value == $max)
        {
            return "max";
        }
        else if ($value <= ($max / 3))
        {
            return "low";
        }
        else if ($value <= ($max / 3 * 2))
        {
            return "mid";
        }
        else
        {
            return "high";
        }
    }

    /**
     * Load all games for an competition
     *
     * @since    1.0.0
     */
    public function get_games_matrix($competitionID)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
		$table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $matrix = array();

        $players = $wpdb->get_results("SELECT id, name
            FROM $table_player
            WHERE competitionid = $competitionID", OBJECT);

        $results =$wpdb->get_results("SELECT * FROM $table_result
            WHERE competitionid = $competitionID ORDER BY date DESC", OBJECT);

        foreach ($players as $player1)
        {
            $duellist = array();

            foreach ($players as $player2)
            {
                $games = array();

                foreach ($results as $result)
                {
                    if (((($result->player1id == $player1->id) || ($result->partner1id == $player1->id)) &&
                         (($result->player2id == $player2->id) || ($result->partner2id == $player2->id))) ||
                        ((($result->player2id == $player1->id) || ($result->partner2id == $player1->id)) &&
                         (($result->player1id == $player2->id) || ($result->partner1id == $player2->id))))
                    {
                        $games[] = $result;
                    }
                }

                array_push($duellist, $games);
            }

            array_push($matrix, $duellist);
        }

        return $matrix;
    }

    /**
     * Create the games standing detail dialogs
     *
     * @since    1.0.0
     */
    public function show_standing_detail_dialogs($competition, $allgames)
    {
        for ($i = 0; $i < sizeof($allgames); $i++)
        {
            $games = $allgames[$i];
            echo "<div id='pl-results-" . $games['id'] . "' class='pl-results' ";
            echo "title='" . __('Results of', 'player-leaderboard') . " " . $allgames[$i]['name'];
            echo "' style='display:none;'>";
            echo "<table class='pl-results-table'>";
            echo "<tbody>";

            $years = [];
            $months = [];
            for ($k = 0; $k < sizeof($games['data']); $k++)
            {
                $date = DateTime::createFromFormat('Y-m-d', $games['data'][$k]->date);
                $year = $date->format('Y');
                $month = $date->format('Y-m');

                if (!array_key_exists($year, $years))
                {
                    $years[$year] = 1;
                }
                if (!array_key_exists($month, $months))
                {
                    $months[$month] = 1;
                }
            }

            $yearmode = 1;
            $monthmode = 1;
            $lastdate = '';
            $lastmonth = 0;
            $lastyear = 0;

            for ($k = 0; $k < sizeof($games['data']); $k++)
            {
                $game = $games['data'][$k];

                if ($game->date != $lastdate)
                {
                    $date = DateTime::createFromFormat('Y-m-d', $games['data'][$k]->date);
                    $currentyear = $date->format('Y');
                    $currentmonth = $date->format('m');
                    // $currentday = $date->format('d');

                    if ($lastdate != '')
                    {
                        // close the results table
                        echo "</tbody>";
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    if (($monthmode == 1) && ($lastmonth != $currentmonth) && ($lastmonth > 0))
                    {
                        // Close the month table
                        echo "</tbody>";
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";                    }

                    if (($yearmode == 1) && ($lastyear != $currentyear) && ($lastyear > 0))
                    {
                        // Close the year table
                        echo "</tbody>";
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    if (($yearmode == 1) && ($lastyear != $currentyear))
                    {
                        echo "<tr>";
                        echo "<td class='pl-text-left'>";
                        echo "<img src='" . plugin_dir_url( __FILE__ ) . "images/plus.png' class='pl-details pl-plus-year' width='12' height='12' />";
                        echo "<img src='" . plugin_dir_url( __FILE__ ) . "images/minus.png' class='pl-details pl-minus-year' width='12' height='12' />";
                        echo  "&nbsp;" . $currentyear . "</td>";
                        echo "</tr>";
                        echo "<tr class='pl-results-year'>";
                        echo "<td>";
                        echo "<table class='pl-results-year-table'>";
                        echo "<tbody>";
                        $lastyear = $currentyear;
                    }

                    if (($monthmode == 1) && ($lastmonth!= $currentmonth))
                    {
                        echo "<tr>";
                        echo "<td class='pl-text-left'>";
                        echo "<img src='" . plugin_dir_url( __FILE__ ) . "images/plus.png' class='pl-details pl-plus-month' width='12' height='12' />";
                        echo "<img src='" . plugin_dir_url( __FILE__ ) . "images/minus.png' class='pl-details pl-minus-month' width='12' height='12' />";
                        echo  "&nbsp;" . $currentmonth . "</td>";
                        echo "</tr>";
                        echo "<tr class='pl-results-month'>";
                        echo "<td>";
                        echo "<table class='pl-results-month-table'>";
                        echo "<tbody>";
                        $lastmonth = $currentmonth;
                    }

                    echo "<tr>";
                    echo "<td class='pl-text-left'>";
                    echo "<img src='" . plugin_dir_url( __FILE__ ) . "images/plus.png' class='pl-details pl-plus' width='12' height='12' />";
                    echo "<img src='" . plugin_dir_url( __FILE__ ) . "images/minus.png' class='pl-details pl-minus' width='12' height='12' />";
                    echo  "&nbsp;" . mysql2date(__('Y/m/d'), $game->date) . "</td>";
                    echo "</tr>";

                    echo "<tr class='pl-results-row'>";
                    echo "<td>";
                    echo "<table class='pl-results-row-table'>";
                    echo "<tbody>";

                    $lastdate = $game->date;
                }

                echo "<tr>";
                if (($game->player1id == $games['id']) || ((isset($game->partner1id)) && ($game->partner1id == $games['id'])))
                {
                     if ($competition->type == 2)
                     {
                        echo "<td colspan=2>";
                    }
                    else
                    {
                        echo "<td style='min-width:50px'>";
                    }

                    $team1rating = 0;
                    for ($j = 0; $j < sizeof($allgames); $j++)
                    {
                        if ($allgames[$j]['id'] == $game->player1id)
                        {
                            $team1rating += $allgames[$j]['rating'];
                            echo $allgames[$j]['name'];
                            // echo " (" . $allgames[$j]['rating'] . ")";
                        }
                    }
                    if (isset($game->partner1id))
                    {
                        echo " / ";
                        for ($j = 0; $j < sizeof($allgames); $j++)
                        {
                            if ($allgames[$j]['id'] == $game->partner1id)
                            {
                                $team1rating += $allgames[$j]['rating'];
                                echo $allgames[$j]['name'];
                                // echo " (" . $allgames[$j]['rating'] . ")";
                            }
                        }
                    }
                    echo "</td><td>&nbsp;-&nbsp;</td>";

                    if ($competition->type == 2)
                    {
                        echo "<td colspan=2>";
                    }
                    else
                    {
                        echo "<td style='min-width:50px'>";
                    }
                    $team2rating = 0;
                    for ($j = 0; $j < sizeof($allgames); $j++)
                    {
                        if ($allgames[$j]['id'] == $game->player2id)
                        {
                            $team2rating += $allgames[$j]['rating'];
                            echo $allgames[$j]['name'];
                            // echo " (" . $allgames[$j]['rating'] . ")";
                        }
                    }
                    if (isset($game->partner2id))
                    {
                        echo " / ";
                        for ($j = 0; $j < sizeof($allgames); $j++)
                        {
                            if ($allgames[$j]['id'] == $game->partner2id)
                            {
                                $team2rating += $allgames[$j]['rating'];
                                echo $allgames[$j]['name'];
                                // echo " (" . $allgames[$j]['rating'] . ")";
                            }
                         }
                    }
                    echo "</td>";

                    if ($competition->type == 2)
                    {
                        echo "</tr><tr><td>";
                        if ($game->player1sets > $game->player2sets)
                        {
                            if ($team1rating > 0)
                            {
                                echo "(" . round($team2rating / $team1rating * $competition->rating, 2) . ")";
                            }
                        }
                        else if ($game->player1sets > 0)
                        {
                            if ($team1rating > 0)
                            {
                                echo "(" . round($team2rating / $team1rating * $competition->rating / 3, 2). ")";
                            }
                        }
                        echo "</td><td colspan=4>";

                        echo $game->player1set1 . ":" .  $game->player2set1 . "&nbsp;";
                        echo $game->player1set2 . ":" .  $game->player2set2 . "&nbsp;";
                        if ($game->player1set3 != $game->player2set3)
                        {
                            echo $game->player1set3 . ":" .  $game->player2set3;
                        }
                        echo "</td>";
                    }
                    else
                    {
                        echo "<td>" .  $game->player1set1 . ":" .  $game->player2set1 . "</td>";
                        echo "<td>" .  $game->player1set2 . ":" .  $game->player2set2 . "</td>";
                        if ($game->player1set3 != $game->player2set3)
                        {
                            echo "<td>" .  $game->player1set3 . ":" .  $game->player2set3 . "</td>";
                        }
                        else
                        {
                            echo "<td></td>";
                        }
                    }
                }
                else
                {
                     if ($competition->type == 2)
                     {
                        echo "<td colspan=2>";
                    }
                    else
                    {
                        echo "<td>";
                    }
                    $team2rating = 0;
                    for ($j = 0; $j < sizeof($allgames); $j++)
                    {
                        if ($allgames[$j]['id'] == $game->player2id)
                        {
                            $team2rating += $allgames[$j]['rating'];
                            echo $allgames[$j]['name'];
                            // echo " (" . $allgames[$j]['rating'] . ")";
                        }
                    }
                    if (isset($game->partner2id))
                    {
                        echo " / ";
                        for ($j = 0; $j < sizeof($allgames); $j++)
                        {
                            if ($allgames[$j]['id'] == $game->partner2id)
                            {
                                $team2rating += $allgames[$j]['rating'];
                                echo $allgames[$j]['name'];
                                // echo " (" . $allgames[$j]['rating'] . ")";
                            }
                        }
                    }
                    echo "</td><td>&nbsp;-&nbsp;</td>";

                    if ($competition->type == 2)
                    {
                        echo "<td colspan=2>";
                    }
                    else
                    {
                        echo "<td>";
                    }

                    $team1rating = 0;
                    for ($j = 0; $j < sizeof($allgames); $j++)
                    {
                        if ($allgames[$j]['id'] == $game->player1id)
                        {
                            $team1rating += $allgames[$j]['rating'];
                            echo $allgames[$j]['name'];
                            // echo " (" . $allgames[$j]['rating'] . ")";
                        }
                    }
                    if (isset($game->partner1id))
                    {
                        echo " / ";
                        for ($j = 0; $j < sizeof($allgames); $j++)
                        {
                            if ($allgames[$j]['id'] == $game->partner1id)
                            {
                                $team1rating += $allgames[$j]['rating'];
                                echo $allgames[$j]['name'];
                                // echo " (" . $allgames[$j]['rating'] . ")";
                            }
                         }
                    }

                    echo "</td>";
                    if ($competition->type == 2)
                    {
                        echo "</tr><tr><td>";
                        if ($game->player2sets > $game->player1sets)
                        {
                            if ($team2rating > 0)
                            {
                                echo "(" . round($team1rating / $team2rating * $competition->rating, 2) . ")";
                            }
                        }
                        else if ($game->player2sets > 0)
                        {
                            if ($team2rating > 0)
                            {
                                echo "(" . round($team1rating / $team2rating * $competition->rating / 3, 2) . ")";
                            }
                        }
                        echo "</td><td colspan=4>";

                        echo $game->player2set1 . ":" .  $game->player1set1 . "&nbsp;";
                        echo $game->player2set2 . ":" .  $game->player1set2 . "&nbsp;";
                        if ($game->player2set3 != $game->player1set3)
                        {
                            echo $game->player2set3 . ":" .  $game->player1set3;
                        }
                        echo "</td>";
                    }
                    else
                    {
                        echo "<td>" .  $game->player2set1 . ":" .  $game->player1set1 . "</td>";
                        echo "<td>" .  $game->player2set2 . ":" .  $game->player1set2 . "</td>";
                        if ($game->player2set3 != $game->player1set3)
                        {
                            echo "<td>" .  $game->player2set3 . ":" .  $game->player1set3 . "</td>";
                        }
                        else
                        {
                            echo "<td></td>";
                        }
                    }
                }
                echo "</tr>";
            }

            // Close the results table
            echo "</tbody>";
            echo "</table>";
            echo "</td>";
            echo "</tr>";

            if ($monthmode == 1)
            {
                // Close the month table
                echo "</tbody>";
                echo "</table>";
                echo "</td>";
                echo "</tr>";                    }

            if ($yearmode == 1)
            {
                // Close the year table
                echo "</tbody>";
                echo "</table>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }
    }

    /**
     * Create the games matrix detail dialogs
     *
     * @since    1.0.0
     */
    public function show_matrix_detail_dialogs($competitionID, $results)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $allgames = $this->get_games_matrix($competitionID);

        for ($i = 0; $i < sizeof($allgames); $i++)
        {
            $duelist = $allgames[$i];
            for ($j = 0; $j < sizeof($duelist); $j++)
            {
                $games1 = 0;
                $games2 = 0;
                $sets1 = 0;
                $sets2 = 0;
                $points1 = 0;
                $points2 = 0;

                $games = $duelist[$j] ;
                $dialogid = 'pl-results-' . $results[$i]['id'] . '-' . $results[$j]['id'];
                $title = __('Duels', 'player-leaderboard') . ' '
                    . esc_html($this->get_player_name($results, $results[$i]['id'])) . ' - '
                    . esc_html($this->get_player_name($results, $results[$j]['id']));
                echo '<div id="' . $dialogid . '" class="pl-results pl-matrix-details-wrapper" title="' . $title . '" style="display:none;">';
                echo '<div class="pl-matrix-details-scroll">';
                echo '<table class="pl-matrix-details ';
                if ($competition->type == 2)
                {
                    echo 'pl-matrix-details-double';
                }
                else
                {
                    echo 'pl-matrix-details-single';
                }
                echo '">';
                /*
                echo '<thead><tr>';
                if ($competition->type == 2)
                {
                    echo "<th><span class='pl-matrix-details-header-text'>" . __('Date', 'player-leaderboard') . "</span></th>";
                    echo "<th><span class='pl-matrix-details-header-text'>" . __('Set1', 'player-leaderboard') . "</span></th>";
                    echo "<th><span class='pl-matrix-details-header-text'>" . __('Set2', 'player-leaderboard') . "</span></th>";
                    echo "<th><span class='pl-matrix-details-header-text'>" . __('Set3', 'player-leaderboard') . "</span></th>";
                }
                else
                {
                    echo '<th style="width: 50%;">' . $results[$i]['name'] . '</th>';
                    echo '<th colspan="3">' . $results[$j]['name'] . '</th>';
                }
                echo '</tr></thead>';
                */
                echo '<tbody>';
                for ($k = 0; $k < sizeof($games); $k++)
                {
                    $game = $games[$k] ;
                    echo '<tr>';
                    echo '<td>' .mysql2date(__('Y/m/d'), $game->date) . '</td>';
                    if (($game->player1id == $results[$i]['id']) ||
                        ((isset($game->partner1id)) && ($game->partner1id == $results[$i]['id'])))
                    {
                        echo '<td colspan=3>';
                        if ($game->player1id == $results[$i]['id'])
                        {
                            echo '<strong>' . esc_html($this->get_player_name($results, $game->player1id)) . '</strong>';
                        }
                        else
                        {
                            echo esc_html($this->get_player_name($results, $game->player1id));
                        }

                        if ($competition->type == 2)
                        {
                            if (isset($game->partner1id))
                            {
                                echo ' / ';
                                if ($game->partner1id == $results[$i]['id'])
                                {
                                    echo '<strong>' . esc_html($this->get_player_name($results, $game->partner1id)) . '</strong>';
                                }
                                else
                                {
                                    echo esc_html($this->get_player_name($results, $game->partner1id));
                                }
                            }
                            echo "</td></tr><tr><td></td>";
                            echo '<td colspan=3>';
                        }
                        else
                        {
                            echo ' - ';
                        }

                        if ($game->player2id == $results[$j]['id'])
                        {
                            echo '<strong>' . esc_html($this->get_player_name($results, $game->player2id)) . '</strong>';
                        }
                        else
                        {
                            echo esc_html($this->get_player_name($results, $game->player2id));
                        }

                        if ($competition->type == 2)
                        {
                            if (isset($game->partner2id))
                            {
                                echo ' / ';
                                if ($game->partner2id == $results[$j]['id'])
                                {
                                    echo '<strong>' . esc_html($this->get_player_name($results, $game->partner2id)) . '</strong>';
                                }
                                else
                                {
                                    echo esc_html($this->get_player_name($results, $game->partner2id));
                                }
                            }
                        }
                        echo "</td></tr><tr><td></td>";

                        $games1 += ($game->player1sets > $game->player2sets)? 1:0;
                        $games2 += ($game->player1sets < $game->player2sets)? 1:0;
                        $sets1 += $game->player1sets;
                        $sets2 += $game->player2sets;
                        $points1 += $game->player1points;
                        $points2 += $game->player2points;

                        echo '<td>' . intval($game->player1set1) . ':' . intval($game->player2set1) . '</td>';
                        echo '<td>' . intval($game->player1set2) . ':' . intval($game->player2set2) . '</td>';
                        if ($game->player1set3 != $game->player2set3)
                        {
                            echo '<td>' . intval($game->player1set3) . ':' . intval($game->player2set3) . '</td>';
                        }
                        else
                        {
                            echo '<td>' .'</td>';
                        }
                    }
                    else
                    {
                        echo '<td colspan=3>';
                        if ($game->player2id == $results[$i]['id'])
                        {
                            echo '<strong>' . esc_html($this->get_player_name($results, $game->player2id)) . '</strong>';
                        }
                        else
                        {
                            echo esc_html($this->get_player_name($results, $game->player2id));
                        }

                        if ($competition->type == 2)
                        {
                            if (isset($game->partner2id))
                            {
                                echo ' / ';
                                if ($game->partner2id == $results[$i]['id'])
                                {
                                    echo '<strong>' . esc_html($this->get_player_name($results, $game->partner2id)) . '</strong>';
                                }
                                else
                                {
                                    echo esc_html($this->get_player_name($results, $game->partner2id));
                                }
                            }

                            echo "</td></tr><tr><td></td>";
                            echo '<td colspan=3>';
                        }
                        else
                        {
                            echo ' - ';
                        }

                        if ($game->player1id == $results[$j]['id'])
                        {
                            echo '<strong>' . esc_html($this->get_player_name($results, $game->player1id)) . '</strong>';
                        }
                        else
                        {
                            echo esc_html($this->get_player_name($results, $game->player1id));
                        }

                        if ($competition->type == 2)
                        {
                            if (isset($game->partner1id))
                            {
                                echo ' / ';
                                if ($game->partner1id == $results[$j]['id'])
                                {
                                    echo '<strong>' . esc_html($this->get_player_name($results, $game->partner1id)) . '</strong>';
                                }
                                else
                                {
                                    echo esc_html($this->get_player_name($results, $game->partner1id));
                                }
                            }
                        }

                        echo "</td></tr><tr><td></td>";

                        $games1 += ($game->player2sets > $game->player1sets)? 1:0;
                        $games2 += ($game->player2sets < $game->player1sets)? 1:0;
                        $sets1 += $game->player2sets;
                        $sets2 += $game->player1sets;
                        $points1 += $game->player2points;
                        $points2 += $game->player1points;

                        echo '<td>' . intval($game->player2set1) . ':' . intval($game->player1set1) . '</td>';
                        echo '<td>' . intval($game->player2set2) . ':' . intval($game->player1set2) . '</td>';
                        if ($game->player2set3 != $game->player1set3)
                        {
                            echo '<td>' . intval($game->player2set3) . ':' . intval($game->player1set3) . '</td>';
                        } else
                        {
                            echo '<td>' .'</td>';
                        }
                    }

                    echo '</tr>';
                 }

                echo '<tr><td colspan=4 height=20></td></tr></tbody></table></div><div>';
                echo '<table class="pl-matrix-details-footer"><tbody>';
                echo '<tr>';
                echo '<td>' . __('Games', 'player-leaderboard') . '</td>';
                echo '<td colspan=3>' . intval($games1) . ':' . intval($games2) . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td>' . __('Sets', 'player-leaderboard') . '</td>';
                echo '<td colspan=3>' . intval($sets1) . ':' . intval($sets2) . '</td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td>' . __('Points', 'player-leaderboard') . '</td>';
                echo '<td colspan=3>' . intval($points1) . ':' . intval($points2) . '</td>';
                echo '</tr>';
                if ($competition->type != 2)
                {
                    echo '<tr>';
                    echo '<td>' . __('Rating', 'player-leaderboard') . '</td>';
                    if (($games1 + $games2) > 0)
                    {
                        echo '<td colspan=3>' . round($games1 * $competition->rating / ($games1 + $games2),1) . ' : ' . round($games2 * $competition->rating / ($games1 + $games2),1) . '</td>';
                    }
                    else
                    {
                        echo '<td colspan=3>' . intval($competition->noduelpoints) . ' : ' . intval($competition->noduelpoints) . '</td>';
                    }
                    echo '</tr>';
                }

                echo '</tbody></table></div></div>';
            }
        }
    }

   /**
     * Create the games matrix styles
     *
     * @since    1.0.0
     */
    public function create_matrix_styles($fontweight, $width)
    {
        $fontfactor = intval($fontweight) / 2;
        echo '<style>';
        echo '.div-pl-table {';
        echo '   font-size: calc(.' . $fontfactor . 'vw + .' . $fontfactor . 'vh + .' . $fontfactor . 'vmin);';
        echo '   display: table;';
        echo '   width: ' . esc_html($width) .';';
        echo '}';
        echo '</style>';
    }

   /**
     * Get player name by id from result list
     *
     * @since    1.0.0
     */
    private function get_player_name($results, $id)
    {
        for ($i = 0; $i < sizeof($results); $i++)
        {
            if ($results[$i]['id'] == $id)
            {
                return $results[$i]['name'];
            }
        }
    }

    /**
     * Create the block styles => Set the background color
     *
     * @since    1.0.0
     */
    private function create_styles($competitionID, $mode)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        echo "<style>\n";
        if ($mode == 'standing')
        {
            echo "table.pl-standings tr:nth-child(even) {";
            echo " background-color: " . $competition->bordercolor . "; }\n";

            echo "table.pl-standings {";
            echo " border: 1px solid " . $competition->bordercolor . "; }\n";

            echo "table.pl-standings thead th {";
            echo " color: " . $competition->textcolor . ";";
            echo " background: " . $competition->headercolor . "; }\n";
        }
        else if ($mode == 'ranking')
        {
            echo "table.pl-ranking tr:nth-child(even) {";
            echo " background-color: " . $competition->bordercolor . "; }\n";

            echo "table.pl-ranking {";
            echo " border: 1px solid " . $competition->bordercolor . "; }\n";

            echo "table.pl-ranking thead th {";
            echo " color: " . $competition->textcolor . ";";
            echo " background: " . $competition->headercolor . "; }\n";
        }
        else if ($mode == 'matrix')
        {
            echo ".div-pl-table-header-cell {";
            echo " color: " . $competition->textcolor . ";";
            echo " border: 1px solid " . $competition->bordercolor . ";";
            echo " background: " . $competition->headercolor . "; }\n";

            echo ".div-pl-table-body-cell {";
            echo " border: 1px solid " . $competition->bordercolor . "; }\n";

            echo ".div-pl-table-body-cell:first-child {";
            echo " color: " . $competition->textcolor . ";";
            echo " border: 1px solid " . $competition->bordercolor . ";";
            echo " background: " . $competition->headercolor . "; }\n";

            echo ".pl-matrix-empty {";
            echo " background-color: " . $competition->bordercolor . "; }\n";

            echo ".pl-matrix-zero {";
            echo " background-color: " . $competition->zerocolor . ";";
            echo " text-align: center; }";

            echo ".pl-matrix-low {";
            echo " background-color: " . $competition->lowcolor . ";";
            echo " text-align: center; }";

            echo ".pl-matrix-mid {";
            echo " background-color: " . $competition->midcolor . ";";
            echo " text-align: center; }";

            echo ".pl-matrix-high {";
            echo " background-color: " . $competition->highcolor . ";";
            echo " text-align: center; }";

            echo ".pl-matrix-max {";
            echo " background-color: " . $competition->maxcolor . ";";
            echo " text-align: center; }";
        }

        echo "</style>";
    }

    /**
     * Render the competitions participants form
     *
     * @since    1.0.0
     */
    public function public_shortcode($atts, $content = null)
    {
        global $cr_ids;
        $cr_ids[] = $atts['id'];

        $mode = 'standing';
    	if(isset($atts['mode']))
	    {
		    $mode = $atts['mode'];
    	}

        ob_start();
        include 'partials/player-leaderboard-public-'. $mode . '.php';
        return ob_get_clean();
    }
}

