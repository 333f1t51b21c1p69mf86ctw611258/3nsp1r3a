<?php 
require_once(APP . 'Vendor' . DS . 'aws' . DS . 'sdk.class.php');
require_once(APP . 'Vendor' . DS . 'aws' . DS . 'services' . DS . 'ses.class.php');

class AWSSESComponent extends Object{

public $ses;
public $emailViewPath = '/Emails';
public $emailLayouts = 'Emails';
public $htmlMessage;
public $from = 'admin@briode.com';
public $returnPath = 'admin@briode.com';
public $to;

public function initialize($controller)
{

}


function startup($controller)
{
   $this->controller =$controller;

   $this->ses = new AmazonSES(array('certificate_authority' => false,
       'key' => 'AKIAJTNLWDJHA4JHQYPQ',
       'secret' => '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'));

}

public function beforeRender($controller)
{
}

public function shutdown($controller)
{
}

public function beforeRedirect($controller)
{
}

private function _gen_briode_subject($subject, $mailContent)
{
    $tag = null;
    if (isset($mailContent['subject_tag'])) $tag = $mailContent['subject_tag'];
    $this->log('_gen_briode_subject', 'debug');
    $this->log($mailContent, 'debug');
    $this->log('tag='.$tag, 'debug');

    /* create a subject with rep info */
    if (empty($tag)) return $subject;

    return 'Tag:'. $tag. " ". $subject;
}

public function _aws_ses($viewTemplate, $mailContent = null)
{
    $this->log('_aws_ses', 'debug');
    //$this->log('default mailContent BEGIN');
    //$this->log($this->controller->request->data);
    //$this->log($this->controller->request->data[$this->controller->modelClass]);
    //$this->log('default mailContent END');

    if(!empty($this->controller->request->data) && $mailContent == null){
        if( isset($this->controller->request->data[$this->controller->modelClass]) ){
            $mailContent = $this->controller->request->data[$this->controller->modelClass];
        }
    }

    $mail = $this->email_templates($viewTemplate);

    $destination = array(
       'ToAddresses' => $this->to
    );
    $message = array(
       'Subject' => array(
           'Data' => $this->_gen_briode_subject($mail['Subject'], $mailContent)
       ),
       'Body' => array()
    );


    $this->controller->set('data', $mailContent);

    $this->htmlMessage = $this->_getHTMLBodyFromEmailViews($mail['ctp']);

    if ($this->htmlMessage != NULL) {
       $message['Body']['Html'] = array(
           'Data' => $this->htmlMessage
       );
   }

   $this->log('AWS:sending email:', 'debug');
   //$this->log($this->from);
   //$this->log($destination);
   //$this->log($message);

    // set forwarded-to bounce email address
   $opt = array('ReturnPath'=>$this->returnPath);
   $response = $this->ses->send_email($this->from, $destination, $message, $opt);
   //$this->log('email status:');
   //$this->log($response);

   $ok = $response->isOK();
   $this->log($ok, 'debug');

   if (!$ok) {
       $this->log('Error sending email from AWS SES: ' . $response->body->asXML(), 'debug');
   }
   return $ok;
}

public function email_templates($name)
{
   $this->templates = array(
	'notifyWFCreation' => array(
       		'ctp' => 'notifyWFCreation', 
		    'Subject' => 'Created(Briode): new case was created'
   		),
	'actionRequired' => array(
       		'ctp' => 'actionRequired', 
		    'Subject' => 'Assigned(Briode): your action is required'
   		),
	'notifyUpdate' => array(
       		'ctp' => 'notifyUpdate', 
		    'Subject' => 'Briode: database update notification'
   		),
	'issueApproved' => array(
       		'ctp' => 'issueApproved', 
		    'Subject' => 'Approved(Briode): your case was approved'
   		),
	'issueRejected' => array(
       		'ctp' => 'issueRejected', 
		    'Subject' => 'Rejected(Briode): your case was rejected'
   		),
	'approved' => array(
            'ctp' => 'approved', 
            'Subject' => 'Approved(Briode): your case was approved'
        ),
	'itaLoggedIn' => array(
            'ctp' => 'itaLoggedIn', 
            'Subject' => 'Briode: ITA login detected')
    );

   return $this->templates[$name];
}

public function _getHTMLBodyFromEmailViews($view)
{
   $currentLayout = $this->controller->layout;
   $currentAction = $this->controller->action;
   $currentView = $this->controller->view;
   $currentOutput = $this->controller->output;

   ob_start();
   $this->controller->output = null;

   $viewPath = $this->emailViewPath . DS . 'html' . DS . $view;
   $layoutPath = $this->emailLayouts . DS . 'html' . DS . 'default';

   $bodyHtml = $this->controller->render($viewPath, $layoutPath);

   ob_end_clean();

   $this->controller->layout = $currentLayout;
   $this->controller->action = $currentAction;
   $this->controller->view = $currentView;
   $this->controller->output = $currentOutput;

   return $bodyHtml;
}
}


