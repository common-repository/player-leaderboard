(function( $ ) {
	'use strict';

	$(document).ready(function(){
        $(".pl-results").hide();

        $(".div-pl-table-body-cell a.details").click(function () {

            var $player1 = $(this).attr('player1');
            var $player2 = $(this).attr('player2');

             var $dialogresults = '#pl-results-' + $player1 + '-' + $player2;

             $($dialogresults).dialog( {
                  modal: true,
                  autoOpen: false,
                  buttons: [{
                        text: "Close",
                        click: function() { $(this).dialog("close"); }
                    }],
                  overlay: {
                     opacity: 0.7,
                     background: "black"
                  }
               });
            $($dialogresults).dialog('open');
		});

        $(".pl-standings a.details").click(function () {

            var $player = $(this).attr('player');

             var $dialogresults = '#pl-results-' + $player;

             $($dialogresults).dialog( {
                  modal: true,
                  autoOpen: false,
                  buttons: [{
                        text: "Close",
                        click: function() { $(this).dialog("close"); }
                    }],
                  overlay: {
                     opacity: 0.7,
                     background: "black"
                  }
               });
            $($dialogresults).dialog('open');
		});

        $(".pl-details").click(function() {

            $(".pl-results-row").hide();

            if ($(this).hasClass("pl-plus"))
            {
                $(".pl-minus").hide();
                $(".pl-plus").show();
                $(this).next().show();

                var detail = $(this).parent().parent().next();
                $(detail).css("display", "table-row");
            }
            else if ($(this).hasClass("pl-plus-month"))
            {
                $(".pl-results-month").hide();
                $(".pl-minus-month").hide();
                $(".pl-plus-month").show();
                $(this).next().show();
                var detail = $(this).parent().parent().next();
                $(detail).css("display", "table-row");
            }
            else if ($(this).hasClass("pl-plus-year"))
            {
                $(".pl-results-month").hide();
                $(".pl-results-year").hide();
                $(".pl-minus-month").hide();
                $(".pl-plus-month").show();
                $(".pl-minus-year").hide();
                $(".pl-plus-year").show();
                $(this).next().show();
                var detail = $(this).parent().parent().next();
                $(detail).css("display", "table-row");
            }
            else
            {
                var detail = $(this).parent().parent().next();
                $(detail).css("display", "none");
                $(this).prev().show();
            }
            $(this).hide();
        });

        $('.sortable th').click(function() {
            var table = $(this).parents('table').eq(0);
            var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
            this.desc = !this.desc
            if (this.desc){
                rows = rows.reverse()
            }
            for (var i = 0; i < rows.length; i++){
                table.append(rows[i])
            }
        });

        function comparer(index) {
            return function(a, b) {
                var valA = getCellValue(a, index);
                var valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
            }
        }

        function getCellValue(row, index){
            return $(row).children('td').eq(index).text();
        }
  });

})( jQuery );
