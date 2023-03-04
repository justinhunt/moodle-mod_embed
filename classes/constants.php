<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace mod_embed;

defined('MOODLE_INTERNAL') || die();

class constants
{
    //component name, db tables, things that define app
    const M_COMPONENT='mod_embed';
    const M_MODNAME='embed';
    const M_TABLE='embed';
    const M_ATTEMPTSTABLE='embed_attempt';
    const M_APP_TABLE='embed_app';
    const M_URL='/mod/embed';
    const M_CLASS_APPSCONTAINER ='appscontainer';
    const M_USE_DATATABLES=true;
    const M_CLASS='mod_embed';

    //app level
    const M_APPLEVEL_SITE =0;
    const M_APPLEVEL_COURSE =1;

    //grading options
    const M_GRADENONE= 0;
    const M_GRADECOMPLETION= 1;
    const M_GRADETOOL= 2;

    //completion options
    const M_COMPLETIONNONE = 0;
    const M_COMPLETIONTIME = 1;
    const M_COMPLETIONTOOL = 2;


    //embed options
    const EMBED_NONE=0;
    const EMBED_FREE=1;
    const EMBED_FLIP=2;
    const EMBED_BUNCEE=3;
    const EMBED_VOICEBOOK=4;
    const EMBED_ZENGENGO=5;
    const EMBED_ENGLISHCENTRAL=6;
    const EMBED_YOUTUBE=7;
}