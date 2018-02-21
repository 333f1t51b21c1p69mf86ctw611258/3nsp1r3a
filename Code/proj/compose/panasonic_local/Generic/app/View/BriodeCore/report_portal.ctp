    <div class="row">
        <div class="col-md-12">
            <?php
            echo $this->MainView->show_breadcrumb('Reports');
            $birt_baseurl = "http://192.168.1.7:8080/birt/preview?__title=Briode%20Report&__report=";
            $birt_baseurl_ext = "http://remote.enspirea.com:8080/birt/preview?__title=Briode%20Report&__report=";
            $list = array(  
                //"report_portal" =>array("Summary", 'Active'),
                $birt_baseurl."P1.rptdesign" => array("Report Portal(internal)", NULL),
                $birt_baseurl_ext."P1.rptdesign" => array("Report Portal(external)", NULL),

                //$birt_baseurl."reportportal.rptdesign" => array("Experimental Portal", NULL),
                //$birt_baseurl."meiji.rptdesign" => array("List of all states(per year)", NULL),
                //$birt_baseurl."meiji_sub.rptdesign" => array("Data for Illinois", NULL),
                //$birt_baseurl."meiji2.rptdesign&RP_State=IL" => array("Data for Illinois", NULL), 
                );
            echo $this->MainView->show_url_list($list, 12);
            echo $this->MainView->showFooter();
            ?>
        </div>
<!--
        <div class="col-md-10">
            <?php
            //echo $this->ReportAppPortal->show();
            ?>
        </div>
-->
    </div>

