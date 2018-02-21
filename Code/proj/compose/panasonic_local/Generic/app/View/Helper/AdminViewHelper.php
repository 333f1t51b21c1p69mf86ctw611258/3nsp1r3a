<?php
App::uses('AppHelper', 'View/Helper');

class AdminViewHelper extends AppHelper {

    public function show_left(){
        $bc = $this->show_breadcrumb('Administration');

        $left1 = '';

        // FIXME plugin name is hardcoded
        $list = array( "/Generic/App1/upload_layout"    =>array("Upload Excel Layout", NULL), );
        $left2 = $this->wrap_well($this->ul_a_href($list, 12));

        $left3 = $this->copyright();

        return $bc. $left1. $left2. $left3;
    }

    public function show_upload_excel_right(){
    }
}
