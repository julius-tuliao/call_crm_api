<?php

// Get Config
$GLOBALS['config'] = parse_ini_file('./private/config.ini');


class Api {

  private function create_token(){
          
      
      $username = $GLOBALS['config']['username'];
      $password = $GLOBALS['config']['password'];
      $endpoint =  $GLOBALS['config']['login_endpoint'];
      
      $credentials = base64_encode("$username:$password");
      
      $headers = [];
      $headers[] = "Authorization: Basic {$credentials}";
      $headers[] = 'Content-Type: application/x-www-form-urlencoded';
      $headers[] = 'Cache-Control: no-cache';
      
      $ch = curl_init();
      
      curl_setopt($ch, CURLOPT_URL, $endpoint);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
      }
      curl_close($ch);
      
      // Debug the result
      $obj = json_decode($result,true);
      

      $file = $GLOBALS['config']['token_file'];
      
      // Append  token to the file
      $current = $obj["token"];
      // Write the contents back to the file
      file_put_contents($file, $current);

      return $obj["token"];
    
  }
    

  public function post_to_bcrm($address,$ch_code,$contact,$source,$start_date,$end_date,$amount,$or_number,$comment,$agent,$disposition_class,$disposition_code){

    
    // Store request in a variable
    $post_data = [ 
      "address"=>$address,
    "ch_code"=>$ch_code,
    "contact"=>$contact,
    "source"=>$source,
    "start_date"=>$start_date,
    "end_date"=>$end_date,
    "amount"=>$amount,
    "or_number"=>$or_number,
    "comment"=>$comment,
    "agent"=>$agent,
    "disposition_class"=>$disposition_class,
    "disposition_code"=>$disposition_code
    ];

    $count_of_retry = 0;
    $max_retry = 10;
    
    do {
    
      $token = file_get_contents($GLOBALS['config']["token_file"]);

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $GLOBALS['config']["status_endpoint"],
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($post_data),
        CURLOPT_HTTPHEADER => array(
          "x-access-tokens: $token",
          "Content-Type: application/json"
        ),
      ));


      // execute
      $response = curl_exec($curl);
      $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      curl_close($curl);

      echo $http_code;
      
      if($http_code == 498 || $http_code == 200){

        echo 'error';
        $this->create_token();
    
      }
  

      $count_of_retry++;
    } while ($count_of_retry <= $max_retry && $http_code != 201);

    
  }

}


$address="";
$ch_code="ode-1";
$contact="12345678";
$source="Mobile";
$start_date=null;
$end_date=null;
$amount=null;
$or_number=null;
$comment="testing lang with nginx";
$agent="GMLG";
$disposition_class="DNC";
$disposition_code="Set Off Fully Paid";


$api = new Api();
$api->post_to_bcrm($address,$ch_code,$contact,$source,$start_date,$end_date,$amount,$or_number,$comment,$agent,$disposition_class,$disposition_code);



?>