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

    if (isset($atts['details']))
	{
		$showdetails = $atts['details'];
	}
    else
	{
		$showdetails = "false";
	}

   	$competitionID = (isset($atts['id']))? intval($atts['id']) : 0;

	if ($competitionID == 0)
	{
		echo(__("Competition ID invalid.",'player-leaderboard'));
        return;
	}

	$competition = $this->get_competition_by_id($competitionID);
	if ($competition == null)
	{
		echo(__("Competition not found.",'player-leaderboard'));
		return;
	}

	$results = $this->get_standings($competitionID);
    if ($showdetails != 'false')
    {
      	$allgames = $this->get_games($competitionID);
    }
?>

<?php if ($showheader != 'false') { ?>
<h2><?php echo esc_html($competition->name) ?></h2>
<h3><?php echo __('Standing', 'player-leaderboard');?></h3>
<?php }?>

<?php $this->create_styles($competitionID, 'standing'); ?>
<?php if ($showdetails != 'false') { $this->show_standing_detail_dialogs($competition, $allgames); }?>

<table class="pl-standings responsive">
    <thead>
        <tr>
        <?php if ($competition->type !=3) { ?>
            <th><?php echo __('Player', 'player-leaderboard');?></th>
        <?php } else { ?>
            <th><?php echo __('Team', 'player-leaderboard');?></th>
        <?php } ?>
        <th><?php echo __('Matches', 'player-leaderboard');?></th>
        <th><?php echo __('Points', 'player-leaderboard');?></th>
        <?php if (($competition->type !=3) || ($competition->bestof > 1)) { ?>
            <th><?php echo __('Sets', 'player-leaderboard');?></th>
        <?php }?>
        <th><?php echo __('Games', 'player-leaderboard');?></th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $i < sizeof($results); $i++) {$item = $results[$i];?>
        <tr>
            <?php if ($showdetails != 'false') { ?>
                <td><a class="details" player="<?php echo $item->id?>"><?php echo esc_html($item->name)?></a></td>
            <?php } else { ?>
                <td><?php echo esc_html($item->name)?></td>
            <?php }?>
            <td><?php echo $item->matches?></td>
            <td><?php echo $item->pointswon?>:<?php echo intval($item->pointslost)?></td>
            <?php if (($competition->type !=3) || ($competition->bestof > 1)) { ?>
                <td><?php echo $item->setswon?>:<?php echo intval($item->setslost)?></td>
            <?php }?>
            <td><?php echo $item->gameswon?>:<?php echo intval($item->gameslost)?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
