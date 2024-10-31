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

class Player_Leaderboard_Export
{
    /**
     * Create a CSV for the currently shown players
     *
     * @since    1.0.0
     * @access   public
     */
    public static function competitions_csv($file, $exportplayers, $exportresults)
    {
        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";

        $sql = "SELECT  'COMPETITION' AS tag, name, kindofsport, type, gender, description, bestof,
            			victory, defeat, draw, rating, noduelpoints,
                        bonuspoints, gamefactor, setfactor, ratings,
                        deltarating, deltapercent, headercolor, bordercolor,
                        textcolor, zerocolor, maxcolor, lowcolor, midcolor,
                        highcolor
                FROM $table_competition";

        if (isset($_GET['competitionID']))
        {
            $competitionID = intval($_GET['competitionID']);
            $sql .= " WHERE id = $competitionID";
        }
        $sql .= " ORDER BY name ASC";

        $competitions = $wpdb->get_results($sql, ARRAY_A);

        $csv_header = array( 'COMPETITIONS', 'name', 'kindofsport', 'type', 'gender',
            'description', 'bestof', 'victory', 'defeat', 'draw', 'rating',
            'noduelpoints', 'bonuspoints', 'gamefactor', 'setfactor', 'ratings',
            'deltarating', 'deltapercent', 'headercolor', 'bordercolor',
            'textcolor', 'zerocolor', 'maxcolor', 'lowcolor', 'midcolor',
            'highcolor');


        fputcsv($file, $csv_header);
        foreach ($competitions as $competition)
        {
            fputcsv($file, $competition);
        }

        if (isset($exportplayers))
        {
            Player_Leaderboard_Export::players_csv($file, $exportresults);
        }
    }

    /**
     * Create a CSV for the currently shown players
     *
     * @since    1.0.0
     * @access   public
     */
    public static function players_csv($file, $exportresults)
    {
        global $wpdb;
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";

        $sql = "SELECT 'PLAYER' AS tag, name, givenname, comment, playerpass,birthday,
                       gender, address1, address2, phone, email, rating,
    			       duels, points, ratingpoints, quotient,
                       ratings, ranking
                FROM $table_player";

        if (isset($_GET['competitionID']))
        {
            $competitionID = intval($_GET['competitionID']);
            $sql .= " WHERE competitionid = $competitionID";
        }
        $sql .= " ORDER BY name ASC";

        $players = $wpdb->get_results($sql, ARRAY_A);

        $csv_header = array( 'PLAYERS', 'name', 'givenname', 'comment', 'playerpass',
            'birthday', 'gener', 'address1', 'address2', 'phone', 'email',
            'rating', 'duels', 'points', 'ratingpoints', 'quotient',
            'ratings', 'ranking');
        fputcsv($file, $csv_header);

        foreach ($players as $player)
        {
            fputcsv($file, $player);
        }

        if (isset($exportresults))
        {
            Player_Leaderboard_Export::results_csv($file);
        }
    }

    /**
     * Create a CSV for the currently show results
     *
     * @since    1.0.0
     * @access   public
     */
    public static function results_csv($file)
    {
        global $wpdb;

        $table_result = "{$wpdb->prefix}player_leaderboard_result";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";

        $sql = "SELECT  'RESULT' AS tag,
                        result.date,
                        p1.name AS player1name,
                        p3.name AS partner1name,
                        p2.name AS player2name,
                        p4.name AS partner2name,
                        result.player1set1,
                        result.player2set1,
                        result.player1set2,
                        result.player2set2,
                        result.player1set3,
                        result.player2set3,
                        result.player1set4,
                        result.player2set4,
                        result.player1set5,
                        result.player2set5,
                        result.player1set6,
                        result.player2set6,
                        result.player1set7,
                        result.player2set7,
                        result.ratingflag
                FROM $table_result AS result
                JOIN  $table_competition AS competition ON competition.id = result.competitionid
                LEFT JOIN $table_player AS p1 ON p1.id = result.player1id
                LEFT JOIN $table_player AS p2 ON p2.id = result.player2id
                LEFT JOIN $table_player AS p3 ON p3.id = result.partner1id
                LEFT JOIN $table_player AS p4 ON p4.id = result.partner2id";

        if (isset($_GET['competitionID']))
        {
            $sql .= " WHERE (competition.id LIKE '%" . esc_sql($_GET['competitionID']) . "%')";
        }

        if (isset($_GET['competitionday']))
        {
            if (strpos($sql, 'WHERE') == false)
            {
                $sql .= " WHERE (";
            }
            else
            {
                $sql .= " AND (";
            }

            $sql .= " result.date = '" . esc_sql($_GET['competitionday']) . "')";
        }

        $sql .= ' ORDER BY result.date';

        $csv_header = array( 'RESULTS', 'date', 'player1', 'partner1', 'player2', 'partner2',
            'player1set1', 'player2set1', 'player1set2', 'player2set2',
            'player1set3', 'player2set3', 'player1set4', 'player2set4',
            'player1set5', 'player2set5', 'player1set6', 'player2set6',
            'player1set7', 'player2set7', 'ratingflag');

        fputcsv($file, $csv_header);
        $results = $wpdb->get_results($sql, ARRAY_A);
        foreach ($results as $result)
        {
            fputcsv($file, $result);
        }
    }
}
