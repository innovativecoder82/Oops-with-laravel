<?php

namespace samarnas;

//use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{

	use SoftDeletes;

	// protected $table     = 'notification';

  protected $fillable = ["sender_id", "receiver_id", "title","message", "type", "status", "image"];

 //  protected $fillable   = [
 //                             'sender_id', 'data', 'status'
 //                          ];

 //  public function saveNotification($data)
 //  { 
 //    // dd($data[]);
 //    $this->sender_id  = $data['sender_id'];
 //    $this->data       = $data['data'];
 //    $this->status     = $data['status'];
 //    $this->type       = $data['type'];
 //    $this->save();
 //    return 1;
 //  }

  public function notification($device_details,$message)
  {
    if($device_details['is_apple'] == 'ANDROID')
    {
      //dd("dsds");
		  // prepare the 
	  $url    = 'https://fcm.googleapis.com/fcm/send';
      $fields = array(
              'to' 		=> $device_details['endpoint'], // single device token (string format)
              'data' 	=> $message // array values
            );
        // building headers for the request
      $headers = array(
              'Authorization: key= AAAAz2R5gCU:APA91bGb3--LIXWYYUewQ9esSmIYKvnttZcBhxMSJO7hkEGi0I4KE40yvFXG75n0PnbZq79SjP016L9q9WL775oy3elbKPwVYGbbU34HBrZFza6kRWJvw4YHSOvDDEEC_5xN6dPGzRZt',
              'Content-Type: application/json'
        );

      $ch = curl_init();
      // Set the url, number of POST vars, POST data
      curl_setopt( $ch,CURLOPT_URL, $url );
      curl_setopt( $ch,CURLOPT_POST, true );
      curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
      curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
      // Disabling SSL Certificate support temporarly
      curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
      curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode($fields));
      $de = curl_exec($ch);
// dd($de);
      return $de;
      // exit();
      // Execute
    }
    else if($device_details['is_apple'] == 'IOS')
    {
      //dd($_SERVER['DOCUMENT_ROOT']);
      $device_details['logs_device_token'] = $device_details['endpoint'];
     // dd($device_details['logs_device_token']);
      // $message  = array (
      //               'title' => "You have a new notification.",
      //               'message' => "welcome",
      //               'notification_type' => 8
      //             );
      $alert    = $message['message'];
      
      // $pemFile  = ('/public_html/samarnas/pem/Samarnas_Dev_Push (1).pem'); // For Development
      // $apns_url = "ssl://gateway.sandbox.push.apple.com:2195"; // For Development
      // $passphrase = "";
     //  $pemFile  = ('/public_html/samarnas/pem/Samarnas_Dev_Push (1).pem'); // For Development
     // $apns_url = "ssl://gateway.sandbox.push.apple.com:2195"; // For Development
     //  $passphrase = "";
     // // $passphrase = "PushChat";
     // $ctx = stream_context_create();
     // $ji = stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
     // $ki = stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
   
     // // Open a connection to the APNS server
     // $fp = stream_socket_client($apns_url, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);


    //  $apnsCert = $_SERVER['DOCUMENT_ROOT'].'/..../..../Samarnas_Dev_Push.pem';
    // $ctx = stream_context_create();
    // stream_context_set_option($ctx, 'ssl', 'local_cert',$apnsCert);
    // stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
    // stream_context_set_option($ctx, 'ssl', 'cafile', $_SERVER['DOCUMENT_ROOT'].'/..../..../entrust_g2_ca.cer');
    // // $fp = stream_socket_client('ssl://gateway.push.apple.com:2195',$err,$errstr,60,STREAM_CLIENT_CONNECT,$ctx);
    // $fp = stream_socket_client('gateway.push.apple.com:2195',$err,$errstr,60,STREAM_CLIENT_CONNECT,$ctx);
      $pemFile  = $_SERVER['DOCUMENT_ROOT'].'/pem/distribution_Samarnas (1).pem'; // For Development
//dd($pemFile);
     // $pemFile  = ('C:\xampp\htdocs\tohome\pem\ToHome_dev_Push.pem'); // For Development
     $apns_url = "ssl://gateway.sandbox.push.apple.com:2195"; // For Development
      $passphrase = "";
      // $passphrase = "PushChat";
      $ctx = stream_context_create();
      $ji = stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
      $ki = stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
     //stream_context_set_option($ctx, 'tls', 'verify_peer', false);
     //stream_context_set_option($ctx, 'tls', 'cafile', $_SERVER['DOCUMENT_ROOT'].'/..../..../entrust_g2_ca.cer');
      // Open a connection to the APNS server
     // $fp = stream_socket_client($apns_url, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

      $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
// echo $fp;
// exit();
      
      //Check socket connectivity
      if (!$fp)
      {
        exit("Failed to connect: $err $errstr" . PHP_EOL);
          
      }
      // else
      // {
      //   exit("connected");
      // }
      
     


      $body['aps'] = array(
                'alert' => $alert,
                'sound' => 'default',
                'badge' => 1,
                'result'=> $message
              );
      $payload = json_encode($body);

      $deviceToken = $device_details['logs_device_token'];

      if(strlen($device_details['logs_device_token']) >= 40) 
      {

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));

        // try 
        // {
        //   $result = fwrite($fp, $msg, strlen($msg));
        // } 
        // catch (Exception $ex) 
        // {
        //   print_r($ex);
        //   exit();
        //   sleep(1); //sleep for 5 seconds
        //   $result = fwrite($fp, $msg, strlen($msg));
        // }
      }
         //dd($result)  ;
      // $this->checkAppleErrorResponse($fp);

      // Close connection
      fclose($fp);
    }
	}

  public function appleNotification()
  {
    $device_details['logs_device_token'] = "E1B478EC2B8A240F94722B5EED08184C9C53C089645F129ADB4D0936FD557D01";
    $message = array (
                  'title' => "You have a new notification.",
                  'message' => "welcome",
                  'notification_type' => 8
                );
    $alert = $message['message'];
    
      $pemFile = ('C:\xampp\htdocs\ofb\pem\OFBAPNSCert.pem'); // For Development
    //   $apns_url = "ssl://gateway.push.apple.com:2195"; // For Production  
      $apns_url = "ssl://gateway.sandbox.push.apple.com:2195"; // For Development
   // }
    //  dd($pemFile);
    $passphrase = "";
      //C:/xampp/htdocs/ofp/pem/OFBAPNSCert.pem
      //C:\xampp\htdocs\ofb\pem
    $ctx = stream_context_create();
   // stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
    $ff = stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
    $df = stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
//dd($ctx);
    // Open a connection to the APNS server
    $fp = stream_socket_client($apns_url, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
 //   $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err,
      //$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);


    if (!$fp)
    {
      exit("Failed to connect: $err $errstr" . PHP_EOL);
        
    }
        //dd($err);

   // dd($fp);

    // if (!$fp)
      // exit("Failed to connect: $err $errstr" . PHP_EOL);

    $body['aps'] = array(
              'alert' => $alert,
              'sound' => 'default',
              'badge' => 0,
              'result'=> $message
            );
    $payload = json_encode($body);

    $deviceToken = $device_details['logs_device_token'];

    if(strlen($device_details['logs_device_token']) >= 40) {

      // Build the binary notification
      $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
      $result = fwrite($fp, $msg, strlen($msg));
    }
          
    // $this->checkAppleErrorResponse($fp);

    // Close connection
    fclose($fp);
  }
  
   public static function pushNotifications($request,$message)
    {
      // print_r("<pre>");
      // print_r($request);
      // print_r("</pre>");
      // exit();
      // $stringss = (string)$request['device_token'];
       // print_r(strlen($request));  exit();
        if(strlen($request) > 64 )
        {
//echo "sdfdsf"; exit();
            $url = 'https://fcm.googleapis.com/fcm/send';
            $fields = array(
                'to' => $request, // single device token (string format)
                'data' =>$message // array values
              );

           // building headers for the request
           $headers = array(
                  'Authorization: key=AAAAz2R5gCU:APA91bGb3--LIXWYYUewQ9esSmIYKvnttZcBhxMSJO7hkEGi0I4KE40yvFXG75n0PnbZq79SjP016L9q9WL775oy3elbKPwVYGbbU34HBrZFza6kRWJvw4YHSOvDDEEC_5xN6dPGzRZt',
                  'Content-Type: application/json'
            );
            $ch = curl_init();
            // Set the url, number of POST vars, POST data
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_URL, $url);
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            // Disabling SSL Certificate support temporarly
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode($fields));
            // Execute post
            $result = curl_exec($ch);
            //print_r($result);
            $response = json_encode($result,true);
            // Close connection
            curl_close($ch);       
        }
        else
        {
      $device_details['logs_device_token'] = $request;
     // print_r($device_details);
     // exit();
      $alert    = $message['message'];
      
     
      $pemFile  = $_SERVER['DOCUMENT_ROOT'].'/pem/distribution_Samarnas (1).pem'; // For Development

     // $pemFile  = ('C:\xampp\htdocs\tohome\pem\ToHome_dev_Push.pem'); // For Development
     $apns_url = "ssl://gateway.sandbox.push.apple.com:2195"; // For Development
      $passphrase = "";
      // $passphrase = "PushChat";
      $ctx = stream_context_create();
      $ji = stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
      $ki = stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
     

      $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

      
      //Check socket connectivity
      if (!$fp)
      {
        exit("Failed to connect: $err $errstr" . PHP_EOL);
          
      }
    

      $body['aps'] = array(
                'alert' => $alert,
                'sound' => 'default',
                'badge' => 1,
                'result'=> $message
              );
      $payload = json_encode($body);

      $deviceToken = $device_details['logs_device_token'];

      if(strlen($device_details['logs_device_token']) >= 40) 
      {

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
      }
      // $this->checkAppleErrorResponse($fp);

      // Close connection
      fclose($fp);
        }
    }
  
  
}
