<?php
App::uses('AppHelper', 'View/Helper');

class ReportViewHelper extends AppHelper {
    public function show($pluginName, $birtBaseUrl, $list_of_reports){
        $header =<<<END
<div class="table-responsive">
    <table class="table table-striped">
END;
        $internal_url_base = '';
        $external_url_base = '';
        foreach( $birtBaseUrl as $label=>$url ){
            if( strcmp($label, 'internal')==0 ){
                $internal_url_base = $url;
            }
            if( strcmp($label, 'external')==0 ){
                $external_url_base = $url;
            }
        }

        $list = '';
        foreach($list_of_reports as $title=>$report_name){
            $list .= '<tr>';
            $list .= '<td>'.$title.'</td>';
            if( !empty($internal_url_base) ){
                $url = $internal_url_base. '/birtmgr/preview?__report='.$report_name.'.rptdesign';
                $list .= '<td><a href="'.$url.'">internal</a></td>';
            }
            if( !empty($external_url_base) ){
                $url = $external_url_base. '/birtmgr/preview?__report='.$report_name.'.rptdesign';
                $list .= '<td><a href="'.$url.'">external</a></td>';
            }
            $list .= '</tr>';
        }

        $footer =<<<END
    </table>
</div>
END;
        return $header. $list. $footer;
    }
}
