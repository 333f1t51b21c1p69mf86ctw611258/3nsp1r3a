<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Helper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class AppHelper extends Helper {

    public function show_breadcrumb($links){
        $bc_begin = "<ol class=\"breadcrumb\">";
        $bc_end = "</ol>";
        $bc = '';
        foreach($links as $url=>$labelAndActive){
            $label  = $labelAndActive[0];
            $active = $labelAndActive[1]; 
            $bc .= "<li class=\"$active\"><a href=\"$url\">$label</a></li>";
        }
 
        return $bc_begin . $bc . $bc_end;
    }

    protected function ul_a_href($list, $colLocation){
        $ul_begin = '<ul class="nav nav-tabs nav-stacked">';
        $li = '';
        foreach($list as $url=>$map){
            $label = $map[0];
            $class = $map[1];
            $count = $map[2];
            $li .=<<<END
        <li class="$class">
            <a href="$url">
                <div align="left" class="col-sm-$colLocation col-md-$colLocation">
END;
            $badge = '';
            if( $count!=0 ){
                $badge =<<<END
                    <span class="badge pull-right">$count</span>
END;
            }
            $li .= $badge;

            $li .=<<<END
                    $label
                </div>
                <i class="glyphicon glyphicon-icon-right"></i>
            </a>
        </li>
END;
        }
        $ul_end = '</ul>';
        return $ul_begin. $li. $ul_end;
    }

    protected function wrap_well($content){
        return '<div class="well">'. $content. '</div>';
    }

    protected function copyright(){
        return '<p class="font_color2" style=" font-weight:500; margin:10px 20px;">&copy 2014-2016 Enspirea LLC</p>';
    }

    protected function convert_date($rawData){
        // FIXME US locale is used
        $pattern = '/^([0-9][0-9][0-9][0-9])\-([0-9]*)\-([0-9]*)(.*)$/';
        if( preg_match($pattern, $rawData, $matches) ){
            return $matches[2] .'/'. $matches[3] .'/'. $matches[1]. $matches[4];
        }
        return $rawData;
    }

    // FIXME: this is specific to workflow...
    protected function convert_status($raw_data){
        if( $raw_data == Configure::read('Status.open') ) return 'Open';
        if( $raw_data == Configure::read('Status.in_progress') ) return 'In Progress';
        if( $raw_data == Configure::read('Status.closed') ) return 'Closed';
        return $raw_data;
    }

    // FIXME: this is specific to workflow...
    protected function convert_OKNG($raw_data){
        if( $raw_data == 1 ) return '<font color="blue">OK</font>';
        if( $raw_data == 0 ) return '<font color="red"><b>NG</b></font>';
        return $raw_data;
    }

    protected function raw_to_label($attr, $raw_data){
        if( in_array($attr, array('status')) ){
            return $this->convert_status($raw_data);
        }
        if( in_array($attr, array('mandatory_flag', 'validation_flag')) ){
            return $this->convert_OKNG($raw_data);
        }

        // FIXME not efficient
        return $this->convert_date($raw_data);
    }

    protected function left_submenu($breadcrumb, $activeTab, $itemToUrlMap, $countsPerItem=NULL){
        $list = array();

        foreach($itemToUrlMap as $item=>$val){
            $item = $val[0];
            $url = $val[1];
            $count = 0;
            if( $countsPerItem != NULL && array_key_exists($item, $countsPerItem) ){
                $count = $countsPerItem[$item];
            }
            $active = '';
            if( strcmp($activeTab, $item)==0 ){
                $active = 'active';
            }
            $list[$url] = array($item, $active, $count);
        }

        $bc = $this->show_breadcrumb($breadcrumb);

        $list = $this->wrap_well($this->ul_a_href($list, 11));

        $copyright = $this->copyright();

        return $bc. $list. $copyright;
    }
}
