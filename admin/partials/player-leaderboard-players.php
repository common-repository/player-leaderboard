<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.software-kunze.de
 * @since      1.0.0
 *
 * @package    Player-Leaderboard
 * @subpackage player-leaderboard/admin/partials
 */
?>
<?php
    if (!current_user_can('manage_options')) {
        wp_die();
    }

    $playersTable = new Player_Leaderboard_Players();

    if (isset( $_GET['export']))
    {
        $filename = 'players.csv';
        $now = gmdate('D, d M Y H:i:s') . ' GMT';

        header('Content-Type: application/octet-stream'); // tells browser to download
        header("Content-Encoding: UTF-8");
        header('Content-Disposition: attachment; filename="' . $filename .'"');
        header('Pragma: no-cache'); // no cache
        header('Expires: ' . $now); // expire date

        $playersTable->generate_csv();
        exit;
    }

    $playersTable->prepare_items();

    $competitions = $playersTable->get_competitions();

    if (isset($_POST['competitionID']))
    {
        $competitionID = intval($_POST['competitionID']);
    }
    else if (isset($_GET['competitionID']))
    {
        $competitionID = intval($_GET['competitionID']);
    }
    else if (sizeof($competitions) == 1)
    {
        $competitionID = $competitions[0]->id;
    }
    else
    {
        $competitionID = 0;
    }

    if (isset($_GET['msg']))
    {
        switch ($_GET['msg'])
        {
            case 1:
                $message = __('Player deleted!', 'player-leaderboard');
                break;
            case 2:
                $message = __('Player successfully changed!', 'player-leaderboard');
                break;
            case 3:
                $message = __('Player successfully created!', 'player-leaderboard');
                break;
        }
    }

    if ($competitionID == 0)
    {
        $errmessage = __('Please select a competition!', 'player-leaderboard');
    }
?>

<div class="wrap">
    <?php if (isset($message)){ ?>
        <div id="message" class="updated notice is-dismissible"><p>
            <?php echo $message ?>
        </p></div>
    <?php } ?>
    <?php if (isset($errmessage)){ ?>
        <div id="errmessage" class="updated error is-dismissible"><p>
            <?php echo $errmessage ?>
        </p></div>
    <?php } ?>

    <hr class="wp-header-end">

    <div class="pl-admin-full">
        <h1 class="pl-admin-header">
            <?php echo __('Manage Players', 'player-leaderboard');?>
        </h1>

        <div class="pl-admin-action-panel-top">
            <?php if ($competitionID == 0) { ?>
                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard")?>"><?php echo __('Back', 'player-leaderboard');?></a>
            <?php } else {?>
                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard&competitionID={$competitionID}")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&mode=edit&competitionID={$competitionID}")?>"><?php echo __('Create New Player', 'player-leaderboard');?></a>
                <a class="button pl-admin-button" href="<?php
                $url = "admin.php?page=player-leaderboard-players&export=players&noheader=1";
                $url = $url . "&competitionID=" . $competitionID;
                if (isset($competitionday) == true)
                {
                    $url = $url . "&competitionday=" . $competitionday;
                }
                echo admin_url(esc_url($url))?>"><?php echo __('CSV Export', 'player-leaderboard');?></a>
            <?php } ?>
        </div>
        <?php if ($competitionID > 0) { ?>
            <?php $playersTable->display(); ?>
        <?php } ?>
	</div>
</div>
