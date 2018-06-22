<?php

//
//  This is a Base Controller which is extended in all controller - Used to Validate tocken, Gendrate token, Tracking and response method Following are the Function used in it.
//    checkUserAuth -- Tocken Validation, Gendrate token and Tracking
//    jsonresSuccess -- Response as success formart
//    jsonresError -- Response as error formart
//    jsonrestokenError -- Response as token not-valid or expired
//    
//
//  Controller          : baseController
//  Author              : Jeya Prakash
//  Project             : 2iequip
//

namespace App\Http\Controllers;

use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Route;
//use App\CustomDbModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Mail;
use App\Jobs\LogsHandler;
use Cache;

class baseController extends Controller {

    protected $UserData;
    protected $endAccessToken;
    protected $s3path;

    public function __construct(Request $request) {
        //Call to checkUserAuth
//        $this->checkUserAuth($request);
    }

    public function checkUserAuth($request) {
        try {
            //Requesting the header details to get the token
            $request_header_params = $request->headers->all();
            $authorizer = app('oauth2-server.authorizer');
            $authorizer->validateAccessToken();
            $getauthusderid = $authorizer->getResourceOwnerId();

            if (!empty($getauthusderid)) {
                $this->UserData = CustomDbModel::getsessionuser($getauthusderid);
                if (isset($this->UserData->id) && !empty($this->UserData->id) && isset($this->UserData->user_type)) {

                    if (isset($request_header_params['logintype'][0])) {
                        $this->UserData->login_type = 1;
                        $checkloginuser = CustomDbModel::rowCheckquery('user_mobile_tokens', array('condition' => array('fk_users_id' => $this->UserData->id, 'device_id' => $request_header_params['deviceid'][0])));
                        if (!$checkloginuser)
                            return $this->jsonrestokenError($this->UserData->login_type);
                    } else {
                        $this->UserData->login_type = 2;
                    }

                    if (!empty($this->UserData->settings->notification_type)) {
                        $this->UserData->settings->notification_type = explode(',', $this->UserData->settings->notification_type);
                    }

                    if (isset($this->UserData->settings->time_zone) && !empty($this->UserData->settings->time_zone)) {
                        date_default_timezone_set($this->UserData->settings->time_zone);
                    } else
                        date_default_timezone_set('UTC');

                    \Config::set('app.timezone', date_default_timezone_get());

                    if ($this->UserData->business_type != config('2iequipstatus.businesstype.equipment_user') && ($this->UserData->user_type == config('2iequipstatus.usertype.customer') || $this->UserData->user_type == config('2iequipstatus.usertype.appuser')) && $this->UserData->customermapped == false) {
                        $this->jsonresusercustomervalidation();
                        exit;
                    }
                    if ($this->UserData->business_type == config('2iequipstatus.businesstype.equipment_user') && ($this->UserData->user_type == config('2iequipstatus.usertype.loactionhead') || $this->UserData->user_type == config('2iequipstatus.usertype.machineowner') || $this->UserData->user_type == config('2iequipstatus.usertype.appuser')) && $this->UserData->locationmapped == false) {
                        $this->jsonresuserlocation();
                        exit;
                    }
                } else {
                    if (isset($this->UserData->login_type)) {
                        return $this->jsonrestokenError($this->UserData->login_type);
                        exit;
                    } else {
                        return $this->jsonrestokensertverError();
                        exit;
                    }
                }
            }
        } catch (\Exception $ex) {
            return $this->jsonrestokensertverError(false);
            exit;
        }
    }

    //Service response success
    public function jsonresSuccess($output) {
        $outputArr["status"] = true;
        $outputArr["response_code"] = 200;
        $outputArr["response"] = $output;
        $request_header_params = getallheaders();
        if (env('APP_LOG_LEVEL') == 'info') {
            $error_log_message = '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Success ' . "\r\n" . 'User_Name: ' . $this->UserData->username . ' User_Email: ' . $this->UserData->emailid . ' User_Type:' . config('2iequipstatus.usertype.' . $this->UserData->user_type) . ' User_CompanyName: ' . $this->UserData->settings->company_name . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------';
            dispatch(new LogsHandler($error_log_message, 'info-' . createddate()));
        }
        if (env('APP_DEBUG') == true) {
            $error_log_message = '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Success ' . "\r\n" . 'User_Name: ' . $this->UserData->username . ' User_Email: ' . $this->UserData->emailid . ' User_Type:' . config('2iequipstatus.usertype.' . $this->UserData->user_type) . ' User_CompanyName: ' . $this->UserData->settings->company_name . "\r\n" . 'Request: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . 'Response: ' . json_encode($output) . "\r\n" . '------------';
            dispatch(new LogsHandler($error_log_message, 'debug-' . createddate()));
        }

        echo json_encode($outputArr);
    }

    //Service response error  
    public function jsonresError($output = array('error' => array('Bad request'))) {
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 400;
        $outputArr["response"] = $output;
        $request_header_params = getallheaders();
        if (env('APP_LOG_LEVEL') == 'info') {
            $error_log_message = '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: ' . json_encode($output) . "\r\n" . 'User_Name: ' . $this->UserData->username . ' User_Email: ' . $this->UserData->emailid . ' User_Type:' . config('2iequipstatus.usertype.' . $this->UserData->user_type) . ' User_CompanyName: ' . $this->UserData->settings->company_name . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------';
            dispatch(new LogsHandler($error_log_message, 'response_error_log-' . createddate()));
        }

        if (env('APP_DEBUG') == true) {
            $error_log_message = '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Success ' . "\r\n" . 'User_Name: ' . $this->UserData->username . ' User_Email: ' . $this->UserData->emailid . ' User_Type:' . config('2iequipstatus.usertype.' . $this->UserData->user_type) . ' User_CompanyName: ' . $this->UserData->settings->company_name . "\r\n" . 'Request: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . 'Response: ' . json_encode($output) . "\r\n" . '------------';
            dispatch(new LogsHandler($error_log_message, 'debug-' . createddate()));
        }

        echo json_encode($outputArr);
    }

    //Token not available or not mached 
    public function jsonrestokenError($logintype) {
        $request_header_params = getallheaders();
        if ($logintype != 1) {
            $outputArr["status"] = FALSE;
            $outputArr["response_code"] = 424;
            $outputArr["response"] = 'Unauthorized request or Session Expired';
            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
            file_put_contents(env("APP_LOG_PATH") . "/session_error_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

            return response($outputArr, 404)->header('Content-Type', 'text/plain');
        } else {
            CustomDbModel::deletequery('user_mobile_tokens', array('fk_users_id' => $this->UserData->id, 'device_id' => $request_header_params['deviceid']), true);
            $outputArr["status"] = FALSE;
            $outputArr["response_code"] = 424;
            $outputArr["response"] = array('message' => 'Unauthorized request or Session Expired');
            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
            file_put_contents(env("APP_LOG_PATH") . "/session_error_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

            echo json_encode($outputArr);
        }
        exit;
    }

    public function jsonrestokensertverError($logoutuser = true) {
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 424;
        if ($logoutuser == true) {
            $outputArr["response"] = array('message' => 'Your email password has been changed. If not please change your password immediately');
            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Reason : Your email password has been changed. If not please change your password immediately' . "\r\n" . '------------' . PHP_EOL;
            file_put_contents(env("APP_LOG_PATH") . "/session_error_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

            echo json_encode($outputArr);
        } else {
            $outputArr["response"] = array('message' => 'Your session has been cleared or logged out');

            return response($outputArr, 504)->header('Content-Type', 'text/plain');
        }
        exit;
    }

    public function trackinginformation($request) {
        $request_header_params = $request;
        $logintype = isset($request_header_params['logintype'][0]) ? $request_header_params['logintype'][0] : 2;
        if ($logintype == 2) {
            $agent = new Agent();
            $broser = $agent->browser();
            $tracking_data = array('fk_users_id' => $this->UserData->id, 'user_name' => $this->UserData->username, 'email' => $this->UserData->emailid, 'user_type' => config('2iequipstatus.usertype.' . $this->UserData->user_type), 'fk_company_id' => $this->UserData->usermaping->company, 'company_name' => $this->UserData->settings->company_name, 'access_token' => $request_header_params['Authorization'], 'tracking_date' => createddatetime(), 'tracking_ip' => getenv('REMOTE_ADDR'), 'tracking_agent' => ($agent->isDesktop() ? $agent->platform() : $agent->device()), 'tracking_browser_details' => $broser . ' / Version ' . $agent->version($broser), 'tracking_url' => isset($request_header_params['Referer'])?$request_header_params['Referer']:'', 'tracking_controller' => Route::getCurrentRoute()->getActionName());
        }elseif ($logintype == 3) {
            $tracking_data = array();
        } else {
            $tracking_data = array('fk_users_id' => $this->UserData->id, 'user_name' => $this->UserData->username, 'email' => $this->UserData->emailid, 'user_type' => config('2iequipstatus.usertype.' . $this->UserData->user_type), 'company_name' => $this->UserData->settings->company_name, 'fk_company_id' => $this->UserData->usermaping->company, 'access_token' => $request_header_params['Authorization'], 'tracking_date' => createddatetime(), 'tracking_ip' => $request_header_params['deviceid'], 'tracking_agent' => $request_header_params['devicetype'], 'tracking_controller' => Route::getCurrentRoute()->getActionName());
        }

        return $tracking_data;
    }

    //Token not available or not mached 
    public function jsonresServerError($ex) {
        $request_header_params = getallheaders();
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 424;
        $outputArr["message"] = "The server is unable to connect.";
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: ' . (string) $ex . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/server_error-" . createddate() . ".log", $error_log_message, FILE_APPEND);
        $this->username = array('thirupathi@vijayglobal.com', 'jeyaprakash@vijayglobal.com');
        $this->mailsubject = 'Error Log ' . createddatetime();
        $this->attachment = env("APP_LOG_PATH") . "/server_error-" . createddate() . ".log";
        $this->filename = "server_error-" . createddate() . ".log";
        $data = array('errormessage' => json_encode($this->trackinginformation($request_header_params)));
//        Mail::send('emails.errorlog', $data, function ($message) {
//            $message->from('noreply@innothinkglobal.com', 'InnoAssist');
//            $message->to($this->username)->subject($this->mailsubject);
//            $message->attach($this->attachment, [ 'as' => $this->filename, 'mime' => 'application/log']);
//        });
        session()->forget('companyid');
        return response($outputArr, 504)->header('Content-Type', 'text/plain');
        exit;
    }

    //getPublicurl - S3
    public function gets3url($filepath) {
        if (!empty($filepath)) {
            try {
                if (isset($this->s3path[$filepath])) {
                    $request = $this->s3path[$filepath];
                } else {
                    $s3 = Storage::disk('s3');
                    $client = $s3->getDriver()->getAdapter()->getClient();
                    $expiry = "+180 minutes";

                    $command = $client->getCommand('GetObject', [
                        'Bucket' => env('S3_BUCKET'),
                        'Key' => $filepath
                    ]);
                    $request = (string) $client->createPresignedRequest($command, $expiry)->getUri();
                    $this->s3path[$filepath] = $request;
                }
                return $request;
            } catch (\Exception $ex) {
                return '/';
            }
        }
    }

    //Subscription Plan restrication    
    public function jsonressubscription($logintype) {
        $request_header_params = getallheaders();
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 424;
        $outputArr["response"] = array('message' => 'User subscribed date is expired or in-activated');
        $error_log_message = PHP_EOL . '------------' . "\R\N" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/subscription_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

        return response($outputArr, 404)->header('Content-Type', 'text/plain');
        exit;
    }

    //Business type 3 user restrication    
    public function jsonresuserlocationvalidation($message) {
        $request_header_params = getallheaders();
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 502;
        $outputArr["message"] = $message;
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/locationnotmapped_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

        echo json_encode($outputArr);
    }

    //Business type 3 contractor restrication    
    public function jsonresusercontractovalidation() {
        $request_header_params = getallheaders();
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 503;
        $outputArr["message"] = getMessageValue('ASSIST_SERVICE_CONTRACTOR_IS_NOT_MAPPED', $this->UserData->id);
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/contractornotmapped_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

        echo json_encode($outputArr);
    }

    //Business type 2 Customer restrication    
    public function jsonresusercustomervalidation() {
        $request_header_params = getallheaders();
        $outputArr = Cache::get($this->UserData->id);
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 503;
        $outputArr["message"] = getMessageValue('ASSIST_CUSTOMER_IS_NOT_MAPPED', $this->UserData->id);
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/customernotmapped_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

        echo json_encode($outputArr);
    }

    //Business type 3 location not mapped
    public function jsonresuserlocation() {
        $request_header_params = getallheaders();
        $outputArr = Cache::get($this->UserData->id);
        $outputArr["status"] = FALSE;
        $outputArr["response_code"] = 503;
        $outputArr["message"] = getMessageValue('ASSIST_LOCATION_IS_NOT_MAPPED', $this->UserData->id);
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Session Expired' . "\r\n" . 'Tracking: ' . json_encode($this->trackinginformation($request_header_params)) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/locationnotmapped_log-" . createddate() . ".log", $error_log_message, FILE_APPEND);

        echo json_encode($outputArr);
    }

    public function logoutputformobileapi($param) {
        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . 'Response: Success ' . "\r\n" . 'User_Name: ' . $this->UserData->username . ' User_Email: ' . $this->UserData->emailid . ' User_Type:' . config('2iequipstatus.usertype.' . $this->UserData->user_type) . ' User_CompanyName: ' . $this->UserData->settings->company_name . "\r\n" . 'Response: ' . json_encode($param) . "\r\n" . '------------' . PHP_EOL;
        file_put_contents(env("APP_LOG_PATH") . "/apilog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
    }

}
