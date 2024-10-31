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
    if (!current_user_can('manage_options')) {
        wp_die();
    }

    if (isset($_GET['competitionID']))
    {
        $competitionID = intval($_GET['competitionID']);
    }

    if (isset($competitionID))
    {
        $players = $this->get_players($competitionID);
        $competition = $this->get_competition($competitionID);

        // Create a dummy result object
        $result = $this->new_result($competitionID);
    }
?>

<div class="wrap">

    <hr class="wp-header-end">

    <div class="pl-admin-full">
        <h1 class="pl-admin-header">
            <?php echo __('Create Results', 'player-leaderboard'); ?>
        </h1>

        <?php if (isset($competitionID)) { ?>
            <form method="post" action="<?php echo admin_url('admin-post.php') ?>">
                <input type="hidden" name="results" value="10" />
                <input type="hidden" name="action" value="pl_action_results" />
                <input type="hidden" name="competitionid" value="<?php echo esc_attr($competitionID) ?>" />

                <div class="pl-admin-form">
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                            <label for="player1id"><?php echo __('Date', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input class="pl-admin-input-50" type="date" name="date" value="<?php echo esc_attr($result->date) ?>" required />
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                            <?php echo __('Selected Competition', 'player-leaderboard');?>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input class="pl-admin-input-80" type="text" name="competitionname" size="50" value="<?php echo esc_attr($competition->name) ?>" readonly />
                        </div>
                    </div>
                    <hr/>

                    <table>
                        <thead>
                            <th>#</th>
                            <th>
                                <?php switch ($competition->type) {
                                    case 1:
                                        echo __('Player 1', 'player-leaderboard');
                                        break;
                                    case 2:
                                        echo __('Double 1', 'player-leaderboard');
                                        break;
                                    case 3:
                                        echo __('Team 1', 'player-leaderboard');
                                        break;
                                } ?>
                            </th>
                            <th>
                                <?php switch ($competition->type) {
                                    case 1:
                                        echo __('Player 2', 'player-leaderboard');
                                        break;
                                    case 2:
                                        echo __('Double 2', 'player-leaderboard');
                                        break;
                                    case 3:
                                        echo __('Team 2', 'player-leaderboard');
                                        break;
                                } ?>
                            </th>
                            <?php if ($competition->bestof < 2 ) { ?>
                                <th><?php echo __('Result', 'player-leaderboard');?></th>
                            <?php } else { ?>
                                <th><?php echo __('Set 1', 'player-leaderboard');?></th>
                                <th><?php echo __('Set 2', 'player-leaderboard');?></th>
                                <th><?php echo __('Set 3', 'player-leaderboard');?></th>
                                <?php if ($competition->bestof > 3 ) { ?>
                                    <th><?php echo __('Set 4', 'player-leaderboard');?></th>
                                    <th><?php echo __('Set 5', 'player-leaderboard');?></th>
                                    <?php if ($competition->bestof > 5 ) { ?>
                                        <th><?php echo __('Set 6', 'player-leaderboard');?></th>
                                        <th><?php echo __('Set 7', 'player-leaderboard');?></th>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                            <th><?php echo __('Comment', 'player-leaderboard');?></th>
                        </thead>
                        <tbody>
                            <?php for($r=1;$r<11;$r++) {  ?>
                                <tr>
                                    <td><?php echo esc_html($r);?>.</td>
                                    <td>
                                        <select name="player1id-<?php echo esc_html($r);?>" >
                                            <option value="0" ><?php echo __('Please select...', 'player-leaderboard'); ?></option>
                                            <?php for($p=0;$p<sizeof($players);$p++) { $player = $players[$p]; ?>
                                              <option value="<?php echo esc_attr($player->id) ?>" ><?php echo esc_html($player->name) ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if ($competition->type == 2 ) { ?>
                                            <select name="partner1id-<?php echo esc_html($r);?>" >
                                                <option value="0" ><?php echo __('Please select...', 'player-leaderboard'); ?></option>
                                                <?php for($p=0;$p<sizeof($players);$p++) { $player = $players[$p]; ?>
                                                  <option value="<?php echo esc_attr($player->id) ?>" ><?php echo esc_html($player->name) ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <select name="player2id-<?php echo esc_html($r);?>" >
                                            <option value="0" ><?php echo __('Please select...', 'player-leaderboard'); ?></option>
                                            <?php for($p=0;$p<sizeof($players);$p++) { $player = $players[$p]; ?>
                                              <option value="<?php echo esc_attr($player->id) ?>" ><?php echo esc_html($player->name) ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if ($competition->type == 2 ) { ?>
                                            <select name="partner2id-<?php echo esc_html($r);?>" >
                                                <option value="0" ><?php echo __('Please select...', 'player-leaderboard'); ?></option>
                                                <?php for($p=0;$p<sizeof($players);$p++) { $player = $players[$p]; ?>
                                                  <option value="<?php echo esc_attr($player->id) ?>" ><?php echo esc_html($player->name) ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </td>
                                    <td>
                            		    <input type="text" name="player1set1-<?php echo esc_html($r);?>" size="3" value="" />
                                        &nbsp;:&nbsp;
                                        <input type="text" name="player2set1-<?php echo esc_html($r);?>" size="3" value="" />
                                    </td>
                                    <?php if ($competition->bestof > 1 ) { ?>
                                        <td>
                                		    <input type="text" name="player1set2-<?php echo esc_html($r);?>" size="3" value="" />
                                            &nbsp;:&nbsp;
                                            <input type="text" name="player2set2-<?php echo esc_html($r);?>" size="3" value="" />
                                        </td>
                                        <td>
                                		    <input type="text" name="player1set3-<?php echo esc_html($r);?>" size="3" value="" />
                                            &nbsp;:&nbsp;
                                            <input type="text" name="player2set3-<?php echo esc_html($r);?>" size="3" value="" />
                                        </td>
                                    <?php } ?>
                                    <?php if ($competition->bestof > 3 ) { ?>
                                        <td>
                                		    <input type="text" name="player1set4-<?php echo esc_html($r);?>" size="3" value="" />
                                            &nbsp;:&nbsp;
                                            <input type="text" name="player2set4-<?php echo esc_html($r);?>" size="3" value="" />
                                        </td>
                                        <td>
                                		    <input type="text" name="player1set5-<?php echo esc_html($r);?>" size="3" value="" />
                                            &nbsp;:&nbsp;
                                            <input type="text" name="player2set5-<?php echo esc_html($r);?>" size="3" value="" />
                                        </td>
                                    <?php } ?>
                                    <?php if ($competition->bestof > 5 ) { ?>
                                        <td>
                                		    <input type="text" name="player1set6-<?php echo esc_html($r);?>" size="3" value="" />
                                            &nbsp;:&nbsp;
                                            <input type="text" name="player2set6-<?php echo esc_html($r);?>" size="3" value="" />
                                        </td>
                                        <td>
                                		    <input type="text" name="player1set7-<?php echo esc_html($r);?>" size="3" value="" />
                                            &nbsp;:&nbsp;
                                            <input type="text" name="player2set7-<?php echo esc_html($r);?>" size="3" value="" />
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <input type="text" name="comment-<?php echo esc_html($r);?>" value="" />
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="pl-admin-action-panel-bottom">
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Create', 'player-leaderboard');?> />
                            <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-results&competitionID={$competitionID}")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                        </div>
                    </div>
                </div>

            </form>
        <?php } ?>
    </div>
</div>
