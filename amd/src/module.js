// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript library for the mod_embed module.
 *
 * @package    mod
 * @subpackage mod_embed
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log'], function($, log) {
    "use strict"; // jshint ;_;

    /*
    This file is to manage the quiz stage
     */

    log.debug('Mod Embed main module : initialising');

    return {





    
    // Define a function to handle the AJAX response.
    complete: function(id,o,args) {
    	var id = id; // Transaction ID.
        var returndata = o.responseText; // Response data.
        var Y = M.mod_embed.jscomplete.Y;
    	//console.log(returndata);
        var result = Y.JSON.parse(returndata);
        if(result.success){
        	M.mod_embed.jscomplete.iscomplete = true;
        	M.mod_embed.timer.iscomplete = true;
        	if(M.mod_embed.timer.showcompletion){
        		Y.one('#embed-completed').setStyle('display', 'block');
        	}
        }
    },
    
    docomplete: function(){
    	if (M.mod_embed.jscomplete.iscomplete){return;}
    	var Y = M.mod_embed.jscomplete.Y;
    	var uri  = 'ajaxcomplete.php?id=' +  M.mod_embed.jscomplete.cmid +
    			'&sesskey=' + M.cfg.sesskey;
		Y.on('io:complete', M.mod_embed.jscomplete.complete, Y,null);
		Y.io(uri);
		return;
    },

// The countdown timer that is used on timed quizzes.
  thetimer: {

    //cleared to false on init
    iscomplete: true,

    // Timestamp at which time runs out, according to the student's computer's clock.
    endtime: 0,

    // This records the id of the timeout that updates the clock periodically,
    // so we can cancel.
    timeoutid: null,

    /**
     * @param Y the YUI object
     * @param start, the timer starting time, in seconds.
     * @param preview, is this a quiz preview?
     */
    init: function(Y, start,  cmid, showcompletion,iscomplete) {


        if(start>0 && !iscomplete){

            this.update();

        }else if(iscomplete){
            if(showcompletion){
                $('#embed-completed').show();
            }
        }
    },

    /**
     * Stop the timer, if it is running.
     */
    stop: function(e) {
        if (timeoutid) {
            clearTimeout(timeoutid);
        }
    },

    /**
     * Function to convert a number between 0 and 99 to a two-digit string.
     */
    two_digit: function(num) {
        if (num < 10) {
            return '0' + num;
        } else {
            return num;
        }
    },

    // Define a function to handle the AJAX response.
    complete: function(id,o,args) {
        var returndata = o.responseText; // Response data.

        $('#embed-timer').hide();
        var result = JSON.parse(returndata);
        if(result.success){
            this.iscomplete = true;
            if(this.showcompletion){
                $('#embed-completed').show();
            }
        }

    },

    // Function to update the clock with the current time left, and submit the quiz if necessary.
    update: function() {
        var secondsleft = Math.floor((this.endtime - new Date().getTime())/1000);

        // If time has expired, set the hidden form field that says time has expired and submit
        if (secondsleft < 0) {
            this.stop(null);
            var uri  = 'ajaxcomplete.php?id=' +  M.mod_embed.timer.cmid +
                '&sesskey=' + M.cfg.sesskey;
            //console.log(uri);
            Y.on('io:complete', this.complete, Y,null);
            Y.io(uri);
            return;
        }

        // If time has nearly expired, change the colour.
        if (secondsleft < 100) {
            $('#embed-timer').removeClass('timeleft' + (secondsleft + 2))
                .removeClass('timeleft' + (secondsleft + 1))
                .addClass('timeleft' + secondsleft);
        }

        // Update the time display.
        var hours = Math.floor(secondsleft/3600);
        secondsleft -= hours*3600;
        var minutes = Math.floor(secondsleft/60);
        secondsleft -= minutes*60;
        var seconds = secondsleft;
        $('#embed-time-left').text(hours + ':' +
            M.mod_embed.timer.two_digit(minutes) + ':' +
            M.mod_embed.timer.two_digit(seconds));


        // Arrange for this method to be called again soon.
        this.timeoutid = setTimeout(M.mod_embed.timer.update, 100);
    }
}


    }
});