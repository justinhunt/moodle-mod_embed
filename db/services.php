<?php
/**
 * Services definition.
 *
 * @package mod_embed
 * @author  Justin Hunt - poodll.com
 */

$functions = array(

        'mod_embed_do' => array(
                'classname'   => 'mod_embed_external',
                'methodname'  => 'do',
                'description' => 'updates instance details the current user',
                'capabilities'=> 'mod/embed:submit',
                'type'        => 'write',
                'ajax'        => true,
        )

);