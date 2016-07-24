<?php

require_once('MistAPI.php');

/*
 * Template class for MistAPI
 * --------------------------
 * To create endpoints use pattern 'method_name'_'endpoint'. Arguments are parsed as array.
 *
 */
class TemplateAPI extends MistAPI
{

    public function get_template(){
        return ['MistAPI' => 'Version 0.1beta'];
    }

}