define(['jquery', 'core/log','core/ajax'], function($, log,ajax) {
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

      init: function (itemdata) {
          this.register_events(itemdata);
      },

      register_events: function (itemdata) {

          var self = this;


          //listen for the grades
          window.addEventListener("message", function (event) {
              log.debug('messageevent', event);

              //if its not really our smartframe host get out of here
              if (event.origin !== itemdata.smartframehost) {
                  return;
              }

              if (event.data.hasOwnProperty(itemdata.smartframetypekey)){
                  switch (event.data.type) {
                      case itemdata.smartframegradekey:
                          //push the grades back to the server
                      case itemdata.smartframecompletekey:
                          //push the completion back to the server
                      case itemdata.smartframereportablekey:
                        //push the reportable data back to the server
                          var savedata = {}
                          $.each(itemdata.smartframedata, function (skey, svalue) {
                              if (event.data.hasOwnProperty(skey)) {
                                  savedata[skey] = event.data[skey];
                              }
                          });
                  }
              }

          });
      }//end of register events function

  };//end of return

});