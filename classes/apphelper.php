<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/08/29
 * Time: 16:12
 */

namespace mod_embed;


class apphelper
{
    protected $cm;
    protected $context;
    protected $mod;
    protected $apps;

    public function __construct($cm) {
        global $DB;
        $this->cm = $cm;
        $this->mod = $DB->get_record(constants::M_TABLE, ['id' => $cm->instance], '*', MUST_EXIST);
        $this->context = \context_module::instance($cm->id);
    }

    public function fetch_apps()
    {
        global $DB;
        if (!$this->apps) {
           /*
            $sql ='SELECT * FROM {' . constants::M_APP_TABLE . '} WHERE moduleid = :moduleid OR ' .
                    ' (courseid = :courseid AND applevel=' . constants::M_APPLEVEL_COURSE . ') ORDER BY name ASC';
            $this->apps = $DB->get_records_sql($sql, ['moduleid' => $this->mod->id, 'courseid' => $this->mod->course]);
           */
            $this->apps = $DB->get_records(constants::M_APP_TABLE , []);
        }
        if($this->apps){
            return $this->apps;
        }else{
            return [];
        }
    }

    public function fetch_app($appid)
    {
        global $DB;
        if (!$this->apps) {
            $apps=$this->fetch_apps();
        }
        if($this->apps){
            foreach($this->apps as $app){
                if($app->id == $appid){
                    return $app;
                }
            }
            return false;
        }else{
            return false;
        }
    }


    public function fetch_apps_for_js(){

        $apps = $this->fetch_apps();
        return $apps;
    }


}//end of class