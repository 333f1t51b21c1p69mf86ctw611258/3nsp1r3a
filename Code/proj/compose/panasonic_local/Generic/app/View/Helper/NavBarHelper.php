<?php
App::uses('AppHelper', 'View/Helper');

class NavBarHelper extends AppHelper {
    var $helpers = array('Form');
    private function get_op($operations){
        if( count($operations)==0 ) return '';//'<li><ul></ul></li>';

        $op_begin =<<<END
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Operations<b class="caret"></b></a>
              <ul class="dropdown-menu">
END;

        $op_options = '';
        $this->log('navbar operations=', 'debug');
        $this->log($operations, 'debug');
        foreach( $operations as $index=>$linkAndLabel ){
            $op_options .= "<li><a href=\"". $linkAndLabel[0]. "\">".$linkAndLabel[1]."</a></li>";
        }

        $op_end =<<<END
              </ul>
            </li>
END;
        return $op_begin. $op_options. $op_end;
    }

    private function get_admin_menu($pluginName, $admin_menu, $tomcatBaseUrl){
        if( strcmp($admin_menu,'disable')==0 ) return '';

        if( isset($tomcatBaseUrl['internal']) ){
            $internal_url = $tomcatBaseUrl['internal'];
        }
        if( isset($tomcatBaseUrl['external']) ){
            $external_url = $tomcatBaseUrl['external'];
        }
        $admin =<<<END
              <ul class="dropdown-menu">
                <li><a href="/Generic/$pluginName/upload_layout">Upload Excel Layout</a></li>
END;
        /*
        if( !empty($internal_url) ){
            $admin .=<<<END
                <li><a href="$internal_url/adminconsoleuib" target="_blank">Admin Console(Internal)</a></li>
END;
        }
        */
        if( !empty($external_url) ){
            $admin .=<<<END
                <li><a href="$external_url/adminconsoleuib" target="_blank">Admin Console</a></li>
END;
        }
        $admin .=<<<END
                <li><a href="/Generic/users/events">Login Activities</a></li>
              </ul>
END;

        return $admin;
    }

    public function getNavBar($name, $pluginName, $pluginModelName, $appName, $urlAndLabel, $operations, $enableReport, $admin_menu, $birt_service, $tomcatBaseUrl, $sessionid){
        # FIXME menu is static, to be generated dynamically
        $outMsg_1 = <<<END
  <div class="navbar navbar-inverse navbar-static-top">
    <div class="navbar-inner" style="background-image: linear-gradient(to bottom,#3d3d3d,#242424);">
      <div class="container-liquid">
        <a class="navbar-brand visible-xs" target="_blank" href="http://www.briode.com">Briode</a>
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <!--<span class="icon-bar">-</span>-->
            <span class="icon-bar">-</span>
            <span class="icon-bar">-</span>
          </button>
        </div>
        <div class="collapse navbar-collapse"> 
          <ul class="nav navbar-nav">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="/Generic/app/webroot/img/applicationSwitch.png" style="height:24px; width:24px;">
              </a>
              <ul class="dropdown-menu">
END;
        $app_list = ''; 
        foreach($urlAndLabel as $url=>$label){
            $app_list .= '<li><a href="';
            $app_list .= '/Generic/'.$url;
            $app_list .= '">'.$label.'</a></li>';
        }

        $outMsg_2 =<<<END
              </ul>
            </li>

            <li> <a class="navbar-brand hidden-xs hidden-sm" target="_blank" href="http://www.briode.com">Briode</a></li>
            <li class='hiddden-xs hidden-sm' style="font-size:18px;"><a href="/Generic/$pluginName/main_menu">$appName</a></li>
END;
        $op = $this->get_op($operations);

        $report =<<<END
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports<b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="https://admin-demo.briode.com/$pluginName/report_menu">Report Menu</a></li>
                <li><a href="https://admin-demo.briode.com/$birt_service/preview?__title=Briode%20Report&amp;__report=ForEnspireademo%2FEP1.rptdesign&sessionid=$sessionid">BI Report Sample</a></li>
              </ul>
            </li>
END;
        if( !$enableReport ){
            $report = '';
        }

        $outMsg_4 =<<<END
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="/Generic/app/webroot/img/default.jpg" style="height:24px; width:24px;">
                $name
                <b class="caret"></b>
              </a>
              <ul class="dropdown-menu">
                <li><a href="/Generic/Users/password_change">Profile</a></li>
                <li><a href="/Generic/Users/logout">Logout</a></li>
              </ul>
            </li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <span class="glyphicon glyphicon-cog" style="font-size:20px;"></span>
              </a>
END;
        $this->log('get_admin_menu='.$admin_menu, 'debug');
        $admin = $this->get_admin_menu($pluginName,$admin_menu, $tomcatBaseUrl);

        $outMsg_5 =<<<END
            </li>
          </ul>
          <div class="navbar-form navbar-right">
END;

        // FIXME: make design consistent with Bootstrap
        $url = '/Generic/'.strtolower($pluginName).'/'.strtolower($pluginModelName).'s/index';
        $pluginModelNameCapitalized = ucfirst(strtolower($pluginModelName));
        $form_id = $pluginModelNameCapitalized. 'IndexForm';
        $input_id = $pluginModelNameCapitalized. 'Keywords';
        $search =<<<END
            <form action="$url" id="$form_id" method="post" accept-charset="utf-8">
              <div style="display:none;">
                <input type="hidden" name="_method" value="POST"/>
              </div>
              <input type="text" id="$input_id" name="data[$pluginModelNameCapitalized][keywords]" class="form-control" placeholder="Search...">
            </form>
END;

        $outMsg_6 =<<<END
          </div>
        </div>
      </div>
    </div>
  </div>
END;
        return $outMsg_1. $app_list. $outMsg_2. $op. $report. $outMsg_4. $admin. $outMsg_5. $search. $outMsg_6;
    }
}
