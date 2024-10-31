<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.software-kunze.de
 * @since      1.0.0
 *
 * @package    Player-Leaderboard
 * @subpackage player-leaderboard/public/partials
 * @author     Alexander Kunze
 */
?>

<?php
	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	if(!isset($atts['id']))
	{
		echo(__("Competition ID not set.",'player-leaderboard'));
        return;
	}

    if(isset($atts['showheader']))
	{
		$showheader = $atts['showheader'];
	}
    else
	{
		$showheader = "false";
	}

    /* Currently not supported
    if(isset($atts['details']))
	{
		$showdetails = $atts['details'];
	}
    else
	{
		$showdetails = "false";
	}
    */

    if(isset($atts['view']))
	{
		$rankingview = $atts['view'];
	}
    else
	{
		$rankingview = "points";
	}

  	$competitionID = (isset($atts['id']))? intval($atts['id']) : 0;
	if($competitionID==0)
	{
		echo(__("Competition ID invalid.",'player-leaderboard'));
        return;
	}

	$competition = $this->get_competition_by_id($competitionID);
	if($competition == null)
	{
		echo(__("Competition not found.",'player-leaderboard'));
		return;
	}

	$results = $this->get_ranking($competitionID, $rankingview);
?>

<?php if ($showheader != 'false') { ?>
<h2><?php echo esc_html($competition->name) ?></h2>
<h3><?php echo __('Ranking', 'player-leaderboard');?></h3>
<?php }?>

<?php $this->create_styles($competitionID, 'ranking'); ?>

<table class="pl-ranking sortable">
    <thead>
        <tr>
        <th><?php echo __('Player', 'player-leaderboard');?></th>
        <?php if ($competition->type != 2) { ?>
        <th><?php echo __('Points', 'player-leaderboard');?></th>
        <th><?php echo __('Duels', 'player-leaderboard');?></th>
        <th><?php echo __('Average', 'player-leaderboard');?></th>
        <?php } else { ?>
        <th><?php echo __('Points', 'player-leaderboard');?></th>
        <th><?php echo __('Rating', 'player-leaderboard');?></th>
        <?php if ($rankingview == 'rating') { ?>
            <th><?php echo __('Ratings', 'player-leaderboard');?></th>
        <?php } else { ?>
            <th><?php echo __('Duels', 'player-leaderboard');?></th>
        <?php }?>
        <th><?php echo __('Average', 'player-leaderboard');?></th>
        <?php }?>
        </tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $i < sizeof($results); $i++) {$item = $results[$i];?>
        <tr>
            <td><?php echo esc_html($item->name)?></td>
            <?php if ($competition->type != 2)
            {
                echo '<td>' . round($item->points,0) . '</td>';
                echo '<td>' . esc_html($item->duels) . '</td>';
                echo '<td>' . (($item->duels> 0)? round($item->points / $item->duels,1) : 0) . '</td>';
            }
            else
            {
                switch ($rankingview)
                {
                    case 'quotient':
                        echo '<td>' . round($item->quotient,2) . '</td>';
                        echo '<td>' . esc_html($item->rating) . '</td>';
                        echo '<td>' . esc_html($item->duels) . '</td>';
                        echo '<td>' . (($item->duels> 0)? round($item->quotient / $item->duels,2) : 0) . '</td>';
                        break;
                    case 'rating':
                    case 'ratings':
                        echo '<td>' . round($item->ratingpoints,2) . '</td>';
                        echo '<td>' . esc_html($item->rating) . '</td>';
                        echo '<td>' . esc_html($item->ratings) . '</td>';
                        echo '<td>' . (($item->ratings> 0)? round($item->ratingpoints / $item->ratings,2) : 0) . '</td>';
                        break;
                    case 'points':
                    case 'average':
                    default:
                        echo '<td>' . round($item->points,0) . '</td>';
                        echo '<td>' . esc_html($item->rating) . '</td>';
                        echo '<td>' . esc_html($item->duels) . '</td>';
                        echo '<td>' . (($item->duels> 0)? round($item->points / $item->duels,2) : 0) . '</td>';
                        break;
                }
            }
            ?>
        </tr>
    <?php }?>
    </tbody>
</table>
