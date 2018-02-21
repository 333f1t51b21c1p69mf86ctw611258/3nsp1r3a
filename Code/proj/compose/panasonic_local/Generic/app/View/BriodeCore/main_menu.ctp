    <div class="row" style="margin:2px;">
        <div class="col-md-2 hidden-xs hidden-sm">
            <?php 
            echo $this->MainView->show_menu_left('ListView', $usertype);
            ?>
        </div>

        <div class="col-md-10">
            <div class="well">
                <?php 
                    //echo $this->TabularAppPortal->show($attrDataArray, $pageFrom, $buttonSpanWidth, $buttonsToEnable); 
                    //echo $this->ListAppPortal->show($attrDataArray, $pageFrom, $buttonSpanWidth, $buttonsToEnable); 
                    echo $this->ListAppPortal->show($attrDataArray, $pluginName, $listModelName, $attrs, $detail_id);
                ?>
            
            </div>
        </div>
    </div> 


