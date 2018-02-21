<?php
App::uses('AppHelper', 'View/Helper');

class MainViewHelper extends AppHelper {
    public function showHeader(){
        return $this->showLogo();
    }

    public function show_menu_left($activeTab){
        $breadcrumb = array('#'=> array('Main View', 'active'));
        $itemToUrlMap = array(
                    array('ListView',       'main_menu')
        );

        return $this->left_submenu($breadcrumb, $activeTab, $itemToUrlMap);
    }

    public function show_url_list($list, $span=2){
        return $this->ul_a_href($list,$span);
    }

    public function showUserInfo($userInst){
        $uname = $userInst['username'];
        $name  = $userInst['name'];
        $title = $userInst['title'];
        $userInfo = <<<END
                <div class="bottomBorder row">
                    <div class="paddingTop col-sm-3 col-md-3">
                        <p>
                            <img class="img60" style="margin-left:20px;" src="/Generic/app/webroot/img/$uname.jpg">
                        </p>
                    </div>
                    <div class="paddingTop col-sm-7 col-md-7" style="margin-left:0px;">
                        <h4 style="margin-bottom:5px;white-space:nowrap;" href="#">$name</h4>
                        <h5 class="font_color2" style="white-space:nowrap;margin:5px 0;font-weight:500;font-family:'Helvetica Neue',Arial,'Hiragino Kaku Gothic Pro',Meiryo,'MS PGothic',sans-serif;" href="#">$title</h5>
                    </div>
                </div>
                <div style="height:150px;"></div>>
END;
       return $userInfo;
    }

    public function showLogo(){
        $logo = <<<END
                <div class="bottomBorder row">
                    <div class="paddingTop col-sm-3 col-md-3">
                        <p>
                            <img style="margin-left:20px;" class="" src="/Generic/app/webroot/img/default.jpg">
                        </p>
                    </div>
                    <div class="col-sm-7 col-md-7" style="padding-top:25px; ">
                        <h4 class="font_color2" align="left" style="font-weight:500;">ENSPIREA LLC's BRIODE</h4>
                    </div>
                </div>
END;
        return $logo;
    }

    public function showCopyright(){
        $copyright = <<<END
<p class="font_color2" style=" font-weight:500; margin: 0;">© 2014 Enspirea LLC</p>
END;
        return $copyright;
    }
}




