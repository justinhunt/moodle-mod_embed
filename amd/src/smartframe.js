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

              if (event.data.hasOwnProperty(itemdata.typekey)){
                  // note that there is no "break" and a single event can grade, complete and report
                  switch (event.data[itemdata.typekey]) {
                      case itemdata.gradekey:
                          //push the grades back to the server
                          ajax.call([{
                              methodname: 'mod_embed_do_grade',
                              args: {contextid: self.contextid, itemdata: itemdata},
                              done: self.callback
                          }]);
                      case itemdata.completekey:
                          //push the completion back to the server
                          ajax.call([{
                              methodname: 'mod_embed_do_complete',
                              args: {contextid: self.contextid, itemdata: itemdata},
                              done: self.callback
                          }]);
                      case itemdata.reportkey:
                        //push the reportable data back to the server
                          /*
                          var savedata = {}
                          $.each(itemdata.smartframedata, function (skey, svalue) {
                              if (event.data.hasOwnProperty(skey)) {
                                  savedata[skey] = event.data[skey];
                              }
                          });
                          */
                          ajax.call([{
                              methodname: 'mod_embed_do_report',
                              args: {contextid: self.contextid, itemdata: itemdata},
                              done: self.callback
                          }]);

                  }
              }

          });
      },//end of register events function

      callback: function (itemdata) {
          //probably override this
      },

  };//end of return

});