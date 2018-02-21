<?php
class AppConfigController extends AppController {

    public function index() {
        $data = $this->AppConfig->generateTreeList(
          null,
          null,
          null,
          '&nbsp;&nbsp;&nbsp;'
        );
        debug($data); die;
    }
}
