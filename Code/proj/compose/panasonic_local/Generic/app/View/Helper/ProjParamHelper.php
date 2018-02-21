<?php
App::uses('AppHelper', 'View/Helper');

class ProjParamHelper extends AppHelper {

    public function setParam($proj_params)
    {
        $html_params = '';
        foreach ($proj_params as $key=>$value) {
            $csskey = 'projparam_'.$key;
            $html_params .= "<input type=\"hidden\" name=\"$key\" id=\"$csskey\" value=\"". $value. "\">\n";
        }
        return $html_params;
    }
}
