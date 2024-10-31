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

    if (isset( $_GET['export']))
    {
        $csv = $this->generate_csv('team');

        $filename = 'team.csv';
        $now = gmdate('D, d M Y H:i:s') . ' GMT';

        header( 'Content-Type: application/octet-stream' ); // tells browser to download

        header( 'Content-Disposition: attachment; filename="' . $filename .'"' );
        header( 'Pragma: no-cache' ); // no cache
        header( 'Expires: ' . $now ); // expire date

        echo $csv;
        exit;
    }

    // playerID and competitionID are passed with the GET request (URL parameters)
    if (isset($_GET['playerID']))
    {
        $playerID = intval($_GET['playerID']);
    }
    if (isset($_GET['competitionID']))
    {
        $competitionID = intval($_GET['competitionID']);
    }

    if (isset($_GET['mode']))
    {
        $mode = $_GET['mode'];
    }

    // Handle the new submit button => reate a new empty player
    if (isset($_POST['player-leaderboard-new']))
    {
        unset($playerID);
    }

    // If we have a playerID then load the player details from the database
    if (isset($playerID) && $playerID > 0)
    {
        // If player id is set, the load the data from the database
        $player = $this->get_player($playerID);
        $results = $this->get_player_results($playerID);
        $competitionID = $player->competitionid;
    }

    $competitions = $this->get_competitions();

    if ((!isset($competitionID)) && (sizeof($competitions) == 1))
    {
        $competitionID = $competitions[0]->id;
    }

    if (isset($competitionID))
    {
        $competition = $this->get_competition($competitionID);
    }

    // If we didn't have player data, then set some default values
    if (!isset($player))
    {
        $player = $this->new_player();
        if (isset($competition))
        {
            $player->competitionid = $competition->id;
            $player->rating = $competition->rating;
        }
        $results = array();
    }

?>

<?php if(isset($player)){ ?>
    <div id="dialog-delete-confirm" class="pl-dialog-confirm" title="<?php echo __('Delete Player', 'player-leaderboard') ?>" display="none">
        <p><?php echo __('Delete the player?', 'player-leaderboard') ?></p>
    </div>
<?php } ?>

<div class="wrap">

    <?php if (isset($message)){ ?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php echo $message ?></p>
        </div>
    <?php } ?>

    <hr class="wp-header-end">

    <div class="pl-admin-column">
        <h1 class="pl-admin-header">
        <?php
            if ((isset($player)) && ($player->id > 0))
            {
                echo (($competition->type== 3)? __('Team', 'player-leaderboard') : __('Player', 'player-leaderboard')) .
                    ' &laquo;' . esc_html($player->name) . '&raquo; ' . __('edit', 'player-leaderboard');
            }
            else
            {
                echo (isset($competition) && ($competition->type== 3))? __('Create Team', 'player-leaderboard') : __('Create Player', 'player-leaderboard');
            }
        ?>
        </h1>

        <?php if (!isset($competitionID)) {?>
              <form>
                <table>
                    <tr>
                        <td><?php echo __('Competition', 'player-leaderboard');?></td>
                        <td>
                            <select class="competitionselect" name="competitionid">
                                <option value="0" 'selected'><?php echo __('Please select...', 'player-leaderboard') ?></option>
                                <?php for($i=0;$i<sizeof($competitions);$i++) { $item = $competitions[$i]; ?>
                                    <option value="<?php echo esc_attr($item->id) ?>"><?php echo esc_html($item->name) ?></option>
                                <?php } ?>
                          </select>
                        </td>
                    </tr>
                </table>
            </form>
        <?php } else { ?>
            <form method="post" action="<?php echo admin_url('admin-post.php') ?>">
                <input type="hidden" name="id" value="<?php echo esc_attr($player->id) ?>" />
                <input type="hidden" name="competitionid" value="<?php echo esc_attr($competitionID) ?>" />
                <input type="hidden" name="ranking" value="<?php echo esc_attr($player->ranking) ?>" />
                <input type="hidden" name="action" value="pl_action_player" />
                <input type="hidden" name="success" value="<?php echo admin_url("admin.php?page=player-leaderboard-players&competitionID={$competitionID}&msg=1")?>" />

                <div class="pl-admin-form">
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                            <?php if ($competition->type == 3) {?>
                                <label for="name"><?php echo __('Name of the team', 'player-leaderboard');?></label>
                            <?php } else { ?>
                                <label for="name"><?php echo __('Name of the player', 'player-leaderboard');?></label>
                            <?php }?>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="name" maxlength="255" size="60" value="<?php echo esc_attr($player->name)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-4">
                            <label for="competitionname"><?php echo __('Competition', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="competitionname" size="40" value="<?php echo esc_attr($competition->name)?>" readonly/>
                            <input type="hidden" name="competitionid" value="<?php echo esc_attr($competition->id)?>" />
                        </div>
                        <?php if ($competition->type != 3) {?>
                            <div class="pl-admin-form-col-4">
                                <label for="gender"><?php echo __('Gender', 'player-leaderboard');?></label>
                            </div>
                            <div class="pl-admin-form-col-8">
                                <?php if (isset($mode)) { ?>
                                    <select name="gender">
                                        <option <?php if ($player->gender == 0 ) echo 'selected' ; ?> value="0"><?php echo __('Unknown', 'player-leaderboard');?></option>
                                        <option <?php if ($player->gender == 1 ) echo 'selected' ; ?> value="1"><?php echo __('Female', 'player-leaderboard');?></option>
                                        <option <?php if ($player->gender == 2 ) echo 'selected' ; ?> value="2"><?php echo __('Male', 'player-leaderboard');?></option>
                                    </select>
                                <?php } else { ?>
                                    <?php switch ($player->gender) {
                                        case 1: $gender = __('Female', 'player-leaderboard'); break;
                                        case 2: $gender = __('Male', 'player-leaderboard'); break;
                                        default: $gender = __('Unknown', 'player-leaderboard'); break;
                                    } ?>
                                    <input class="pl-admin-input-50" type="text" name="gender" value="<?php echo $gender ?>" readonly />
                                <?php }?>
                            </div>
                            <div class="pl-admin-form-col-4">
                                <label for="birthday"><?php echo __('Bithday', 'player-leaderboard');?></label>
                            </div>
                            <div class="pl-admin-form-col-8">
                                <input type="date" name="birthday" value="<?php echo esc_attr($player->birthday) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                            </div>
                        <?php }?>
                        <div class="pl-admin-form-col-4">
                            <label for="playerpass"><?php echo __('Player Pass', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="playerpass" maxlength="32" size="40" value="<?php echo esc_attr($player->playerpass)?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-4">
                            <?php if ($competition->type == 3) {?>
                                <label for="rating"><?php echo __('Rating of the team', 'player-leaderboard');?></label>
                            <?php } else { ?>
                                <label for="rating"><?php echo __('Rating of the player', 'player-leaderboard');?></label>
                            <?php }?>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="number" name="rating" min="0" max="99" value="<?php echo esc_attr($player->rating) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <?php if ($competition->type == 3) {?>
                            <div class="pl-admin-form-col-4">
                                <label for="ranking"><?php echo __('Ranking Position', 'player-leaderboard');?></label>
                            </div>
                            <div class="pl-admin-form-col-8">
                                <input type="number" name="ranking" min="1" max="99" value="<?php echo esc_attr($player->ranking) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                            </div>
                        <?php }?>
                        <div class="pl-admin-form-col-4">
                            <label for="comment"><?php echo __('Comment', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <textarea name="comment" maxlength="255" cols="60" rows="4" <?php echo (isset($mode))? '':'readonly'?>><?php echo esc_textarea($player->comment)?></textarea>
                        </div>
                        <div class="pl-admin-form-col-4">
                            <label for="address1"><?php echo __('Address', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="address1" maxlength="256" size="60" value="<?php echo esc_attr($player->address1) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-4">
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="address2" maxlength="256" size="60" value="<?php echo esc_attr($player->address2) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-4">
                            <label for="phone"><?php echo __('Phone', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="phone" maxlength="256" size="60" value="<?php echo esc_attr($player->phone) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-4">
                            <label for="email"><?php echo __('Email', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="text" name="email" maxlength="256" size="60" value="<?php echo esc_attr($player->email) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-4">
                            <label for="ranking"><?php echo __('Rating', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input type="number" name="ranking" value="<?php echo esc_attr($player->ranking) ?>" readonly />
                        </div>
                    </div>
                </div>

                <div class="pl-admin-action-panel-bottom">
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                            <?php if (!isset($mode)) {  ?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-players&competitionID={$competitionID}")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                            <?php }  ?>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <?php if (isset($mode)) {  ?>
                                <?php if (isset($playerID) && $playerID > 0) {  ?>
                                    <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Save', 'player-leaderboard');?> />
                                <?php } else {  ?>
                                    <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Create', 'player-leaderboard');?> />
                                <?php }?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&playerID={$player->id}")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                            <?php } else {  ?>
                                <a class="button page-action pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&mode=edit&playerID={$player->id}")?>"><?php echo __('Edit', 'player-leaderboard');?></a>
                                <input class="button pl-admin-button" type="submit" name="player-leaderboard-delete" value=<?php echo __('Delete', 'player-leaderboard');?> />
                            <?php }  ?>
                        </div>
                    </div>
                </div>
            </form>
        <?php }  ?>
    </div>
    <?php if (($player->id > 0) && (!isset($mode))) { ?>
    <div class="pl-admin-column">
        <h1 class="pl-admin-header"><?php echo __('Results of', 'player-leaderboard');?>&nbsp;<?php echo $player->name?></h1>
        <table class="wp-list-table widefat fixed striped posts pl-results-tab">
             <thead>
                <tr>
                  <th class="manage-column column-title column-primary"><?php echo __('Date', 'player-leaderboard');?></th>
                  <?php if ((isset($competition)) && ($competition->type == 2)) {?>
                  <th class="manage-column column-title column-primary"><?php echo __('Double1', 'player-leaderboard');?></th>
                  <th class="manage-column column-title column-primary"><?php echo __('Double2', 'player-leaderboard');?></th>
                  <?php } else if ((isset($competition)) && ($competition->type == 3)) {?>
                  <th class="manage-column column-title column-primary"><?php echo __('Team1', 'player-leaderboard');?></th>
                  <th class="manage-column column-title column-primary"><?php echo __('Team2', 'player-leaderboard');?></th>
                  <?php } else {?>
                  <th class="manage-column column-title column-primary"><?php echo __('Player1', 'player-leaderboard');?></th>
                  <th class="manage-column column-title column-primary"><?php echo __('Player2', 'player-leaderboard');?></th>
                  <?php } ?>
                  <?php if ($competition->bestof < 2 ) { ?>
                      <th class="manage-column column-title column-primary"><?php echo __('Game', 'player-leaderboard');?></th>
                  <?php } else {?>
                      <th class="manage-column column-title column-primary"><?php echo __('Set1', 'player-leaderboard');?></th>
                      <?php if ($competition->bestof > 1 ) { ?>
                          <th class="manage-column column-title column-primary"><?php echo __('Set2', 'player-leaderboard');?></th>
                          <th class="manage-column column-title column-primary"><?php echo __('Set3', 'player-leaderboard');?></th>
                          <?php if ($competition->bestof > 3 ) { ?>
                              <th class="manage-column column-title column-primary"><?php echo __('Set4', 'player-leaderboard');?></th>
                              <th class="manage-column column-title column-primary"><?php echo __('Set5', 'player-leaderboard');?></th>
                              <?php if ($competition->bestof > 5 ) { ?>
                                  <th class="manage-column column-title column-primary"><?php echo __('Set6', 'player-leaderboard');?></th>
                                  <th class="manage-column column-title column-primary"><?php echo __('Set7', 'player-leaderboard');?></th>
                              <?php } ?>
                          <?php } ?>
                    <?php } ?>
                  <?php } ?>
                  <th class="manage-column column-title column-primary"><?php echo __('Points', 'player-leaderboard');?></th>
                  <th class="manage-column column-title column-primary"><?php echo __('Sets', 'player-leaderboard');?></th>
                  <th class="manage-column column-title"><?php echo __('Action', 'player-leaderboard');?></th>
                </tr>
             </thead>
             <tbody>
            <?php $points1 = 0; $points2 = 0; $sets1 = 0; $sets2=0; ?>
            <?php foreach ($results as $result) {  ?>
              <?php $points1 += $result->player1points; $points2 += $result->player2points; $sets1 += $result->player1sets; $sets2 +=$result->player2sets; ?>
              <tr>
            	<td><?php echo mysql2date(__('Y/m/d', 'player-leaderboard'), $result->date); ?></td>
                <?php if ($competition->type == 2) {?>
                	<td><?php if ($result->player1id == $player->id) { echo '<strong>';} ?><?php echo esc_html($result->player1name) ?><?php if ($result->player1id == $player->id) {echo '</strong>';} ?>
                    / <?php if ($result->partner1id == $player->id) { echo '<strong>';} ?><?php echo esc_html($result->partner1name) ?><?php if ($result->partner1id == $player->id) {echo '</strong>';} ?></td>
                	<td><?php if ($result->player2id == $player->id) { echo '<strong>';} ?><?php echo esc_html($result->player2name) ?><?php if ($result->player2id == $player->id) {echo '</strong>';} ?>
                    / <?php if ($result->partner2id == $player->id) { echo '<strong>';} ?><?php echo esc_html($result->partner2name) ?><?php if ($result->partner2id == $player->id) {echo '</strong>';} ?></td>
                <?php } else {?>
                	<td><?php if ($result->player1id == $player->id) { echo '<strong>';} ?><?php echo esc_html($result->player1name) ?><?php if ($result->player1id == $player->id) {echo '</strong>';} ?></td>
                	<td><?php if ($result->player2id == $player->id) echo '<strong>'; ?><?php echo esc_html($result->player2name) ?><?php if ($result->player2id == $player->id) echo '</strong>'; ?></td>
                <?php } ?>
            	<td><?php echo esc_html($result->player1set1) ?> : <?php echo esc_html($result->player2set1) ?></td>
                <?php if ($competition->bestof > 1 ) { ?>
            	    <td><?php echo esc_html($result->player1set2) ?> : <?php echo esc_html($result->player2set2) ?></td>
                    <?php if ($result->player1set3 != $result->player2set3) {?>
                    	<td><?php echo esc_html($result->player1set3) ?> : <?php echo esc_html($result->player2set3) ?></td>
                    <?php } else {?>
                    	<td></td>
                    <?php } ?>
                    <?php if ($competition->bestof > 3 ) { ?>
                	    <td><?php echo esc_html($result->player1set4) ?> : <?php echo esc_html($result->player2set4) ?></td>
                        <?php if ($result->player1set5 != $result->player2set5) {?>
                        	<td><?php echo esc_html($result->player1set5) ?> : <?php echo esc_html($result->player2set5) ?></td>
                        <?php } else {?>
                        	<td></td>
                        <?php } ?>
                        <?php if ($competition->bestof > 5 ) { ?>
                    	    <td><?php echo esc_html($result->player1set6) ?> : <?php echo esc_html($result->player2set6) ?></td>
                            <?php if ($result->player1set7 != $result->player2set7) {?>
                            	<td><?php echo esc_html($result->player1set7) ?> : <?php echo esc_html($result->player2set7) ?></td>
                            <?php } else {?>
                            	<td></td>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            	<td><?php echo esc_html($result->player1points) ?> : <?php echo esc_html($result->player2points) ?></td>
            	<td><?php echo esc_html($result->player1sets) ?> : <?php echo esc_html($result->player2sets) ?></td>
                <?php if ($competitionID) {?>
                	<td><a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-result&resultID={$result->id}&playerID={$player->id}&competitionID={$competitionID}")?>"><?php echo __('Edit', 'player-leaderboard');?></a></td>
                <?php } else {?>
                	<td><a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-result&resultID={$result->id}&playerID={$player->id}")?>"><?php echo __('Edit', 'player-leaderboard');?></a></td>
                <?php } ?>
              </tr>
            <?php }?>
             </tbody>
             <tfoot>
              <tr>
                <?php if ($competition->bestof < 2 ) { ?>
                  	<td colspan=4></td>
                <?php } else if ($competition->bestof > 1 ) { ?>
                  	<td colspan=6></td>
                <?php } else if ($competition->bestof > 3 ) { ?>
                   	<td colspan=8></td>
                <?php } else if ($competition->bestof > 5 ) { ?>
                   	<td colspan=10></td>
                <?php } ?>
            	<td><?php echo esc_html($points1) ?> : <?php echo esc_html($points2) ?></td>
            	<td><?php echo esc_html($sets1) ?> : <?php echo esc_html($sets2) ?></td>
            	<td></td>
              </tr>
            </tfoot>
            </table>
        <?php }?>
    </div>
</div>
