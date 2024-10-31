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

    if (isset($_FILES['csvimport']))
    {
        $resetratings = (isset($_POST['resetratings']))? 1 : 0;
        $importresults = (isset($_POST['importresults']))? intval($_POST['importresults']) : 0;
        $message = $this->csv_import(null, 1, $resetratings, $importresults);
    }

    $competitions = $this->get_competitions();

    if ((isset($_GET['msg'])) && (!isset($message)))
    {
        switch (intval($_GET['msg']))
        {
            case 1:
                $message = __('Competition deleted!', 'player-leaderboard');
                break;
            case 2:
                $message = __('Competition successfully changed!', 'player-leaderboard');
                break;
            case 3:
                $message = __('Competition successfully created!', 'player-leaderboard');
                break;
        }
    }
?>

<div class="wrap">

    <?php if (isset($message)){ ?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php echo $message ?></p>
        </div>
    <?php } ?>

    <hr class="wp-header-end">

    <div class="pl-admin-full">
        <h1 class="pl-admin-header">
            <?php echo __('Manage Competitions', 'player-leaderboard');?>
        </h1>

        <div class="pl-admin-action-panel-top">
            <form method="post" enctype="multipart/form-data">
                <div class="pl-admin-form-group">
                    <div class="pl-admin-form-col-4">
                        <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-competition&mode=edit")?>"><?php echo __('Create New Competition', 'player-leaderboard');?></a>
                    </div>
                    <div class="pl-admin-form-col-2">
                    </div>
                    <div class="pl-admin-form-col-2">
                        <label for="csv-import" class="button pl-admin-button">
                            <?php echo __('CSV Import', 'player-leaderboard');?>
                        </label>
                        <input id="csv-import" class="pl-csv-import" type="file" accept=".csv" name="csvimport" onchange="form.submit()" />
                    </div>
                    <div class="pl-admin-form-col-2">
                        <label for="resetratings"><?php echo __('Reset Ratings', 'player-leaderboard') ?></label>
                        <input type="checkbox" name="resetratings" value="1" checked />
                        <span class="pl-help-tip" data-tooltip="<?php echo __('If this option is checked, the rating will be reset during the import', 'player-leaderboard');?>">?</span>
                    </div>
                    <div class="pl-admin-form-col-2">
                        <label for="importresults"><?php echo  __('Import Results', 'player-leaderboard') ?></label>
                        <input type="checkbox" name="importresults" value="1" checked />
                        <span class="pl-help-tip" data-tooltip="<?php echo __('If this option is not checked, the results are not imported', 'player-leaderboard');?>">?</span>
                    </div>
                </div>
            </form>
        </div>

        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th class="manage-column column-title column-primary" width="15%"><?php echo __('Name', 'player-leaderboard');?></th>
                    <th class="manage-column column-title column-primary" width="10%"><?php echo __('Type', 'player-leaderboard');?></th>
                    <th class="manage-column column-title column-primary" width="10%"><?php echo __('Kind of Sport', 'player-leaderboard');?></th>
                    <th class="manage-column column-title column-primary" width="20%"><?php echo __('Description', 'player-leaderboard');?></th>
                    <th class="manage-column column-title column-primary" width="10%"><?php echo __('Ratings', 'player-leaderboard');?></th>
                    <th class="manage-column column-title column-primary" width="15%"><?php echo __('Shortcut', 'player-leaderboard');?></th>
                    <th class="manage-column column-title" width="20%"><?php echo __('Action', 'player-leaderboard');?></th>
                </tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < sizeof($competitions); $i++) {$item = $competitions[$i];?>
                <tr>
        	        <td><?php echo esc_html($item->name) ?></td>
                    <td>
                        <?php switch ($item->type) {
                            case 1: echo __('Single', 'player-leaderboard'); break;
                            case 2: echo __('Double', 'player-leaderboard'); break;
                            case 3: echo __('Team', 'player-leaderboard') ; break;
                            default: echo '*' . esc_html($item->type) . '*'; break;
        				}?>
                    </td>
                    <td>
                    <?php switch ($item->kindofsport) {
                        case 'Badminton': echo __('Badminton', 'player-leaderboard'); break;
                        case 'Tennis': echo __('Tennis', 'player-leaderboard'); break;
                        case 'TableTennis': echo __('Table Tennis', 'player-leaderboard'); break;
                        case 'Squash': echo __('Squash', 'player-leaderboard'); break;
                        case 'Basketball': echo __('Basketball', 'player-leaderboard'); break;
                        case 'Field Hockey': echo __('Field Hockey', 'player-leaderboard'); break;
                        case 'Handball': echo __('Handball', 'player-leaderboard'); break;
                        case 'Hockey': echo __('Hockey', 'player-leaderboard'); break;
                        case 'Soccer': echo __('Soccer', 'player-leaderboard'); break;
                        case 'Volleyball': echo __('Volleyball', 'player-leaderboard'); break;
                        default: echo '*' . esc_html($item->kindofsport) . '*'; break;
    				}?>
                    </td>
                    <td><?php echo esc_html($item->description) ?></td>
                    <td><?php echo esc_html($item->ratings) ?></td>
                    <td><code><?php echo "[player_leaderboard id=$item->id]"?></code></td>
                    <td>
                        <a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-competition&competitionID={$item->id}")?>"><?php echo __('Details', 'player-leaderboard');?></a>
                        &nbsp;|&nbsp;
                        <a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-competition&mode=edit&competitionID={$item->id}")?>"><?php echo __('Edit', 'player-leaderboard');?></a>
                        &nbsp;|&nbsp;
                        <a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-players&competitionID={$item->id}")?>"><?php echo __('Players', 'player-leaderboard');?></a>
                        &nbsp;|&nbsp;
                        <a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-results&&competitionID={$item->id}")?>"><?php echo __('Results', 'player-leaderboard');?></a>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>

    </div>
</div>