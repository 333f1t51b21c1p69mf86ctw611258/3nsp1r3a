<?php
/**
 * The calling Controller
 *
 * @var Controller
 */
App::uses('Component', 'Controller');
class TimeCardReaderComponent extends Component {

    //public $controller;

    private $row_data = array();
    private $current_row = 1;
    private $end_of_people = false;
    private $end_of_dept = false;
    private $end_of_state = false;
    private $end_of_file = false;

    private $objPHPExcel;
    private $activeSheet;
    private $highestRow;
    private $highestColumn;

    private $year;
    private $month;
    private $state;
    private $dept;
    private $person_name;

    private $person_data;

    private $people_data;

    private $calendar_holidays = 0;

    public function startup(Controller $controller) {
        $this->controller = $controller;
    }

    private function init_php_excel($timesheet_file){
        if( isset($this->objPHPExcel) ){
            unset($this->objPHPExcel);
            $this->people_data = array();
        }
        $this->end_of_people = false;
        $this->end_of_dept = false;
        $this->end_of_state = false;
        $this->end_of_file = false;
        $this->current_row = 1;
        $this->objPHPExcel = PHPExcel_IOFactory::load($timesheet_file);
        $this->activeSheet = $this->objPHPExcel->getActiveSheet();
        $this->highestRow = $this->activeSheet->getHighestRow();
        $this->highestColumn = $this->objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    }

    private function read_row($msg=NULL){
        if( $msg!=NULL ){
            //print_r("\n".'read_row'.$this->current_row.':'.$msg);
        }
        $this->row_data = array(
            'A'=>$this->activeSheet->getCell('A'.$this->current_row)->getFormattedValue(),
            'B'=>$this->activeSheet->getCell('C'.$this->current_row)->getFormattedValue(),
            'C'=>$this->activeSheet->getCell('D'.$this->current_row)->getFormattedValue(),
        );
        $this->current_row++;
    }

    private function undo_read()
    {
        $this->current_row--;
    }

    private function read_person_name($handlerPluginModel, $handlerPluginModelName)
    {
        $this->read_row('read_person_name');

        $pattern = '/([\w]*), ([\w]*)/';
        if (!preg_match($pattern, $this->row_data['A'], $matches)) {
            $this->end_of_file = true;
            return false;
        }

        $timecard_name = $this->row_data['A'];
        $this->log('finding timecard name:'.$timecard_name,'debug');
        $person = $handlerPluginModel->findByTimeCard($timecard_name);
        if (!empty($person)) {
            $this->log('person in handler=','debug');
            $this->log($person, 'debug');
            $this->person_name = $person[$handlerPluginModelName]['AS400'];
        } else {
            // FIXME
            // if person not found in handler list, mark NOT FOUND
            $this->log('timecard name not found!','debug');
            //$this->end_of_file = true;
            $this->person_name = 'NOT FOUND:'.$timecard_name;
        }

        return true;
    }

    private function read_person_week()
    {
        $this->read_row('read_person_week');

        $pattern = '/[0-9]*\/[0-9]*\/[0-9]* \- [0-9]*\/[0-9]*\/[0-9]*/';
        if( !preg_match($pattern, $this->row_data['A'], $matches) ){
            $this->end_of_file = true;
            return false;
        }

        return true;
    }

    private function read_person_header()
    {
        $this->read_row('header');
       
        // do nothing

        if($this->row_data['A']!="Date"){
           $this->end_of_file = true;
           return false;
        }

        return true;
    }


    private function read_person_days_of_week()
    {
        for ($i=0; $i<7; $i++) {
            $this->read_row('read_person_days_of_week with i='.$i);

            // read date, hours
            $pattern_date = '/([0-9]*)\-([0-9]*)\-([0-9]*)/';
            $pattern_hour = '/([0-9]*)hr\s([0-9]*)min/';
            if (!preg_match($pattern_date, $this->row_data['A'], $matches_date)) {
                $this->end_of_file = true;
                $this->log('UNEXPECTED DATE FORMAT', 'debug');
                return false;
            }
            
            $current_date = DateTime::createFromFormat('Y-m-d', 
                '20'
                .$matches_date[3]
                .'-'
                .$matches_date[1]
                .'-'.$matches_date[2]
            );

            $this->log('current_date=', 'debug');
            $this->log($current_date, 'debug');

            // skip prev month' dates if any
            $current_month = intval($matches_date[1]);
            if ($current_month != intval($this->month)) {
                $this->log('skipping irrelevant month', 'debug');
                continue; // previous month data - skip and process next
            }

            $total_workhour_in_min = 0;
            $total_non_workhour_in_min = 0;
            if (empty($this->row_data['B'])) {
                // do nothing
            } else if (preg_match($pattern_hour, $this->row_data['B'], $matches_workhour)) {
                $total_workhour_in_min 
                    = intval($matches_workhour[1])*60 + intval($matches_workhour[2]);
            } else {
                $this->end_of_file = true;
                $this->log('UNEXPECTED WORKHOUR FORMAT', 'debug');
                return false;
            }
            if (empty($this->row_data['C'])) {
                // do nothing
            } else if (preg_match($pattern_hour, $this->row_data['C'], $matches_non_workhour)) {
                $total_non_workhour_in_min 
                    = intval($matches_non_workhour[1])*60 + intval($matches_non_workhour[2]);
            } else {
                $this->end_of_file = true;
                $this->log('UNEXPECTED NON-WORKHOUR FORMAT', 'debug');
                return false;
            }

            $date_normalized = '20'.$matches_date[3].'/'.$matches_date[1].'/'.$matches_date[2];
            $this->person_data[$date_normalized] = array(
                                'workmin' => $total_workhour_in_min,
                                'non_workmin' => $total_non_workhour_in_min,
            );
        }

        // skip total lines
        for ($i=0; $i<2; $i++) {
            $this->read_row('skip_x2');
        }
    }
    
    private function read_person($handlerPluginModel, $handlerPluginModelName)
    {
        if (!$this->read_person_name($handlerPluginModel, $handlerPluginModelName)) {
            $this->end_of_people = true;
            $this->undo_read();
            return;
        }

        $this->log('reading person: '.$this->person_name, 'debug');
        $this->person_data = array();
        while (true) {
            if (!$this->read_person_header()) {
                $this->read_row('skip_x2 to next user');
                $this->read_row('skip_x2 to next user');
                return;
            }

            $this->read_person_days_of_week();
        }
    }

    private function get_workdays_of_month($month, $year)
    {
        $lastday = date("t",mktime(0,0,0,$month,1,$year));
        $weekdays=0;
        for ($day=29;$day<=$lastday;$day++) {
            $wd = date("w",mktime(0,0,0,$month,$day,$year));
            if($wd > 0 && $wd < 6) $weekdays++;
        }

        return $weekdays + 20 - $this->calendar_holidays;
    }

    private function read_people($handlerPluginModel, $handlerPluginModelName)
    {
        $this->end_of_people = false;
        while (!$this->end_of_people) {
            $this->read_person($handlerPluginModel, $handlerPluginModelName);
            $this->people_data[$this->person_name] = array(
                'year'     => $this->year,
                'month'    => $this->month,
                'workdays' => $this->get_workdays_of_month($this->month, $this->year),
                'state'    => $this->state,
                'dept'     => $this->dept,
                'data'     => $this->person_data,
            );
        }
    }

    private function read_dept($handlerPluginModel, $handlerPluginModelName)
    {
        $this->read_row('read_dept');
       
        $pattern = '/[A-Za-z\s][A-Za-z\s][A-Za-z\s][A-Za-z\s]*/'; 
        if (!preg_match($pattern, $this->row_data['A'], $matches)) {
            $this->end_of_dept = true;
            $this->undo_read();
            return;
        }

        $this->dept = $matches[0];
        $this->read_people($handlerPluginModel, $handlerPluginModelName);
    }

    private function read_state($handlerPluginModel, $handlerPluginModelName)
    {
        $this->end_of_state = false;
        $this->read_row('read_state');

        $pattern = '/^[A-Z][A-Z]$/';
        if (!preg_match($pattern, $this->row_data['A'], $matches)) {
            $this->undo_read();
            $this->end_of_state = true;
            return;
        }
        $this->state = $matches[0];

        $this->end_of_dept = false;
        while (!$this->end_of_dept) {
            $this->read_dept($handlerPluginModel, $handlerPluginModelName);
        }
    }

    private function add_total()
    {
        foreach ($this->people_data as $person => $data) {
            $total_min = 0;
            $summary = array();
            foreach ($data['data'] as $date => $hours) {
                $total_min += intval($hours['workmin'])
                              + intval($hours['non_workmin']);
            }
            $this->people_data[$person]['total_workmin'] = $total_min;
        }
    }

    private function get_calendar_holidays(
        $holidayPluginModel, 
        $holidayPluginModelName
    ) {
        $this->calendar_holidays = $holidayPluginModel->find('count',array(
            'conditions' => array(
                'MONTH('.$holidayPluginModelName.'.Holiday_Date) =' => $this->month
            )
        ));
        $this->log('calendar_holidays='.$this->calendar_holidays, 'debug');
    }

    // read Excel
    public function read_timecard(
        $holidayPluginModel,
        $holidayPluginModelName,
        $handlerPluginModel,
        $handlerPluginModelName,
        $timesheet_file,
        $year,
        $month
    ) {
        $this->year = $year;
        $this->month = $month;
        $this->init_php_excel($timesheet_file);

        $this->get_calendar_holidays($holidayPluginModel, $holidayPluginModelName);

        while (!$this->end_of_state) {
            $this->read_state($handlerPluginModel, $handlerPluginModelName);
        }

        $this->add_total();
        // array of people
        // array( <name> => array(
        //            <year>     => ??,
        //            <month>    => ??,
        //            <workdays> => ??,
        //            <state>    => ??,
        //            <dept>     => ??,
        //            <data>     => array(
        //                  <date_1> => array( 
        //                         <workmin>     => ??,
        //                         <non_workmin> => ??,
        //                  ),
        //                  <date_2> => array( 
        //                         <workmin>     => ??,
        //                         <non_workmin> => ??,
        //                  ),
        //                  .....
        //            ),
        //            <total_workmin> => ??,
        //        );
        // );

        $this->log('people_data=', 'debug');
        $this->log($this->people_data, 'debug');

        return $this->people_data;
    }
}
