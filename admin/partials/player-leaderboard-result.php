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

    // print_r($_POST);

    // resultID, playerID and competitionID are passed with the GET request (URL parameters)
    if (isset($_GET['resultID']))
    {
        $resultID = (int) $_GET['resultID'];
    }

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

    if (isset($_POST['player-leaderboard-new']))
    {
        unset($resultID);
    }

    if (isset($resultID) && $resultID > 0)
    {
        $result = $this->get_result($resultID);
        if (!isset($competitionID))
        {
            $competitionID = $result->competitionid;
        }
    }

    $competitions = $this->get_competitions();

    if (!isset($competitionID) && (sizeof($competitions) == 1))
    {
        $competitionID = $competitions[0]->id;
    }

    if (isset($competitionID))
    {
        $players = $this->get_players($competitionID);

        for ($i=0;$i<sizeof($competitions);$i++)
        {
            if ($competitions[$i]->id == $competitionID)
            {
                $competition = $competitions[$i];
                break;
            }
        }

        if (!isset($result))
        {
            $result = $this->new_result($competitionID);
        }

        $result->competitionname = $competition->name;
    }

?>

<?php if(isset($result)){ ?>
    <div id="dialog-delete-confirm" class="pl-dialog-confirm" title="<?=__('Delete Result', 'player-leaderboard') ?>" display="none">
        <p><?=__('Delete the result?', 'player-leaderboard') ?></p>
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
            <?php if (isset($mode)) { ?>
                <?php echo (isset($result) && $result->id > 0) ? __('Edit Result', 'player-leaderboard') : __('Create Result', 'player-leaderboard')?>
            <?php } else { ?>
                <?php echo __('Show Result', 'player-leaderboard'); ?>
            <?php } ?>
        </h1>

      <?php if (isset($result->competitionid)) { ?>
      <form method="post" action="<?php echo admin_url('admin-post.php') ?>">
        <input type="hidden" name="id" value="<?php echo esc_attr($result->id) ?>" />
        <input type="hidden" name="action" value="pl_action_result" />
        <input type="hidden" name="competitionid" value="<?php echo esc_attr($result->competitionid) ?>" />
        <input type="hidden" name="success" value="<?php echo admin_url("admin.php?page=player-leaderboard-results&competitionID={$competitionID}&msg=1")?>" />

        <?php if (isset($playerID)) {?>
            <input type="hidden" name="parent" value="<?php echo esc_attr($playerID) ?>" />
        <?php }?>
        <?php if (isset($competitionID)) {?>
            <input type="hidden" name="grandparent" value="<?php echo esc_attr($competitionID) ?>" />
        <?php }?>

        <div class="pl-admin-form">
            <div class="pl-admin-form-group">
                <div class="pl-admin-form-col-4">
                    <label for="player1id"><?php echo __('Date', 'player-leaderboard');?></label>
                </div>
                <div class="pl-admin-form-col-8">
                    <input class="pl-admin-input-50" type="date" name="date" value="<?php echo esc_attr($result->date) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                </div>
            </div>
            <div class="pl-admin-form-group">
                <div class="pl-admin-form-col-4">
                    <?php echo __('Selected Competition', 'player-leaderboard');?>
                </div>
                <div class="pl-admin-form-col-8">
                    <input class="pl-admin-input-80" type="text" name="competitionname" size="50" value="<?php echo esc_attr($result->competitionname) ?>" readonly />
                </div>
            </div>
            <hr/>
            <div class="pl-admin-form-group">
                <div class="pl-admin-form-col-4">
                    <?php switch ($competition->type) {
                        case 1:
                            echo '<label for="player2id">' . __('Player 1', 'player-leaderboard') . '</label>';
                            break;
                        case 2:
                            echo '<label for="player2id">' . __('Double 1', 'player-leaderboard') . '</label>';
                            break;
                        case 3:
                            echo '<label for="player2id">' . __('Team 1', 'player-leaderboard') . '</label>';
                            break;
                    } ?>
                </div>
                <div class="pl-admin-form-col-8">
                  <?php if (isset($mode)) { ?>
                      <select class="pl-admin-input-40" name="player1id" >
                        <?php for($i=0;$i<sizeof($players);$i++) { $item = $players[$i]; ?>
                          <option value="<?php echo esc_attr($item->id) ?>" <?php echo ($item->id == $result->player1id) ? 'selected' : ''?>><?php echo esc_html($item->name) ?></option>
                        <?php } ?>
                      </select>
                      <?php if ($competition->type == 2 ) { ?>
                          &nbsp;/&nbsp;
                          <select class="pl-admin-input-40" name="partner1id" >
                            <?php for($i=0;$i<sizeof($players);$i++) { $item = $players[$i]; ?>
                              <option value="<?php echo esc_attr($item->id) ?>" <?php echo ($item->id == $result->partner1id) ? 'selected' : ''?>><?php echo esc_attr($item->name) ?></option>
                            <?php } ?>
                          </select>
                      <?php } ?>
                  <?php } else { ?>
                       <input class="pl-admin-input-80" type="text" name="player1name" size="50" value="<?php echo ($this->get_player_name($result->player1id)) . (($competition->type == 2 )? ('&nbsp;/&nbsp;' . ($this->get_player_name($result->partner1id))) : ''); ?>" readonly />
                  <?php } ?>
                </div>
              </div>
              <div class="pl-admin-form-group">
                <div class="pl-admin-form-col-4">
                    <?php switch ($competition->type) {
                        case 1:
                            echo '<label for="player2id">' . __('Player 2', 'player-leaderboard') . '</label>';
                            break;
                        case 2:
                            echo '<label for="player2id">' . __('Double 2', 'player-leaderboard') . '</label>';
                            break;
                        case 3:
                            echo '<label for="player2id">' . __('Team 2', 'player-leaderboard') . '</label>';
                            break;
                    } ?>
                </div>
                <div class="pl-admin-form-col-8">
                  <?php if (isset($mode)) { ?>
                      <select class="pl-admin-input-40" name="player2id">
                        <?php for($i=0;$i<sizeof($players);$i++) { $item = $players[$i]; ?>
                          <option value="<?php echo esc_attr($item->id) ?>" <?php echo ($item->id == $result->player2id) ? 'selected' : ''?>><?php echo esc_html($item->name) ?></option>
                        <?php } ?>
                      </select>
                      <?php if ($competition->type == 2 ) { ?>
                          &nbsp;/&nbsp;
                          <select class="pl-admin-input-40" name="partner2id" >
                            <?php for($i=0;$i<sizeof($players);$i++) { $item = $players[$i]; ?>
                              <option value="<?php echo esc_attr($item->id) ?>" <?php echo ($item->id == $result->partner2id) ? 'selected' : ''?>><?php echo esc_attr($item->name) ?></option>
                            <?php } ?>
                          </select>
                      <?php } ?>
                      <?php } else { ?>
                          <input class="pl-admin-input-50" type="text" name="player2name" size="50" value="<?php echo ($this->get_player_name($result->player2id)) . (($competition->type == 2 )? ('&nbsp;/&nbsp;' . ($this->get_player_name($result->partner2id))) : ''); ?>" readonly />
                  <?php } ?>
                </div>
              </div>
              <?php if ($competition->bestof < 2 ) { ?>
                  <div class="pl-admin-form-group">
                    <div class="pl-admin-form-col-4">
                        <label for="player1set1"><?php echo __('Result', 'player-leaderboard');?></label>
                    </div>
            		<div class="pl-admin-form-col-8">
            		    <input type="text" name="player1set1" size="4" value="<?php echo esc_attr($result->player1set1) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        &nbsp;:&nbsp;
                        <input type="text" name="player2set1" size="4" value="<?php echo esc_attr($result->player2set1) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                    </div>
            	  </div>
              <?php } ?>
              <?php if ($competition->bestof > 1 ) { ?>
                  <div class="pl-admin-form-group">
                    <div class="pl-admin-form-col-4">
                        <label for="player1set1"><?php echo __('Set 1', 'player-leaderboard');?></label>
                    </div>
            		<div class="pl-admin-form-col-8">
            		    <input type="text" name="player1set1" size="4" value="<?php echo esc_attr($result->player1set1) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        &nbsp;:&nbsp;
                        <input type="text" name="player2set1" size="4" value="<?php echo esc_attr($result->player2set1) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                    </div>
            	  </div>
                  <div class="pl-admin-form-group">
                    <div class="pl-admin-form-col-4">
                        <label for="player1set2"><?php echo __('Set 2', 'player-leaderboard');?></label>
                    </div>
            		<div class="pl-admin-form-col-8">
            		    <input type="text" name="player1set2" size="4" value="<?php echo esc_attr($result->player1set2) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        &nbsp;:&nbsp;
                        <input type="text" name="player2set2" size="4" value="<?php echo esc_attr($result->player2set2) ?>" required <?php echo (isset($mode))? '':'readonly'?> />
                    </div>
            	  </div>
                  <div class="pl-admin-form-group">
                    <div class="pl-admin-form-col-4">
                        <label for="player1set3"><?php echo __('Set 3', 'player-leaderboard');?></label>
                    </div>
            		<div class="pl-admin-form-col-8">
            		    <input type="text" name="player1set3" size="4" value="<?php echo esc_attr($result->player1set3) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        &nbsp;:&nbsp;
                        <input type="text" name="player2set3" size="4" value="<?php echo esc_attr($result->player2set3) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                    </div>
            	  </div>
                  <?php if ($competition->bestof > 3 ) { ?>
                      <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                            <label for="player1set4"><?php echo __('Set 4', 'player-leaderboard');?></label>
                        </div>
                		<div class="pl-admin-form-col-8">
                		    <input type="text" name="player1set4" size="4" value="<?php echo esc_attr($result->player1set4) ?>"  <?php echo (isset($mode))? '':'readonly'?> />
                            &nbsp;:&nbsp;
                            <input type="text" name="player2set4" size="4" value="<?php echo esc_attr($result->player2set4) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                	  </div>
                      <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-4">
                            <label for="player1set5"><?php echo __('Set 5', 'player-leaderboard');?></label>
                        </div>
                		<div class="pl-admin-form-col-8">
                		    <input type="text" name="player1set5" size="4" value="<?php echo esc_attr($result->player1set5) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                            &nbsp;:&nbsp;
                            <input type="text" name="player2set5" size="4" value="<?php echo esc_attr($result->player2set5) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                	  </div>
                      <?php if ($competition->bestof > 5 ) { ?>
                          <div class="pl-admin-form-group">
                            <div class="pl-admin-form-col-4">
                                <label for="player1set6"><?php echo __('Set 6', 'player-leaderboard');?></label>
                            </div>
                    		<div class="pl-admin-form-col-8">
                    		    <input type="text" name="player1set6" size="4" value="<?php echo esc_attr($result->player1set6) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                                &nbsp;:&nbsp;
                                <input type="text" name="player2set6" size="4" value="<?php echo esc_attr($result->player2set6) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                            </div>
                    	  </div>
                          <div class="pl-admin-form-group">
                            <div class="pl-admin-form-col-4">
                                <label for="player1set7"><?php echo __('Set 7', 'player-leaderboard');?></label>
                            </div>
                    		<div class="pl-admin-form-col-8">
                    		    <input type="text" name="player1set7" size="4" value="<?php echo esc_attr($result->player1set7) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                                &nbsp;:&nbsp;
                                <input type="text" name="player2set7" size="4" value="<?php echo esc_attr($result->player2set7) ?>" <?php echo (isset($mode))? '':'readonly'?> />
                            </div>
                    	  </div>
                      <?php } ?>
                  <?php } ?>
              <?php } ?>
              <div class="pl-admin-form-group">
                <div class="pl-admin-form-col-4">
                    <label for="player1games"><?php echo __('Comment', 'player-leaderboard');?></label>
                </div>
                <div class="pl-admin-form-col-8">
                    <textarea class="pl-admin-input-80" name="comment" maxlength="255" cols="60" rows="4" <?php echo (isset($mode))? '':'readonly'?> ><?php echo esc_textarea($result->comment)?></textarea>
                </div>
              </div>

              <?php if ($competition->bestof < 6 ) { ?>
                  <input type="hidden" name="player2set6" value="<?php echo esc_attr($result->player2set6) ?>" />
                  <input type="hidden" name="player2set7" value="<?php echo esc_attr($result->player2set7) ?>" />
                  <input type="hidden" name="player1set6" value="<?php echo esc_attr($result->player1set6) ?>" />
                  <input type="hidden" name="player1set7" value="<?php echo esc_attr($result->player1set7) ?>" />
                  <?php if ($competition->bestof < 4 ) { ?>
                      <input type="hidden" name="player1set4" value="<?php echo esc_attr($result->player1set4) ?>" />
                      <input type="hidden" name="player1set5" value="<?php echo esc_attr($result->player1set5) ?>" />
                      <input type="hidden" name="player2set4" value="<?php echo esc_attr($result->player2set4) ?>" />
                      <input type="hidden" name="player2set5" value="<?php echo esc_attr($result->player2set5) ?>" />
                      <?php if ($competition->bestof < 2 ) { ?>
                          <input type="hidden" name="player1set2" value="<?php echo esc_attr($result->player1set2) ?>" />
                          <input type="hidden" name="player1set3" value="<?php echo esc_attr($result->player1set3) ?>" />
                          <input type="hidden" name="player2set2" value="<?php echo esc_attr($result->player2set2) ?>" />
                          <input type="hidden" name="player2set3" value="<?php echo esc_attr($result->player2set3) ?>" />
                      <?php } ?>
                  <?php } ?>
              <?php } ?>
        </div>

        <div class="pl-admin-action-panel-bottom">
            <div class="pl-admin-form-group">
                <div class="pl-admin-form-col-4">
                    <?php if (!isset($mode)) {  ?>
                        <?php if (isset($playerID)) {?>
                            <?php if (isset($competitionID)) {?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&playerID={$playerID}&competitionID={$competitionID}")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                            <?php } else { ?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&playerID={$playerID}")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                            <?php }?>
                        <?php } else { ?>
                            <?php if (isset($competitionID)) {?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-results&competitionID={$competitionID}")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                            <?php } else { ?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                            <?php }?>
                        <?php }?>
                    <?php }  ?>
                </div>
                <div class="pl-admin-form-col-8">
                    <?php if (isset($mode)) {  ?>
                        <?php if (isset($resultID) && $resultID > 0) {  ?>
                            <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Save', 'player-leaderboard');?> />
                        <?php } else {  ?>
                            <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Create', 'player-leaderboard');?> />
                        <?php }?>
                        <?php if (isset($playerID)) {?>
                            <?php if (isset($competitionID)) {?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&playerID={$playerID}&competitionID={$competitionID}")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                            <?php } else { ?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&playerID={$playerID}")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                            <?php }?>
                        <?php } else { ?>
                            <?php if (isset($competitionID)) {?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-results&competitionID={$competitionID}")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                            <?php } else { ?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                            <?php }?>
                        <?php }?>
                    <?php } else {  ?>
                        <a class="button page-action pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-result&mode=edit&resultID={$result->id}")?>"><?php echo __('Edit', 'player-leaderboard');?></a>
                        <input class="button pl-admin-button" type="submit" name="player-leaderboard-delete" value=<?php echo __('Delete', 'player-leaderboard');?> />
                    <?php }  ?>
                </div>
            </div>
        </div>

      </form>
      <?php } ?>
    </div>
</div>
