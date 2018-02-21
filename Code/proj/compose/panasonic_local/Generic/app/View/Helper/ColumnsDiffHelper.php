<?php
App::uses('AppHelper', 'View/Helper');

class ColumnsDiffHelper extends AppHelper {
    public function display_column_diff($diff1, $diff2){
        $msgs = '';
        if (isset($diff1)&&isset($diff2)) {
            if ($diff1) {
                $msgs .= '<div class="alert alert-warning">Column(s) ';
                foreach ($diff1 as $value) {
                    $msgs .= $value. ' ';
                }
                $msgs .= 'were deactivated <br></div>';
            }
            if ($diff2) {
                $msgs .= '<div class="alert alert-error alert-warning">Column(s) ';
                foreach ($diff2 as $value) {
                    $msgs .= $value. ' ';
                }
                $msgs .= 'were added <br></div>';
            }
        }
        return $msgs;
    }
}
