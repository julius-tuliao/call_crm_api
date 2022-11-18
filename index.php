<?php



// Get Config
$GLOBALS['config'] = parse_ini_file('./private/config.ini');

function create_token(){
        
    
    $username = $GLOBALS['config']['username'];
    $password = $GLOBALS['config']['password'];
    $endpoint = 'http://172.20.0.228/login';
    
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
    
    // echo $obj["token"];

    $file = $GLOBALS['config']['token_file'];
    
    // Append a new person to the file
    $current = $obj["token"];
    // Write the contents back to the file
    file_put_contents($file, $current);

    return $obj["token"];
  
}
  

function post_to_bcrm($address,$ch_code,$contact,$source,$start_date,$end_date,$amount,$or_number,$comment,$agent,$disposition_class,$disposition_code){

  
  // Store request in a variable
  $postData = [ 
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

  $max_retry = 0;
  
  
  do {
  
    $token = file_get_contents($GLOBALS['config']["token_file"]);

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $GLOBALS['config']["status_endpoint"],
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($postData),
      CURLOPT_HTTPHEADER => array(
        "x-access-tokens: $token",
        "Content-Type: application/json"
      ),
    ));


    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    echo $httpcode;
    
    if($httpcode == 498 || $httpcode == 200){

      echo 'error';
      create_token();
  
    }
 

    $max_retry++;
  } while ($max_retry <= 10 && $httpcode != 201);

  
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

post_to_bcrm($address,$ch_code,$contact,$source,$start_date,$end_date,$amount,$or_number,$comment,$agent,$disposition_class,$disposition_code);



?>