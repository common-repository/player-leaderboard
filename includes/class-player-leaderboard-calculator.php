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

class Player_Leaderboard_Calulator
{
    public static function getStandings($competitionID)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $standings = $wpdb->get_results("SELECT id, name,
            0 AS matches, 0 AS pointswon, 0 AS pointslost,
            0 AS setswon, 0 AS setslost, 0 AS gameswon, 0 AS gameslost,
            0 AS points
            FROM $table_player
            WHERE competitionid = $competitionID", OBJECT);

        $results =$wpdb->get_results("SELECT * FROM $table_result
            WHERE competitionid = $competitionID", OBJECT);

        // error_log(print_r($results, true));

        foreach ($results as $result)
        {
            foreach ($standings as $standing)
            {
                // Check if result contains player1 or partner1
                if (($result->player1id == $standing->id) || ($result->partner1id == $standing->id))
                {
                    $standing->matches++;
                    $standing->pointswon = $standing->pointswon + $result->player1points;
                    $standing->pointslost = $standing->pointslost + $result->player2points;
                    $standing->setswon = $standing->setswon + $result->player1sets;
                    $standing->setslost = $standing->setslost + $result->player2sets;

                    // Calculate games and points based on the won sets
                    if ($result->player1sets > $result->player2sets)
                    {
                        // player1 won
                        $standing->gameswon++;
                        $standing->points += $competition->victory;
                    }
                    else if ($result->player2sets > $result->player1sets)
                    {
                        // player1 lost
                        $standing->gameslost++;
                        $standing->points += $competition->defeat;
                    }
                    else
                    {
                        // game tie
                        $standing->points += $competition->draw;
                    }
                }

                if (($result->player2id == $standing->id) || ($result->partner2id == $standing->id))
                {
                    $standing->matches++;
                    $standing->pointswon = $standing->pointswon + $result->player2points;
                    $standing->pointslost = $standing->pointslost + $result->player1points;
                    $standing->setswon = $standing->setswon + $result->player2sets;
                    $standing->setslost = $standing->setslost + $result->player1sets;
                    if ($result->player2sets > $result->player1sets)
                    {
                        $standing->gameswon++;
                        $standing->points += $competition->victory;
                    }
                    else if ($result->player1sets > $result->player2sets)
                    {
                        $standing->gameslost++;
                        $standing->points += $competition->defeat;
                    }
                    else
                    {
                        $standing->points += $competition->draw;
                    }
                }
            }
        }

        return $standings;
    }

    /**
     * Load ranking for an competition
     *
     * @since    1.0.0
     */
    public static function getRankingSingle($competitionID)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $ranking = $wpdb->get_results("SELECT id, name, rating, 0 AS points, 0 AS duels, 0 AS quotient
            FROM $table_player
            WHERE competitionid = $competitionID", OBJECT);

        $results =$wpdb->get_results("SELECT * FROM $table_result
            WHERE competitionid = $competitionID", OBJECT);

        foreach ($ranking as $player1)
        {
            foreach ($ranking as $player2)
            {
                $won = 0;
                $lost = 0;

                foreach ($results as $result)
                {
                    if (($result->player1id == $player1->id) &&
                        ($result->player2id == $player2->id))
                    {
                        $player1->points += $competition->bonuspoints;
                        if ($result->player1sets > $result->player2sets)
                        {
                            $won++;
                        }
                        else if ($result->player1sets < $result->player2sets)
                        {
                            $lost++;
                        }
                    }

                    if (($result->player2id == $player1->id) &&
                        ($result->player1id == $player2->id))
                    {
                        $player1->points += $competition->bonuspoints;
                        if ($result->player2sets > $result->player1sets)
                        {
                            $won++;
                        }
                        else if ($result->player2sets < $result->player1sets)
                        {
                            $lost++;
                        }
                    }
                }

                if (($won > 0) || ($lost > 0))
                {
                    $player1->points += (($won  / ($won + $lost)) * $competition->rating);
                    $player1->duels++;
                }
                else
                {
                    // No game played
                    $player1->points += $competition->noduelpoints;
                }
            }
        }
        return $ranking;
    }

    /**
     * Load ranking for an competition
     *
     * @since    1.0.0
     */
    public static function getRankingDouble($competitionID, $deltarating)
    {
        global $wpdb;

        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        $ranking = $wpdb->get_results("SELECT id, name, rating, ratings, ratingpoints,
            points AS playerpoints, quotient AS playerquotient, duels AS playerduels,
            0 AS points, 0 AS duels, 0 AS quotient, 0 AS average, 0 AS pointsquotient
            FROM $table_player
            WHERE competitionid = $competitionID", OBJECT);

        $results =$wpdb->get_results("SELECT * FROM $table_result
            WHERE competitionid = $competitionID", OBJECT);

        if (isset($competition->gamefactor) == false)
        {
            $competition->gamefactor = 1;
        }
        if (isset($competition->setfactor) == false)
        {
            $competition->setfactor = 0;
        }

        if (($competition->gamefactor + $competition->setfactor) == 0)
        {
            $competition->gamefactor = 1;
        }
        foreach ($ranking as $player1)
        {
            if (isset($player1->ratings) == false)
            {
                $player1->ratings = 0;
            }
            if (isset($player1->ratingpoints) == false)
            {
                $player1->ratingpoints = 0;
            }
            if (isset($player1->playerpoints) == false)
            {
                $player1->playerpoints = 0;
            }
            if (isset($player1->playerquotient) == false)
            {
                $player1->playerquotient = 0;
            }
            if (isset($player1->playerduels) == false)
            {
                $player1->playerduels = 0;
            }

            foreach ($results as $result)
            {
                if (($deltarating == 0) || ($result->ratingflag == 0))
                {
                    $team1 = 0;
                    $team2 = 0;
                    $resultfound = false;
                    $team1sets = 0;
                    $team2sets = 0;

                    // Check if result contains a result for player1
                    // team1 (player1 or partner1)
                    if (($result->player1id == $player1->id) ||
                        ($result->partner1id == $player1->id))
                    {
                        // team1 consists of player1 and partner1
                        foreach ($ranking as $team1player)
                        {
                            if (($result->player1id == $team1player->id) ||
                                ($result->partner1id == $team1player->id))
                            {
                                // Calculate the rating of team1
                                $team1 += $team1player->rating;
                                break;
                            }
                        }

                        // team2 consists of player2 and partner2
                        foreach ($ranking as $team2player)
                        {
                            if (($result->player2id == $team2player->id) ||
                                ($result->partner2id == $team2player->id))
                            {
                                // Calculate the rating of team2
                                $team2 += $team2player->rating;
                            }
                        }

                        $team1sets = $result->player1sets;
                        $team2sets = $result->player2sets;
                        $team1points = $result->player1points;
                        $team2points = $result->player2points;
                        $resultfound = true;
                    }

                    // Check if result contains a result for player1
                    // team2 (player2 or partner2)
                    if (($result->player2id == $player1->id) ||
                        ($result->partner2id == $player1->id))
                    {
                        // team1 consists of player2 and partner2
                        foreach ($ranking as $team1player)
                        {
                            if (($result->player2id == $team1player->id) ||
                                ($result->partner2id == $team1player->id))
                            {
                                // Calculate the rating of team1
                                $team1 += $team1player->rating;
                                break;
                            }
                        }

                        // team2 consists of player1 and partner1
                        foreach ($ranking as $team2player)
                        {
                            if (($result->player1id == $team2player->id) ||
                                ($result->partner1id == $team2player->id))
                            {
                                $team2 += $team2player->rating;
                            }
                        }

                        $team1sets = $result->player2sets;
                        $team2sets = $result->player1sets;
                        $team1points = $result->player2points;
                        $team2points = $result->player1points;
                        $resultfound = true;
                    }

                    if ($resultfound == true)
                    {
                        // Calculate the team rate
                        $teamrate = $team2 / $team1;

                        $quotientdiff = 0;
                        $pointsdiff = 0;

                        // Check if the team 1 is the winner
                        if ($team1sets > $team2sets)
                        {
                            // The winner quotient is incremented by the teamrate multiplicated by the game factor
                            $quotientdiff += ($teamrate * $competition->gamefactor);

                            // The point are in incremented by the points of the losing team multiplacted by the game factor
                            $pointsdiff += ($team2 * $competition->gamefactor);
                        }

                        if (($team1sets + $team2sets) > 0)
                        {
                            // The quotient is incremented by the teamrate multiplacted with the set ration
                            // and the competition set factor
                            $quotientdiff += ($teamrate * ($team1sets / ($team1sets + $team2sets)) * $competition->setfactor);

                            // The point are incremeted by the losing team2 points multiplacted with
                            // the set ration and the competition set factor
                            $pointsdiff += ($team2 * ($team1sets / ($team1sets + $team2sets)) * $competition->setfactor);
                        }

                        $player1->quotient += ($quotientdiff / ($competition->gamefactor + $competition->setfactor));
                        $player1->points += ($pointsdiff / ($competition->gamefactor + $competition->setfactor));

                        $player1->pointsquotient += ($teamrate * ($team1points / $team2points));

                        // Increment the number of played duels
                        $player1->duels++;

                        // Add bonus points for each played game
                        $player1->points += $competition->bonuspoints;
                    }
                }
            }

            if ($player1->duels > 0)
            {
                $player1->average = $player1->points / $player1->duels;
            }
            else
            {
                $player1->points += $competition->noduelpoints;
                $player1->average = 0;
            }
        }
        return $ranking;
    }

    /**
     * Calc ranking for an competition
     *
     * @since    1.0.0
     */
    public static function calcRankingDouble($competitionID)
    {
        $rankingresults = array();

        global $wpdb;
        $table_competition = "{$wpdb->prefix}player_leaderboard_competition";
        $table_player = "{$wpdb->prefix}player_leaderboard_player";
        $table_result = "{$wpdb->prefix}player_leaderboard_result";

        $competition = $wpdb->get_row("SELECT * FROM $table_competition WHERE id = $competitionID");

        // Get the current double ranking for the competition
        $allrankings =
            Player_Leaderboard_Calulator::getRankingDouble($competitionID, $competition->deltarating);

        // Sort the ranking based on the quotient
        usort($allrankings, "Player_Leaderboard_Calulator::compare_ranking_quotient");

        // The point for the leader is equal to the number of players
        // Maybe use 100 as base?
        $ranking_counter = sizeof($allrankings);

        // Loop thru the ranking and calculate the new rating based on the
        // ranking position and the old rating
        foreach ($allrankings as $ranking)
        {
            $ranking_points = ($ranking_counter * $competition->rating) / sizeof($allrankings);

            // The new rating is the middle of the old rating and the
            // current ranking position
            $rating = round(($ranking->rating + $ranking_points) / 2);
            $ratingpoints = 0;

            if (($ranking->playerduels == 0) && ($ranking->duels == 0))
            {
                // Without a duel use the rating default from competition
                $rating = $competition->rating / 2;
            }

            if ($ranking->duels > 0)
            {
                $ratingpoints = $ranking->quotient / $ranking->duels;
                $ranking->ratings++;
            }

            // error_log(print_r($ranking, true));

            $rankingresult = new stdClass();
            $rankingresult->id = $ranking->id;
            $rankingresult->oldrating = $ranking->rating;
            $rankingresult->rating = $rating;
            $rankingresult->name = $ranking->name;
            $rankingresult->duels = $ranking->duels;
            $rankingresult->oldduels = $ranking->playerduels;
            $rankingresult->points = $ranking->points;
            $rankingresult->oldpoints = $ranking->playerpoints;
            $rankingresult->quotient = $ranking->quotient;
            $rankingresult->oldquotient = $ranking->playerquotient;
            $rankingresult->ratings = $ranking->ratings;
            $rankingresult->oldratingpoints = $ranking->ratingpoints;
            if ($competition->deltarating == 1)
            {
                $rankingresult->points += $ranking->playerpoints;
                $rankingresult->quotient += $ranking->playerquotient;
                $rankingresult->duels += $ranking->playerduels;
                $rankingresult->ratingpoints = ($competition->deltapercent * $ranking->ratingpoints / 100) + $ratingpoints;
            }
            else
            {
                $rankingresult->ratingpoints = $ranking->ratingpoints + $ratingpoints;
            }
            $rankingresults [] = $rankingresult;

            // error_log(print_r($rankingresult, true));

            $ranking_counter --;
        }

        return $rankingresults;
    }

    /**
     * Compare ranking based on the calculated quotient
     *
     * @since    1.0.0
     */
    public static function compare_ranking_quotient($ranking1 , $ranking2)
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

}

?>
