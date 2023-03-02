define(['jquery', 'core/log'], function($, log) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('Poodll Embed SmartFrame: initialising');

  return {

    //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
     },

    init: function(itemdata) {
      this.register_events(itemdata);
    },

    register_events: function(itemdata) {
      
      var self = this;


      //listen for the grades
      window.addEventListener("message", function(event) {
        log.debug('messageevent',event);

            //if its not really our smartframe host get out of here
            if (event.origin !== itemdata.smartframehost) {
                return;
            }
            //pass back results and transition
            var gradesdata = event.data;
            //each instance of teacher tools itemtype will catch this event, so we need to filter the ones we handle to just this instanc
           //thats why we pass the itemdata around
            if(typeof gradesdata != 'undefined' && gradesdata.itemid == itemdata.id) {
                var thepercent = gradesdata.percent;

                $(".minilesson_nextbutton").prop("disabled", true);
                setTimeout(function () {
                    $(".minilesson_nextbutton").prop("disabled", false);
                    self.next_question(gradesdata.percent);
                }, 500);
            }
          }, false
      );
      
    }
  };
});