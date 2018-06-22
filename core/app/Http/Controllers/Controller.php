<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    //Service response success
    public function jsonresSuccess($output) {
        $outputArr["status"] = true;
        $outputArr["response_code"] = 200;
        $outputArr["response"] = $output;
        $request_header_params = getallheaders();
        echo json_encode($outputArr);
    }
    
     public function jsonresServerError($ex) {
        $request_header_params = getallheaders();
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 424;
        $outputArr["message"] = "The server is unable to connect.";
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: ' . (string) $ex . "\r\n" . 'Tracking: ' . 'testers ' . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/server_error-" . createddate() . ".log", $error_log_message, FILE_APPEND);
        $this->username = array('felixrajesh.s@mookambikainfo.com
');
        $this->mailsubject = 'Error Log ' . createddatetime();
        $this->attachment = env("APP_LOG_PATH") . "/server_error-" . createddate() . ".log";
        $this->filename = "server_error-" . createddate() . ".log";
        $data = array('errormessage' => json_encode('testers '));
//        Mail::send('emails.errorlog', $data, function ($message) {
//            $message->from('noreply@innothinkglobal.com', 'InnoAssist');
//            $message->to($this->username)->subject($this->mailsubject);
//            $message->attach($this->attachment, [ 'as' => $this->filename, 'mime' => 'application/log']);
//        });
        session()->forget('companyid');
        return response($outputArr, 504)->header('Content-Type', 'text/plain');
        exit;
    }
    
    //Service response error  
    public function jsonresError($output = array('error' => array('Bad request'))) {
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 400;
        $outputArr["response"] = $output;
        $request_header_params = getallheaders();
        echo json_encode($outputArr);
    }
    
}
