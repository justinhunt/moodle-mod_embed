<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_embed\output;

use \mod_embed\constants;
use \mod_embed\utils;

class renderer extends \plugin_renderer_base {



    public function do_iframe_embed($embed){
        $the_url = utils::get_src_from_iframe($embed->embeddata);
        if(!empty($the_url)) {
            $tdata = [];
            $tdata['iframe_src_url'] = $the_url;
            //finally render template and return
            return $this->render_from_template('mod_embed/embed_free', $tdata);
        }else{
            return '';
        }
    }

}
