define(['jquery','core/log','core/ajax', 'core/notification'], function($,log, ajax, notification) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('Embed selector: initialising');


    return{

        init: function(opts) {
            //pick up opts from html
            var theid = '#amdopts_' + opts.controlid;
            var configcontrol = $(theid).get(0);
            if (configcontrol) {
                opts = JSON.parse(configcontrol.value);
                $(theid).remove();
            } else {
                //if there is no config we might as well give up
                log.debug(' Controller: No config found on page. Giving up.');
                return;
            }

            this.register_events(opts['embedables'],opts['triggercontrol'], opts['updatecontrol']);
        },

        register_events: function(embedables,trigger,update){
            $('[name="' + trigger + '"]').on('change',function(){
                var newvalue = $(this).val();
                var selectedembedable=false;
                $.each(embedables,function(){
                    if(this.id==newvalue){
                        selectedembedable=this;
                    }
                });
                if(selectedembedable) {
                    $('[name="' + update + '"]').val(selectedembedable.details);
                    if(selectedembedable.details.trim()===''){return;}
                    var tdata=[];
                    tdata['details'] =selectedembedable.details.split('\n');
                    templates.render('mod_embed/embedable_details',tdata).then(
                        function(html,js){
                            var d= $('#mod_embed_embedable_details');
                            d.html(html);
                        }
                    );
                    //lets also hide the red warning message
                    $('.mod_embed_selector_explainer').hide();
                }
            });
        }

};//end of return value
});

