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

    if(isset($atts['details']))
	{
		$showdetails = $atts['details'];
	}
    else
	{
		$showdetails = "false";
	}

    $competitionID = (isset($atts['id']))? intval($atts['id']) : 0;

	if ($competitionID==0)
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

    $type = 'games';
	if(isset($atts['type']))
    {
        $type = $atts['type'];
	}

    $fontweight = '100%';
	if(isset($atts['fontweight']))
    {
        $fontweight = $atts['fontweight'];
	}

    $width = '100%';
	if(isset($atts['width']))
    {
        $width = $atts['width'];
	}

	$results = $this->get_matrix($competitionID, $type);
    $max = $this->get_matrix_max($results);
?>


<?php if ($showheader != 'false') { ?>
<h2><?php echo esc_html($competition->name) ?></h2>
<?php }?>

<?php $this->create_styles($competitionID, 'matrix'); ?>
<?php $this->create_matrix_styles($fontweight, $width); ?>

<?php if ($showdetails != 'false') { $this->show_matrix_detail_dialogs($competitionID, $results); }?>

<div class="div-pl-table pl-responsive">
    <div class="div-pl-table-header">
        <div class="div-pl-table-header-row">
            <div class="div-pl-table-header-cell"></div>
            <?php for ($i = 0; $i < sizeof($results); $i++) {$player = $results[$i];?>
                <div class='div-pl-table-header-cell'>
                    <div class='div-pl-table-header-cell-vertical'><?php echo esc_html($player['name'])?></div>
                </div>
            <?php }?>
        </div>
    </div>
    <div class="div-pl-table-body">
    <?php for ($i = 0; $i < sizeof($results); $i++) {$row = $results[$i];?>
        <div class="div-pl-table-body-row">
            <div class="div-pl-table-body-cell"><?php echo esc_html($row['name']) ?></div>
            <?php for ($j = 0; $j < sizeof($row['data']); $j++) {$data = $row['data'][$j] ;?>
                <?php if ($i == $j) { ;?>
                    <div class="div-pl-table-body-cell pl-matrix-empty"></div>
                <?php } else {?>
                    <?php if ($showdetails != 'false') { ?>
                        <div class="div-pl-table-body-cell pl-matrix-<?php echo $this->get_matrix_class($data,$max) ?>"><a class="details" player1="<?php echo esc_attr($row['id'])?>" player2="<?php echo esc_attr($results[$j]['id'])?>"><?php echo intval($data)?></a></div>
                    <?php } else {?>
                        <div class="div-pl-table-body-cell pl-matrix-<?php echo $this->get_matrix_class($data,$max) ?>"><?php echo intval(round($data))?></div>
                    <?php }?>
                <?php }?>
            <?php }?>
        </div>
    <?php }?>
    </div>
</div>
