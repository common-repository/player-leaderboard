(function( $ ) {
	'use strict';

	$(document).ready(function(){
        var currentForm;

        $(".pl-dialog-confirm").hide();
        $(".pl-players-tab").hide();

        $('.pl-admin-color-field').wpColorPicker();
        $('.pl-admin-color-readonly').wpColorPicker();

		$('input[name="player-leaderboard-delete"]').click(function(e) {
		     currentForm = $(this).closest('form');
             var id = currentForm.find('[name = "id"]').val();
             var successurl = currentForm.find('[name = "success"]').val();
             var postaction = currentForm.find('[name = "action"]').val();
             var formaction  = currentForm.attr('action');

             $('#dialog-delete-confirm').dialog( {
                  modal: true,
                  autoOpen: false,
                  buttons: [{
                        text: adminobject.no,
                        click: function() { $(this).dialog("close"); }
                    },{
                        text: adminobject.yes,
                        click: function() {
                            $.ajax({
                                url: formaction,
                                type: 'post',
                                data: {
                                    action: postaction,
                                    id: id,
                                },
                                success: function(response) {
                                    window.location.href = successurl;
                                }
                            })
                            $(this).dialog("close");
                        }
                    }],
                  overlay: {
                     opacity: 0.7,
                     background: "black"
                  }
               });
            $('#dialog-delete-confirm').dialog('open');
            return false;
		});

        $('input[name="player-leaderboard-recalc"]').click(function(e) {
		     currentForm = $(this).closest('form');
             $('#dialog-recalc-confirm').dialog( {
                  modal: true,
                  autoOpen: false,
                  buttons: [{
                        text: translations.no,
                        click: function() { $(this).dialog("close"); }
                    },{
                        text: translations.yes,
                        click: function() { currentForm.submit(); $(this).dialog("close");  }
                    }],
                  overlay: {
                     opacity: 0.7,
                     background: "black"
                  }
               });
            $('#dialog-recalc-confirm').dialog('open');
            return false;
		});

		$('.competitionselect').change(function() {
		    window.location.href = window.location.href + '&competitionID=' + $(this).val();
		});

		$('.competitiondayselect').change(function() {
		    currentForm = $(this).closest('form');
            currentForm.submit();
		});

        $('.pl-player-tab').click(function(e) {
            $('.pl-player-tab').toggleClass('nav-tab-active');
            $('.pl-results-tab').toggle();
            $('.pl-players-tab').toggle();
            return false;
		});

		$('.pl-typeselect').change(function() {
		    switch ($(this).val())
            {
                case "1":
                    $('.pl-type-double').hide();
                    $('.pl-type-team').hide();
                    $('.pl-type-single').show();
                    break;
                case "2":
                    $('.pl-type-single').hide();
                    $('.pl-type-team').hide();
                    $('.pl-type-double').show();
                    break;
                case "3":
                    $('.pl-type-single').hide();
                    $('.pl-type-double').hide();
                    $('.pl-type-team').show();
                    break;
            }
		});

		$('.pl-kindofsportselect').change(function() {
		    switch ($(this).val())
            {
                case 'Tennis':
                    $('.pl-type-team-tennis').show();
                    $('.pl-type-team-badminton').hide();
                    $('.pl-type-team-tabletennis').hide();
                    $('.pl-type-team-squash').hide();
                    break;
                case 'TableTennis':
                    $('.pl-type-team-tennis').hide();
                    $('.pl-type-team-badminton').hide();
                    $('.pl-type-team-tabletennis').show();
                    $('.pl-type-team-squash').hide();
                    break;
                case 'Badminton':
                    $('.pl-type-team-tennis').hide();
                    $('.pl-type-team-badminton').show();
                    $('.pl-type-team-tabletennis').hide();
                    $('.pl-type-team-squash').hide();
                    break;
                case 'Squash':
                    $('.pl-type-team-tennis').hide();
                    $('.pl-type-team-badminton').hide();
                    $('.pl-type-team-tabletennis').hide();
                    $('.pl-type-team-squash').show();
                    break;
            }
		});
  });

})( jQuery );
