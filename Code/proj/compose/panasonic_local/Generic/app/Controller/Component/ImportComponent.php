<?php
App::uses('Component', 'Controller');
class ImportComponent extends Component {

    public $components = array('Auth', 'TimeCardReader');

    private $filename;

    private $objPHPExcel;
    private $activeSheet;
    private $highest_row;
    private $highest_column;
    private $row_index = 1;
   
    private $header;
    private $keys;
    private $rows;

    /*
    private function getActiveSheet($excelObj)
    {
        if (empty($this->activeSheet)) {
            $this->activeSheet = $excelObj->getActiveSheet();
        }
        return $this->activeSheet;
    }
    */

    private function read_row()
    {
        //$activeSheet = $this->getActiveSheet($this->objPHPExcel);

        $row = array();
        for( $j='A'; $j != $this->highest_column; ++$j ){
            $cell_id = "$j".strval($this->row_index);
            $cell_val = $this->activeSheet->getCell($cell_id)->getFormattedValue();
            $cell_val = PHPExcel_Shared_String::SanitizeUTF8($cell_val);
            $row[$cell_id] = $cell_val;
        }
        $this->row_index++;

        return $row;
    }

    private function set_keys(){
        $this->keys = array();
        foreach($this->header as $cell_id=>$cell_val){
            $key_pattern = '/(.*)\(key\)$/';
            if( preg_match($key_pattern, $cell_val, $matched) ){
                $this->header[$cell_id] = $matched[1];
                $this->keys[$cell_id] = $matched[1];
            }
        }
        //$this->log('set_keys:header:', 'debug');
        //$this->log($this->header, 'debug');
        //$this->log('set_keys:keys:', 'debug');
        //$this->log($this->keys, 'debug');
    }

    private function read_header(){
        if( $this->row_index > $this->highest_row ) return;
        $this->header = $this->read_row();
        $this->set_keys();
    }

    private function read_rows(){
        $this->rows = array();
        while( $this->row_index <= $this->highest_row ){
            $to_save = array();
            $cur_row = $this->read_row();
            for( $i='A'; $i!=$this->highest_column; $i++ ){
                $header_index = $i.'1';
                $cur_index = $i.strval($this->row_index-1);
                $to_save[$this->header[$header_index]] = $cur_row[$cur_index];
            }
            $to_save['creator_id'] = $this->Auth->user('username');
            $to_save['created_at'] = date('Y-m-d H:i:s');
            $to_save['updator_id'] = $this->Auth->user('username');
            $to_save['updated_at'] = date('Y-m-d H:i:s');
            array_push( $this->rows, $to_save );
        }
    }

    private function init_php_excel()
    {
        $this->objPHPExcel = PHPExcel_IOFactory::load($this->filename);
        $this->activeSheet = $this->objPHPExcel->getActiveSheet();
        $this->highest_row = $this->activeSheet->getHighestRow();
        $this->highest_column = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

        $this->highest_column++;
    }

    private function remove_matched_row($pluginModel, $condition){
        $this->log('remove_matched_row:', 'debug');
        $this->log($condition, 'debug');
        $pluginModel->deleteAll($condition, false);
    }

    private function get_condition_for_matched_keys($pluginModelName, $a_row){
        $retval = array();
        foreach($this->keys as $cell_id=>$cell_val){
            $retval[$pluginModelName.'.'.$cell_val] = $a_row[$cell_val];
        }

        $this->log('get_condition_for_matched_keys:', 'debug');
        $this->log($retval, 'debug');
        return $retval;
    }

    public function get_rows($import_file, $pluginModel, $pluginModelName){
        //FIXME filename hard-coded
        $this->filename = $import_file;

        $this->init_php_excel();
        $this->read_header();
        $this->read_rows();

        // remove all matching rows
        if( isset($this->keys) && isset($this->rows) ){
            foreach( $this->rows as $a_row ){
                $condition = $this->get_condition_for_matched_keys($pluginModelName, $a_row);
                $this->remove_matched_row($pluginModel, $condition);
            }
        }

        return $this->rows;
    }

    // FIXME: App25(meiji) specific
    public function get_timesheet_rows($daysPluginModel, $daysPluginModelName, $monthPluginModel, $monthPluginModelName, $holidayPluginModel, $holidayPluginModelName, $handlerPluginModel, $handlerPluginModelName, $import_file, $year_month){

        $this->log('get_timesheet_rows,year/month:', 'debug');
        $this->log($year_month, 'debug');
        $this->log('get_timesheet_rows,target_file:'.$import_file, 'debug');
        $data = $this->TimeCardReader->read_timecard($holidayPluginModel,
                                                     $holidayPluginModelName,
                                                     $handlerPluginModel,
                                                     $handlerPluginModelName,
                                                     $import_file, 
                                                     $year_month['year'],
                                                     $year_month['month']);
        
        // check if data of target date range already exists
        // if so delete them first
        foreach( $data as $name=>$metadata ){
            $begin_year = intval($metadata['year']);
            $begin_month = intval($metadata['month']);
            $end_year = intval($metadata['year']);
            $end_month = intval($metadata['month']) + 1;
            if( intval($metadata['month']) == 12 ){
                $end_year ++;
                $end_month = 1;
            }
            $begin_date = strval($begin_year).'-'.strval($begin_month).'-'.'01';
            $end_date = strval($end_year).'-'.strval($end_month).'-'.'01';
            // remove existing data before import
            $date_col_name = 'work_date';
            $daysPluginModel->deleteAll(array(
                            $daysPluginModelName.'.'.$date_col_name.'>='.$begin_date,
                            $daysPluginModelName.'.'.$date_col_name.'<'.$end_date,
            ));
            $year_col_name = 'target_year';
            $month_col_name = 'target_month';
            $this->log('deleting year/month for summary:'.$begin_year.'/'.$begin_month, 'debug');
            $monthPluginModel->deleteAll(array(
                            $monthPluginModelName.'.'.$year_col_name => $begin_year,
                            $monthPluginModelName.'.'.$month_col_name => $begin_month,
            ));
            break;
        }

        $days_to_save = array();
        foreach( $data as $name=>$metadata ){
            $state = $metadata['state'];
            $dept = $metadata['dept'];
            $days = $metadata['data'];
            foreach( $days as $d => $min ){
                $workhour = $min['workmin'];
                $non_workhour = $min['non_workmin'];
                array_push($days_to_save, array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updator_id' => $this->Auth->user('username'),
                    'State' => $state,
                    'Department' => $dept,
                    'Name' => $name,
                    'work_date' => $d,
                    'workday_minutes' => $workhour,
                    'non_workday_minutes' => $non_workhour,
                ));
            }
        }

        $month_to_save = array();
        foreach( $data as $name=>$metadata ){
            $year = $metadata['year'];
            $month = $metadata['month'];
            $workdays = $metadata['workdays'];
            $state = $metadata['state'];
            $dept = $metadata['dept'];
            $total_workmin = $metadata['total_workmin'];
            $overtime_min = intval($total_workmin) - (8*60*intval($workdays)); 
            $overtime_min = ( $overtime_min > 0 ) ? $overtime_min : 0;
            array_push($month_to_save, array(
                'updated_at' => date('Y-m-d H:i:s'),
                'updator_id' => $this->Auth->user('username'),
                'State' => $state,
                'Department' => $dept,
                'Name' => $name,
                'target_year' => $year,
                'target_month' => $month,
                'workday_minutes' => $total_workmin,
                'business_days' => $workdays,
                'overtime_minutes' => $overtime_min,
            ));
        }

        return array('days' => $days_to_save, 
                     'months' => $month_to_save);
    }
}
