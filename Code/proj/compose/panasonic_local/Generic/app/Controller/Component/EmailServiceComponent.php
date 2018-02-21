<?php
App::uses('Component', 'Controller');
require APP. DS. 'Vendor/autoload.php';
use Aws\Ses\SesClient;
use GuzzleHttp\Promise;

define('MY_CONCURRENCY', 6);

class ResultHelper {
    public $success = true;
    public $result_text = "";
    public $message_id = "";
}

/*
 * code origin
 * http://www.codeproject.com/Articles/786596/How-to-Use-Amazon-SES-to-Send-Email-from-PHP
 */
class EmailServiceComponent extends Component {

    //const version = "1.0";
    const AWS_SEC = 'ml7YxiYZ8HasEJZirI4REWg7sGU5irQqCfkDtHzb';
    const AWS_KEY = 'AKIAI7PNDR3G3RZAP5TA';
    const AWS_REGION = "us-west-2";
    const MAX_ATTACHMENT_NAME_LEN = 10;

    /**
     * Usage:
        $params = array(
          "to" => "email1@gmail.com",
          "subject" => "Some subject",
          "message" => "<strong>Some email body</strong>",
          "from" => "sender@verifiedbyaws",
          //OPTIONAL
          "replyTo" => "reply_to@gmail.com",
          //OPTIONAL
          "files" => array(
            1 => array(
               "name" => "filename1",
              "filepath" => "/path/to/file1.txt",
              "mime" => "application/octet-stream"
            ),
            2 => array(
               "name" => "filename2",
              "filepath" => "/path/to/file2.txt",
              "mime" => "application/octet-stream"
            ),
          )
        );
      
      $res = SESUtils::sendMail($params);
      
     * NOTE: When sending a single file, omit the key (ie. the '1 =>')
     * or use 0 => array(...) - otherwise the file will come out garbled
     * ie. use:
     *    "files" => array(
     *        0 => array( "name" => "filename", "filepath" => "path/to/file.txt",
     *          "mime" => "application/octet-stream")
     *
     * For the 'to' parameter, you can send multiple recipiants with an array
     *    "to" => array("email1@gmail.com", "other@msn.com")
     * use $res->success to check if it was successful
     * use $res->message_id to check later with Amazon for further processing
     * use $res->result_text to look for error text if the task was not successful
     *
     * @param array $params - array of parameters for the email
     * @return \ResultHelper
     */
    public static function sendMail($params) {

        $to = self::getParam($params, 'to', true);
        $subject = self::getParam($params, 'subject', true);
        $body = self::getParam($params, 'message', true);
        $from = self::getParam($params, 'from', true);
        $replyTo = self::getParam($params, 'replyTo');
        $files = self::getParam($params, 'files');

        $res = new ResultHelper();

        // get the client ready
        $client = SesClient::factory(array(
                'credentials' => array(
                    'key' => self::AWS_KEY,
                    'secret' => self::AWS_SEC,
                ),
                'region' => self::AWS_REGION,
                'version' => 'latest'
        ));

        // build the message
        if (is_array($to)) {
            $to_str = rtrim(implode(',', $to), ',');
        } else {
            $to_str = $to;
        }

        $msg = "To: $to_str\n";
        $msg .= "From: $from\n";

        if ($replyTo) {
            $msg .= "Reply-To: $replyTo\n";
        }

        // in case you have funny characters in the subject
        $subject = mb_encode_mimeheader($subject, 'UTF-8');
        $msg .= "Subject: $subject\n";
        $msg .= "MIME-Version: 1.0\n";
        $msg .= "Content-Type: multipart/mixed;\n";
        $boundary = uniqid("_Part_".time(), true); //random unique string
        $boundary2 = uniqid("_Part2_".time(), true); //random unique string
        $msg .= " boundary=\"$boundary\"\n";
        $msg .= "\n";

        // now the actual body
        $msg .= "--$boundary\n";

        //since we are sending text and html emails with multiple attachments
        //we must use a combination of mixed and alternative boundaries
        //hence the use of boundary and boundary2
        $msg .= "Content-Type: multipart/alternative;\n";
        $msg .= " boundary=\"$boundary2\"\n";
        $msg .= "\n";
        $msg .= "--$boundary2\n";

        // first, the plain text
        $msg .= "Content-Type: text/plain; charset=utf-8\n";
        $msg .= "Content-Transfer-Encoding: 7bit\n";
        $msg .= "\n";
        $msg .= strip_tags($body); //remove any HTML tags
        $msg .= "\n";

        // now, the html text
        $msg .= "--$boundary2\n";
        $msg .= "Content-Type: text/html; charset=utf-8\n";
        $msg .= "Content-Transfer-Encoding: 7bit\n";
        $msg .= "\n";
        $msg .= $body;
        $msg .= "\n";
        $msg .= "--$boundary2--\n";

        // add attachments
        if (is_array($files)) {
            $count = count($files);
            foreach ($files as $file) {
                $msg .= "\n";
                $msg .= "--$boundary\n";
                $msg .= "Content-Transfer-Encoding: base64\n";
                $clean_filename = self::clean_filename($file["name"], self::MAX_ATTACHMENT_NAME_LEN);
                $msg .= "Content-Type: {$file['mime']}; name=$clean_filename;\n";
                $msg .= "Content-Disposition: attachment; filename=$clean_filename;\n";
                $msg .= "\n";
                $msg .= base64_encode(file_get_contents($file['filepath']));
                $msg .= "\n--$boundary";
            }
            // close email
            $msg .= "--\n";
        }

        $request = array(
            'RawMessage' => array(
                'Data' => $msg
            ),
            'Source' => $from,
            'Destinations' => $to
        );

        $promiseGenerator = function() use ($client, $request) {
            yield $client->sendRawEmailAsync($request);
        };


        $retmsg = '';
        // now send the email out
        $promise = Promise\each_limit(
            $promiseGenerator(),
            MY_CONCURRENCY,
            function ($result) {
                $messageId = $result->get('MessageId');
                //echo("Email sent! Message ID: $messageId"."\n");
                $retmsg = 'Email sent, msgId:'.$messageId;
            },
            function (Exception $e) {
                //echo("The email was not sent. Error message: ");
                //echo($e->getMessage()."\n");
                $retmsg = 'Email not sent, msgId:'.$e->getMessage();
            }
        );
        $promise->wait();
        return $retmsg;
    }

    private static function getParam($params, $param, $required = false) {
        $value = isset($params[$param]) ? $params[$param] : null;
        if ($required && empty($value)) {
            throw new Exception('"'.$param.'" parameter is required.');
        } else {
            return $value;
        }
    }

    /**
    Clean filename function - to get a file friendly
    **/
    public static function clean_filename($str, $limit = 0, $replace=array(), $delimiter='-') {
        if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\.\/_| -]/", '', $clean);
        $clean = preg_replace("/[\/| -]+/", '-', $clean);
        
        if ($limit > 0) {
            //don't truncate file extension
            $arr = explode(".", $clean);
            $size = count($arr);
            $base = "";
            $ext = "";
            if ($size > 0) {
                for ($i = 0; $i < $size; $i++) {
                    if ($i < $size - 1) { //if it's not the last item, add to $bn
                        $base .= $arr[$i];
                        //if next one isn't last, add a dot
                        if ($i < $size - 2)
                            $base .= ".";
                    } else {
                        if ($i > 0)
                            $ext = ".";
                        $ext .= $arr[$i];
                    }
                }
            }
            $bn_size = mb_strlen($base);
            $ex_size = mb_strlen($ext);
            $bn_new = mb_substr($base, 0, $limit - $ex_size);
            // doing again in case extension is long
            $clean = mb_substr($bn_new.$ext, 0, $limit);
        }
        return $clean;
    }
    
    private function _send_email($params){
        // $recipients, $email_from, $subject, $body, $attachment
        extract($params);

        $this->log('_send_email, params=', 'debug');
        $this->log($params, 'debug');

        $attachment_list = null;
        if (!empty($attachment)) {
            $mime_xlsx =
                "application/octet-stream ";
                //"application/vnd.ms-excel";
                //"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            $attachment_list = array(
                "0" => array(
                    "name" => "winwin.xlsx",
                    "filepath" => $attachment,
                    "mime" => $mime_xlsx
                )
            );
        }

        $result = $this->sendMail(
            array(
                "to" => $recipients,
                "subject" => $subject,
                "message" => $body,
                "from" => $email_from,
                "replyTo" => $email_from,
                "files" => $attachment_list
            )
        );
        $this->log('_send_email: result=', 'debug');
        $this->log($result, 'debug');
    }

    public function send_approve_notification($params)
    {
        $this->log('send_approve_notification', 'debug');
        $this->log($params, 'debug');

        $body = $this->_gen_approve_with_attachment_body($params);
        $email_from = Configure::read('Generic.email_from');
        
        $subject = 'Approved(Briode): your case was approved';
        if (isset($params['contents']['map']['subject_tag'])) {
            $subject= 'Tag:'
                .$params['contents']['map']['subject_tag'].' '.$subject;
        }

        extract($params); // recipients, attachment
        $this->_send_email(
            compact(
                'body',
                'email_from',
                'subject',

                'recipients',
                'attachment'
            )
        );
    }

    /**
      * FIXME 
      * Move logic to pylib/AWSSESManager.py
      */
    private function _gen_approve_with_attachment_body($data){
        $this->log('gen_approve_with_attachment_email, data=', 'debug');
        $this->log($data, 'debug');

        $contents = $data['contents'];
        $usernames = implode(',', array_unique($data['usernames']));

        $this->log('contents:', 'debug');
        $this->log($contents, 'debug');

        $case_id = $contents['map']['subject_id'];
        $case_link = $contents['map']['url'];
        $case_creator = $contents['map']['creator_id'];
        $case_summary = $contents['map']['summary'];

        // FIXME: Panasonic specific
        /* custom fields */
        $case_requested_by = '';
        $case_ship_to = '';
        if (isset($contents['map']['Requested_by'])) {
            $case_requested_by = $contents['map']['Requested_by'];
            $case_ship_to = $contents['map']['Ship_to'];
        }

        $title = 'Your case '.$case_id.' was approved.';

        //$this->log('title='.$title, 'debug');
        //$this->log('usernames='.$usernames, 'debug');
/*
        $header = $data['data']['contents']['header'];
        $map = $data['data']['contents']['map'];
        $link = '';
        foreach( $header as $h ){
            $value = $map[$h];
            $link .= $h. ":&nbsp;". $value."<BR>";
        }
*/
        $msg =<<<END
<html>
<head>
    <title>$title</title>
</head>
<body>
Dear: $usernames

<BR>
<BR>
Your case was approved. Please see the attachment.
<BR>
<BR>
url: $case_link
<BR>
subject_id: $case_id
<BR>
creator_id: $case_creator
<BR>
summary: $case_summary
<BR>
Requested_by: $case_requested_by
<BR>
Ship_to: $case_ship_to
<BR>
<BR>
<BR>
Sent by <a href="briode.com">Briode</a> workflow engine.
</body>
</html>
END;
        return $msg;
    }
}
