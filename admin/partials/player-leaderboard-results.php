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
 * @subpackage result-leaderboard/admin/partials
 */
?>
<?php
    if (!current_user_can('manage_options'))
    {
        wp_die();
    }

    $resultsTable = new Player_Leaderboard_Results();

    if (isset( $_GET['export']))
    {
        $filename = 'results.csv';
        $now = gmdate('D, d M Y H:i:s') . ' GMT';

        header('Content-Type: application/octet-stream'); // tells browser to download
        header("Content-Encoding: UTF-8");
        header('Content-Disposition: attachment; filename="' . $filename .'"');
        header('Pragma: no-cache'); // no cache
        header('Expires: ' . $now); // expire date

        $resultsTable->generate_csv();
        exit;
    }

    $resultsTable->prepare_items();
    $competitions = $resultsTable->get_competitions();

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

    if (isset($_POST['competitionday']))
    {
        $competitionday = sanitize_text_field($_POST['competitionday']);
    }

    if ($competitionID != 0)
    {
        $competitiondays = $resultsTable->get_competition_days($competitionID);
    }

    if (isset($_GET['msg']))
    {
        switch ($_GET['msg'])
        {
            case 1:
                $message = __('Result deleted!', 'player-leaderboard');
                break;
            case 2:
                $message = __('Result successfully changed!', 'player-leaderboard');
                break;
            case 3:
                $message = __('Result successfully created!', 'player-leaderboard');
                break;
            case 4:
                $message = __('Results successfully created!', 'player-leaderboard');
                break;
        }
    }

    if ($competitionID == 0)
    {
        $message = __('Please select a competition!', 'player-leaderboard');
    }
?>

<div class="wrap">
    <?php if (isset($message)){ ?>
        <div id="message" class="updated notice is-dismissible"><p>
            <?php echo esc_html($message) ?>
        </p></div>
    <?php } ?>

    <hr class="wp-header-end">

    <div class="pl-admin-full">
        <h1 class="pl-admin-header">
            <?php echo __('Manage Results', 'player-leaderboard');?>
        </h1>

        <form method="post">
            <input type="hidden" name="page" value="player-leaderboard-result" />
            <div class="pl-admin-action-panel-top">
                <?php if ($competitionID == 0) { ?>
                    <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                <?php } else {?>
                    <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard&competitionID={$competitionID}")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                    <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-result&mode=edit&competitionID={$competitionID}")?>"><?php echo __('New Result', 'player-leaderboard');?></a>
                    <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-results-multiple&competitionID={$competitionID}")?>"><?php echo __('New Results', 'player-leaderboard');?></a>
                    <a class="button pl-admin-button" href="<?php
                    $url = "admin.php?page=player-leaderboard-results&export=results&noheader=1";
                    $url = $url . "&competitionID=" . $competitionID;

                    if (isset($competitionday) == true)
                    {
                        $url = $url . "&competitionday=" . $competitionday;
                    }
                    if (isset($_POST["s"]) == true)
                    {
                        $url = $url . "&s=" . sanitize_text_field($_POST["s"]);
                    }
                    echo admin_url(esc_url($url))?>"><?php echo __('CSV Export', 'player-leaderboard');?></a>
                <?php } ?>
                <?php $resultsTable->search_box(__('search', 'player-leaderboard'), 'search_id'); ?>
                <?php if (isset($competitiondays) == true) { ?>
                    <p class="search-box"><?php echo __('Date', 'player-leaderboard');?>:
                    <select class="competitiondayselect" name="competitionday" >
                        <?php if ((isset($competitionday) == false) || ($competitionday == 0)) { ?>
                            <option value="0" selected><?php echo __('Please Select', 'player-leaderboard');?></option>
                        <?php } ?>
                        <?php for($i=0;$i<sizeof($competitiondays);$i++) { $item = $competitiondays[$i]; ?>
                            <option value="<?php echo esc_attr($item->date) ?>" <?php echo ((isset($competitionday)) && ($item->date == $competitionday)) ? 'selected' : ''?>><?php echo mysql2date(__('Y/m/d', 'player-leaderboard'), $item->date) ?></option>
                        <?php } ?>
                    </select>
                    </p>
                <?php } ?>
            </div>
            <?php if ($competitionID > 0) { ?>
                <?php $resultsTable->display(); ?>
            <?php } ?>
        </form>
    </div>
</div>
