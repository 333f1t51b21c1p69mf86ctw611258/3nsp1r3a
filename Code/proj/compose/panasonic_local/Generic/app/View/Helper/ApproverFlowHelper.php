<?php
App::uses('AppHelper', 'View/Helper');

class ApproverFlowHelper extends AppHelper {

    // Variables for editable params
    var $editable_source_html = <<<END
<table class="table table-bordered table-hover">
    <tr class="success">
        <td width="20%">No</td>
        <td>Layer</td>
        <td>Department</td>
        <td>Approver ID</td>
    </tr>
    <tbody>
    <tr> <td>1</td> <td>Applicant</td>         <td>MYDEPT    </td> <td>MYID</td> </tr>
    <tr> <td>2</td> <td>Approver 1</td> <td>APPROVERDEPT_1</td> <td>APPROVERID_1</td> </tr>
    <tr> <td>3</td> <td>Approver 2</td> <td>APPROVERDEPT_2</td> <td>APPROVERID_2</td> </tr>
    <tr> <td>4</td> <td>Approver 3</td> <td>APPROVERDEPT_3</td> <td>APPROVERID_3</td> </tr>
    <tr> <td>5</td> <td>Approver 4</td> <td>APPROVERDEPT_4</td> <td>APPROVERID_4</td> </tr>
    </tbody>
</table>
END;

    // Variables for editable params
    var $noneditable_source_html_begin = <<<END
<table class="table table-bordered table-hover">
    <tr class="success">
    <td width="20%">No</td>
    <td>Layer</td>
    <td>Department</td>
    <td>Title</td>
    <td>Approver ID</td>
    </tr>
    <tbody>
END;

    var $noneditable_source_html_end = <<<END
    </tbody>
    </table>
END;

    public function outputEditableFlow($workflow){
        $this->log('outputEditableFlow,workflow=','debug');
        $this->log($workflow, 'debug');

        // remove array label if any
        $approverFlow = $workflow['approverFlow'];
        $options = $workflow['options'];
        $wfparam = $workflow['wfparam'];
        $this->log('outputEditableFlow(), approverFlow=', 'debug');
        $this->log($approverFlow, 'debug');
        $this->log('outputEditableFlow(), options=', 'debug');
        $this->log($options, 'debug');
        $this->log('outputEditableFlow(), wfparam=', 'debug');
        $this->log($wfparam, 'debug');


        $dom = new DOMDocument;
        $dom->loadHTML($this->editable_source_html);

        $selectAttr = array( 'class'=>'span2' );

        $applicantElems = array('MYNAME', 'MYDEPT', 'MYTITLE', 'MYID');
        $approverElems  = array('APPROVERNAME');
        //sleep(1000);
        foreach ($dom->getElementsByTagName('td') as $elem){
            //$this->log('outputEditableFlow elem=');
            //$this->log($elem);
            $layer = 0;
            foreach( $approverFlow as $layer=>$approver ){
                // Create Applicant's UI
                if( $layer== 0 ){
                    foreach( $applicantElems as $elemKey ){
                        $regEx = '/'. $elemKey. '/';
                        // echo $regEx;
                        if( preg_match($regEx, $elem->textContent, $matched ) ){
                            // create TD as container
                            $td = $dom->createElement('td');
                            $td->nodeValue = $approver[$elemKey];
                            // replace DOM's TD with the container
                            //$this->log('parentNode=');
                            //$this->log($elem->parentNode);
                            //$this->log('textContent=');
                            //$this->log($elem->textContent);
                            $elem->parentNode->replaceChild($td, $elem);
                        }
                     }
                } else {
                    foreach( $approverElems as $elemKeyBase ){
                        $elemKey = $elemKeyBase. '_'. $layer;
                        //echo "***approverRepl";
                        //print_r($approver[$elemKey]);
                        //echo "approverRepl***";
                        $regEx = '/'. $elemKey. '/';
                        // echo $regEx;
                        if( preg_match($regEx, $elem->textContent, $matched ) ){
                            // create TD as container
                            $td = $dom->createElement('td');
                            $td->nodeValue = $approver[$elemKey];
                            // replace DOM's TD with the container
                            $elem->parentNode->replaceChild($td, $elem);
                        }
                    }
                }
                // Create Approver's UI
                foreach( $options as $pdKeyBase=>$pdOptions ){
                    $pdKey = $pdKeyBase. '_'. $layer;
                    $regEx = '/'. $pdKey. '/';
                    // echo $regEx;
                    if( preg_match($regEx, $elem->textContent, $matched ) ){
                        // echo $matched, "\n";
                        // set select
                        $pulldown = $dom->createElement('select');
                        foreach( $selectAttr as $attr=>$val ){
                            $pulldown->setAttribute($attr, $val);
                        }
                        $pulldown->setAttribute('name', $pdKey);
                        $pulldown->setAttribute('id', $pdKey);
                        $pulldown->setAttribute('style', 'margin-bottom:5px;margin-top:5px;');
                        // set option & default value
                        $selected = 1;
                        $selectedValue = "";
                        for($j=0; $j<count($pdOptions); $j++){
                            $option = $dom->createElement('option');
                            $option->setAttribute('value',$pdOptions[$j]);
                            if ($pdOptions[$j] == $approver[$pdKey] ){
                                $selected = $j;
                                $selectedValue = $pdOptions[$j];
                                $option->setAttribute('selected', 'selected');
                            }
                            $option->nodeValue = $pdOptions[$j];
                            $pulldown->appendChild($option);
                        }
                        //echo "selected=". $selected. "\n";
                        //$pulldown->setAttribute('selected', $selectedValue);

                        // create TD as container
                        $td = $dom->createElement('td');
                        $td->appendChild($pulldown);

                        // replace DOM's TD with the container
                        $elem->parentNode->replaceChild($td, $elem);
                    }
                }
            }
        }
        $optionsHTML = $dom->saveHTML();

        // replace remainig APPROVER_X with selectable options
        $dom = new DOMDocument;
        $dom->loadHTML($optionsHTML);
        foreach ($dom->getElementsByTagName('td') as $elem){
            for( $i=$layer; $i<=$wfparam['MaxLayer']; $i++ ){
                $pdKey = 'APPROVER'. '[A-Z]*'. '_'. $i;
                $regEx = '/'. $pdKey. '/';
                //echo $regEx, $elem->textContent;
                if( preg_match($regEx, $elem->textContent, $matchedArray ) ){
                    //print_r($matched);
                    foreach( $options as $optionKey=>$optionValues ){
                        $matched = $matchedArray[0];
                        if( $matched == $optionKey. '_'. $i ){
                            $pulldown = $dom->createElement('select');

                            foreach( $selectAttr as $attr=>$val ){
                                $pulldown->setAttribute($attr, $val);
                            }

                            $pulldown->setAttribute('name', $matched);
                            $pulldown->setAttribute('id', $matched);
                            $pulldown->setAttribute('style', 'margin-bottom:5px;margin-top:5px;');

                            for($j=0; $j<count($optionValues); $j++){
                                $option = $dom->createElement('option');
                                $option->setAttribute('value',$optionValues[$j]);
                                $option->nodeValue = $optionValues[$j];
                                $pulldown->appendChild($option);
                            }

                            // create TD as container
                            $td = $dom->createElement('td');
                            $td->appendChild($pulldown);

                            // replace DOM's TD with the container
                            $elem->parentNode->replaceChild($td, $elem);
                        }
                    }
                }
            }
        }
        $optionsHTML = $dom->saveHTML();

        return $optionsHTML;
    }

    public function outputNonEditableFlow($approvers){

        $title = "dummyTitle";
        $id = "dummyId";
        $approverLines = "";
        $layer = 0;
        foreach( $approvers as $app ){
            $approverLayer = NULL;
            if( $app['row'] == '1' ){
                $approverLayer = "Applicant ";
            }else{
                $approverLayer = "Approver ". $layer;
            } 
            $approverLines = $approverLines.  "<tr> <td>". 
                             $app['row']. "</td> <td>". 
                             $approverLayer. "</td> <td>". 
                             $app['department']. "</td> <td>". 
                             $app['title']. "</td> <td>". 
                             $app['id']. "</td> </tr>\n";
            $layer ++;
        }

        $msg = $this->noneditable_source_html_begin. 
               $approverLines. 
               $this->noneditable_source_html_end;

        return $msg;
    }
}

?>
