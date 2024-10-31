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
    // error_log(print_r($_POST, true));
    // error_log(print_r($_FILES, true));
    if (!current_user_can('manage_options'))
    {
        wp_die();
    }

    if (isset( $_GET['csvexport']))
    {
        $filename = 'competition.csv';
        $now = gmdate('D, d M Y H:i:s') . ' GMT';

        header('Content-Type: application/octet-stream' ); // tells browser to download
        // header("Content-Encoding: UTF-8");
        header('Content-Disposition: attachment; filename="' . $filename .'"' );
        header('Pragma: no-cache' ); // no cache
        header('Expires: ' . $now ); // expire date

        $this->generate_csv();
        exit;
    }

    if (isset($_GET['competitionID']))
    {
        $competitionID = intval($_GET['competitionID']);
    }
    else if (isset($_POST['id']))
    {
        $competitionID = intval($_POST['id']);
    }

    if (isset($_GET['mode']))
    {
        $mode = $_GET['mode'];
    }

    if (isset($_GET['competitiontype']))
    {
        $competitiontype = intval($_GET['competitiontype']);
    }

    if (isset($_POST['action']))
    {
        switch ($_POST['action'])
        {
            case 'pl_recalc_competition':
                $this->recalc_competition($competitionID);
                $message = __('Competition recalculated!', 'player-leaderboard');
                break;
            case 'pl_recalc_preview':
                $rankingpreview = $this->recalc_preview($competitionID);
                break;
            default:
                break;
        }
    }

    if (isset($_FILES['csvimport']))
    {
        $resetratings = (isset($_POST['resetratings']))? intval($_POST['resetratings']) : 0;
        $competitiontype = (isset($_POST['competitiontype']))? intval($_POST['competitiontype']) : 1;
        $importresults = (isset($_POST['importresults']))? intval($_POST['importresults']) : 0;
        $message = $this->csv_import($competitionID, $competitiontype, $resetratings, $importresults);
    }

    if (isset($competitionID) && $competitionID > 0)
    {
        $competition = $this->get_competition($competitionID);
        $players = $this->get_players($competitionID);
    }
    else
    {
        $competition = $this->new_competition();
    }

?>

<?php if(isset($competition)) { ?>
    <div id="dialog-recalc-confirm" class="pl-dialog-confirm" title="<?php echo __('Recalc Conmpetition', 'player-leaderboard') ?>" display="none">
        <p><?=__('Recalc the competition ranking?', 'player-leaderboard') ?></p>
    </div>
    <div id="dialog-delete-confirm" class="pl-dialog-confirm" title="<?php echo __('Delete Conmpetition', 'player-leaderboard') ?>" display="none">
        <p><?=__('Delete the competition?', 'player-leaderboard') ?></p>
    </div>
<?php } ?>

<div class="wrap">
    <?php if (isset($message)){ ?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php echo $message ?></p>
        </div>
    <?php } ?>
    <?php if (isset($message_error)){ ?>
        <div id="message" class="notice notice-error is-dismissible">
            <p><?php echo $message_error ?></p>
        </div>
    <?php } ?>

    <hr class="wp-header-end">

    <div class="pl-admin-column">
        <h1 class="pl-admin-header">
            <?php echo ((isset($competition) && $competition->id > 0) ? (esc_html($competition->name) . __(' edit', 'player-leaderboard')) : __('Create Competition', 'player-leaderboard')) ?>
        </h1>
        <?php if (!isset($mode)) {  ?>
            <div class="pl-admin-action-panel-top">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo esc_attr($competition->id)?>" />
                    <input type="hidden" name="competitiontype" value="<?php echo esc_attr($competition->type)?>" />
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3"></div>
                        <div class="pl-admin-form-col-3">
                            <label for="csv-import" class="button pl-admin-button">
                                <?php echo __('CSV Import', 'tournament-manager');?>
                            </label>
                            <input id="csv-import" class="pl-csv-import" type="file" accept=".csv" name="csvimport" onchange="form.submit()" >
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="resetratings"><?php echo  __('Reset Ratings', 'player-leaderboard') ?></label>
                            <input type="checkbox" name="resetratings" value="1" checked />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('If this option is checked, the rating will be reset during the import', 'player-leaderboard');?>">?</span>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="importresults"><?php echo  __('Import Results', 'player-leaderboard') ?></label>
                            <input type="checkbox" name="importresults" value="1" checked />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('If this option is not checked, the results are not imported', 'player-leaderboard');?>">?</span>
                        </div>
                    </div>
                </form>
            </div>
        <?php } ?>
        <div class="pl-admin-row">
            <form method="post" action="<?php echo admin_url('admin-post.php') ?>">
                <input type="hidden" name="id" value="<?php echo esc_attr($competition->id)?>" />
                <input type="hidden" name="action" value="pl_action_competition" />
                <input type="hidden" name="success" value="<?php echo admin_url("admin.php?page=player-leaderboard&msg=1")?>" />
                <div class="pl-admin-form">
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="name"><?php echo __('Name of the competition', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-8">
                            <input class="pl-admin-input-100" type="text" name="name" title="Dieses ist ein kleiner Test" maxlength="255" size="50" value="<?php echo esc_attr($competition->name)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="description"><?php echo __('Description', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-9">
                            <textarea class="pl-admin-input-100" name="description" maxlength="512" cols="60" rows="4" <?php echo (isset($mode))? '':'readonly'?>><?php echo esc_textarea($competition->description)?></textarea>
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="type"><?php echo __('Type', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <?php if (isset($mode)) { ?>
                                <select class="pl-typeselect pl-admin-input-90" name="type">
                                    <option <?php if ($competition->type== 1) echo 'selected' ; ?> value="1"><?php echo __('Single', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->type== 2) echo 'selected' ; ?> value="2"><?php echo __('Double', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->type== 3) echo 'selected' ; ?> value="3"><?php echo __('Team', 'player-leaderboard');?></option>
                                </select>
                            <?php } else { ?>
                                <?php switch ($competition->type) {
                                    case 1: $type = __('Female', 'player-leaderboard'); break;
                                    case 2: $type = __('Male', 'player-leaderboard'); break;
                                    default: $type = __('Unknown', 'player-leaderboard'); break;
                                } ?>
                                <input class="pl-admin-input-80" type="text" name="type" value="<?php echo $type ?>" readonly />
                            <?php }?>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="gender"><?php echo __('Gender', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <?php if (isset($mode)) { ?>
                                <select class="pl-admin-input-80" name="gender">
                                    <option <?php if ($competition->gender== 1) echo 'selected' ; ?> value="1"><?php echo __('Female', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->gender== 2) echo 'selected' ; ?> value="2"><?php echo __('Male', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->gender== 3) echo 'selected' ; ?> value="3"><?php echo __('Mixed', 'player-leaderboard');?></option>
                                </select>
                            <?php } else { ?>
                                <?php switch ($competition->gender) {
                                    case 1: $gender = __('Female', 'player-leaderboard'); break;
                                    case 2: $gender = __('Male', 'player-leaderboard'); break;
                                    case 2: $gender = __('Mixed', 'player-leaderboard'); break;
                                    default: $gender = __('Unknown', 'player-leaderboard'); break;
                                } ?>
                                <input class="pl-admin-input-80" type="text" name="gender" value="<?php echo $gender ?>" readonly />
                            <?php }?>
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="kindofsport"><?php echo __('Kind of Sport', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-9">
                            <?php if (isset($mode)) { ?>
                                <select class="pl-admin-input-80 pl-kindofsportselect pl-type-single pl-type-double" name="kindofsport" <?php if ($competition->type == 3) echo 'style="display: none;"'; ?>>
                                    <option value=""><?php echo __('Please select...', 'player-leaderboard') ?></option>
                                    <option <?php if ($competition->kindofsport== 'Badminton' ) echo 'selected' ; ?> value="Badminton"><?php echo __('Badminton', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Tennis' ) echo 'selected' ; ?> value="Tennis"><?php echo __('Tennis', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'TableTennis' ) echo 'selected' ; ?> value="TableTennis"><?php echo __('Table Tennis', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Squash' ) echo 'selected' ; ?> value="Squash"><?php echo __('Squash', 'player-leaderboard');?></option>
                                </select>
                                <select class="pl-admin-input-80 pl-kindofsportselect pl-type-team" name="kindofsportteam" <?php if ($competition->type != 3) echo 'style="display: none;"'; ?>>
                                    <option value=""><?php echo __('Please select...', 'player-leaderboard') ?></option>
                                    <option <?php if ($competition->kindofsport== 'Basketball' ) echo 'selected' ; ?> value="Basketball"><?php echo __('Basketball', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Field Hockey' ) echo 'selected' ; ?> value="Field Hockey"><?php echo __('Field Hockey', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Handball' ) echo 'selected' ; ?> value="Handball"><?php echo __('Handball', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Hockey' ) echo 'selected' ; ?> value="Hockey"><?php echo __('Hockey', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Soccer' ) echo 'selected' ; ?> value="Soccer"><?php echo __('Soccer', 'player-leaderboard');?></option>
                                    <option <?php if ($competition->kindofsport== 'Volleyball' ) echo 'selected' ; ?> value="Volleyball"><?php echo __('Volleyball', 'player-leaderboard');?></option>
                                </select>
                            <?php } else { ?>
                                <?php switch ($competition->kindofsport) {
                                    case 'Badminton': $kindofsport = __('Badminton', 'player-leaderboard'); break;
                                    case 'Tennis': $kindofsport = __('Tennis', 'player-leaderboard'); break;
                                    case 'TableTennis': $kindofsport = __('TableTennis', 'player-leaderboard'); break;
                                    case 'Squash': $kindofsport = __('Squash', 'player-leaderboard'); break;
                                    case 'Basketball': $kindofsport = __('Basketball', 'player-leaderboard'); break;
                                    case 'Field Hockey': $kindofsport = __('Field Hockey', 'player-leaderboard'); break;
                                    case 'Hockey': $kindofsport = __('Hockey', 'player-leaderboard'); break;
                                    case 'Soccer': $kindofsport = __('Soccer', 'player-leaderboard'); break;
                                    case 'Volleyball': $kindofsport = __('Volleyball', 'player-leaderboard'); break;
                                    default: $kindofsport = __('Unknown', 'player-leaderboard'); break;
                                } ?>
                                <input class="pl-admin-input-80" type="text" name="kindofsport" value="<?php echo $kindofsport ?>" readonly />
                            <?php }?>
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="bestof"><?php echo __('Best-Of', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="bestof"  min="1" max="7" maxlength="1" value="<?php echo esc_attr($competition->bestof)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('Maximum of possible played sets', 'player-leaderboard');?>">?</span>
                        </div>
                        <div class="pl-admin-form-col-6">
                        </div>
                    </div>
                    <div class="pl-type-single pl-admin-form-group" <?php if ($competition->type != 1) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="singlerating"><?php echo __('Rating', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="singlerating" min="1" max="1000" maxlength="1" value="<?php echo esc_attr($competition->rating)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('Maximum points for a duel', 'player-leaderboard');?>">?</span>
                        </div>
                        <div class="pl-admin-form-col-6">
                        </div>
                    </div>
                    <div class="pl-type-single pl-type-double" <?php if ($competition->type == 3) echo 'style="display: none;"'; ?>>
                        <hr/>
                    </div>
                    <div class="pl-type-single pl-type-double pl-admin-form-group" <?php if ($competition->type == 3) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="victory"><?php echo __('Victory Points', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="victory" min="1" max="3" maxlength="1" value="<?php echo esc_attr($competition->victory)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="defeatame"><?php echo __('Defeat Points', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="defeat"  min="0" max="1" maxlength="1" value="<?php echo esc_attr($competition->defeat)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                    <div class="pl-type-single pl-type-double pl-admin-form-group" <?php if ($competition->type == 3) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="draw"><?php echo __('Draw Points', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="draw"  min="0" max="2" maxlength="1" value="<?php echo esc_attr($competition->draw)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="bonuspoints"><?php echo __('Bonus Points', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="bonuspoints" min="0" max="10" maxlength="1" size="50" value="<?php echo esc_attr($competition->bonuspoints)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('Bonus rating points for each played game', 'player-leaderboard');?>">?</span>
                        </div>
                    </div>
                    <div class="pl-type-double" <?php if ($competition->type != 2) echo 'style="display: none;"'; ?>>
                        <hr/>
                    </div>
                    <div class="pl-type-double pl-admin-form-group" <?php if ($competition->type != 2) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="doublerating"><?php echo __('Rating', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="doublerating" min="1" max="1000" maxlength="1" value="<?php echo esc_attr($competition->rating)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('Base rating points for a each played duel', 'player-leaderboard');?>">?</span>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="noduelpoints"><?php echo __('No Duel Points', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="noduelpoints" min="0" max="10" maxlength="1" value="<?php echo esc_attr($competition->noduelpoints)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                            <span class="pl-help-tip" data-tooltip="<?php echo __('Bonus rating points for a not played duel', 'player-leaderboard');?>">?</span>
                        </div>
                    </div>
                    <div class="pl-type-double pl-admin-form-group" <?php if ($competition->type != 2) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="gamefactor"><?php echo __('Game Factor', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="gamefactor" min="0" max="10" maxlength="1" value="<?php echo esc_attr($competition->gamefactor)?>" required <?php echo (isset($mode))? '':'readonly'?>/>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="setfactor"><?php echo __('Set Factor', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="setfactor"  min="0" max="10" maxlength="1" value="<?php echo esc_attr($competition->setfactor)?>" required <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                    <div class="pl-type-double pl-admin-form-group" <?php if ($competition->type != 2) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="ratings"><?php echo __('Number Ratings', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="ratings" min="1" max="10" maxlength="1" value="<?php echo esc_attr($competition->ratings)?>" readonly/>
                            <span class="pl-help-tip" data-tooltip="<?php echo __('Number of recalculated ratings for this competition', 'player-leaderboard');?>">?</span>
                        </div>
                    </div>
                    <div class="pl-type-double pl-admin-form-group" <?php if ($competition->type != 2) echo 'style="display: none;"'; ?>>
                        <div class="pl-admin-form-col-3">
                            <label for="deltarating"><?php echo __('Delta Ratings', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="checkbox" name="deltarating" value="1" <?php if ($competition->deltarating == 1) echo 'checked' ; ?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="deltapercent"><?php echo __('Delta Percent', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input class="pl-admin-input-80" type="number" name="deltapercent" min="1" max="100" maxlength="1" value="<?php echo esc_attr($competition->deltapercent)?>" <?php echo (isset($mode))? '':'readonly'?>/>
                        </div>
                    </div>
                    <div>
                        <hr/>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="headercolor"><?php echo __('Header', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3" onclick="return false;">
                            <input type="text" name="headercolor" value="<?php echo esc_attr($competition->headercolor)?>" class="pl-admin-color-readonly" data-default-color="#38a5ff" <?php echo (isset($mode))? '':'disabled'?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="lowcolor"><?php echo __('Matrix Low Color', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input type="text" name="lowcolor" value="<?php echo esc_attr($competition->lowcolor)?>" class="pl-admin-color-field" data-default-color="#ffffbb" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="bordercolor"><?php echo __('Border Color', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input type="text" name="bordercolor" value="<?php echo esc_attr($competition->bordercolor)?>" class="pl-admin-color-field" data-default-color="#f2f2f2" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="midcolor"><?php echo __('Matrix Mid Color', 'player-leaderboard');?></label>
                        </div>
                         <div class="pl-admin-form-col-3">
                             <input type="text" name="midcolor" value="<?php echo esc_attr($competition->midcolor)?>" class="pl-admin-color-field" data-default-color="#ffff77" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="textcolor"><?php echo __('Text Color', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input type="text" name="textcolor" value="<?php echo esc_attr($competition->textcolor)?>" class="pl-admin-color-field" data-default-color="#ffffff" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="highcolor"><?php echo __('Matrix High Color', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input type="text" name="highcolor" value="<?php echo esc_attr($competition->highcolor)?>" class="pl-admin-color-field" data-default-color="#ffff33" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <label for="zerocolor"><?php echo __('Matrix Zero Color', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input type="text" name="zerocolor" value="<?php echo esc_attr($competition->zerocolor)?>" class="pl-admin-color-field" data-default-color="#ffffff" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                        <div class="pl-admin-form-col-3">
                            <label for="maxcolor"><?php echo __('Matrix Max Color', 'player-leaderboard');?></label>
                        </div>
                        <div class="pl-admin-form-col-3">
                            <input type="text" name="maxcolor" value="<?php echo esc_attr($competition->maxcolor)?>" class="pl-admin-color-field" data-default-color="#90ee90" <?php echo (isset($mode))? '':'readonly'?> />
                        </div>
                    </div>
                </div>

                <div class="pl-admin-action-panel-bottom">
                    <div class="pl-admin-form-group">
                        <div class="pl-admin-form-col-3">
                            <?php if (!isset($mode)) {  ?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard")?>"><?php echo __('Back', 'player-leaderboard');?></a>
                            <?php }  ?>
                        </div>
                        <div class="pl-admin-form-col-9">
                            <?php if (isset($mode)) {  ?>
                                <?php if (isset($competitionID) && $competitionID > 0) {  ?>
                                    <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Save', 'player-leaderboard');?> />
                                <?php } else {  ?>
                                    <input class="button pl-admin-button" type="submit" name="player-leaderboard-save" value=<?php echo __('Create', 'player-leaderboard');?> />
                                <?php }?>
                                <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-competition&competitionID={$competition->id}")?>"><?php echo __('Cancel', 'player-leaderboard');?></a>
                            <?php } else {  ?>
                                <a class="button page-action pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-competition&mode=edit&competitionID={$competition->id}")?>"><?php echo __('Edit', 'player-leaderboard');?></a>
                                <input class="button pl-admin-button" type="submit" name="player-leaderboard-delete" value=<?php echo __('Delete', 'player-leaderboard');?> />
                                <?php if (((isset($competition)) && ($competition->id > 0))) {?>
                                    <a class="button pl-admin-button" href="<?php
                                    $url = "admin.php?page=player-leaderboard-competition&csvexport=club&noheader=1";
                                    $url = $url . "&competitionID=" . $competition->id;
                                    echo admin_url(esc_url($url))?>"><?php echo __('CSV Export', 'player-leaderboard');?></a>
                                <?php }?>
                            <?php }  ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!isset($mode)) {  ?>
        <div class="pl-admin-column">
            <h1 class="pl-admin-header">
                <?php echo __('Preview', 'player-leaderboard');?>
            </h1>
            <div class="pl-admin-action-panel-top">
                <?php if (isset($rankingpreview)) {  ?>
                    <a class="button pl-admin-button" href="<?php echo admin_url("admin.php?page=player-leaderboard-competition&competitionID={$competition->id}")?>"><?php echo __('Hide Preview', 'player-leaderboard');?></a>
                <?php } else {  ?>
                    <form method="post">
                        <input type="hidden" name="id" value="<?php echo esc_attr($competition->id)?>" />
                        <input type="hidden" name="action" value="pl_recalc_preview" />
                        <div class="pl-admin-form-group">
                            <input class="button pl-admin-button" type="submit" name="player-leaderboard-preview" value=<?php echo  __('Show Preview', 'player-leaderboard');?> />
                        </div>
                    </form>
                <?php }?>
            </div>
            <?php if (isset($rankingpreview)) {  ?>
                <?php $this->create_styles($competitionID); ?>
                <table class="pl-preview wp-list-table widefat fixed striped posts">
                    <thead>
                        <tr>
                            <th id="name" class="manage-column column-title column-primary"><?php echo __('Name', 'player-leaderboard');?></th>
                            <th id="rating" class="manage-column column-title column-primary"><?php echo __('Rating', 'player-leaderboard');?></th>
                            <th id="duels" class="manage-column column-title column-primary"><?php echo __('Duels', 'player-leaderboard');?></th>
                            <th id="points" class="manage-column column-title column-primary"><?php echo __('Points', 'player-leaderboard');?></th>
                            <th id="quotient" class="manage-column column-title column-primary"><?php echo __('Quotient', 'player-leaderboard');?></th>
                            <th id="ratingpoints" class="manage-column column-title column-primary"><?php echo __('Rating Points', 'player-leaderboard');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankingpreview as $ranking) { ?>
                            <tr>
                                <td><?php echo  esc_html($ranking->name) ?></td>
                                <td><?php if (isset($ranking->oldrating)) echo esc_html($ranking->oldrating) ?>&nbsp;->&nbsp;<?php echo  esc_html($ranking->rating) ?></td>
                                <td><?php echo  esc_html($ranking->oldduels) ?>&nbsp;->&nbsp;<?php echo esc_html($ranking->duels) ?></td>
                                <td><?php echo  esc_html(round($ranking->oldpoints,2)) ?>&nbsp;/&nbsp;<?php echo esc_html(($ranking->oldduels > 0)? round($ranking->oldpoints / $ranking->oldduels,2) : 0)?>&nbsp;->&nbsp;
                                <?php echo esc_html(round($ranking->points,2)) ?>&nbsp;/&nbsp;<?php echo esc_html(($ranking->duels > 0)? round($ranking->points / $ranking->duels,2) : 0)?></td>
                                <td><?php echo esc_html(round($ranking->oldquotient,2)) ?>&nbsp;/&nbsp;<?php echo esc_html(($ranking->oldduels > 0)? round($ranking->oldquotient / $ranking->oldduels,2) : 0)?>&nbsp;->&nbsp;
                                <?php echo esc_html(round($ranking->quotient,2)) ?>&nbsp;/&nbsp;<?php echo esc_html(($ranking->duels > 0)? round($ranking->quotient / $ranking->duels,2) : 0)?></td>
                                <td><?php echo esc_html(round($ranking->oldratingpoints,2)) ?>&nbsp;/&nbsp;<?php echo esc_html(($ranking->ratings > 1)? round($ranking->oldratingpoints / ($ranking->ratings - 1),2) : 0)?>&nbsp;->&nbsp;
                                <?php echo esc_html(round($ranking->ratingpoints,2)) ?>&nbsp;/&nbsp;<?php echo esc_html(($ranking->ratings > 0)? round($ranking->ratingpoints / $ranking->ratings,2) : 0)?></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            <?php }?>
        </div>
    <?php }?>

    <?php if ((!isset($rankingpreview)) && (isset($players)) && (!isset($mode))) { ?>
        <div class="pl-admin-column">
            <h1 class="pl-admin-header">
                <?php echo (($competition->type == 3)? __('Teams of', 'player-leaderboard') : __('Players of', 'player-leaderboard')) . ' ' . esc_html($competition->name);?>
            </h1>
            <div class="pl-admin-action-panel-top">
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo esc_attr($competition->id)?>" />
                    <input type="hidden" name="action" value="pl_recalc_competition" />
                    <div class="pl-admin-form-group">
                        <input class="button pl-admin-button" type="submit" name="player-leaderboard-recalc" value=<?php echo  __('Recalc', 'player-leaderboard');?> />
                    </div>
                </form>
            </div>
            <div>
                <table class="wp-list-table widefat fixed striped posts">
                    <thead>
                        <tr>
                            <th class="manage-column column-title column-primary"><?php echo __('Name', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Rating', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Duels', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Points', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Points Average', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Quotient', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Rating Points', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Ratings', 'player-leaderboard');?></th>
                            <th class="manage-column column-title column-primary"><?php echo __('Rating Average', 'player-leaderboard');?></th>
                            <th class="manage-column column-title"><?php echo __('Action', 'player-leaderboard');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($players as $player) { ?>
                            <tr>
                                <td><?php echo esc_html($player->name) ?></td>
                                <td><?php echo esc_html($player->rating) ?></td>
                                <td><?php echo esc_html($player->duels) ?></td>
                                <td><?php echo esc_html(round($player->points,2)) ?></td>
                                <td><?php echo esc_html(($player->duels > 0)? round($player->points / $player->duels,2) : 0)?></td>
                                <td><?php echo esc_html(($player->duels > 0)? round($player->quotient / $player->duels,2) : 0)?></td>
                                <td><?php echo esc_html(round($player->ratingpoints,2)) ?></td>
                                <td><?php echo esc_html($player->ratings) ?></td>
                                <td><?php echo esc_html(($player->ratings > 0)? round($player->ratingpoints / $player->ratings,2) : 0) ?></td>
                                <td>
                                <a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&playerID={$player->id}&competitionID={$competition->id}")?>"><?php echo __('Details', 'player-leaderboard');?></a>
                                &nbsp;|&nbsp;
                                <a class="page-action" href="<?php echo admin_url("admin.php?page=player-leaderboard-player&mode=edit&playerID={$player->id}&competitionID={$competition->id}")?>"><?php echo __('Edit', 'player-leaderboard');?></a>
                                </td>
                                <td></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php }?>
</div>
