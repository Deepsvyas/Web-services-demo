<?php error_reporting(0); 
require_once('../configuration.php');
header('Content-type: application/json');
//site url $site_url
//site name $site_name
//site logolink  $site_logo_link
//Instantiate object for db clas   $mysql
// admin email  ADMIN_EMAIL
//from email FROM_EMAIL
// get base url $base_url
$tbl_users                  =   $dbPrefix.'users';
$tbl_profile                =   $dbPrefix.'profile'; 
$tbl_category               =   $dbPrefix.'category';
$tbl_subcategory            =   $dbPrefix.'subcategory';
$tbl_section                =   $dbPrefix.'section';
$tbl_brand                  =   $dbPrefix.'brand';
$tbl_type                   =   $dbPrefix.'type';
$tbl_model                  =   $dbPrefix.'model';
$tbl_voltage                =   $dbPrefix.'voltage';
$tbl_wattage                =   $dbPrefix.'wattage';
$tbl_color                  =   $dbPrefix.'color';
$tbl_fittingtype            =   $dbPrefix.'fittingtype';
$tbl_product_price          =   $dbPrefix.'product_price';
$tbl_order_product          =   $dbPrefix.'order_product';
$tbl_product_images         =   $dbPrefix.'product_images';
$tbl_product_catelog        =   $dbPrefix.'product_catelog';
$tbl_order                  =   $dbPrefix.'order';
$tbl_order_detail           =   $dbPrefix.'order_detail';
$tbl_delivery_option        =   $dbPrefix.'delivery_option';
$tbl_shipping_electrician   =   $dbPrefix.'shipping_electrician';
$tbl_checkout_shipping      =   $dbPrefix.'checkout_shipping';

$tbl_products               =   $dbPrefix.'products';
$tbl_product_color          =   $dbPrefix.'product_color';
$tbl_product_fittingtype    =   $dbPrefix.'product_fitting_type';
$tbl_product_catalog        =   $dbPrefix.'product_catalog';

$tbl_whatlite_category      =   $dbPrefix.'whatlite_category';
$tbl_whatlite_subcategory   =   $dbPrefix.'whatlite_subcategory';
$tbl_whatlite_section       =   $dbPrefix.'whatlite_section';

$tbl_starlite_category      =   $dbPrefix.'starlite_category';
$tbl_starlite_point         =   $dbPrefix.'starlite_point';
$tbl_starlite_award         =   $dbPrefix.'starlite_award';

$tbl_energy_porvider_list   =   $dbPrefix.'energy_porvider_list';
$tbl_energy_porvider        =   $dbPrefix.'energy_provider';
$refertofriends             =   $dbPrefix.'refer_to_friends';

$refertomobile             =   $dbPrefix.'refer_to_mobile';

$tbl_users_products         =   $dbPrefix.'users_products';
$tbl_catalog_images         =   $dbPrefix.'catalog_images';

$path_image = $site_url.'admin/upload/products_images/';
$typepath_image = $site_url.'admin/upload/type_images/';
$pro_pic_path = $site_url.'admin/upload/userprofileimages/';
$path_catalogimage = $site_url.'admin/upload/catalogs_images/';
$path_dealimages = $site_url.'admin/upload/dealimages/';

//push notification function for send push notification to user
function push_notification(){
    $tbl_users                  =   'wl_users';
    $tbl_starlite_point         =   'wl_starlite_point';
    //select device and registerd id like device token for registerd id for android
    $android_array=array();
    $iphone_array=array();
    $sql="select device_model,device_registered_id from $tbl_users WHERE device_model!=''";
    $exe=mysql_query($sql);
    if(mysql_num_rows($exe)>0)
    {
        while($dataobject=mysql_fetch_object($exe)){
            if($dataobject->device_model=='Android'){
                $android_array[]=$dataobject->device_registered_id;
            }elseif($dataobject->device_model!='' && $dataobject->device_model!='Android'){
                $iphone_array[]=$dataobject->device_registered_id;
            }
        }
    }
    /* IOS notification : */
   //print_r($android_array);
    if(count($iphone_array)>0){
        foreach ($iphone_array as  $devicetoken) {
            // Put your device token here (without spaces):
            //$deviceToken = '7853876f5fc3210643a536428a4667d61ca3cf603f8c84c2f3f56ded50f1906f';
            $deviceToken = $devicetoken;

            //select start lite point according to user
            $sql="SELECT sum(sp.points) as totalpoint FROM $tbl_starlite_point as sp INNER JOIN $tbl_users as u on sp.user_id=u.id WHERE u.device_registered_id='$deviceToken'"; 
            $exe=mysql_query($sql);
            $rowdata=mysql_fetch_object($exe);            
            $total_statlite_point=$rowdata->totalpoint;

            // Put your private key's passphrase here:
            $passphrase = 'aaaaaa';

            // Put your alert message here:
            $message = 'Congratulation! '. PHP_EOL.' your starlite points is '.$total_statlite_point;
            
            if($total_statlite_point!=''){  
                $ctx = stream_context_create();
                stream_context_set_option($ctx, 'ssl', 'local_cert', 'WL.pem');
                stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

                // Open a connection to the APNS server
                $fp = stream_socket_client(
                'ssl://gateway.push.apple.com:2195', $err,
                $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

                //if (!$fp)
                   // exit("Failed to connect: $err $errstr" . PHP_EOL);
                   // echo 'Connected to APNS' . PHP_EOL;

                // Create the payload body
                    $body['aps'] = array(
                       'alert' => $message,
                       'sound' => 'default'
                    );

                // Encode the payload as JSON
                $payload = json_encode($body);

                // Build the binary notification
                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

                // Send it to the server
                $result = fwrite($fp, $msg, strlen($msg));

               // if (!$result)
                   //echo 'Message not delivered' . PHP_EOL;die;
               // else
                  // echo 'Message successfully delivered' . PHP_EOL;die;

                // Close the connection to the server
                fclose($fp);
            }
            $total_statlite_point='';
        }
    }
        



    /* Android: notification */
    if(count($android_array)>0){
        foreach ($android_array as  $regIdg) {
            $apiKey="AIzaSyBZb30TuHqthm_Xfp12zvQIT47qwx3-I-Q";
            //echo 'Message not delivered';die;

            //select start lite point according to user
            $sql="SELECT sum(sp.points) as totalpoint FROM $tbl_starlite_point as sp INNER JOIN $tbl_users as u on sp.user_id=u.id WHERE u.device_registered_id='$regIdg'";
            $exe=mysql_query($sql);
            $rowdata=mysql_fetch_object($exe);            
            $total_statlite_point=$rowdata->totalpoint;


            $message = 'Congratulation! '. PHP_EOL.' your starlite points is '.$total_statlite_point;
            
            $url = 'https://android.googleapis.com/gcm/send';
            $regIdg = array($regIdg);

            $fields = array('registration_ids'  => $regIdg,
                            'data'=> array( "message" => $message ),);
            
            $headers = array('Authorization: key=' . $apiKey,
                             'Content-Type: application/json');
            if($total_statlite_point!=''){ 
                // Open connection
                $ch = curl_init();
                // Set the url, number of POST vars, POST data
                curl_setopt( $ch, CURLOPT_URL, $url );
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
                //echo $ch;
                // Execute post
                
                    $result = curl_exec($ch);

                // if ($result === FALSE)
                    //echo 'Message not delivered' . PHP_EOL;
                //else
                //    echo 'Message successfully delivered' . PHP_EOL;            
                    // if ($result === FALSE) {

                    //     die('Curl failed: ' . curl_error($ch));
                    // }
                curl_close($ch);
                //print_r($result);die;
            }
            $total_statlite_point='';
        }
    }
        
}


//signup for user
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'register')) {
//checking for blank value
    //print_r($_POST);
    $json=json_decode(file_get_contents("php://input"));

        $email = $json->email; 
        $fullname= $json->fullname;     
        $user_pass1 = $json->password;
        $user_pass2 = $json->cpassword;
        $mobile_no = $json->mobile_no;
        $verify_code = $json->verify_code;
        $device_model = $json->device_model;
        $device_registered_id = $json->device_registered_id;    

        $sqlgetid=mysql_query("SELECT id FROM $refertomobile WHERE mobile_no='$mobile_no' AND verify_code='$verify_code'");
        $countRows  =   $mysql->dbGetNumRows($sqlgetid);
        if($countRows==0 && $verify_code!=''){
           $message = array(
                "success" => "false",
                "error" => "Promotional Code is invalid. Either enter valid code or do not fill.",
                "status" => "not registered",
            ); 
            echo json_encode(array('result' => $message));
       }else{

            if ($mobile_no!='' && $email != "" && $user_pass1 != "" && $user_pass1 == $user_pass2) {        
                $password = encryptIt($user_pass1);
                
                // checking availability
                $row_check=chk_avail_user($tbl_users, $email);
                //mobile varification
                $verifymobile=mysql_query("SELECT id FROM $tbl_users WHERE mobile_no='$mobile_no'");
                $checkmobie=mysql_num_rows($verifymobile);
                if ($row_check > 0) {
                    $message = array(
                        "success" => "false",
                        "error" => "Please enter different email, email already exists",
                        "status" => "not registered",
                    );
                    
                    echo json_encode(array('result' => $message));
                    @mysql_close($link);
                }elseif($checkmobie>0){
                    $message = array(
                        "success" => "false",
                        "error" => "Please enter different mobile number, mobile number already exists",
                        "status" => "not registered",
                    );
                    
                    echo json_encode(array('result' => $message));
                    @mysql_close($link);
                } else {            
                    
                        $form_data_user= array(
                            'email'                 => $email,
                            'password'              => $password,
                            'block'                 => 0,
                            'group_id'              =>  2,
                            'mobile_no'             => $mobile_no,
                            'device_model'          => $device_model,
                            'device_registered_id'  => $device_registered_id         
                             );
                            $where="";
                            $add_profile  =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);
                           $userid= mysql_insert_id();
                        $form_data= array(
                            'user_id'       =>$userid,   
                            'firstname'     =>$fullname,                  
                            'contactno'     =>  $mobile_no                          
                             );

                        $where="";           
                        $add_profile    =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data, $where);
                        if($add_profile>0){
                            
                            $subject = "Successfully registered";

                        $body   ='
                        <html>
                            <body>
                              <div  style="background: none repeat scroll 0 0 #F4F5F5;
                                    float: left;
                                    margin: 0 1%;
                                   width: 96%;">
                                    <div style=" background: none repeat scroll 0 0 #2c3742;border-radius: 4px; ">
                                        <h2 style="color: #FFFFFF;margin-bottom: 5px; margin-top:0px;padding: 3px 6px;text-transform: capitalize;">'.$site_name.'</h2>
                                    </div>
                                    <div style="float: left; padding:0px 1%;">
                                        <div style="width:100%; float:left;">
                                            <p style="margin:0px; padding:0px;">Dear '.ucfirst($firstname).',</p>
                                            <div style=" margin-top:10px; padding-left:20px;">
                                                <p style="margin:0px 0px 10px 0px; padding:0px;">You are registered successfully.<br/>Your Login Credential are mentioned below:</p>
                                                <p> <strong>Email</strong>
                                                    : '.$email.'
                                                </p>
                                                <p> <strong>Password</strong>
                                                    : '.$user_pass1.'
                                                </p>
                                            </div>
                                        </div>
                                        <div style="  float: left;
                                    margin-bottom: 2%;
                                    margin-top: 10%;
                                    width: 100%;">
                                            <p style="margin:0px; padding:0px;">Best Regards</p>
                                            <p style="margin:0px; padding:0px;">'.$site_name.'</p>
                                            <img src="'.$site_url.'images/logo.png" alt="" border="0" width="150" >
                                        </div>
                                    </div>
                                    <div style=" background: none repeat scroll 0 0 #B2B2B2; height:20px; width:100%; float:left; "></div>
                                    </div>
                                    </body></html>';

                            $mail123 = sentmail($email,$subject,$body);

                        }
                        //update udid for refer user when register
                        if($add_profile>0){
                        //echo "SELECT id,contactno FROM $tbl_users WHERE verify_code='$verify_code'";
                        $sqlgetid=mysql_query("SELECT id,user_id FROM $refertomobile WHERE mobile_no='$mobile_no' AND verify_code='$verify_code'");
                        $countRows  =   $mysql->dbGetNumRows($sqlgetid);
                        $getid=mysql_fetch_object($sqlgetid);
                        $existid=$getid->id;
                        $senderid=$getid->user_id;
                        
                        if($countRows>0){                
                            $sql="UPDATE $refertomobile SET download_flag='1' WHERE id='$existid'";
                            $exe=mysql_query($sql);
                                $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='2'"));
                                $getpoint1=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='3'"));

                                $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$senderid','$getpoint->points','Send Invitation')");
                                    $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$userid','$getpoint1->points','Receive Invitation')");

                        
                            //push notification function for send push notification to user
                            push_notification();
                            
                            }
                        
                         } 
                    
                        $query="SELECT * FROM $tbl_users as u INNER JOIN $tbl_profile as p ON p.user_id=u.id WHERE u.id='$userid'";
                        $resultQuery    =   mysql_query($query);
                        $countRows  =   $mysql->dbGetNumRows($resultQuery);
                        $row    =   mysql_fetch_object($resultQuery); 
                        $message['success']         = "true";
                        $message['error']           = "null";
                        $message['status']          ='Registred successfully!';
                        $message['user_id']         = $row->user_id;
                        $message['user_email']      = $row->email;
                        $message['user_name']       = (stripcslashes($row->firstname));
                        $message['user_address']    = (stripcslashes($row->address));
                        $message['user_state']      = (stripcslashes($row->state));
                        $message['user_country']    = (stripcslashes($row->country));
                        $message['user_zipcode']    = $row->zipcode;
                        $message['user_contactno']  = $row->contactno;
                        if($row->userimage!=''){
                            $message['user_image']      = $pro_pic_path.$row->userimage;

                        }else{
                            $message['user_image']      = $pro_pic_path.'../defultuser.png';
                        }
                    
                    
                    echo json_encode(array('result' => $message));
                    @mysql_close($link);
                }
            } else {

                $message = array(
                    "success" => "false",
                    "error" => "Required data is missing",
                    "status" => "Please enter required data ! Or check password",
                );
               

                echo json_encode(array('result' => $message));
            }
        }
}


//signup for user for web sign up
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'register_web')) {
//checking for blank value 
    $json=json_decode(file_get_contents("php://input"));

        $fullname= $json->fullname;
        $email = $json->email;
        $rdate = date('Y-m-d H:i:s');       
        $user_pass1 = $json->password;
        $user_pass2 = $json->cpassword;       
        $contactno = $json->contactno; 

        
    if ($fullname != "" && $email != "" && $user_pass1 != "" && $user_pass1 == $user_pass2) {
        
        $password = encryptIt($user_pass1);
        
    // checking availability
    $row_check=chk_avail_user($tbl_users, $email);

        if ($row_check > 0) {
            $message = array(
                "success" => "false",
                "error" => "Please select different email, email already exists",
                "status" => "not registered",
            );
            
            echo json_encode(array('result' => $message));
            @mysql_close($link);
        } else {

            $form_data_user= array(
                    'email'             => $email,
                    'password'          => $password,
                    'block'             => 0,
                    'group_id'          =>  2,
                    'registered_date'   => $rdate,
                    'contactno'     =>  $contactno           
                     );
                $where='';
                $add_edit_user  =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);

                $userid= mysql_insert_id();
                $form_data= array(
                    'user_id'       =>$userid,
                    'firstname'     =>$fullname,                    
                    'contactno'     =>  $contactno                          
                     );

                $where="";           
                $add_profile    =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data, $where);
            if($add_profile>0){
                // $subject = "Successfully registered";
                // $message = " You are now registered successfully";          
                // $headers = "From:" . FROM_EMAIL;
                // $mail123 = mail($to, $subject, $message, $headers);
            }                    


            $query="SELECT * FROM $tbl_users as u INNER JOIN $tbl_profile as p ON p.user_id=u.id WHERE u.id='$userid'";
            $resultQuery    =   mysql_query($query);
            $countRows  =   $mysql->dbGetNumRows($resultQuery);
            $row    =   mysql_fetch_object($resultQuery); 
            $message['success']         = "true";
            $message['error']           = "null";
            $message['status']          ='Registred successfully!';
            $message['user_id']         = $row->user_id;
            $message['user_email']      = $row->email;
            $message['user_name']       = (stripcslashes($row->firstname));
            $message['user_address']    = (stripcslashes($row->address));
            $message['user_state']      = (stripcslashes($row->state));
            $message['user_country']    = (stripcslashes($row->country));
            $message['user_zipcode']    = $row->zipcode;
            $message['user_contactno']  = $row->mobile_no;
            if($row->userimage!=''){
                $message['user_image']      = $pro_pic_path.$row->userimage;

            }else{
                $message['user_image']      = $pro_pic_path.'defultuser.jpg';
            }
            
            echo json_encode(array('result' => $message));
            @mysql_close($link);
        }
    } else {

        $message = array(
            "success" => "false",
            "error" => "Required data is missing",
            "status" => "Please enter required data ! Or check password",
        );
       

        echo json_encode(array('result' => $message));
    }
}


//signup for user
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'fbregister')) {
//checking for blank value
    //print_r($_POST);
    $json=json_decode(file_get_contents("php://input"));

        $fullname= $json->fullname;
        $email = $json->email;
        $rdate = date('Y-m-d H:i:s'); 
        $fuid=$json->fuid;
        $device_model = $json->device_model;
        $device_registered_id = $json->device_registered_id;

    
    if ($fullname!= "" && $email!= "") {
        
    
        // checking availability
        $row_check=chk_avail_user1($tbl_users, $email, $fuid);

        if ($row_check > 0) {  

            $query="SELECT * FROM $tbl_users as u INNER JOIN $tbl_profile as p ON p.user_id=u.id WHERE u.email='$email' AND u.group_id='2'";
        $resultQuery    =   mysql_query($query);
        $countRows  =   $mysql->dbGetNumRows($resultQuery);
        
        //Define session
        $row    =   mysql_fetch_object($resultQuery);                
        $_SESSION['SESS_ID']        =   $row->user_id;
        $_SESSION['SESS_EMAIL']     =   $row->email;    

            $message['success']         = "true";
            $message['error']           = "null";
            $message['status']          ='Loged in successfully!';
            $message['user_id']         = $row->user_id;
            $message['user_email']      = $row->email;
            $message['user_name']       = (stripcslashes($row->firstname));
            $message['user_address']    = (stripcslashes($row->address));
            $message['user_state']      = (stripcslashes($row->state));
            $message['user_country']    = (stripcslashes($row->country));
            $message['user_zipcode']    = $row->zipcode;
            $message['user_contactno']  = $row->mobile_no;
            if($row->userimage!=''){
                $message['user_image']      = $pro_pic_path.$row->userimage;
            
            }else{
                $message['user_image']      = $pro_pic_path.'defultuser.jpg';
            }
            
        //update device name and registered id for device when login a user on mobile device
        if($device_model!=''){
            $form_data_user= array(                    
                    'device_model'          => $device_model,
                    'device_registered_id'  => $device_registered_id           
                     );
                $where="WHERE id='$row->user_id'";
                $add_edit_user  =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);
            }
            echo json_encode(array('result' => $message));
            @mysql_close($link);
        } else {

            $form_data_user= array(
                    'email'             => $email,              
                    'block'             => 0,
                    'group_id'          =>  2,
                    'fuid'          =>  $fuid,
                    'registered_date'   => $rdate,
                    'device_model'          => $device_model,
                    'device_registered_id'          => $device_registered_id           
                     );
                $where='';
                $add_edit_user  =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);

                $userid= mysql_insert_id();
                $form_data= array(
                    'user_id'       =>$userid,
                    'firstname'     =>$fullname                 
                                        
                     );

                $where="";           
                $add_profile    =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data, $where);
            


            if($add_profile>0){
                // $subject = "Successfully registered";
                // $message = " You are now registered successfully";          
                // $headers = "From:" . FROM_EMAIL;
                // $mail123 = mail($to, $subject, $message, $headers);
                    $query="SELECT * FROM $tbl_users as u INNER JOIN $tbl_profile as p ON p.user_id=u.id WHERE u.id='$userid'";
                    $resultQuery    =   mysql_query($query);
                    $countRows  =   $mysql->dbGetNumRows($resultQuery);

                    //Define session
                    $row    =   mysql_fetch_object($resultQuery);                
                    

                    $message['success']         = "true";
                    $message['error']           = "null";
                    $message['status']          ='Registered successfully!';
                    $message['user_id']         = $row->user_id;
                    $message['user_email']      = $row->email;
                    $message['user_name']       = (stripcslashes($row->firstname));
                    $message['user_address']    = (stripcslashes($row->address));
                    $message['user_state']      = (stripcslashes($row->state));
                    $message['user_country']    = (stripcslashes($row->country));
                    $message['user_zipcode']    = $row->zipcode;
                    $message['user_contactno']  = $row->mobile_no;
                    if($row->userimage!=''){
                    $message['user_image']      = $pro_pic_path.$row->userimage;

                    }else{
                    $message['user_image']      = $pro_pic_path.'defultuser.jpg';
                    }
            }

            
           

            echo json_encode(array('result' => $message));
            @mysql_close($link);
        }
    } else {

        $message = array(
            "success" => "false",
            "error" => "null",
            "status" => "Please enter required data ! Or check password",
        );
       

        echo json_encode(array('result' => $message));
    }
}

//login--------------login----------
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'login')) {

    $json=json_decode(file_get_contents("php://input"));
    
    $email = $json->email;
    $user_pass1 = $json->password;
    $device_model = $json->device_model;
    $device_registered_id = $json->device_registered_id;



    
    if ($email!= "" || $password!= "") {
        //getting values    
        
        $password = encryptIt($user_pass1);
        //query for authentication      

        $query="SELECT * FROM $tbl_users as u INNER JOIN $tbl_profile as p ON p.user_id=u.id WHERE (u.email='$email' OR u.mobile_no='$email') AND u.password='$password' AND u.group_id='2' AND u.block='0'";
        $resultQuery    =   mysql_query($query);
        $countRows  =   $mysql->dbGetNumRows($resultQuery);
        if($countRows>0)
        {
        //Define session
        $row    =   mysql_fetch_object($resultQuery); 

            $message['success']         = "true";
            $message['error']           = "null";
            $message['status']          ='Loged in successfully!';
            $message['user_id']         = $row->user_id;
            $message['user_email']      = $row->email;
            $message['user_name']       = (stripcslashes($row->firstname));
            $message['user_address']    = (stripcslashes($row->address));
            $message['user_state']      = (stripcslashes($row->state));
            $message['user_country']    = (stripcslashes($row->country));
            $message['user_zipcode']    = $row->zipcode;
            $message['user_contactno']  = $row->mobile_no;
            if($row->userimage!=''){
                $message['user_image']      = $pro_pic_path.$row->userimage;
            
            }else{
                $message['user_image']      = $pro_pic_path.'defultuser.jpg';
            }
            
        //update device name and registered id for device when login a user on mobile device
        if($device_model!=''){
            $form_data_user= array(                    
                    'device_model'          => $device_model,
                    'device_registered_id'  => $device_registered_id           
                     );
                $where="WHERE id='$row->user_id'";
                $add_edit_user  =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);
            }
            //push notification function for send push notification to user
            push_notification();
            $str = str_replace("\/", "/", json_encode(array('result' => $message)));
            echo $str;
            @mysql_close($link);
        } else {
            $message= array(
                "success" => "false",
                "error" => "Invalid Username or Password",
                "status"=> "Not loged in"
            );
            
            $str = str_replace("\/", "/", json_encode(array('result' => $message)));
            echo $str;
          
            /* disconnect from the db */
            @mysql_close($link);
        }
    } else {

        $message = array(
            "success" => "false",
            "error" => "null",
            "status" => "Please enter Username and Password correctly",
        );
        
        echo json_encode(array('result' => $message));
    }
}

//forgot password
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'forgot')) {

    $json=json_decode(file_get_contents("php://input"));
     $email = $json->email;
    $query = "SELECT email,password FROM $tbl_users WHERE email='$email'";

    $result = mysql_query($query);
    $row = mysql_fetch_object($result);

    if (mysql_num_rows($result)) {
        $to = $row->email;       
        $password = decryptIt($row->password);
        
        $subject="Forgot password request.";
        
        $body   ='<html><body>
              <div  style="background: none repeat scroll 0 0 #F4F5F5;
            float: left;
            margin: 0 1%;
           width: 96%;">
            <div style=" background: none repeat scroll 0 0 #2c3742;border-radius: 4px; ">
                <h2 style="color: #FFFFFF;margin-bottom: 5px; margin-top:0px;padding: 3px 6px;text-transform: capitalize;">'.$site_name.'</h2>
            </div>
            <div style="float: left; padding:0px 1%;">
                <div style="width:100%; float:left;">
                    <p style="margin:0px; padding:0px;">Dear '.ucfirst($name).',</p>
                    <div style=" margin-top:10px; padding-left:20px;">
                        <p style="margin:0px 0px 10px 0px; padding:0px;">Your login details are mentioned below:</p>
                        <p> <strong>Email</strong>
                            : '.$email.'
                        </p>
                        <p> <strong>Password</strong>
                            : '.$password.'
                        </p>
                    </div>
                </div>
                <div style="  float: left;
            margin-bottom: 2%;
            margin-top: 10%;
            width: 100%;">
                    <p style="margin:0px; padding:0px;">Best Regards</p>
                    <p style="margin:0px; padding:0px;">'.$site_name.'</p>
                    <img src="'.$site_url.'images/logo.png" alt="" border="0" width="150" >
                </div>
            </div>
            <div style=" background: none repeat scroll 0 0 #B2B2B2; height:20px; width:100%; float:left; "></div>
            </div>
        </body></html>';
       

               
        $sendMail   =   sentmail($to,$subject,$body);

        $message = array(
            "success" => "true",
            "error" => "null",
            "status" => "Your password has been sent on your registered email id.",
            "email" =>  $email,
            "password"=>$password
        );
        echo json_encode(array('result' => $message));
    } else {
        $message= array(
            "success" => "false",
            "error" => "Email address is not registered",
            "status" => "mail not sent"
        );
        $str = str_replace("\/", "/", json_encode(array('result' => $message)));
        echo $str;
        /* disconnect from the db */
        @mysql_close($link);
    }
}


//profile data to show
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'profiledata')) {
        
    $json=json_decode(file_get_contents("php://input"));

    $userid=$json->user_id;

    if ($userid != "") {
    
        //select start lite point according to user
        $sql="SELECT sum(points) as totalpoint FROM $tbl_starlite_point WHERE user_id='$userid' and lottery_generated='0'";
        $exe=mysql_query($sql);
        $rowdata=mysql_fetch_object($exe);
        $total_statlite_point=$rowdata->totalpoint;
    //push notification function for send push notification to user
        //push_notification();
        //getting profile data
        
                
        $query="SELECT * FROM $tbl_users as u INNER JOIN $tbl_profile as p ON p.user_id=u.id WHERE u.id='$userid' AND u.group_id='2'";
        $resultQuery    =   mysql_query($query);
        $countRows  =   $mysql->dbGetNumRows($resultQuery);
        if($countRows>0)
        {
        //Define session
        $row    =   mysql_fetch_object($resultQuery);    

        $sqlmode="SELECT * FROM wl_general_setting WHERE id='1'";
        $resulstmode=$mysql->dbLoadObjectlst($sqlmode);
        $gst=$resulstmode[0]->gst;
            
        
            $message['success']         = "true";
            $message['error']           = "null";
            $message['status']          = "user exists";
            $message['user_id']         = $row->user_id;
            $message['user_email']      = $row->email;
            $message['user_password']   = (stripcslashes(decryptIt($row->password)));
            $message['user_name']       = (stripcslashes($row->firstname));
            $message['user_address']    = (stripcslashes($row->address));
            $message['user_state']      = (stripcslashes($row->state));
            $message['user_country']    = (stripcslashes($row->country));
            $message['user_zipcode']    = $row->zipcode;
            $message['user_contactno']  = $row->mobile_no;
            $message['starlite_points'] = $total_statlite_point;
            $message['gst']=$gst;
            $message['apicredentail']   = array(
                    "acct1.UserName" => $resulstmode[0]->apiusername,
                    "acct1.Password" => $resulstmode[0]->apipassword,
                    "acct1.Signature" => $resulstmode[0]->apisignature,
                    "acct1.AppId" => $resulstmode[0]->apiid
                );
            if($row->userimage!=''){
                $message['user_image']      = $pro_pic_path.$row->userimage;
            
            }else{
                $message['user_image']      = "null";
            }
            
            $str = str_replace("\/", "/", json_encode(array('result' => $message)));
            echo $str;
            @mysql_close($link);
        } else {
            $message = array(
                "success" => "false",
                "error" => "User data not available or user not exists.",
                "status"=>"user not exists"
            );
            
            $str = str_replace("\/", "/", json_encode(array('result' => $message)));
            echo $str;
          
            /* disconnect from the db */
            @mysql_close($link);
        }
    } else {

        $message = array(
            "success" => "false",
            "error" => "User data not available or user not exists.",
        );
        
        echo json_encode(array('result' => $message));
    }
}


//Profile edit  for user
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'editprofile')) {
//checking for blank value
        $json=json_decode(file_get_contents("php://input"));
            $id             = $json->user_id;

            $email          = trim($json->email);
            
            $newpassword1   = trim($json->newpassword);
            $cnewpassword1  = trim($json->cnewpassword);
            $firstname      = trim($json->fullname);
            $lastname       = trim($json->lastname);
            $address        = trim($json->address);
            $city           = trim($json->city);
            $state          = trim($json->state);
            $country        = trim($json->country);
            $zipcode        = trim($json->zipcode);
            $contactno      = trim($json->contactno);

            $verifymobile=mysql_query("SELECT id FROM $tbl_users WHERE mobile_no='$contactno' and id!='$id'");
            $checkmobie=mysql_num_rows($verifymobile);
            if($checkmobie>0){
                $message = array(
                    "success" => "false",
                    "error" => "Please enter different mobile number, mobile number already exists",
                    "status" => "not registered",
                );
            }else{
       
         
            if ($email != "" && $id!='') {
                
                
                    
                    $password=encryptIt($newpassword1);
                    
                    $rdate=date('Y-m-d H:i:s');


                    

                    if($newpassword1!='' && $newpassword1==$cnewpassword1){
                        $form_data_user= array(
                            'email'             => $email,
                            'password'          => $password,               
                            'group_id'          =>  2,
                            'mobile_no'         => $contactno,
                            'registered_date'   => $rdate           
                             );
                        $where="WHERE id='$id'";
                        $add_edit_user1 =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);

                        $form_data= array(              
                            'firstname'     => ucfirst($firstname),
                            'lastname'      => ucfirst($lastname),
                            'address'       => $address,
                            'city'          => ucfirst($city),
                            'state'         => ucfirst($state),
                            'country'       => 'AU',
                            'zipcode'       => $zipcode,
                            'contactno'      => $contactno
                                        
                             );
                        
                        $where="WHERE user_id='$id'";            
                        $add_edit_profile   =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data, $where);
                        if($add_edit_profile>0){
                            $message = array(
                            "success" => "true",
                            "error" => "null",
                            "status" => "Profile updated successfully.",
                            );
                        }else{
                            $message = array(
                                "success" => "false",
                                "error" => "Something is wrong to insert data, Please try again later.",
                                "status"=>"Data not updated"
                            );
                        }
                    
                    }elseif($newpassword1==''){

                        $form_data_user= array(
                            'email'             => $email,                                  
                            'group_id'          =>  2,
                            'registered_date'   => $rdate           
                             );
                        $where="WHERE id='$id'";
                        $add_edit_user1 =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);

                        $form_data= array(              
                            'firstname'     => ucfirst($firstname),
                            'lastname'      => ucfirst($lastname),
                            'address'       => $address,
                            'city'          => ucfirst($city),
                            'state'         => ucfirst($state),
                            'country'       => 'AU',
                            'zipcode'       => $zipcode,
                            'contactno'     => $contactno,             
                             );
                        
                        $where="WHERE user_id='$id'";            
                        $add_edit_profile   =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data, $where);
                        if($add_edit_profile>0){
                            $message = array(
                            "success" => "true",
                            "error" => "null",
                            "status" => "Profile updated successfully",
                            );
                        }else{
                            $message = array(
                                "success" => "false",
                                "error" => "Something is wrong to insert data, Please try again later.",
                                "status"=>"Data not updated"
                            );
                        }
                        
                    }else{
                        $message = array(
                            "success" => "false",
                            "error" => "Please enter password or check confirm password",
                            "status" => "Please enter password or check confirm password",
                        );
                       

                    }
                            
            } else {

                $message = array(
                    "success" => "false",
                    "error" => "Something is wrong to insert data, Please try again later.",
                    "status"=>"Data not updated"
                );
                
                
            }
        }
    echo json_encode(array('result' => $message));
}


//upload user profile image
//upload product order images 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='userprofileimage'){
    //print_r($_POST);
     //print_r($_FILES);

     if(isset($_FILES['file_upload']['name']) && !empty($_FILES['file_upload']['name'])) {      
             $select_fields =   'userimage';
             $where =   "WHERE user_id ='".$_POST['user_id']."'";
             $getDataimageDrtails = $mysql->dbLoadObjectList($tbl_profile,$select_fields,$where);
             $profile_img = $getDataimageDrtails[0]->userimage;
             unlink($pro_pic_path.$profile_img);

             $image = new SimpleImage();            
             $userimage =   time().str_replace(" ","_",$_FILES['file_upload']['name']);
             $image->load($_FILES['file_upload']['tmp_name']);      
             // $image->resize(150,150);
             $path=$_SERVER['DOCUMENT_ROOT'].'/admin/upload/userprofileimages/'.$userimage;            
             $image->save($path);           
            } 
            //$id=$_POST['user_id'];
            $xplode=explode('.', $_FILES['file_upload']['name']);
            $id=$xplode[0];
    if($id!='' && $userimage!=''){
            //if($id!=''){

            $form_data_user= array(
                
                'userimage' => $userimage
                        
            );
                $where="user_id='$id'";
                $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data_user, $where);
                if($insert_oreder){
                    $message = array(
                        "success" => "true",
                        "error" => "null",
                        "status"=>"Profile image inserted !"
                    );
                }else{
                    $message = array(
                        "success" => "false",
                        "error" => "Something is wrong to insert data, Please try again later.",
                        "status"=>"Profile image not inserted"
                    );
                }
             }else{

                 $message = array(
                        "success" => "false",
                        "error" => "Data missing",
                        "status"=>"Profile image not inserted"
                    );
             }
            echo json_encode(array('result' => $message)); 
}


//CATEGORY LIST for the Products
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'categories')) {
//getting list of categories for products   
    $query = "SELECT category,id FROM $tbl_whatlite_category WHERE status='0'";
    $result = mysql_query($query);
    while ($row = mysql_fetch_object($result)) {
        $message[] = array(
            "cat_id" => $row->id,
            "cat_name" => (stripcslashes($row->category))
            
        );
    }  
    echo json_encode(array('result' => $message));  
}

//SUB CATEGORY LIST for the Products
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'subcategory')) {
//getting list of SUB categories for products   
    $json=json_decode(file_get_contents("php://input"));

    $cat_id=$json->cat_id;
    $query = "SELECT subcategory,id FROM $tbl_whatlite_subcategory WHERE status='0' and cat_id='$cat_id'";
    $result = mysql_query($query);
    while ($row = mysql_fetch_object($result)) {
        $message[] = array(
            "subcategory_id" => $row->id,
            "subcategory_name" => (stripcslashes($row->subcategory))
            
        );
    }  
    echo json_encode(array('result' => $message));  
}

//Section LIST for the Products
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'section')) {
//getting list of Section for products  
    
$json=json_decode(file_get_contents("php://input"));
    $cat_id=$json->cat_id;
    $subcat_id=$json->subcat_id;

    $query = "SELECT section,id FROM $tbl_whatlite_section WHERE status='0' and cat_id='$cat_id' and subcat_id='$subcat_id'";
    $result = mysql_query($query);
    while ($row = mysql_fetch_object($result)) {
        $message[] = array(
            "section_id" => $row->id,
            "section_name" => (stripcslashes($row->section))
            
        );
    }  
    echo json_encode(array('result' => $message));  
}


//whatlite library Common category section
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'whatlite_library_commondata')) {
//getting list of categories for products   
    $query = "SELECT category,id FROM $tbl_whatlite_category WHERE status='0'";
    $result = mysql_query($query);
    while ($row = mysql_fetch_object($result)) {

        $query1 = "SELECT subcategory,id FROM $tbl_whatlite_subcategory WHERE status='0' and cat_id='$row->id'";
        $result1 = mysql_query($query1);
        while ($row1 = mysql_fetch_object($result1)) {

            $query2 = "SELECT section,id FROM $tbl_whatlite_section WHERE status='0' and cat_id='$row->id' and subcat_id='$row1->id'";
            $result2 = mysql_query($query2);
            while ($row2 = mysql_fetch_object($result2)) {
                $section[] = array(
                    "section_id" => $row2->id,
                    "section_name" => (stripcslashes($row2->section))
                    
                );
            } 
            $subcategory[] = array(
                "subcategory_id" => $row1->id,
                "subcategory_name" => (stripcslashes($row1->subcategory)),
                "section"=>$section
                
            );
            unset($section);
        }  

        $category[] = array(
            "cat_id" => $row->id,
            "cat_name" => (stripcslashes($row->category)),
            "subcategory"=>$subcategory
            
        );
        unset($subcategory);
    }  
    $message = array(
            "success" => "true",
            "error" => "null",
            'data'=>$category
            );
    echo json_encode(array('result' => $message));  
}



//Common data for model dependancy 
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'commonmodeldata')) {
//getting list of model for products    
    $json=json_decode(file_get_contents("php://input"));

    $brand=$json->brand_id;
    $type=$json->type_id;
    $query = "SELECT m.model,m.id FROM $tbl_model as m INNER JOIN $tbl_product_price as p ON p.model_id=m.id WHERE m.status='0' AND p.status='0' AND m.brand_id='$brand' AND m.type_id='$type' GROUP BY m.model";
    $result = mysql_query($query);
    while ($row = mysql_fetch_object($result)) {
        $query1 = "SELECT v.voltage,v.id FROM $tbl_voltage as v INNER JOIN $tbl_product_price as p ON p.voltage_id=v.id WHERE v.status='0' AND p.status='0' AND v.model_id='$row->id' GROUP BY v.voltage";
        $result1 = mysql_query($query1);
        while ($row1 = mysql_fetch_object($result1)) {
            $voltage[] = array(
                "voltage_id" => $row1->id,
                "voltage_name" => (stripcslashes($row1->voltage))
                
            );
        }
        $query2 = "SELECT w.wattage,w.id FROM $tbl_wattage as w INNER JOIN $tbl_product_price as p ON p.wattage_id=w.id WHERE w.status='0' AND p.status='0' AND w.model_id='$row->id' GROUP BY w.wattage";
        $result2 = mysql_query($query2);
        while ($row2 = mysql_fetch_object($result2)) {
            $wattage[] = array(
                "wattage_id" => $row2->id,
                "wattage_name" => (stripcslashes($row2->wattage))
                
            );
        }
        $query3 = "SELECT color,id FROM $tbl_color WHERE status='0' AND model_id='$row->id'";
        $result3 = mysql_query($query3);
        while ($row3 = mysql_fetch_object($result3)) {
            $color[] = array(
                "color_id" => $row3->id,
                "color_name" => (stripcslashes($row3->color))
                
            );
        }  
        $query4 = "SELECT f.fittingtype,f.id FROM $tbl_fittingtype as f INNER JOIN $tbl_product_price as p ON p.fittingtype_id=f.id WHERE f.status='0' AND p.status='0' AND f.model_id='$row->id' GROUP BY f.fittingtype";
        $result4 = mysql_query($query4);
        while ($row4 = mysql_fetch_object($result4)) {
            $fittingtype[] = array(
                "fittingtype_id" => $row4->id,
                "fittingtype_name" => (stripcslashes($row4->fittingtype))
                
            );
        }
        $model[] = array(
            "model_id" => $row->id,
            "model_name" => $row->model,
            "voltages"=>$voltage,
            "wattages"=>$wattage,
            "colors"=>$color,
            "fittingtypes"=>$fittingtype
            
        );
        unset($voltage);
        unset($wattage);
        unset($color);
        unset($fittingtype);
               
    } 
     $message = array(
            "success" => "true",
            "error" => "null",
            'models'=>$model
            ); 
    echo json_encode(array('result' => $message));   
}


//Brand and type  LIST for the Products and also shipping/ delivery price list
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'commondata')) {

        $query = "SELECT b.brand,b.id FROM $tbl_brand as b INNER JOIN $tbl_products as p ON p.brand_id=b.id WHERE b.status='0' AND p.status='0' GROUP BY b.brand";
                $result = mysql_query($query);
                while ($row = mysql_fetch_object($result)) {
                    $brand[] = array(
                        "brand_id" => $row->id,
                        "brand_name" => (stripcslashes($row->brand))
                        
                    );
                } 
        $query = "SELECT t.type,t.id FROM $tbl_type as t INNER JOIN $tbl_products as p ON p.type_id=t.id WHERE t.status='0' AND p.status='0' GROUP BY t.type";
            $result = mysql_query($query);
            while ($row = mysql_fetch_object($result)) {
                $type[] = array(
                    "type_id" => $row->id,
                    "type_name" => (stripcslashes($row->type))
                    
                );
            } 
         $query = "SELECT * FROM $tbl_delivery_option WHERE status='0'";
            $result = mysql_query($query);
            while ($row = mysql_fetch_object($result)) {
                $delivery=stripcslashes(stripcslashes($row->delivery)).'(extra $'. stripcslashes(stripcslashes($row->dprice)).')';
                $description=$row->description;
                $delivery_option[] = array(
                    "id" => $row->id,
                    "delivery_option" => (stripcslashes($delivery)),
                    "delivery_price" => $row->dprice,
                    "shortdescription" => (stripcslashes($description))                    
                );
            } 
    $sqlmode="SELECT gst FROM wl_general_setting WHERE id='1'";
    $resulstmode=$mysql->dbLoadObjectlst($sqlmode);
    $gst=$resulstmode[0]->gst;

    $message['brand']=$brand;
    $message['type']=$type;
    $message['delivery_option']=$delivery_option;
    $message['gst']=$gst;
    $message['dealimages']=$path_dealimages;
   

    echo json_encode(array('result' => $message)); 

}



//upload product order images 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='uploadcatalogimages'){

    if(isset($_FILES['file_catalog']['name']) && !empty($_FILES['file_catalog']['name'])) {       
    $image = new SimpleImage();         
    $productimage   =   time().str_replace(" ","_",$_FILES['file_catalog']['name']);
    $image->load($_FILES['file_catalog']['tmp_name']);       
    $image->resize(280,230);
    $path=$_SERVER['DOCUMENT_ROOT'].'/admin/upload/catalogs_images/'.$productimage;            
    $image->save($path);            
    } 

    $xplode=explode('.', $_FILES['file_catalog']['name']);
    $product_id=$xplode[0];
    //$product_id=$_REQUEST['catelog_id'];

    $imagesforproduct=$path_catalogimage.$productimage;
    
    if($product_id!='' && $productimage!=''){

            $form_data_user= array(
                'catalog_id'    => $product_id,
                'catalog_image' => $productimage,
                'status'        =>0
                        
            );
                $where='';
                $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_catalog_images, $form_data_user, $where);
                if($insert_oreder){
                    $message = array(
                        "success" => "true",
                        "error" => "null",
                        "status"=>"Catalog image inserted !",
                        "image_path"=>$imagesforproduct
                    );
                }else{
                    $message = array(
                        "success" => "false",
                        "error" => "Something is wrong to insert data, Please try again later.",
                        "status"=>"Catalog image not inserted"
                    );
                }
            }else{

                 $message = array(
                        "success" => "false",
                        "error" => "Data missing",
                        "status"=>"Catalog image not inserted"
                    );
            }
            echo json_encode(array('result' => $message)); 
}



//product order now section
if(isset($_REQUEST['method']) && $_REQUEST['method']=='order_now'){

    $json=json_decode(file_get_contents("php://input")); 
    
    $userid                     =   $json->user_id;
    //delivery/ shipping method
    $delivery_option_id         =   $json->delivery_option_id;
    $delivery_price             =   $json->delivery_price;
    //transaction id after successfull
    $txtid                      =   'WL'.time();
    $txtstatus                  =   'In Progress';

   

    $totalamount                =   $json->totalamount;
    $paytype                    =   $json->paytype;

    //electrician information
    $electrician_name           =   $json->electrician_name;
    $electrician_email          =   $json->electrician_email;
    $electrician_contact        =   $json->electrician_contact;
    $electrician_address        =   $json->electrician_address;

    //shipping information 
    $firstname                  =   $json->firstname;

    $email                      =   $json->email;
    $contactno                  =   $json->contactno;
    $shipping_address           =   $json->shipping_address;

    $state                      =   $json->state;
    $country                    =   $json->country;
    $zipcode                    =   $json->zipcode;
    
    //array of product_id, and in sub array in this of color_id and fitting_type_id
    $product                    =   $json->product;

    /*********************************comission calculation********************************************/
    $countrycomm=mysql_fetch_object(mysql_query("SELECT inv.investor_id,inc.country FROM wl_investor_postcode as inv INNER JOIN wl_investor_commission as inc   where inc.country_type='$country' and inv.country='$country'"));
    $countrycommission=$countrycomm->country;
    $countryinvestor=$countrycomm->investor_id;



    $statecomm=mysql_fetch_object(mysql_query("SELECT inv.investor_id,inc.state FROM wl_investor_postcode as inv INNER JOIN wl_investor_commission as inc   where inc.country_type='$country' and inv.state='$state'"));
    $statecommission=$statecomm->state;
    $stateinvestor=$statecomm->investor_id;

    $postcodecomm=mysql_fetch_object(mysql_query("SELECT inv.investor_id,inc.zipcode FROM wl_investor_postcode as inv INNER JOIN wl_investor_commission as inc   where inc.country_type='$country' and inv.postcode_id='$zipcode'"));
    $postcodecommission=$postcodecomm->zipcode;
    $postcodeinvestor=$postcodecomm->investor_id;

    /*********************************End comission calculation********************************************/

   
    
    $rdate = date('Y-m-d'); 
    $order_no=time();
    if($txtid!='' && $userid!=''){
                $form_order= array(
                    'txnid'             => $txtid,
                    'transaction_status'=> $txtstatus,
                    'user_id'           => $userid,
                    'txt_type'          => $paytype,
                    'totalamount'       => $totalamount,                                       
                    'status'            => 2,
                    'created'           => $rdate,
                    'cinvestor'         => $countryinvestor, 
                    'sinvestor'         => $stateinvestor, 
                    'pinvestor'         => $postalcodeinvestor 
                     );

                $where='';
                $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_order, $form_order, $where);
                $orderid=mysql_insert_id();

                //insert electrician and shipping address
                if($electrician_email!=''){
                    $checkshipiing1=mysql_query("select * from $tbl_shipping_electrician where electrician_email='$electrician_email'");
                    $form_electrician=array(
                        'electrician_name'=>$electrician_name,
                        'electrician_email'=>$electrician_email,
                        'electrician_contact'=>$electrician_contact,
                        'electrician_address'=>$electrician_address,
                        'user_id'=>$userid
                        );
                    if(mysql_num_rows($checkshipiing1)>0){
                        $where="WHERE electrician_email='$electrician_email'";
                    }else{
                        $where='';
                        $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='6'"));
                        $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$userid','$getpoint->points','Electrician Nomination')");
                        
                //push notification function for send push notification to user
                    push_notification();
                    }
                

                $insert_elec  =   $mysql->dbQueryRowsAffected($tbl_shipping_electrician, $form_electrician, $where);
                }
                //insert shipping information into table 
                

                $form_electrician=array(
                    'firstname'=>$firstname,
                  
                    'email'=>$email,                    
                    'contactno'=>$contactno,
                    'address'=>$shipping_address,
                    
                    'state'=>$state,
                    'country'=>'AU',
                    'zipcode'=>$zipcode,
                    'user_id'=>$userid,
                    'order_id'=>$orderid
                    );
                
                    $where='';
                               
                $insert_ship =   $mysql->dbQueryRowsAffected($tbl_checkout_shipping, $form_electrician, $where);

                //insertstar lite point per order
                //select points and insert into startlite table
        
                $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='4'"));
      

                $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$userid','$getpoint->points','Per Order')");
                
            //push notification function for send push notification to user
                push_notification();
                //catalog insertion and order detail insertion
                foreach($product as $product1){
                    $cat_id=$product1->cat_id;
                    $subcat_id=$product1->subcat_id;
                    $section_id=$product1->section_id;
                    $product_id=$product1->product_id;    
                    $vendor_id=$product1->vendor_id;    
                    //color id                   
                    $color_id=$product1->color_id;                  
                    //fittingtype id                     
                    $fittingtype_id=$product1->fittingtype_id;

                    $quantity=$product1->quantity;
                    $price=$product1->price;

                    $rdate = date('Y-m-d'); 
                    $order_no=time();

                    if($cat_id!='' && $subcat_id!='' && $section_id!=''){
                        
                        //catalog insertion
                        $form_data_user= array(
                                        'order_no'          => $order_no,
                                        'user_id'           => $userid,
                                        'cat_id'            => $cat_id,
                                        'subcat_id'         => $subcat_id,
                                        'section_id'        => $section_id,
                                        'product_id'        => $product_id,
                                        'color_id'          => $color_id,
                                        'fittingtype_id'    => $fittingtype_id,
                                        'quantity'          => $quantity,
                                        'price'             => $price,
                                        'status'            => 0,
                                        'created'           => $rdate           
                                    );
                        $checkcatalog="SELECT id FROM $tbl_product_catalog WHERE user_id='$userid' AND cat_id='$cat_id' AND subcat_id='$subcat_id' AND section_id='$section_id' AND  product_id='$product_id' and color_id='$color_id' and fittingtype_id='$fittingtype_id'";
                        $resulstcatalogid=$mysql->dbLoadObjectlst($checkcatalog);
                        if(mysql_num_rows(mysql_query($checkcatalog))>0){
                            //update catalog if exists
                            $where="WHERE id='".$resulstcatalogid[0]->id."'";
                            $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_product_catalog, $form_data_user, $where);
                        }else{
                            //insert catalog if doesnot exists
                            $where="";
                            $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_product_catalog, $form_data_user, $where);

                            $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='1'"));
                            $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$user_id','$getpoint->points','Library Plan Entry')");
                            
                        //push notification function for send push notification to user
                            push_notification();
                        }
                    }
                    $product_id=$product_id;
                   // echo "SELECT vendor_id FROM $tbl_products WHERE id='$product_id'";
                    $selectvdid=mysql_fetch_object(mysql_query("SELECT vendor_id FROM $tbl_products WHERE id='$product_id'"));
                    $vendorids[]=$selectvdid->vendor_id;
                    $vndorarray=array_unique($vendorids);
                    //print_r($vndorarray);
                    //select points from product table for user
                    $pointsfromproduct=mysql_fetch_object(mysql_query("select * from $tbl_products where id='$product_id'"));
                    $spoint=$pointsfromproduct->saepoints;
                    //order details insertion
                    $form_orderdetail= array(
                        'orderid'           => $orderid,
                        'product_id'        => $product_id,
                        'pquantity'         => $quantity,
                        'color_id'          => $color_id,
                        'fittingtype_id'    => $fittingtype_id,
                        'cost'              => $price,
                        'star_points'       => $spoint 
                         );
                    $where='';
                    $insert_oreder1 =   $mysql->dbQueryRowsAffected($tbl_order_detail, $form_orderdetail, $where);
                    
                    //start lite point as per product to customer
                    $pointssae=(int)($spoint*$quantity);
                    $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$userid','$pointssae','perproduct-$product_id($quantity)')");
                    
                //push notification function for send push notification to user
                    push_notification();
                }
                if($insert_oreder){


                    $message = array(
                        "success" => "true",
                        "error" => "null",
                        "status"=>"Order inserted !",
                        "order_id"=>$txtid
                    );
                }else{
                    $message = array(
                        "success" => "false",
                        "error" => "Something is wrong to insert data, Please try again later.",
                        "status"=>"Order not inserted"
                    );
                }
            }else{

                 $message = array(
                        "success" => "false",
                        "error" => "Data missing",
                        "status"=>"Order not inserted"
                    );
            }
    $sqlmode="SELECT gst,gst_paypal FROM wl_general_setting WHERE id='1'";
    $resulstmode=$mysql->dbLoadObjectlst($sqlmode);
    $gst=$resulstmode[0]->gst;
    $gst_paypal=$resulstmode[0]->gst_paypal;



    $saeentry="SELECT points FROM wl_starlite_category WHERE id='7'";
    $resulstsae=$mysql->dbLoadObjectlst($saeentry);
    $saeentryp=$resulstsae[0]->points;


   

   


    $cinvestoramt=0;
    $sinvestoramt=0;
    $pinvestoramt=0;
    $adminitotoalamt=0;
    $totalprice=0;
    $gsttotalamt=0;
   
    foreach ($vndorarray as $vendorid) {
       
   

    /*********************************ADMIN  calculation********************************************/
    $vednoradminsql="SELECT od.star_points as saepoints,od.cost,od.pquantity,od.product_id,sh.dprice,((od.pquantity*od.cost)+sh.dprice) as price,vc.commission as vcomm,vc.slcommission as scomm,(((od.pquantity*od.cost)+sh.dprice)*vc.commission/100) as admincommission FROM wl_products as p INNER JOIN wl_order_detail as od ON od.product_id=p.id INNER JOIN wl_order as o ON o.id=od.orderid INNER JOIN wl_vendor_commission as vc on vc.user_id=p.vendor_id INNER JOIN $tbl_delivery_option as sh ON sh.id=p.shipping_id where p.vendor_id='$vendorid' and od.orderid='$orderid'";
    $esevendor=mysql_query($vednoradminsql);
    $totalsp=0;
    $adminamt=0;
    $vendoramt=0;
    $cinvestoramt1=0;
    $sinvestoramt1=0;
    $pinvestoramt1=0;
    


    while($admincomm=mysql_fetch_object($esevendor)){

        $fetchremainpt=mysql_fetch_object(mysql_query("SELECT remain_point FROM wl_commissions where vendorid='$vendorid' ORDER BY id DESC"));

        $totalsp=$totalsp+$admincomm->saepoints+$fetchremainpt->remain_point;

        $gstamt=($admincomm->price*$gst)/100;  
        $gsttotalamt=$gsttotalamt+$gstamt;
        $gsttotalamt=number_format($gsttotalamt,2,'.','');



        $adm=($admincomm->admincommission);
        $adm=number_format($adm,2,'.','');
        $adm1=($admincomm->admincommission);
        $adm1=number_format($adm1,2,'.','');

        $vnd=(($admincomm->price-$adm));
        $vnd=number_format($vnd,2,'.','');
        $vnd1=(($admincomm->price-$adm));
        $vnd1=number_format($vnd1,2,'.','');

        $cinv=($adm*$countrycommission/100);
        $sinv=($adm*$statecommission/100);
        $pinv=($adm*$postcodecommission/100);

        $adm=$adm-($cinv+$sinv+$pinv);
        $adm=number_format($adm,2,'.','');

        
        $adminamt=$adminamt+$adm;
        $adminamt=number_format($adminamt,2,'.','');
        $vendoramt=$vendoramt+$vnd;
        $vendoramt=number_format($vendoramt,2,'.','');
        $cinvestoramt1=$cinv;
        $cinvestoramt1=number_format($cinvestoramt1,2,'.','');
        $cinvestoramt=$cinvestoramt+$cinv;
        $cinvestoramt=number_format($cinvestoramt,2,'.','');
        $sinvestoramt=$sinvestoramt+$sinv;
        $sinvestoramt=number_format($sinvestoramt,2,'.','');
        $sinvestoramt1=$sinv;
        $sinvestoramt1=number_format($sinvestoramt1,2,'.','');
        $pinvestoramt=$pinvestoramt+$pinv;
        $pinvestoramt=number_format($pinvestoramt,2,'.','');
        $pinvestoramt1=$pinv;
        $pinvestoramt1=number_format($pinvestoramt1,2,'.','');

    $saecomm=(int)($totalsp/$saeentryp);
    $comsae=(($saecomm*$admincomm->scomm));

    $comsae=number_format($comsae,2,'.','');



    $product_id=$admincomm->product_id;
    $order_id=$orderid;
    $total_point=$totalsp;
    $remain_point=($totalsp%$saeentryp);
    $sae_commission=$admincomm->scomm;
    $sae_amount=$saecomm;
    $vendor_id=$vendorid;
    $cinvestor_id=$countryinvestor;
    $sinvestor_id=$stateinvestor;
    $pinvestor_id=$postcodeinvestor;
    $product_price=$admincomm->cost;
    $proudct_quantity=$admincomm->pquantity;
    $sh_amount=$admincomm->dprice;
    $gst=$gst;
    $total_amount=$admincomm->price;
    $vendor_comminssion=$admincomm->vcomm;
    $cinv_comminssion=$countrycommission;
    $sinv_comminssion=$statecommission;
    $pinv_comminssion=$postcodecommission;
    $vendor_amount=$vnd1;
    $admin_amount=$adm1;
    $cinv_amount=$cinvestoramt1;
    $sinv_amount=$sinvestoramt1;
    $pinv_amount=$pinvestoramt1;



    $insertcomm="INSERT INTO `wl_commissions` (`id`, `product_id`, `order_id`, `total_point`, `remain_point`, `sae_commission`, `sae_amount`, `vendor_id`, `cinvestor_id`, `sinvestor_id`, `pinvestor_id`, `product_price`, `proudct_quantity`, `sh_amount`,`gst`, `total_amount`, `vendor_comminssion`, `cinv_comminssion`, `sinv_comminssion`, `pinv_comminssion`, `vendor_amount`, `admin_amount`, `cinv_amount`, `sinv_amount`, `pinv_amount`,`s_points`, `pay_status`) VALUES (NULL, '$product_id', '$order_id', '$total_point', '$remain_point', '$sae_commission', '$sae_amount', '$vendor_id', '$cinvestor_id', '$sinvestor_id', '$pinvestor_id', '$product_price', '$proudct_quantity', '$sh_amount','$gst', '$total_amount', '$vendor_comminssion', '$cinv_comminssion', '$sinv_comminssion', '$pinv_comminssion', '$vendor_amount', '$admin_amount', '$cinv_amount', '$sinv_amount', '$pinv_amount','$admincomm->saepoints', '0')";
    mysql_query($insertcomm);


    }


    $adminitotoalamt=$adminitotoalamt+$adminamt+$saecomm;
    $adminitotoalamt=number_format($adminitotoalamt,2,'.','');
    /* *******************************ADMIN calculation end*****************************************/
    $fetchv=mysql_fetch_object(mysql_query("SELECT paypal_id from $tbl_profile WHERE user_id='$vendorid'"));
    $arrayv=$vendorpayarra['vendor'];
    if(count($arrayv)>1){
        for($i=0;$i<count($arrayv);$i++){
        if($arrayv[$i]['receiverEmail']==$fetchv->paypal_id){
                //echo 'yes match';
                $vendorpayarra['vendor'][$i]=array(
                'receiverEmail'=>$fetcha->paypal_id,
                'receiverAmount'=>sprintf("%01.2f",$arrayv[$i]['receiverAmount']+$vendoramt-$saecomm, 2, '.', '')
                );
            }else{
               $vendorpayarra['vendor'][]=array(
                    'receiverEmail'=>$fetchv->paypal_id,
                    'receiverAmount'=>sprintf("%01.2f",$vendoramt-$saecomm)
                    );
            }
        }
    }else{
       $vendorpayarra['vendor'][]=array(
            'receiverEmail'=>$fetchv->paypal_id,
            'receiverAmount'=>sprintf("%01.2f",$vendoramt-$saecomm)
            );
    }


    }

    $fetcha=mysql_fetch_object(mysql_query("SELECT paypal_id from $tbl_profile WHERE user_id='1'"));
    //print_r($vendorpayarra['vendor']);
    $checka='no';
    
    $arraya=$vendorpayarra['vendor'];
    for($i=0;$i<count($arraya);$i++){

        for($i=0;$i<count($vendorpayarra['vendor']);$i++){
            if($vendorpayarra['vendor'][$i]['receiverEmail']==$fetcha->paypal_id){
                
                $checka='yes';
            }
        }

        if($arraya[$i]['receiverEmail']==$fetcha->paypal_id && $checka=='yes'){
            //echo 'yes match';
            $vendorpayarra['vendor'][$i]=array(
            'receiverEmail'=>$fetcha->paypal_id,
            'receiverAmount'=>sprintf("%01.2f",$arraya[$i]['receiverAmount']+$adminitotoalamt)
            );
        }else{
           $vendorpayarra['vendor'][]=array(
            'receiverEmail'=>$fetcha->paypal_id,
            'receiverAmount'=>sprintf("%01.2f",$adminitotoalamt)
            ); 
        }
    }
    //echo in_array($fetcha->paypal_id, $vendorpayarra['vendor']);

    if($countryinvestor!=''){
        $fetch1=mysql_fetch_object(mysql_query("SELECT paypal_id from $tbl_profile WHERE user_id='$countryinvestor'"));
        $checkc='no';
        if($fetch1->paypal_id!='' && $cinvestoramt!=''){
            $arrayc=$vendorpayarra['vendor'];
            for($i=0;$i<count($arrayc);$i++){

                for($i=0;$i<count($vendorpayarra['vendor']);$i++){
                    if($vendorpayarra['vendor'][$i]['receiverEmail']==$fetch1->paypal_id){
                        
                        $checkc='yes';
                    }
                }
                    if($arrayc[$i]['receiverEmail']==$fetch1->paypal_id && $checkc=='yes'){
                        //echo 'yes match';
                        $vendorpayarra['vendor'][$i]=array(
                        'receiverEmail'=>$fetch1->paypal_id,
                        'receiverAmount'=>sprintf("%01.2f",$arrayc[$i]['receiverAmount']+$cinvestoramt)
                        );
                    }else{
                        $vendorpayarra['vendor'][]=array(
                        'receiverEmail'=>$fetch1->paypal_id,
                        'receiverAmount'=>sprintf("%01.2f",$cinvestoramt)
                        );
                    }
                }

        }
        
    }
    if($stateinvestor!=''){
        $fetch2=mysql_fetch_object(mysql_query("SELECT paypal_id from $tbl_profile WHERE user_id='$stateinvestor'"));
        $checks='no';
         if($fetch2->paypal_id!='' && $sinvestoramt!=''){
            $arrays=$vendorpayarra['vendor'];
            for($i=0;$i<count($arrays);$i++){
                for($i=0;$i<count($vendorpayarra['vendor']);$i++){
                    if($vendorpayarra['vendor'][$i]['receiverEmail']==$fetch2->paypal_id){
                        
                        $checks='yes';
                    }
                }

                    if($arrays[$i]['receiverEmail']==$fetch2->paypal_id && $checks=='yes'){
                        //echo 'yes match';
                        $vendorpayarra['vendor'][$i]=array(
                        'receiverEmail'=>$fetch2->paypal_id,
                        'receiverAmount'=>sprintf("%01.2f",$arrays[$i]['receiverAmount']+$sinvestoramt)
                        );
                    }else{
                        $vendorpayarra['vendor'][]=array(
                        'receiverEmail'=>$fetch2->paypal_id,
                        'receiverAmount'=>sprintf("%01.2f",$sinvestoramt)
                        );
                    }
                }
         
        }
    }
    if($postcodeinvestor!=''){
        $fetch3=mysql_fetch_object(mysql_query("SELECT paypal_id from $tbl_profile WHERE user_id='$postcodeinvestor'"));
        $checkp='no';
        if($fetch3->paypal_id!='' && $pinvestoramt!=''){
        $arrayp=$vendorpayarra['vendor'];
            for($i=0;$i<count($arrayp);$i++){
                for($i=0;$i<count($vendorpayarra['vendor']);$i++){
                    if($vendorpayarra['vendor'][$i]['receiverEmail']==$fetch3->paypal_id){
                        
                        $checkp='yes';
                    }
                }

                    if($arrayp[$i]['receiverEmail']==$fetch3->paypal_id  && $checks=='yes'){
                        //echo 'yes match';
                        $vendorpayarra['vendor'][$i]=array(
                        'receiverEmail'=>$fetch3->paypal_id,
                        'receiverAmount'=>sprintf("%01.2f",$arrayp[$i]['receiverAmount']+$pinvestoramt)
                        );
                    }else{
                        $vendorpayarra['vendor'][]=array(
                        'receiverEmail'=>$fetch3->paypal_id,
                        'receiverAmount'=>sprintf("%01.2f",$pinvestoramt)
                        );
                    }
                }

        }
    }
    //gst payment 
    $check='no';
    $j='';
    if($gst_paypal!=''){
       $arrayg=$vendorpayarra['vendor'];
      // print_r($vendorpayarra['vendor']);
        for($i=0;$i<count($vendorpayarra['vendor']);$i++){
            if($vendorpayarra['vendor'][$i]['receiverEmail']==$gst_paypal){
                $j=$i;
                $check='yes';
            }
        }
        //echo $j;         
        if($check=='no'){
            //echo 'yes match';
            $vendorpayarra['vendor'][]=array(
            'receiverEmail'=>$gst_paypal,
            'receiverAmount'=>sprintf("%01.2f",$gsttotalamt)
            );
        }else{
            //echo $vendorpayarra['vendor'][$j]['receiverAmount'];
            $vendorpayarra['vendor'][$j]=array(
            'receiverEmail'=>$gst_paypal,
            'receiverAmount'=>sprintf("%01.2f",$vendorpayarra['vendor'][$j]['receiverAmount']+$gsttotalamt)
            );
        }
        
    }
///print_r($vendorpayarra);
    $message['vendorarray']=$vendorpayarra;
    echo json_encode(array('result' => $message)); 
}

//My order history section  new
if(isset($_REQUEST['method']) && $_REQUEST['method']=='returntoweb'){
        $json=json_decode(file_get_contents("php://input"));
        $txnid=$json->order_id;
        $success=$json->success;
        $transaction_id=$json->transaction_id;

        if($success=='COMPLETED'){
        // print_r($json);
        $odid=mysql_fetch_object(mysql_query("SELECT id FROM $tbl_order WHERE txnid='".$txnid."'"));
        $updatedata=mysql_query("UPDATE $tbl_order SET txnid='$transaction_id', transaction_status='completed',status='0' WHERE txnid='".$txnid."'");
      
        $updatedata1=mysql_query("UPDATE wl_commissions SET pay_status='1' WHERE order_id='".$odid->id."'");
     

        $sqlmode="SELECT gst FROM wl_general_setting WHERE id='1'";
        $resulstmode=$mysql->dbLoadObjectlst($sqlmode);
        $gst=$resulstmode[0]->gst;



        $saeentry="SELECT points FROM wl_starlite_category WHERE id='7'";
        $resulstsae=$mysql->dbLoadObjectlst($saeentry);
        $saeentryp=$resulstsae[0]->points;


        

        $orderid=$odid->id;
        //send invoice mail 
        $select = "SELECT od.id as id,od.product_id ,p.title,u.user_id as user_id,u.firstname as fullname,u.address as address,u.state as state, u.country as country,u.zipcode as zipcode,pr.mobile_no as contactno,od.pquantity as quantity,od.cost as price,d.dprice,d.delivery,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage,p.vendor_id,color.color,fit.fittingtype, pr.email FROM $tbl_order_detail as od LEFT JOIN $tbl_products as p ON p.id=od.product_id  LEFT JOIN $tbl_order as o ON o.id=od.orderid LEFT JOIN $tbl_users as pr ON pr.id=o.user_id LEFT JOIN $tbl_profile as u ON u.user_id=o.user_id LEFT JOIN $tbl_brand as b ON b.id=p.brand_id LEFT JOIN $tbl_type as t ON t.id=p.type_id LEFT JOIN $tbl_model as m ON m.id=p.model_id LEFT JOIN $tbl_voltage as v ON v.id=p.voltage_id LEFT JOIN $tbl_wattage as w ON w.id=p.wattage_id LEFT JOIN $tbl_delivery_option as d ON d.id=p.shipping_id LEFT JOIN $tbl_color as color on color.id=od.color_id LEFT JOIN $tbl_fittingtype as fit ON fit.id=od.fittingtype_id WHERE od.orderid='$orderid' ";
        $exe=mysql_query($select);
        $addresssql=mysql_fetch_object(mysql_query($select));
        $shipping=mysql_fetch_object(mysql_query("select * from $tbl_checkout_shipping where user_id='$addresssql->user_id' and order_id='$orderid'")); 
        $invoicesql=mysql_fetch_object(mysql_query("select * from $tbl_order where id='$orderid'"));

        $body='
        <html>
        <head>
        <title>Invoice</title>
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet" type="text/css">
        </head>

        <body style="padding:0; margin:0; font-family: Open Sans, sans-serif;">
           <div style="padding:20px; margin:0; width:1024px;">
             <h2 style="font-size:30px; text-transform:capitalize; color:#3f5472; margin:0;">Invoice</h2>
             <div style="margin: 0; padding-top: 10px;">
        <table style="width:1024px; padding:0; margin:0; border-collapse:collapse; white-space:nowrap;">
                  <tr style="background-color:#fd6b32; color:#fff; padding:0px; display:inline-block; width:1024px;">
                     <td style="display: inline-block; float: left; line-height: 17px; padding: 10px; width:321px;">Billing Address</td>
                     <td style="display: inline-block; float: left; line-height: 17px; padding: 10px; width:321px;">Shipping Address</td>
                     <td style="display: inline-block; float: left; line-height: 17px; padding: 10px; width:322px;">Invoice Detail</td>
                  </tr>
               </table>


               <table style="width:100%; padding:30px 0; margin:0; border-collapse:collapse; white-space:nowrap; 
                            display:inline-block; width:100%;">
                <tr style="width:100%;">
                    <td style="display: inline-block; float: left; width:330px; padding:0 0 0 10px;">
                    '.stripcslashes($addresssql->fullname).'<br/> '.stripcslashes($addresssql->address).'<br/> '.stripcslashes($addresssql->state).'- '.stripcslashes($addresssql->zipcode).' '.stripcslashes($addresssql->country).'</td>
                    <td style="display: inline-block; float: left;  padding:0 0 0 10px; width:330px;">
                    '.stripcslashes($shipping->firstname).'<br/> '.stripcslashes($shipping->address).' <br/>'.stripcslashes($shipping->state).'- '.stripcslashes($shipping->zipcode).' '.stripcslashes($shipping->country).'</td>
                    <td style="display: inline-block; float: left;  padding:0 0 0 10px; width:330px;">
                        <strong>Invoice No</strong> : ORDWL'.$txnid.'<br/>
                        <strong>Date </strong>: '.$invoicesql->created.' <br/>
                        <strong>Mobile No</strong> : '.$addresssql->contactno.'
                    </td>
                  </tr>
               </table>
           <table style="padding:30px 0; margin:0; border-collapse:collapse; white-space:nowrap; width:1024px">
            <thead>
                <tr style="background-color:#333333; padding:0; color:#fff; width:1024px;">
                    <th style="text-align:left; font-weight:400; padding:5px;">Sr no.</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Name</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Quantity</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Amount</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Shipping</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Total</th>
                </tr>
            </thead>
            <tbody>
        ';
        $total=0; $i=1; while($objectdata=mysql_fetch_object($exe )) {
        $body.='
                <tr>
                    <td >'.$i.'</td>
                    <td style="padding-top:10px;">'.stripcslashes($objectdata->title).'<br />
                    '. stripcslashes($objectdata->brand).' '.  stripcslashes($objectdata->type). stripcslashes($objectdata->model).' '.    stripcslashes($objectdata->voltage).' '.   stripcslashes($objectdata->wattage) .'<br/> in'.  stripcslashes($objectdata->color).'color and'.  stripcslashes($objectdata->fittingtype) .'</td>
                    <td>'.$objectdata->quantity.'</td>
                    <td>$'.$objectdata->price.'</td>
                    <td>$'.number_format($objectdata->dprice, 2, '.', '').'<br/>('. $objectdata->delivery .')
                    </td>
                    <td>$'.$price= number_format((($objectdata->price*$objectdata->quantity)+$objectdata->dprice), 2, '.', '').'</td>
                </tr>';
            $total=$total+$price; $i++;
            $selectvdid=mysql_fetch_object(mysql_query("SELECT vendor_id FROM $tbl_products WHERE id='$objectdata->product_id'"));
            $vendorids[]=$selectvdid->vendor_id;
            $vndorarray=array_unique($vendorids);
          }
        $body.='
        <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="padding-bottom:10px;"><strong>Sub Total</strong></td>
                    <td style="padding-bottom:10px;"><strong>$'.number_format($total, 2, '.', '') .'</strong></td>
                </tr>
         <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="padding-bottom:10px;"><strong>GST Charge('.$gst.'%)</strong></td>
                    <td style="padding-bottom:10px;"><strong>$'.number_format($total*$gst/100, 2, '.', '') .'</strong></td>
                </tr>
                
                <tr style="background-color:#3f5472;">
                    <td >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td style="color:#fff; padding:10px;"><strong>Total</strong></td>
                    <td style="color:#fff; padding:10px;"><strong>$'.number_format(($total+$invoicesql->delivery_price+($total*$gst/100)), 2, '.', '').'</strong></td>
                </tr>
            </tbody>
            </table>
             <table style="width:1024px; padding:20px 0; ">
                <tr>
                    <td style="font-size:14px; font-weight:500; color:#666;">Best Regard <br/> 
                        Whatlite <br/> <br/>
                      '.$site_logo_link.'
                    </td>
                </tr>
             </table>
             </div>
           </div>   
        </body>
        </html>';
        $email1=$addresssql->email;
        $email2=ADEMAIL;
        $subject = "Order Invoice";
        //echo $email1,$subject,$body;
        //echo $email2,$subject,$body;
        $mail1 = sentmail($email1,$subject,$body);
        $mail12 = sentmail($email2,$subject,$body);


        $cinvestoramt=0;
        $sinvestoramt=0;
        $pinvestoramt=0;
        $adminitotoalamt=0;
        $totalprice=0;
       
        foreach ($vndorarray as $vendorid) {
           $select1 = "SELECT od.id as id ,p.title,u.user_id as user_id,u.firstname as fullname,u.address as address,u.state as state, u.country as country,u.zipcode as zipcode,pr.mobile_no as contactno,od.pquantity as quantity,od.cost as price,d.dprice,d.delivery,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage,p.vendor_id,color.color,fit.fittingtype, pr.email FROM $tbl_order_detail as od LEFT JOIN $tbl_products as p ON p.id=od.product_id  LEFT JOIN $tbl_order as o ON o.id=od.orderid LEFT JOIN $tbl_users as pr ON pr.id=o.user_id LEFT JOIN $tbl_profile as u ON u.user_id=o.user_id LEFT JOIN $tbl_brand as b ON b.id=p.brand_id LEFT JOIN $tbl_type as t ON t.id=p.type_id LEFT JOIN $tbl_model as m ON m.id=p.model_id LEFT JOIN $tbl_voltage as v ON v.id=p.voltage_id LEFT JOIN $tbl_wattage as w ON w.id=p.wattage_id LEFT JOIN $tbl_delivery_option as d ON d.id=p.shipping_id LEFT JOIN $tbl_color as color on color.id=od.color_id LEFT JOIN $tbl_fittingtype as fit ON fit.id=od.fittingtype_id WHERE od.orderid='$orderid' AND p.vendor_id='".$vendorid."'";

        $exe1=mysql_query($select1);
        $body1='
        <html>
        <head>
        <title>Invoice</title>
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet" type="text/css">
        </head>

        <body style="padding:0; margin:0; font-family: Open Sans, sans-serif;">
           <div style="padding:20px; margin:0; width:1024px;">
             <h2 style="font-size:30px; text-transform:capitalize; color:#3f5472; margin:0;">Invoice</h2>
             <div style="margin: 0; padding-top: 10px;">
        <table style="width:1024px; padding:0; margin:0; border-collapse:collapse; white-space:nowrap;">
                  <tr style="background-color:#fd6b32; color:#fff; padding:0px; display:inline-block; width:1024px;">
                     <td style="display: inline-block; float: left; line-height: 17px; padding: 10px; width:321px;">Billing Address</td>
                     <td style="display: inline-block; float: left; line-height: 17px; padding: 10px; width:321px;">Shipping Address</td>
                     <td style="display: inline-block; float: left; line-height: 17px; padding: 10px; width:322px;">Invoice Detail</td>
                  </tr>
               </table>


               <table style="width:100%; padding:30px 0; margin:0; border-collapse:collapse; white-space:nowrap; 
                            display:inline-block; width:100%;">
                <tr style="width:100%;">
                    <td style="display: inline-block; float: left; width:330px; padding:0 0 0 10px;">
                    '.stripcslashes($addresssql->fullname).' <br/>'.stripcslashes($addresssql->address).'<br/> '.stripcslashes($addresssql->state).'- '.stripcslashes($addresssql->zipcode).' '.stripcslashes($addresssql->country).'</td>
                    <td style="display: inline-block; float: left;  padding:0 0 0 10px; width:330px;">
                    '.stripcslashes($shipping->firstname).'<br/> '.stripcslashes($shipping->address).' <br/>'.stripcslashes($shipping->state).'- '.stripcslashes($shipping->zipcode).' '.stripcslashes($shipping->country).'</td>
                    <td style="display: inline-block; float: left;  padding:0 0 0 10px; width:330px;">
                        <strong>Invoice No</strong> : ORDWL'.$txnid.'<br/>
                        <strong>Date </strong>: '.$invoicesql->created.' <br/>
                        <strong>Mobile No</strong> : '.$addresssql->contactno.'
                    </td>
                  </tr>
               </table>
           <table style="padding:30px 0; margin:0; border-collapse:collapse; white-space:nowrap; width:1024px">
            <thead>
                <tr style="background-color:#333333; padding:0; color:#fff; width:1024px;">
                    <th style="text-align:left; font-weight:400; padding:5px;">Sr no.</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Name</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Quantity</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Amount</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Shipping</th>
                    <th style="text-align:left; font-weight:400; padding:5px;">Total</th>
                </tr>
            </thead>
            <tbody>
        ';
        $total=0; $i=1; while($objectdata=mysql_fetch_object($exe1 )) {
        $body1.='
                <tr>
                    <td >'.$i.'</td>
                    <td style="padding-top:10px;">'.stripcslashes($objectdata->title).'<br />
                    '. stripcslashes($objectdata->brand).' '.  stripcslashes($objectdata->type). stripcslashes($objectdata->model).' '.    stripcslashes($objectdata->voltage).' '.   stripcslashes($objectdata->wattage) .'<br/> in'.  stripcslashes($objectdata->color).'color and'.  stripcslashes($objectdata->fittingtype) .'</td>
                    <td>'.$objectdata->quantity.'</td>
                    <td>$'.$objectdata->price.'</td>
                    <td>$'.number_format($objectdata->dprice, 2, '.', '').'<br/>('. $objectdata->delivery .')
                    </td>
                    <td>$'.$price= number_format((($objectdata->price*$objectdata->quantity)+$objectdata->dprice), 2, '.', '').'</td>
                </tr>';
            $total=$total+$price; $i++;}
        $body1.='
        <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="padding-bottom:10px;"><strong>Sub Total</strong></td>
                    <td style="padding-bottom:10px;"><strong>$'.number_format($total, 2, '.', '') .'</strong></td>
                </tr>
         <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="padding-bottom:10px;"><strong>GST Charge('.$gst.'%)</strong></td>
                    <td style="padding-bottom:10px;"><strong>$'.number_format($total*$gst/100, 2, '.', '') .'</strong></td>
                </tr>
                
                <tr style="background-color:#3f5472;">
                    <td >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td style="color:#fff; padding:10px;"><strong>Total</strong></td>
                    <td style="color:#fff; padding:10px;"><strong>$'.number_format(($total+$invoicesql->delivery_price+($total*$gst/100)), 2, '.', '').'</strong></td>
                </tr>
            </tbody>
            </table>
             <table style="width:1024px; padding:20px 0; ">
                <tr>
                    <td style="font-size:14px; font-weight:500; color:#666;">Best Regard <br/> 
                        Whatlite <br/> <br/>
                      '.$site_logo_link.'
                    </td>
                </tr>
             </table>
             </div>
           </div>   
        </body>
        </html>';
        $fetch=mysql_fetch_object(mysql_query("SELECT email from $tbl_users WHERE id='$vendorid'"));
        $vendoremail=$fetch->email;
        $subject = "Order Invoice";    
       // echo $vendoremail,$subject,$body1;
        $mail1 = sentmail($vendoremail,$subject,$body1);
      }
    
        $message = array(
                "success" => "true",
                "error" => "null",
                "status"=>"Payment successfull!",
                "order_id"=>'ORD'.$orderid
            );
        }else{
            $message = array(
                "success" => "false",
                "error" => "Something is wrong, payment not done.",
                "status"=>"Order not inserted"
            );
        }
echo json_encode(array('result' => $message)); 
}

//My order history section  new
if(isset($_REQUEST['method']) && $_REQUEST['method']=='myorderhistory'){

        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;

        $sqlmode="SELECT gst FROM wl_general_setting WHERE id='1'";
        $resulstmode=$mysql->dbLoadObjectlst($sqlmode);
        $gst=$resulstmode[0]->gst;

        $select="SELECT o.id AS id, o.created AS created,o.txnid AS transaction_id, o.transaction_status AS transaction_status, o.status AS shipped_status,o.totalamount AS amount, u.firstname as username  FROM $tbl_order AS o LEFT JOIN $tbl_profile AS u ON u.user_id = o.user_id  WHERE delete_action='' and o.user_id ='$userid' ORDER BY o.id DESC";
        $object=mysql_query($select);
        if(mysql_num_rows($object)){
        while($objectdata=mysql_fetch_object($object)){
            
                //select product data form orderid
                $select_product="SELECT pc.title,pc.product_image,od.product_id as product_id,od.pquantity as pquantity,od.cost as price,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage,c.color as color,f.fittingtype as fittingtype,del.delivery AS delivery,del.dprice AS dprice,vend.firstname as vendor FROM $tbl_order_detail as od LEFT JOIN $tbl_products as pc ON pc.id=od.product_id  LEFT JOIN $tbl_brand as b ON b.id=pc.brand_id LEFT JOIN $tbl_type as t ON t.id=pc.type_id LEFT JOIN $tbl_model as m ON m.id=pc.model_id LEFT JOIN $tbl_voltage as v ON v.id=pc.voltage_id LEFT JOIN $tbl_wattage as w ON w.id=pc.wattage_id LEFT JOIN $tbl_color as c ON c.id=od.color_id LEFT JOIN $tbl_fittingtype as f ON f.id=od.fittingtype_id LEFT JOIN $tbl_delivery_option as del ON del.id=pc.shipping_id LEFT JOIN $tbl_profile as vend ON vend.user_id=pc.vendor_id WHERE od.orderid='$objectdata->id' ORDER BY od.id DESC";
                $select_product_exe=mysql_query($select_product);
                $productquntity=mysql_num_rows($select_product_exe);
                if($productquntity>0){
                    while($select_product_fetch=mysql_fetch_object($select_product_exe)){
                        $brand=$select_product_fetch->brand;
                        $type=$select_product_fetch->type;
                    //product array
                    $product[]=array(
                        "product_id"=>$select_product_fetch->product_id,
                        "quantity"=>$select_product_fetch->pquantity,
                        "product_price"=>$select_product_fetch->price,
                        "shipping_option"=>$select_product_fetch->delivery.'(extra $'.$select_product_fetch->dprice.')', 
                        "shipping_price"=>$select_product_fetch->dprice,
                        "sold_by"=>$select_product_fetch->vendor,
                        "product_imagepath"=>$path_image,
                        "product_image"=>$select_product_fetch->product_image,                        
                        "title"=>$select_product_fetch->title,
                        "brand"=>$select_product_fetch->brand,
                        "type"=>$select_product_fetch->type,
                        "voltage"=>$select_product_fetch->voltage,
                        "wattage"=>$select_product_fetch->wattage,
                        "color"=>$select_product_fetch->color,
                        "fittingtype"=>$select_product_fetch->fittingtype,
                        "description"=>  $select_product_fetch->brand.' '. $select_product_fetch->type.' '. $select_product_fetch->voltage.' '.$select_product_fetch->wattage.' in '. $select_product_fetch->color.' color and '. $select_product_fetch->fittingtype.' fitting type'

                        );
                    }
                }
                if($objectdata->shipped_status==0) { $status='In Progress';}elseif($objectdata->shipped_status==1){ $status='Shipped';}elseif($objectdata->shipped_status==2){$status='Cancel';}else{$status='Complete';}
                $listed_d = date('d M Y', strtotime($objectdata->created));
                //billing address detail for order
                //fetch userinformation for user billing address
                $billselect="SELECT * FROM $tbl_profile WHERE user_id='$userid'";
                $addresssql=mysql_fetch_object(mysql_query($billselect));

                $billing_address=stripcslashes($addresssql->firstname).PHP_EOL.stripcslashes($addresssql->address).PHP_EOL.stripcslashes($addresssql->state).' '.stripcslashes($addresssql->country).'-'.stripcslashes($addresssql->zipcode); 
                //shipping address details for order
                //fetch userinformation for user shipping address
                $shipselect="SELECT * FROM $tbl_checkout_shipping WHERE user_id='$userid' and order_id='$objectdata->id'";
                $shaddresssql=mysql_fetch_object(mysql_query($shipselect));

                $shipping_address=stripcslashes($shaddresssql->firstname).PHP_EOL.stripcslashes($shaddresssql->address).PHP_EOL.stripcslashes($shaddresssql->state).' '.stripcslashes($shaddresssql->country).'-'.stripcslashes($shaddresssql->zipcode); 
                //invoice detail for order 
                $invoicedetail = array(
                    'invoice_no' => $objectdata->transaction_id,
                    'payment_status' => $objectdata->transaction_status,
                    'mobile_no' => $shaddresssql->contactno,
                    'order_no' => 'ORDWL'.$objectdata->id,
                    'order_status' => $status,
                    'order_date' => $listed_d
                    );
                

                $order[] = array(
                        "orderid"=>$objectdata->id,
                        "date"=>$listed_d,
                        "totalamount"=>$objectdata->amount,
                        "username"=>$objectdata->username,
                        "description"=>  $brand.' '. $type,
                        "quantity"=>"$productquntity",                        
                        "transaction_id"=>$objectdata->transaction_id,
                        "transaction_status"=>$objectdata->transaction_status,
                        "shipped_status"=>$status,
                        "gst_charge"=>$gst,
                        "invoice_detail"=>$invoicedetail,
                        "billing_address"=>$billing_address,
                        "shipping_address"=>$shipping_address,
                        "products"=>$product                   
                        
                        
                    );
                unset($product);
        }
        $message = array(
                        "success" => "true",
                        "error" => "null",                       
                         "orders"=>$order                   
                        
                        
                    );
        unset($order);

    }else{
        $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
    }
    echo json_encode(array('result' => $message)); 
}



//my order web service
if(isset($_REQUEST['method']) && $_REQUEST['method']=='myorder'){
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        //select order data
        $select="SELECT o.id AS id, o.created AS created,o.txnid AS transaction_id, o.transaction_status AS transaction_status, o.status AS shipped_status,o.totalamount AS amount, u.firstname as username  FROM $tbl_order AS o INNER JOIN $tbl_profile AS u ON u.user_id = o.user_id  WHERE delete_action='' and o.user_id ='$userid' AND o.status='0' ORDER BY o.id DESC";
        $object=mysql_query($select);
        if(mysql_num_rows($object)){
        while($objectdata=mysql_fetch_object($object)){
            
                //select product data form order
                $select_product="SELECT od.product_id as product_id,od.pquantity as pquantity,od.cost as price,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage,c.color as color,f.fittingtype as fittingtype,del.delivery AS delivery,del.dprice AS dprice FROM $tbl_order_detail as od INNER JOIN $tbl_products as pc ON pc.id=od.product_id  INNER JOIN $tbl_brand as b ON b.id=pc.brand_id INNER JOIN $tbl_type as t ON t.id=pc.type_id INNER JOIN $tbl_model as m ON m.id=pc.model_id INNER JOIN $tbl_voltage as v ON v.id=pc.voltage_id INNER JOIN $tbl_wattage as w ON w.id=pc.wattage_id INNER JOIN $tbl_color as c ON c.id=od.color_id INNER JOIN $tbl_fittingtype as f ON f.id=od.fittingtype_id INNER JOIN $tbl_delivery_option as del ON del.id=pc.shipping_id WHERE od.orderid='$objectdata->id' ORDER BY od.id DESC";
                $select_product_exe=mysql_query($select_product);
                $productquntity=mysql_num_rows($select_product_exe);
                if($productquntity>0){
                    while($select_product_fetch=mysql_fetch_object($select_product_exe)){
                        $brand=$select_product_fetch->brand;
                        $type=$select_product_fetch->type;
                    $product[]=array(
                        "product_id"=>$select_product_fetch->product_id,
                        "quantity"=>$select_product_fetch->pquantity,
                        "product_price"=>$select_product_fetch->price,
                        "delevery_option"=>$select_product_fetch->delivery.'(extra $'.$select_product_fetch->dprice.')', 
                        "delevery_price"=>$select_product_fetch->dprice,
                        "description"=>  $select_product_fetch->brand.' '. $select_product_fetch->voltage.' '.$select_product_fetch->wattage

                        );
                    }
                }
                if($objectdata->shipped_status==0) { $status='In Progress';}elseif($objectdata->shipped_status==1){ $status='Shipped';}elseif($objectdata->shipped_status==3){$status='Complete';}else{$status='Cancel';}
                $listed_d = date('d M Y', strtotime($objectdata->created));
                $order[] = array(
                        "orderid"=>$objectdata->id,
                        "date"=>$listed_d,
                        "totalamount"=>$objectdata->amount,
                        "username"=>$objectdata->username,
                        "description"=>  $brand.' '. $type,
                        "quantity"=>"$productquntity",
                        
                        "transaction_id"=>$objectdata->transaction_id,
                        "transaction_status"=>$objectdata->transaction_status,
                         "shipped_status"=>$status,
                         "products"=>$product                   
                        
                        
                    );
                unset($product);
        }
        $message = array(
                        "success" => "true",
                        "error" => "null",                       
                         "orders"=>$order                   
                        
                        
                    );
        unset($order);

    }else{
        $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
    }
    echo json_encode(array('result' => $message));

}



// add edit category by user 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='add_edit_category'){
    
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        $category=$json->category;
        //check if category name already exists 
        
        
        
        if($catid=='0'){
            $sql=mysql_query("SELECT id from $tbl_whatlite_category where category='$category' AND user_id='$userid'");
        $check=mysql_num_rows($sql);
            if($check > 0){
            $message = array(
                            "success" => "false",
                            "error" => "Category name already exists",
                            'status'=>"Category not inserted !"
                            );
            }else{  
                $form_data= array(
                    'category'      =>ucfirst($category),
                    'status'        =>0,
                    'user_id'       =>$userid                           
                     );
                $where="";           
                $add_edit_category  =   $mysql->dbQueryRowsAffected($tbl_whatlite_category, $form_data, $where);
                $cat_id=mysql_insert_id();
                if($add_edit_category>0){
                    $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Category Inserted Successfully !",
                            "cat_id"=>"$cat_id"
                            );
                }else{
                    $message = array(
                            "success" => "false",
                            "error" => "Data missing",
                            'status'=>"Category not inserted !"
                            );
                }
            }
        }else{
                $form_data= array(
                    'category'      =>ucfirst($category)                                        
                     );
                $where="WHERE id='$catid' AND user_id='$userid'";            
                $add_edit_category  =   $mysql->dbQueryRowsAffected($tbl_whatlite_category, $form_data, $where);
                if($add_edit_category>0){
                    $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Category Updated Successfully !",
                            "cat_id"=>"$catid"
                            );
                }else{
                    $message = array(
                            "success" => "false",
                            "error" => "Data missing",
                            'status'=>"Category not updated !"
                            );
                }
        }
        
        echo json_encode(array('result' => $message));  
}
// Delete category by user 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='delete_category'){
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        
        //check if category name already exists and delete category 
        $check=mysql_num_rows(mysql_query("SELECT id FROM $tbl_whatlite_category where id='$catid' AND user_id='$userid'"));
            $sql=mysql_query("DELETE from $tbl_whatlite_category where id='$catid' AND user_id='$userid'");     
            if($check>0){
            $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Category Deleted Successfully !"
                            );
            }else{  
                $message = array(
                            "success" => "true",
                            "error" => "Category not deleted Successfully !",
                            'status'=>"Category not deleted Successfully !"
                            );
                
            }
                
        echo json_encode(array('result' => $message));  
}


// add edit sub category by user 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='add_edit_subcategory'){
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        $subcategory=$json->subcategory;
        //check if category name already exists 
        
        
        
        if($subcatid=='0'){
            
            $sql=mysql_query("SELECT id from $tbl_whatlite_subcategory where subcategory='$subcategory' AND user_id='$userid' and cat_id='$catid'");
        $check=mysql_num_rows($sql);
            if($check > 0){
            $message = array(
                            "success" => "false",
                            "error" => "Subcategory name already exists",
                            'status'=>"Subcategory not Inserted Successfully !"
                            );
            }else{  
                $form_data= array(
                    'cat_id'        =>$catid,
                    'subcategory'   =>ucfirst($subcategory),
                    'status'        =>0,
                    'user_id'       =>$userid                           
                     );
                $where="";           
                $add_edit_category  =   $mysql->dbQueryRowsAffected($tbl_whatlite_subcategory, $form_data, $where);
                $subcat_id=mysql_insert_id();
                if($add_edit_category>0){
                    $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Subcategory Inserted Successfully !",
                            "subcat_id"=>"$subcat_id"
                            );
                }else{
                    $message = array(
                            "success" => "false",
                            "error" => "Data missing",
                            'status'=>"Subcategory not inserted !"
                            );
                }
            }
        }else{
                $form_data= array(                  
                    'subcategory'   =>ucfirst($subcategory),                                        
                     );
                $where="WHERE id='$subcatid' AND user_id='$userid' AND cat_id='$catid'";             
                $add_edit_category  =   $mysql->dbQueryRowsAffected($tbl_whatlite_subcategory, $form_data, $where);
                if($add_edit_category>0){
                    $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Subcategory Updated Successfully !",
                            "subcat_id"=>"$subcatid"
                            );
                }else{
                    $message = array(
                            "success" => "false",
                            "error" => "Data missing",
                            'status'=>"Subcategory not updated !"
                            );
                }
        }
        
        echo json_encode(array('result' => $message));  
}
// Delete sub category by user 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='delete_subcategory'){
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        
        //check if sub category name already exists and delete sub  category 
        $check=mysql_num_rows(mysql_query("SELECT id FROM $tbl_whatlite_subcategory where id='$subcatid' AND user_id='$userid' AND cat_id='$catid'"));   
            $sql=mysql_query("DELETE from $tbl_whatlite_subcategory where id='$subcatid' AND user_id='$userid' AND cat_id='$catid'");       
            if($check>0){
            $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Subcategory Deleted Successfully"
                            );
            }else{  
                $message = array(
                            "success" => "true",
                            "error" => "Subcategory not deleted!",
                            'status'=>"Subcategory not deleted!"
                            );
                
            }
                
        echo json_encode(array('result' => $message));  
}


// add edit section by user 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='add_edit_section'){
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        $sectionid=$json->section_id;
        $section=$json->section;
        
                
        if($sectionid=='0'){
            
            $sql=mysql_query("SELECT id from $tbl_whatlite_section where section='$section' AND user_id='$userid' and cat_id='$catid' and subcat_id='$subcatid'");
        $check=mysql_num_rows($sql);
            if($check > 0){
            $message = array(
                            "success" => "false",
                            "error" => "Section name already exists",
                            'status'=>"Section not Inserted Successfully!"
                            );
            }else{  
                $form_data= array(
                    'cat_id'        =>$catid,
                    'subcat_id'     =>$subcatid,
                    'section'       =>ucfirst($section),
                    'status'        =>0,
                    'user_id'       =>$userid                           
                     );
                $where="";           
                $add_edit_category  =   $mysql->dbQueryRowsAffected($tbl_whatlite_section, $form_data, $where);
                $section_id=mysql_insert_id();
                if($add_edit_category>0){
                    $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Section Inserted Successfully !",
                            "section_id"=>"$section_id"
                            );
                }else{
                    $message = array(
                            "success" => "false",
                            "error" => "Data missing",
                            'status'=>"Section not inserted !"
                            );
                }
            }
        }else{
                $form_data= array(                  
                    'section'   =>ucfirst($section),                                        
                     );
                $where="WHERE id='$sectionid' AND user_id='$userid' AND cat_id='$catid' AND subcat_id='$subcatid'";          
                $add_edit_category  =   $mysql->dbQueryRowsAffected($tbl_whatlite_section, $form_data, $where);
                if($add_edit_category>0){
                    $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Section Updated Successfully !",
                            "section_id"=>"$sectionid"
                            );
                }else{
                    $message = array(
                            "success" => "false",
                            "error" => "Data missing",
                            'status'=>"Section not updated !"
                            );
                }
        }
        
        echo json_encode(array('result' => $message));  
}
// Delete section by user 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='delete_section'){
        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        $sectionid=$json->section_id;
        
        //check if sub category name already exists and delete sub  category 
        $check=mysql_num_rows(mysql_query("SELECT id FROM $tbl_whatlite_section where id='$sectionid' AND user_id='$userid' AND cat_id='$catid' AND subcat_id='$subcatid'"));     
            $sql=mysql_query("DELETE from $tbl_whatlite_section where id='$sectionid' AND user_id='$userid' AND cat_id='$catid' AND subcat_id='$subcatid'");        
            if($check>0){
            $message = array(
                            "success" => "true",
                            "error" => "null",
                            'status'=>"Section Deleted Successfully"
                            );
            }else{  
                $message = array(
                            "success" => "true",
                            "error" => "Section not deleted !",
                            'status'=>"Section not deleted !"
                            );
                
            }
                
        echo json_encode(array('result' => $message));  
}



//category and sub category for my catalog
if(isset($_REQUEST['method']) && $_REQUEST['method']=='cat_subcat_mycatalog'){

        $json=json_decode(file_get_contents("php://input"));

        $userid=$json->user_id;
        $category1=mysql_query("SELECT * FROM  $tbl_whatlite_category WHERE (user_id='$userid' OR user_id='0') AND status='0'");
        //$object=mysql_query($select);
        if(mysql_num_rows($category1)){
            
            while($categoryfetch=mysql_fetch_object($category1)){
                $subcategory1=mysql_query("SELECT * FROM $tbl_whatlite_subcategory   WHERE cat_id='$categoryfetch->id' AND (user_id='$userid' OR user_id='0') AND status='0'");
                    while($subcategoryfetch=mysql_fetch_object($subcategory1)){
                        if($subcategoryfetch->user_id=='0'){$flag='yes';}else{$flag='no';}
                        $subcategory[]=array(
                        "subcategory_id"=>$subcategoryfetch->id, 
                        "subcategory"=>(stripcslashes($subcategoryfetch->subcategory)),
                        "isadmin"=>"$flag"
                        );
                    }
                    if($categoryfetch->user_id=='0'){$flag1='yes';}else{$flag1='no';}
                    $category[]=array(
                        "cat_id"=>$categoryfetch->id,
                        "category"=>(stripcslashes($categoryfetch->category)),
                        "isadmin"=>"$flag1",
                        'subcategory'=>$subcategory
                       
                    );
                    unset($subcategory);
                
            }
            $message = array(
                        "success" => "true",
                        "error" => "null",
                        'data'=>$category
                        );
                        unset($category);
        }else{
            $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
    }

    echo json_encode(array('result' => $message)); 
        
}
//select section behalf of category and sub category for my catalog
if(isset($_REQUEST['method']) && $_REQUEST['method']=='cat_subcat_section_mycatalog'){

        $json=json_decode(file_get_contents("php://input"));
        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        
        $section1=mysql_query("SELECT * FROM $tbl_whatlite_section WHERE subcat_id='$subcatid' AND cat_id='$catid' AND (user_id='$userid' OR user_id='0') AND status='0'");

      //$object=mysql_query($select);
        if(mysql_num_rows($section1)){            
            
                    while($sectionfetch=mysql_fetch_object($section1)){
                        if($sectionfetch->user_id=='0'){$flag='yes';}else{$flag='no';}
                        $section[]=array(
                            "section_id"=>$sectionfetch->id,
                            "section"=>(stripcslashes($sectionfetch->section)),
                            "isadmin"=>"$flag"
                            );
                    }           
            $message = array(
                        "success" => "true",
                        "error" => "null",
                        'data'=>$section
                        );
                        unset($section);
        }else{
            $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
    }

    echo json_encode(array('result' => $message)); 
        
}

//product order now section and add into catelog as product selected from product list
if(isset($_REQUEST['method']) && $_REQUEST['method']=='add_mycatalog'){

    $json=json_decode(file_get_contents("php://input")); 
    
    $user_id=$json->user_id;
    $cat_id=$json->cat_id;
    $subcat_id=$json->subcat_id;
    $section_id=$json->section_id;
    
    $product_id=$json->product_id;
    
    $color_id=$json->color_id;
    $fittingtype_id=$json->fittingtype_id;
    
    $quantity=$json->quantity;
    $price=$json->price;
    
    $rdate = date('Y-m-d'); 
    $order_no=time();
    if($price!='' && $user_id!='' && $product_id!=''){
       
    $form_data_user= array(
            'order_no'          => $order_no,
            'user_id'           => $user_id,
            'cat_id'            => $cat_id,
            'subcat_id'         => $subcat_id,
            'section_id'        => $section_id,
            'product_id'        => $product_id,         
            'color_id'          => $color_id,
            'fittingtype_id'    => $fittingtype_id,
            'quantity'          => $quantity,
            'price'             => $price,
            'status'            => 0,
            'created'           => $rdate           
                 );
    //print_r($form_data_user);die;
     $checkcatalog="SELECT id FROM $tbl_product_catalog WHERE user_id='$user_id' AND cat_id='$cat_id' AND subcat_id='$subcat_id' AND section_id='$section_id' AND  product_id='$product_id' and color_id='$color_id' and fittingtype_id='$fittingtype_id'  AND product_type='0'";
    $resulstcatalogid=$mysql->dbLoadObjectlst($checkcatalog);
    if(mysql_num_rows(mysql_query($checkcatalog))>0){
    //update catalog if exists
        $where="WHERE id='".$resulstcatalogid[0]->id."'";
        $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_product_catalog, $form_data_user, $where);
    }else{
    //insert catalog if doesnot exists
        $where="";
        $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_product_catalog, $form_data_user, $where);

        //select points and insert into startlite table
        
        $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='1'"));
        $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$user_id','$getpoint->points','Library Plan Entry')");
        
    //push notification function for send push notification to user
        push_notification();
    }               
    if($insert_oreder){

        
        $message = array(
             "success" => "true",
             "error" => "null",
             "status"=>"product inserted into catalog !"
             );
     }else{
         $message = array(
             "success" => "false",
             "error" => "Something is wrong to insert data, Please try again later.",
              "status"=>"product not inserted into catalog"
                );
      }
    }else{
    $message = array(
                    "success" => "false",
                    "error" => "Data missing",
                    "status"=>"product not inserted into catalog"
                );
        }
        echo json_encode(array('result' => $message)); 
    }


//product order now section and add into catelog as users products for vendor suggestion
if(isset($_REQUEST['method']) && $_REQUEST['method']=='add_mycatalog_user_product'){

    $json=json_decode(file_get_contents("php://input")); 
    
   // print_r($json);
    $user_id=$json->user_id;
    $cat_id=$json->cat_id;
    $subcat_id=$json->subcat_id;
    $section_id=$json->section_id; 
    $product_id=$json->product_id;
    $color=$json->color_name;
    $fittingtype=$json->fittingtype_name;
     //print_r($json);
    
    // $brand_id=$json->brand_id;
    // $type_id=$json->type_id;
    // $model_id=$json->model_id;
    // $voltage_id=$json->voltage_id;
    // $wattage_id=$json->wattage_id;
    
    $brand_id=$json->brand_id;
    //check brand name exist or insert new
    $brandname=strtolower(trim($json->brand_name));
    $brandcheck=mysql_fetch_object(mysql_query("SELECT id FROM $tbl_brand WHERE brand='".$brandname."'"));
    if($brand_id=='' && $brandcheck->id==''){
        $form_data= array(
                    'brand'     =>$brandname,
                    'status'    =>0                         
                     );
        $where="";           
        $add_edit_brand =   $mysql->dbQueryRowsAffected($tbl_brand, $form_data, $where);
        $brand_id=mysql_insert_id();
    }else{
        $brand_id=$brandcheck->id;
    }
    //check type name exist or insert new
    $type_id=$json->type_id;
    $typename=strtolower(trim($json->type_name));
    $typecheck=mysql_fetch_object(mysql_query("SELECT id FROM $tbl_type WHERE type='".$typename."'"));
    if($type_id=='' && $typecheck->id==''){
        $form_data= array(
                    'type'      =>$typename,
                    'status'    =>0                         
                     );
        $where="";           
        $add_edit_type  =   $mysql->dbQueryRowsAffected($tbl_type, $form_data, $where);
        $type_id=mysql_insert_id();
    }else{
        $type_id=$typecheck->id;
    }
    //check model already exist or insert new
    $model_id=$json->model_id;
    $modelname=strtolower(trim($json->model_name));
    $modelcheck=mysql_fetch_object(mysql_query("SELECT id FROM $tbl_model WHERE model='".$modelname."'"));
    if($model_id=='' && $modelcheck->id==''){
        $form_data= array(
                    'model'     =>$modelname,
                    'brand_id'=>$brand_id,
                    'type_id'=>$type_id,
                    'status'    =>0                         
                     );
        $where="";           
        $add_edit_model =   $mysql->dbQueryRowsAffected($tbl_model, $form_data, $where);
        $model_id=mysql_insert_id();
    }else{
        $model_id=$modelcheck->id;
    }
    //check voltage already exist or insert new
    $voltage_id=$json->voltage_id;
    $voltagename=trim($json->voltage_name);
    $voltagecheck=mysql_fetch_object(mysql_query("SELECT id FROM $tbl_voltage WHERE voltage='".$voltagename."'"));
    if($voltage_id=='' && $voltagecheck->id==''){
        $form_data= array(
                    'voltage'       =>$voltagename,                    
                    'status'    =>0                         
                     );
        $where="";           
        $add_edit_voltage   =   $mysql->dbQueryRowsAffected($tbl_voltage, $form_data, $where);
        $voltage_id=mysql_insert_id();
    }else{
        $voltage_id=$voltagecheck->id;
    }
    //check wattage already exist or insert new
    $wattage_id=$json->wattage_id;
    $wattagename=trim($json->wattage_name);
    $wattagecheck=mysql_fetch_object(mysql_query("SELECT id FROM $tbl_wattage WHERE wattage='".$wattagename."' "));
    if($wattage_id=='' && $wattagecheck->id==''){
        $form_data= array(
                    'wattage'       =>$wattagename,
                    'status'    =>0                         
                     );
        $where="";           
        $add_edit_wattage   =   $mysql->dbQueryRowsAffected($tbl_wattage, $form_data, $where);
        $wattage_id=mysql_insert_id();
    }else{
        $wattage_id=$wattagecheck->id;
    }


    
    $rdate = date('Y-m-d'); 
    $order_no=time();
    
    //form data array product form
    $form_data= array(
        'user_id'           =>$user_id,         
        'brand_id'          =>$brand_id,
        'type_id'           =>$type_id,
        'model_id'          =>$model_id,
        'voltage_id'        =>$voltage_id,
        'wattage_id'        =>$wattage_id, 
        'color'             =>$color,
        'fittingtype'      =>$fittingtype,   
        'status'            =>0
    );
    //select product already exist or not
    //print_r($form_data_user);die;
    if($product_id!='')
    {
        //update catalog if exists
            $where="WHERE id='".$product_id ."'";
            $add_edit_sub_product   =   $mysql->dbQueryRowsAffected($tbl_users_products, $form_data, $where);
            $product_id=$product_id;
    }else{
         $checkcatalog1="SELECT id FROM $tbl_users_products WHERE user_id='$user_id' AND brand_id='$brand_id' AND type_id='$type_id' AND model_id='$model_id' AND  voltage_id='$voltage_id' AND wattage_id='$wattage_id'";
        $resulstcatalogid1=$mysql->dbLoadObjectlst($checkcatalog1);
        if(mysql_num_rows(mysql_query($checkcatalog1))>0){
        //update catalog if exists
            $where="WHERE id='".$resulstcatalogid1[0]->id."'";
            $add_edit_sub_product   =   $mysql->dbQueryRowsAffected($tbl_users_products, $form_data, $where);
            $product_id=$resulstcatalogid1[0]->id;
        }else{
        //insert catalog if doesnot exists
            $where="";
            $add_edit_sub_product   =   $mysql->dbQueryRowsAffected($tbl_users_products, $form_data, $where);
            $product_id=mysql_insert_id();
        }
    }

    //catalog form data for insert data
    if($user_id!='' && $product_id!=''){       
        $form_data_user= array(
                'order_no'          => $order_no,
                'user_id'           => $user_id,
                'cat_id'            => $cat_id,
                'subcat_id'         => $subcat_id,
                'section_id'        => $section_id,
                'product_id'        => $product_id,                         
                'status'            => 0,
                'product_type'      => '1',
                'created'           => $rdate           
                     );
        //print_r($form_data_user);die;
         $checkcatalog="SELECT id FROM $tbl_product_catalog WHERE user_id='$user_id' AND cat_id='$cat_id' AND subcat_id='$subcat_id' AND section_id='$section_id' AND  product_id='$product_id' and color_id='' and fittingtype_id='' AND product_type='1'";
        $resulstcatalogid=$mysql->dbLoadObjectlst($checkcatalog);
        if(mysql_num_rows(mysql_query($checkcatalog))>0){
        //update catalog if exists
            $where="WHERE id='".$resulstcatalogid[0]->id."'";
            $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_product_catalog, $form_data_user, $where);
            $catalog_id=$resulstcatalogid[0]->id;
        }else{
        //insert catalog if doesnot exists
            $where="";
            $insert_oreder  =   $mysql->dbQueryRowsAffected($tbl_product_catalog, $form_data_user, $where);
            $catalog_id=mysql_insert_id();
            //select points and insert into startlite table
            
            $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='1'"));
            $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$user_id','$getpoint->points','Library Plan Entry')");
            
        //push notification function for send push notification to user
            push_notification();
        }               
        if($insert_oreder){        
            $message = array(
                 "success" => "true",
                 "error" => "null",
                 "status"=>"product inserted into catalog !",
                 "catalog_id"=>$catalog_id
                 );
         }else{
             $message = array(
                 "success" => "false",
                 "error" => "Something is wrong to insert data, Please try again later.",
                  "status"=>"product not inserted into catalog"
                    );
          }
    }else{
        $message = array(
                    "success" => "false",
                    "error" => "Data missing",
                    "status"=>"product not inserted into catalog"
                );
    }
    
    echo json_encode(array('result' => $message)); 
}


//View My catelog NEw
if(isset($_REQUEST['method']) && $_REQUEST['method']=='viewmycatalog'){

        $json=json_decode(file_get_contents("php://input"));

        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        $sectionid=$json->section_id;
        
        $select="SELECT p.id as id,p.product_sku,p.stock,p.retail_sale,p.price as product_price,p.sale_price,catalog.id as catalogid,p.title as title,p.description as description,p.brand_id as brand_id,p.type_id as type_id,p.model_id as model_id,p.voltage_id as voltage_id,p.wattage_id as wattage_id,p.shipping_id,p.vendor_id,p.product_image,tp.firstname as soldby,tp.company_logo,d_option.delivery,d_option.dprice,d_option.description as delivery_desc,catalog.color_id as color_id,catalog.fittingtype_id as fittingtype_id,catalog.created as created,catalog.quantity as quantity,catalog.price as price,catalog.user_id as user_id,u.firstname as firstname,b.brand as brand,t.type as type,v.voltage as voltage,w.wattage as wattage,c.color as color,f.fittingtype as fittingtype,catalog.product_type FROM $tbl_product_catalog as catalog LEFT JOIN $tbl_products as p ON p.id=catalog.product_id LEFT JOIN $tbl_profile as u ON u.user_id=catalog.user_id LEFT JOIN $tbl_profile as tp ON p.vendor_id=tp.user_id LEFT JOIN $tbl_brand as b ON b.id=p.brand_id LEFT JOIN $tbl_type as t ON t.id=p.type_id LEFT JOIN $tbl_model as m ON m.id=p.model_id LEFT JOIN $tbl_voltage as v ON v.id=p.voltage_id LEFT JOIN $tbl_wattage as w ON w.id=p.wattage_id LEFT JOIN $tbl_color as c ON c.id=catalog.color_id LEFT JOIN $tbl_fittingtype as f ON f.id=catalog.fittingtype_id LEFT JOIN $tbl_delivery_option as d_option ON p.shipping_id=d_option.id WHERE catalog.subcat_id='$subcatid' AND catalog.cat_id='$catid' AND catalog.section_id='$sectionid' AND catalog.user_id='$userid'  AND catalog.product_type='0' AND p.title!=''";
        
        
         $object=mysql_query($select);
        

        $fittingval=NULL;
        $colorval=NULL;
        //$object=mysql_query($select);


        if(mysql_num_rows($object)){                         
         while($objectdata=mysql_fetch_object($object)){

            $img=mysql_query("select * from $tbl_catalog_images where catalog_id='$objectdata->catalogid'");
               while($images=mysql_fetch_object($img)){
                         $imagesforproduct=$path_catalogimage.$images->catalog_image;
                                   $allimages[]=array(
                                        "images"=>$imagesforproduct
                                    );
                     }
         
         //select COLORs of products
         //print_r($allimages);
          $colorexe=mysql_query("SELECT c.color,c.id FROM $tbl_color as c INNER JOIN $tbl_product_color as pc ON c.id=pc.color_id WHERE pc.product_id='$objectdata->id'");
                while($colorfetch=mysql_fetch_object($colorexe)){
                    $colorval[]=array(
                        'color_id'=>$colorfetch->id,
                        'color'   =>ucfirst((stripcslashes($colorfetch->color)))
                    );
                }
                
                //select fitting types of products
                
                $fittingexe=mysql_query("SELECT f.fittingtype,f.id FROM $tbl_fittingtype as f INNER JOIN $tbl_product_fittingtype as pf ON f.id=pf.fittingtype_id WHERE pf.product_id='$objectdata->id'");
                while($fittingfetch=mysql_fetch_object($fittingexe)){
                    $fittingval[]=array(
                        'fittingtype_id'    =>$fittingfetch->id,
                        'fittingtype'       =>ucfirst((stripcslashes($fittingfetch->fittingtype)))
                    );
                    
                }
         
            $img=mysql_query("select * from $tbl_product_images where product_id='$objectdata->id'");
               while($images=mysql_fetch_object($img)){
                         $imagesforproduct=$path_image.$images->product_image;
                                   $allimages[]=array(
                                        "images"=>$imagesforproduct
                                    );
                     }
               if($objectdata->status==0) { $status='In Progress';}else{ $status='Shipped';}
               $listed_d = date('d M Y', strtotime($objectdata->created));
               
               
               if($objectdata->company_logo!=''){
                    $company_logo=$site_url.'/admin/upload/vendorlogos/'.$objectdata->company_logo;
                }else{
                    $company_logo=$site_url.'/admin/upload/whatlitelogo.png';
                }
                

                $description[] = array( 
                        "catalog_id"=>$objectdata->catalogid,
                        "product_id"=>$objectdata->id,
                        "product_sku"=>$objectdata->product_sku,
                        "product_stock"=>$objectdata->stock,
                        'product_title'     =>(stripcslashes($objectdata->title)),
                        'product_description'=>(stripcslashes($objectdata->description)),
                        "date"=>$listed_d,
                        "brand_id"=>$objectdata->brand_id,
                        "brand"=>ucfirst((stripcslashes($objectdata->brand))), 
                        "type_id"=>$objectdata->type_id,
                        "type"=>ucfirst((stripcslashes($objectdata->type))), 
                        
                        "voltage_id"=>$objectdata->voltage_id,
                        "voltage"=>(stripcslashes($objectdata->voltage)),
                        "wattage_id"=>$objectdata->wattage_id,
                        "wattage"=>(stripcslashes($objectdata->wattage)),
                        'price_type'     =>$objectdata->retail_sale,
                        'product_price'     =>$objectdata->price,
                        'product_sale_price'     =>$objectdata->sale_price,
                        'company_logo'  => $company_logo,
                        "product_imagepath"=>$path_image,
                        "product_image"=>$objectdata->product_image,
                        "sold_by"=>(stripcslashes($objectdata->soldby)),
                        "shipping_id"=>$objectdata->shipping_id,
                        "shipping_name"=>(stripcslashes($objectdata->delivery)),
                        "shipping_price"=>$objectdata->dprice,
                        "shipping_description"=>(stripcslashes($objectdata->delivery_desc)),
                        "colors"=>$colorval,
                        "fittingtype"=>$fittingval,   
                        "product_type" =>'0',                    
                        "description"=>ucfirst((stripcslashes($objectdata->brand))).' '.ucfirst((stripcslashes($objectdata->fittingtype))).' '.ucfirst((stripcslashes($objectdata->type))).' '.(stripcslashes($objectdata->voltage)).' '.(stripcslashes($objectdata->wattage)).' '.ucfirst((stripcslashes($objectdata->color))).' '.ucfirst((stripcslashes($objectdata->model))),
                        "catalog_images"=> $allimages              
                        
                    );
                    unset($allimages);
                }
                          
           
        
        }
    $select="SELECT p.id as id,catalog.id as catalogid,p.brand_id as brand_id,p.type_id as type_id,p.model_id as model_id,p.voltage_id as voltage_id,p.wattage_id as wattage_id,p.color,p.fittingtype,catalog.created as created,catalog.user_id as user_id,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage FROM $tbl_product_catalog as catalog LEFT JOIN $tbl_users_products as p ON p.id=catalog.product_id  LEFT JOIN $tbl_brand as b ON b.id=p.brand_id LEFT JOIN $tbl_type as t ON t.id=p.type_id LEFT JOIN $tbl_model as m ON m.id=p.model_id LEFT JOIN $tbl_voltage as v ON v.id=p.voltage_id LEFT JOIN $tbl_wattage as w ON w.id=p.wattage_id  WHERE catalog.subcat_id='$subcatid' AND catalog.cat_id='$catid' AND catalog.section_id='$sectionid' AND catalog.user_id='$userid'  AND catalog.product_type='1'";

    $object=mysql_query($select);
        

        $fittingval=NULL;
        $colorval=NULL;
        //$object=mysql_query($select);
        if(mysql_num_rows($object)){                         
         while($objectdata=mysql_fetch_object($object)){         
            $img=mysql_query("select * from $tbl_catalog_images where catalog_id='$objectdata->catalogid'");
               while($images=mysql_fetch_object($img)){
                         $imagesforproduct=$path_catalogimage.$images->catalog_image;
                                   $allimages[]=array(
                                        "images"=>$imagesforproduct
                                    );
                     }
              
               $listed_d = date('d M Y', strtotime($objectdata->created));
                $description[] = array( 
                        "catalog_id"=>$objectdata->catalogid,
                        "product_id"=>$objectdata->id,
                        "product_sku"=>"",
                        "product_stock"=>"",
                        'product_title'     =>"",
                        'product_description'=>"",
                        "date"=>$listed_d,
                        "brand_id"=>$objectdata->brand_id,
                        "brand"=>ucfirst((stripcslashes($objectdata->brand))), 
                        "type_id"=>$objectdata->type_id,
                        "type"=>ucfirst((stripcslashes($objectdata->type))), 
                        
                        "voltage_id"=>$objectdata->voltage_id,
                        "voltage"=>(stripcslashes($objectdata->voltage)),
                        "wattage_id"=>$objectdata->wattage_id,
                        "wattage"=>(stripcslashes($objectdata->wattage)),
                        'price_type'     =>"",
                        'product_price'     =>"",
                        'product_sale_price'     =>"",
                        'company_logo'  => "",
                        "product_imagepath"=>"",
                        "product_image"=>"",
                        "sold_by"=>"",
                        "shipping_id"=>"",
                        "shipping_name"=>"",
                        "shipping_price"=>"",
                        "shipping_description"=>"",
                        "colors"=>$objectdata->color,
                        "fittingtype"=>$objectdata->fittingtype,   
                        "product_type" =>'1',                    
                        "description"=>ucfirst((stripcslashes($objectdata->brand))).' '.ucfirst((stripcslashes($objectdata->type))).' '.(stripcslashes($objectdata->voltage)).' '.(stripcslashes($objectdata->wattage)).' '.ucfirst((stripcslashes($objectdata->color))).' '.  ucfirst((stripcslashes($objectdata->fittingtype))),
                        "catalog_images"=> $allimages           
                        
                    );
                    unset($allimages);
                }                   
            
        
        }
        if(!empty($description)){

            $message = array(
                        "success" => "true",
                        "error" => "null",
                        'data'=>$description
                        );
                        unset($description);
        }
        else{
            $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
        }

    //echo json_encode(array('result' => $message)); 
    echo json_encode(array('result' => $message));
}



//delete catalog by user or their particular product which is added by them
if(isset($_REQUEST['method']) && $_REQUEST['method']=='delete_catalog'){

    $json=json_decode(file_get_contents("php://input"));

    $userid=$json->user_id;
    $catalog_id=$json->catalog_id;
        
    $where="WHERE user_id='$userid' AND id='$catalog_id'";
    $delete=$mysql->dbRowsDelete($tbl_product_catalog, $where);
    
    if($delete>0){
        $message = array(
                "success" => "true",
                "error" => "null",
                'status'=>"Catalog library plan deleted successfully"
                );  
    }else{
    $message = array(
                "success" => "false",
                "error" => "Catalog library plan not deleted",
                'status'=>"Catalog library plan not deleted"
                );
    }
    echo json_encode(array('result' => $message));

}

//View My catelog product list as added by user ownsef
if(isset($_REQUEST['method']) && $_REQUEST['method']=='viewmycatalog_user_product'){

        $json=json_decode(file_get_contents("php://input"));

        $userid=$json->user_id;
        $catid=$json->cat_id;
        $subcatid=$json->subcat_id;
        $sectionid=$json->section_id;
        
        $select="SELECT p.id as id,catalog.id as catalogid,p.brand_id as brand_id,p.type_id as type_id,p.model_id as model_id,p.voltage_id as voltage_id,p.wattage_id as wattage_id,catalog.created as created,catalog.user_id as user_id,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage FROM $tbl_product_catalog as catalog LEFT JOIN $tbl_users_products as p ON p.id=catalog.product_id  LEFT JOIN $tbl_brand as b ON b.id=p.brand_id LEFT JOIN $tbl_type as t ON t.id=p.type_id LEFT JOIN $tbl_model as m ON m.id=p.model_id LEFT JOIN $tbl_voltage as v ON v.id=p.voltage_id LEFT JOIN $tbl_wattage as w ON w.id=p.wattage_id  WHERE catalog.subcat_id='$subcatid' AND catalog.cat_id='$catid' AND catalog.section_id='$sectionid' AND catalog.user_id='$userid'  AND catalog.product_type='1'";
        
        
         $object=mysql_query($select);
        

        $fittingval=NULL;
        $colorval=NULL;
        //$object=mysql_query($select);
        if(mysql_num_rows($object)){                         
         while($objectdata=mysql_fetch_object($object)){         
            $img=mysql_query("select * from $tbl_catalog_images where catalog_id='$objectdata->catalogid'");
               while($images=mysql_fetch_object($img)){
                         $imagesforproduct=$path_catalogimage.$images->catalog_image;
                                   $allimages[]=array(
                                        "images"=>$imagesforproduct
                                    );
                     }
              
               $listed_d = date('d M Y', strtotime($objectdata->created));
                $description[] = array( 
                        "catalog_id"=>$objectdata->catalogid,
                        "product_id"=>$objectdata->id,
                      
                        "date"=>$listed_d,
                        "brand_id"=>$objectdata->brand_id,
                        "brand"=>ucfirst((stripcslashes($objectdata->brand))), 
                        "type_id"=>$objectdata->type_id,
                        "type"=>ucfirst((stripcslashes($objectdata->type))), 
                        "model_id"=>$objectdata->model_id,
                        "model"=>ucfirst((stripcslashes($objectdata->model))),   
                        "voltage_id"=>$objectdata->voltage_id,
                        "voltage"=>(stripcslashes($objectdata->voltage)),
                        "wattage_id"=>$objectdata->wattage_id,
                        "wattage"=>(stripcslashes($objectdata->wattage)),
                        "description"=>ucfirst((stripcslashes($objectdata->brand))).' '.ucfirst((stripcslashes($objectdata->type))).' '.(stripcslashes($objectdata->voltage)).' '.(stripcslashes($objectdata->wattage)).' '.ucfirst((stripcslashes($objectdata->model))) ,
                        "catalog_images"=> $allimages           
                        
                    );
                    unset($allimages);
                }                   
            $message = array(
                        "success" => "true",
                        "error" => "null",
                        'data'=>$description
                        );
                        unset($description);
        
        }else{
            $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
    }

    //echo json_encode(array('result' => $message)); 
    echo json_encode(array('result' => $message));
}

//delete catalog by user or their particular product which is added by them
if(isset($_REQUEST['method']) && $_REQUEST['method']=='delete_catalog_user_product'){

    $json=json_decode(file_get_contents("php://input"));

    $userid=$json->user_id;
    $catalog_id=$json->catalog_id;


    $select_fields  =   'catalog_image';        
    $where  =   "WHERE catalog_id ='".$catalog_id."'";
    $getDataimageDrtails =  $mysql->dbLoadObjectList($tbl_catalog_images,$select_fields,$where);
    $catalog_image = $getDataimageDrtails[0]->catalog_image;
    unlink('upload/catalogs_images/'.$catalog_image);
    //delete catalog images
    $where="WHERE catalog_id='$catalog_id'";
    $delete=$mysql->dbRowsDelete($tbl_catalog_images, $where);

    //delete catalog  
    $where="WHERE user_id='$userid' AND id='$catalog_id'";
    $delete=$mysql->dbRowsDelete($tbl_product_catalog, $where);
    
    if($delete>0){
        $message = array(
                "success" => "true",
                "error" => "null",
                'status'=>"Catalog library plan deleted successfully"
                );  
    }else{
    $message = array(
                "success" => "false",
                "error" => "Catalog library plan not deleted",
                'status'=>"Catalog library plan not deleted"
                );
    }
    echo json_encode(array('result' => $message));

}


//View all catalog product with category, subcategory and section at a time
if(isset($_REQUEST['method']) && $_REQUEST['method']=='viewallmycatalog'){

        $json=json_decode(file_get_contents("php://input"));

        $userid=$json->user_id;       
        
         $select="SELECT p.id as id,p.product_image as product_image,catalog.id as catalogid,p.title as title,p.description as description,p.brand_id as brand_id,p.type_id as type_id,p.model_id as model_id,p.voltage_id as voltage_id,p.wattage_id as wattage_id,catalog.color_id as color_id,catalog.fittingtype_id as fittingtype_id,catalog.created as created,catalog.quantity as quantity,catalog.price as price,catalog.user_id as user_id,u.firstname as firstname,b.brand as brand,t.type as type,m.model as model,v.voltage as voltage,w.wattage as wattage,c.color as color,f.fittingtype as fittingtype,cat.id as cat_id,cat.category as category,subcat.id as subcat_id,subcat.subcategory as subcategory, section.id as section_id,section.section as section FROM $tbl_product_catalog as catalog INNER JOIN $tbl_products as p ON p.id=catalog.product_id INNER JOIN $tbl_profile as u ON u.user_id=catalog.user_id INNER JOIN $tbl_brand as b ON b.id=p.brand_id INNER JOIN $tbl_type as t ON t.id=p.type_id INNER JOIN $tbl_model as m ON m.id=p.model_id INNER JOIN $tbl_voltage as v ON v.id=p.voltage_id INNER JOIN $tbl_wattage as w ON w.id=p.wattage_id INNER JOIN $tbl_color as c ON c.id=catalog.color_id INNER JOIN $tbl_fittingtype as f ON f.id=catalog.fittingtype_id INNER JOIN $tbl_whatlite_category as cat ON cat.id=catalog.cat_id INNER JOIN $tbl_whatlite_subcategory as subcat ON subcat.id=catalog.subcat_id INNER JOIN $tbl_whatlite_section as section ON section.id=catalog.section_id  WHERE catalog.user_id='$userid'";
         $object=mysql_query($select);
        

        
        //$object=mysql_query($select);
        if(mysql_num_rows($object)){                         
         while($objectdata=mysql_fetch_object($object)){            
               if($objectdata->status==0) { $status='In Progress';}else{ $status='Shipped';}
               $listed_d = date('d M Y', strtotime($objectdata->created));
                $description[] = array( 
                        "catalog_id"=>$objectdata->catalogid,
                        "product_id"=>$objectdata->id,
                        'product_title'     =>(stripcslashes($objectdata->title)),
                        'product_description'=>(stripcslashes($objectdata->description)),
                        "date"=>$listed_d,
                        "brand_id"=>$objectdata->brand_id,
                        "brand"=>ucfirst((stripcslashes($objectdata->brand))), 
                        "type_id"=>$objectdata->type_id,
                        "type"=>ucfirst((stripcslashes($objectdata->type))), 
                        "model_id"=>$objectdata->model_id,
                        "model"=>ucfirst((stripcslashes($objectdata->model))),   
                        "voltage_id"=>$objectdata->voltage_id,
                        "voltage"=>(stripcslashes($objectdata->voltage)),
                        "wattage_id"=>(stripcslashes($objectdata->wattage_id)),
                        "wattage"=>(stripcslashes($objectdata->wattage)),
                        "color_id"=>$objectdata->color_id,
                        "color"=>ucfirst((stripcslashes($objectdata->color))),
                        "fittingtype_id"=>$objectdata->fittingtype_id,
                        "fittingtype"=>ucfirst((stripcslashes($objectdata->fittingtype))),
                        "cat_id"=>$objectdata->cat_id,
                        "cat_name"=>ucfirst((stripcslashes($objectdata->category))),
                        "subcat_id"=>$objectdata->subcat_id,
                        "subcat_name"=>ucfirst((stripcslashes($objectdata->subcategory))),
                        "section_id"=>$objectdata->section_id,
                        "section_name"=>ucfirst((stripcslashes($objectdata->section))),
                        "quantity"=>ucfirst($objectdata->quantity),  
                        "description"=>ucfirst((stripcslashes($objectdata->brand))).' '.ucfirst((stripcslashes($objectdata->fittingtype))).' '.ucfirst((stripcslashes($objectdata->type))).' '.(stripcslashes($objectdata->voltage)).' '.(stripcslashes($objectdata->wattage)).' '.ucfirst((stripcslashes($objectdata->color))).' '.ucfirst((stripcslashes($objectdata->model))),
                        "amount"=>$objectdata->price,
                        'product_imagepath' =>$path_image,
                        "product_image"=>$objectdata->product_image
                    );
                    
                            }                   
            $message = array(
                        "success" => "true",
                        "error" => "null",
                        'data'=>$description
                        );
                        unset($description);
        
        }else{
            $message = array(
                        "success" => "false",
                        "error" => "Data record not found",
                        "status"=>"not any record"
                    );
    }

    echo json_encode(array('result' => $message)); 
}





//common web service for brand, type, voltage, wattage, color and fitting type
//Brand LIST for the Products
if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'specifications')) {

        $brandsql = "SELECT b.brand,b.id FROM $tbl_brand as b INNER JOIN $tbl_products as p ON p.brand_id=b.id  WHERE b.status='0' GROUP BY b.brand ORDER BY b.id ASC";
        
                $resultbrand = mysql_query($brandsql);
                while ($rowbrand = mysql_fetch_object($resultbrand)) {
                    $brand[] = array(
                        "brand_id" => $rowbrand->id,
                        "brand_name" => ucfirst((stripcslashes($rowbrand->brand)))
                        
                    );
                } 
        $typesql = "SELECT t.type,t.id,t.type_image FROM $tbl_type as t INNER JOIN $tbl_products as p ON p.type_id=t.id WHERE t.status='0'  GROUP BY t.type ORDER BY t.id ASC";
            $resulttype = mysql_query($typesql);
            while ($rowtype = mysql_fetch_object($resulttype)) {
                $type[] = array(
                    "type_id" => $rowtype->id,
                    "type_name" => ucfirst((stripcslashes($rowtype->type))),
                    'type_imagepath' =>$typepath_image,
                    "type_image" => $rowtype->type_image
                    
                );
            } 
        
        $query1 = "SELECT v.voltage,v.id FROM $tbl_voltage as v INNER JOIN $tbl_products as p ON p.voltage_id=v.id  WHERE v.status='0' GROUP BY v.voltage ORDER BY v.id ASC";
        $result1 = mysql_query($query1);
        while ($row1 = mysql_fetch_object($result1)) {
            $voltage[] = array(
                "voltage_id" => $row1->id,
                "voltage_name" => (stripcslashes($row1->voltage))
                
            );
        }
        $query2 = "SELECT w.wattage,w.id FROM $tbl_wattage as w INNER JOIN $tbl_products as p ON p.wattage_id=w.id WHERE w.status='0'  GROUP BY w.wattage ORDER BY w.id ASC";
        $result2 = mysql_query($query2);
        while ($row2 = mysql_fetch_object($result2)) {
            $wattage[] = array(
                "wattage_id" => $row2->id,
                "wattage_name" => (stripcslashes($row2->wattage))
                
            );
        }
        $query3 = "SELECT color,id FROM $tbl_color WHERE status='0' GROUP BY color ORDER BY id ASC";
        $result3 = mysql_query($query3);
        while ($row3 = mysql_fetch_object($result3)) {
            $color[] = array(
                "color_id" => $row3->id,
                "color_name" => ucfirst((stripcslashes($row3->color)))
                
            );
        }  
        $query4 = "SELECT f.fittingtype,f.id FROM $tbl_fittingtype as f  WHERE f.status='0' GROUP BY f.fittingtype ORDER BY f.id ASC";
        $result4 = mysql_query($query4);
        while ($row4 = mysql_fetch_object($result4)) {
            $fittingtype[] = array(
                "fittingtype_id" => $row4->id,
                "fittingtype_name" => ucfirst((stripcslashes($row4->fittingtype)))
                
            );
        }
        $message = array(
            "brand"=>$brand,
            "type"=>$type,
            "voltages"=>$voltage,
            "wattages"=>$wattage,
            "colors"=>$color,
            "fittingtypes"=>$fittingtype
            
        );
        $message['dealimagespath']=$path_dealimages;
        $message['dealimages']=array('1.png','2.png','3.png','4.png','5.png');
        unset($brand);
        unset($type);
        unset($voltage);
        unset($wattage);
        unset($color);
        unset($fittingtype);         
    
   

    echo json_encode(array('result' => $message)); 
}

//all products list with fittingtypes and colors 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='productlist'){

        $json=json_decode(file_get_contents("php://input"));
        $records_start_from=$json->records_start_from;
        $no_of_records_show=$json->no_of_records_show;

        $brandarray=array();
        $typearray=array();
        $voltagearray=array();
        $wattagearray=array();
        $colorarray=array();
        $fittingtypearray=array();

        foreach ($json->brand as $jsonvalue) {            
            $brandarray[]       = "'".$jsonvalue->brand_id."'";
        }
        foreach ($json->type as $jsonvalue) {  
            $typearray[]        = "'".$jsonvalue->type_id."'";
        }
        foreach ($json->voltage as $jsonvalue) {  
            $voltagearray[]     = "'".$jsonvalue->voltage_id."'";
        }
        foreach ($json->wattage as $jsonvalue) {  
            $wattagearray[]     = "'".$jsonvalue->wattage_id."'";
        }
        foreach ($json->color as $jsonvalue) {  
            $colorarray[]       = "'".$jsonvalue->color_id."'";
        }
        foreach ($json->fittingtype as $jsonvalue) {  
            $fittingtypearray[] = "'".$jsonvalue->fittingtype_id."'";
        }

        $brand      = implode(' OR p.brand_id = ',$brandarray);
        $type       = implode(' OR p.type_id = ',$typearray);
        $voltage    = implode(' OR p.voltage_id = ',$voltagearray);
        $wattage    = implode(' OR p.wattage_id = ',$wattagearray);
        $color      = implode(' OR col.color_id = ',$colorarray);
        $fittingtype= implode(' OR ft.fittingtype_id = ',$fittingtypearray);

        $prd_id=$json->prd_id;
        if($prd_id==''){
            $varwhere='';
        }else{
           $varwhere=" AND p.id='$prd_id' "; 
        }
        
        //select products query
        $select="SELECT p.id as id,p.vendor_id as vendor_id,u.firstname as fullname,u.company_logo,u.paypal_id,p.product_sku as sku,p.stock as stock,p.brand_id,p.type_id,p.voltage_id,p.wattage_id, p.status as status,p.retail_sale,p.price as price,p.sale_price,p.product_image as product_image,p.title as title, p.description as description,shp.id as shpid,shp.delivery as delivery,shp.dprice as dprice,shp.description as shippingdesc, b.brand as brand, p.brand_id as brandid, t.type as type,v.voltage as voltage,w.wattage as wattage FROM $tbl_products as p INNER JOIN $tbl_brand as b ON b.id=p.brand_id INNER JOIN $tbl_type as t ON t.id=p.type_id  INNER JOIN $tbl_voltage as v ON v.id=p.voltage_id INNER JOIN $tbl_wattage as w ON w.id=p.wattage_id INNER JOIN $tbl_product_color as col ON col.product_id=p.id INNER JOIN $tbl_product_fittingtype as ft ON ft.product_id=p.id INNER JOIN $tbl_delivery_option as shp ON shp.id=p.shipping_id INNER JOIN $tbl_profile as u ON u.user_id=p.vendor_id where p.title!='' and p.status='0' and p.delete_action='0' $varwhere ";

        if(sizeof($brandarray)>0){
         $select .= "AND (p.brand_id=".$brand. ") ";
        }
        if(sizeof($typearray)>0){
         $select .= "AND (p.type_id=".$type. ") ";
        }
        if(sizeof($voltagearray)>0){
         $select .= "AND (p.voltage_id=".$voltage. ") ";
        }
        if(sizeof($wattagearray)>0){
         $select .= "AND (p.wattage_id=".$wattage. ") ";
        }
        if(sizeof($colorarray)>0){
         $select .= "AND (col.color_id=".$color. ") ";
        }
        if(sizeof($fittingtypearray)>0){
         $select .= "AND (ft.fittingtype_id=".$fittingtype. ") ";
        }

        if($json->product_search!=''){
            $values=$json->product_search;
            $select .= "AND (p.title LIKE '%$values%' OR p.description LIKE '%$values%') ";
        }

        $select .=" GROUP BY p.id ORDER BY p.id DESC LIMIT $records_start_from,$no_of_records_show";
       
        $object=mysql_query($select); 
        if(mysql_num_rows($object)>0){
            while($objectdata=mysql_fetch_object($object)) {
                //select colors for product
                
                $colorexe=mysql_query("SELECT c.color,c.id FROM $tbl_color as c INNER JOIN $tbl_product_color as pc ON c.id=pc.color_id WHERE pc.product_id='$objectdata->id'");
                while($colorfetch=mysql_fetch_object($colorexe)){
                    $colorval[]=array(
                        'color_id'=>$colorfetch->id,
                        'color'   =>ucfirst($colorfetch->color)
                    );
                }
                
                //select fitting types of products
                
                $fittingexe=mysql_query("SELECT f.fittingtype,f.id FROM $tbl_fittingtype as f INNER JOIN $tbl_product_fittingtype as pf ON f.id=pf.fittingtype_id WHERE pf.product_id='$objectdata->id'");
                while($fittingfetch=mysql_fetch_object($fittingexe)){
                    $fittingval[]=array(
                        'fittingtype_id'    =>$fittingfetch->id,
                        'fittingtype'       =>ucfirst($fittingfetch->fittingtype)
                    );
                    
                }
                if($objectdata->product_image!=''){
                    $img=stripslashes($objectdata->product_image);
                }else{
                    $img='defultproduct.jpg';
                }
                if($objectdata->dprice!=''){
                    $shippprice=$objectdata->dprice;
                }else{
                    $shippprice=0;
                }
                
                if($objectdata->company_logo!=''){
                    $company_logo=$site_url.'/admin/upload/vendorlogos/'.$objectdata->company_logo;
                }else{
                    $company_logo=$site_url.'/admin/upload/whatlitelogo.png';
                }
                if(!empty($colorval) && !empty($fittingval)){
                   $products[]=array(
                    'product_id'=>$objectdata->id,
                    'product_sku'   =>$objectdata->sku,
                    'product_stock'   =>$objectdata->stock,
                    'product_title'     =>ucfirst(stripslashes($objectdata->title)),
                    'product_description'=>ucfirst(stripslashes($objectdata->description)),
                    'product_brand_id'=>$objectdata->brand_id,
                    'product_brand'     =>ucfirst(stripslashes($objectdata->brand)),
                    'product_type_id'=>$objectdata->type_id,
                    'product_type'      =>ucfirst(stripslashes($objectdata->type)),
                    
                    'product_voltage_id'=>$objectdata->voltage_id,
                    'product_voltage'   =>stripslashes($objectdata->voltage),
                    'product_wattage_id'=>$objectdata->wattage_id,
                    'product_wattage'   =>stripslashes($objectdata->wattage),
                    'price_type'     =>$objectdata->retail_sale,
                    'product_price'     =>$objectdata->price,
                    'product_sale_price'     =>$objectdata->sale_price,
                    'product_imagepath' =>$path_image,
                    'product_image'     =>$img,
                    'vendor_id'         =>$objectdata->vendor_id,
                    'paypal_id'         =>$objectdata->paypal_id,
                    'sold_by'       =>$objectdata->fullname,
                    'company_logo'  => $company_logo,
                    'shipping_id'       =>$objectdata->shpid,
                    'shipping_name'     =>$objectdata->delivery,
                    'shipping_price'     =>$shippprice,
                    'shipping_description'     =>$objectdata->shippingdesc,
                    'colors'        =>$colorval,
                    'fittingtype'   =>$fittingval
                );
                unset($fittingval);
                unset($colorval); 
             }
                
                
            }

            $sqlmode="SELECT gst FROM wl_general_setting WHERE id='1'";
            $resulstmode=$mysql->dbLoadObjectlst($sqlmode);
            $gst=$resulstmode[0]->gst;


            $message = array(
                    "success"       => "true",
                    "error"         => "null",
                    "gst"=>$gst,
                    'products'      => $products
                );
                unset($products);
                
        
        }else{
            $message = array(
                    "success"   => "false",
                    "error"     => "Data record not found",                 
                   );
    }
    echo json_encode(array('result' => $message)); 
}


//silmilar product web service 
if(isset($_REQUEST['method']) && $_REQUEST['method']=='similar_products'){

        $json=json_decode(file_get_contents("php://input"));
        $records_start_from=$json->records_start_from;
        $no_of_records_show=$json->no_of_records_show;

        
        $brand      = $json->brand_id;
        $type       = $json->type_id;
        $model      = $json->model_id;
        $voltage    = $json->voltage_id;
        $wattage    = $json->wattage_id;
   

        $product_id=$json->product_id;
        
        if($product_id==''){
            $varwhere='';
        }else{
           $varwhere=" AND p.id!='$product_id' "; 
        }
        
        //select products query
        $select="SELECT p.id as id,p.vendor_id as vendor_id,u.firstname as fullname,u.company_logo,p.product_sku as sku,p.stock as stock,p.brand_id,p.type_id,p.voltage_id,p.wattage_id, p.status as status,p.retail_sale,p.price as price,p.sale_price,p.product_image as product_image,p.title as title, p.description as description,shp.id as shpid,shp.delivery as delivery,shp.dprice as dprice,shp.description as shippingdesc, b.brand as brand, p.brand_id as brandid, t.type as type,v.voltage as voltage,w.wattage as wattage FROM $tbl_products as p INNER JOIN $tbl_brand as b ON b.id=p.brand_id INNER JOIN $tbl_type as t ON t.id=p.type_id  INNER JOIN $tbl_voltage as v ON v.id=p.voltage_id INNER JOIN $tbl_wattage as w ON w.id=p.wattage_id INNER JOIN $tbl_product_color as col ON col.product_id=p.id INNER JOIN $tbl_product_fittingtype as ft ON ft.product_id=p.id INNER JOIN $tbl_delivery_option as shp ON shp.id=p.shipping_id INNER JOIN $tbl_profile as u ON u.user_id=p.vendor_id where p.title!='' and p.status='0' and p.delete_action='0' $varwhere ";

        if(!empty($brand) && $brand!='null'){
         $select .= "AND (p.brand_id=".$brand. ") ";
        }
        if(!empty($type) && $type!='null'){
         $select .= " AND (p.type_id=".$type. ") ";
        }
        
        if(!empty($voltage) && $voltage!='null'){
         $select .= " AND (p.voltage_id=".$voltage. ") ";
        }
        if(!empty($wattage) && $wattage!='null'){
         $select .= " AND (p.wattage_id=".$wattage. ") ";
        }
        

        $select .=" GROUP BY p.id ORDER BY p.id DESC LIMIT $records_start_from,$no_of_records_show";
       
        $object=mysql_query($select); 
        if(mysql_num_rows($object)>0){
            while($objectdata=mysql_fetch_object($object)) {
                //select colors for product
                
                $colorexe=mysql_query("SELECT c.color,c.id FROM $tbl_color as c INNER JOIN $tbl_product_color as pc ON c.id=pc.color_id WHERE pc.product_id='$objectdata->id'");
                while($colorfetch=mysql_fetch_object($colorexe)){
                    $colorval[]=array(
                        'color_id'=>$colorfetch->id,
                        'color'   =>ucfirst($colorfetch->color)
                    );
                }
                
                //select fitting types of products
                
                $fittingexe=mysql_query("SELECT f.fittingtype,f.id FROM $tbl_fittingtype as f INNER JOIN $tbl_product_fittingtype as pf ON f.id=pf.fittingtype_id WHERE pf.product_id='$objectdata->id'");
                while($fittingfetch=mysql_fetch_object($fittingexe)){
                    $fittingval[]=array(
                        'fittingtype_id'    =>$fittingfetch->id,
                        'fittingtype'       =>ucfirst($fittingfetch->fittingtype)
                    );
                    
                }
                if($objectdata->product_image!=''){
                    $img=stripslashes($objectdata->product_image);
                }else{
                    $img='defultproduct.jpg';
                }
                

                if($objectdata->company_logo!=''){
                    $company_logo=$site_url.'/admin/upload/vendorlogos/'.$objectdata->company_logo;
                }else{
                    $company_logo=$site_url.'/admin/upload/whatlitelogo.png';
                }

                if(!empty($colorval) && !empty($fittingval)){
                    $products[]=array(
                        'product_id'=>$objectdata->id,
                        'product_sku'   =>$objectdata->sku,
                        'product_stock'   =>$objectdata->stock,
                        'product_title'     =>ucfirst(stripslashes($objectdata->title)),
                        'product_description'=>ucfirst(stripslashes($objectdata->description)),
                        'product_brand_id'=>$objectdata->brand_id,
                        'product_brand'     =>ucfirst(stripslashes($objectdata->brand)),
                        'product_type_id'=>$objectdata->type_id,
                        'product_type'      =>ucfirst(stripslashes($objectdata->type)),
                        
                        'product_voltage_id'=>$objectdata->voltage_id,
                        'product_voltage'   =>stripslashes($objectdata->voltage),
                        'product_wattage_id'=>$objectdata->wattage_id,
                        'product_wattage'   =>stripslashes($objectdata->wattage),
                        'price_type'     =>$objectdata->retail_sale,
                        'product_price'     =>$objectdata->price,
                        'product_sale_price'     =>$objectdata->sale_price,
                        'company_logo'  => $company_logo,
                        'product_imagepath' =>$path_image,
                        'product_image'     =>$img,
                        'sold_by'       =>$objectdata->fullname,
                        'shipping_id'       =>$objectdata->shpid,
                        'shipping_name'     =>$objectdata->delivery,
                        'shipping_price'     =>$objectdata->dprice,
                        'shipping_description'     =>$objectdata->shippingdesc,
                        'colors'        =>$colorval,
                        'fittingtype'   =>$fittingval
                    );
                    unset($fittingval);
                    unset($colorval);
                }
            }
            $message = array(
                    "success"       => "true",
                    "error"         => "null",
                    'products'      => $products
                );
                unset($products);
                
        
        }else{
            $message = array(
                    "success"   => "false",
                    "error"     => "Data record not found",                 
                   );
    }
    echo json_encode(array('result' => $message)); 
}

//Energy Provider list web service
if(isset($_REQUEST['method']) && $_REQUEST['method']=='energy_provider_list'){    

   $select="SELECT * FROM $tbl_energy_porvider_list WHERE status='0'";
    $exe=mysql_query($select);

    if(mysql_num_rows($exe)>0){
        while($fetchdata=mysql_fetch_object($exe)){
            $provider[]= array(
                'provider_id' => $fetchdata->id,
                'provider_name' => $fetchdata->provider_name
                 );
        }
        $message = array(
                "success" => "true",
                "error" => "null",
                "providers" => $provider
            );
        unset($provider);
    }else{
        $message = array(
                "success" => "false",
                "error" => "Record Not Found"                
            );
    }
    echo json_encode(array('result' => $message));
}


//Energy Provider informaion save into database web service
if(isset($_REQUEST['method']) && $_REQUEST['method']=='energy_provider'){
    $json               = json_decode(file_get_contents("php://input"));
    $userid             = $json->user_id;
    $energy_provider_id = $json->energy_provider_id;
    $account_no         = $json->account_no;
    $biller_code        = $json->biller_code;
    $reference_no       = $json->reference_no;

    $formdata= array(
                'energy_provider'  => $energy_provider_id,                 
                'account_no'       => $account_no,
                'biller_code'      => $biller_code,
                'reference_no'     => $reference_no,
                'user_id'          => $userid                             
        );

    $select="SELECT * FROM $tbl_energy_porvider WHERE user_id='$userid'";
    $exe=mysql_query($select);
    if(mysql_num_rows($exe)>0){
         $where="WHERE user_id='$userid'"; 
    }else{
        $where="";
        $getpoint=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='5'"));
        $startlitepointsender=mysql_query("insert into $tbl_starlite_point (user_id,points,creadited_through) values ('$userid','$getpoint->points','Energy Provider nomination')");
        
    //push notification function for send push notification to user
        push_notification();
    }       
    $insertdata   =   $mysql->dbQueryRowsAffected($tbl_energy_porvider, $formdata, $where);
    if($insertdata>0){
        $message = array(
                "success" => "true",
                "error" => "null",
                "status" => "Energy provider information saved !"
            );
    }else{
        $message = array(
                "success" => "false",
                "error" => "Something is wrong to insert data, Please try again later.",
                "status" => "Energy provider information not saved !"
            );
    }
    echo json_encode(array('result' => $message));
}

//Energy Provider information web service
if(isset($_REQUEST['method']) && $_REQUEST['method']=='energy_provider_information'){    
    $json               = json_decode(file_get_contents("php://input"));
    $userid             = $json->user_id;

    $select="SELECT * FROM $tbl_energy_porvider as epi INNER JOIN $tbl_energy_porvider_list as epl ON epl.id=epi.energy_provider WHERE user_id='$userid'";
    $exe=mysql_query($select);

    if(mysql_num_rows($exe)>0){
       $fetchdata=mysql_fetch_object($exe);
            
        $message = array(
            "success" => "true",
            "error" => "null",
            'energy_provider_id'  => $fetchdata->id,
            'energy_provider'  => $fetchdata->provider_name,                 
            'account_no'       => $fetchdata->account_no,
            'biller_code'      => $fetchdata->biller_code,
            'reference_no'     => $fetchdata->reference_no
            );
    }else{
        $message = array(
                "success" => "false",
                "error" => "Record Not Found"                
            );
    }
    echo json_encode(array('result' => $message));
}

// refer a friend and get starlite points
if(isset($_REQUEST['method']) && $_REQUEST['method']=='refer_friend'){
    $json              = json_decode(file_get_contents("php://input"));
    $userid            = $json->user_id;
    $mobile_no         = $json->mobile_no;
    $verify_code       = $json->verify_code;

    if($mobile_no!='' && $userid!=''){
         
        $sql=mysql_query("select * from $refertomobile where mobile_no='$mobile_no'");
        $getdata=mysql_fetch_object($sql);
        $existid=$getdata->id;
        $downloadflag=$getdata->download_flag;

        $check=mysql_num_rows($sql);
         if($check>0 && $downloadflag==1 ){

            $message = array(
                    "success" => "false",
                    "error" => "Mobile number already exists",
                    "status" => "Invitation not sent !",
                );
        }elseif($check>0 && $downloadflag==0 ){
                $sql="UPDATE $refertomobile SET user_id='$userid',verify_code='$verify_code' WHERE id='$existid' AND mobile_no='$mobile_no'";
                $exe=mysql_query($sql);
                if($exe>0){
                    $message = array(
                        "success" => "true",
                        "error" => "null",
                        "status" => "Invitation sent successfully !",
                );
                }else{
                    $message = array(
                        "success" => "false",
                        "error" => "Something is wrong",
                        "status" => "Invitation not sent !",
                    );
                }

            }
            else{                
               $sql="INSERT INTO $refertomobile (id,user_id,mobile_no,verify_code) VALUES(NULL,'$userid','$mobile_no','$verify_code')";
                $exe=mysql_query($sql);
                if($exe>0){
                    $message = array(
                        "success" => "true",
                        "error" => "null",
                        "status" => "Invitation sent successfully !",
                );
                }else{
                    $message = array(
                        "success" => "false",
                        "error" => "Something is wrong",
                        "status" => "Invitation not sent !",
                    );
                }
            } 
        
    }else{
        $message = array(
                "success" => "false",
                "error" => "Data is missing",
                "status" => "Invitation not sent !",
            );
    }
    echo json_encode(array('result' => $message));
       
}

// refer a friend and get starlite points
if(isset($_REQUEST['method']) && $_REQUEST['method']=='refer_friend1'){
    $json              = json_decode(file_get_contents("php://input"));
    $userid            = $json->user_id;
    $friend_mobile     = $json->friend_mobile;

    if($friend_mobile!=''){
         $message   = "Download APP and Get starlite point and win a lottery";
         $message   .= "<a href='".$site_url."application.php?userid=".$userid ."'>Click here to Download</a>";
         //$mail123   = sentmail($friend_email, $subject, $body);
         if(mysql_num_rows(mysql_query("select * from $refertofriends where mobile_no='$friend_mobile'"))==0){
             $sql="INSERT INTO $refertofriends (id,senderid,mobile_no) VALUES(NULL,'$userid','$friend_mobile')";
             $insert=mysql_query($sql);

             if($insert>0){
                $message = array(
                    "success" => "true",
                    "error" => "null",
                    "status" => "Invitation sent successfully !",
                );
            }else{
                $message = array(
                    "success" => "false",
                    "error" => "SMS not sent, Please check mobile number",
                    "status" => "Invitation not sent !",
                );
            }
        }else{
        $message = array(
                "success" => "false",
                "error" => "Mobile number already exists",
                "status" => "Invitation not sent !",
            );
        }
    }else{
        $message = array(
                "success" => "false",
                "error" => "Please provide mobile number",
                "status" => "Invitation not sent !",
            );
    }
    echo json_encode(array('result' => $message));
       
}

// download app with requested link and get code before registration
if(isset($_REQUEST['method']) && $_REQUEST['method']=='get_code'){
    $json                   = json_decode(file_get_contents("php://input"));
    $mobile_no               = $json->mobile_no;  
    $getcode=generateRandomString();
    $bydefualtiuserid='0';
    
    $checkmbsql=mysql_query("select id,block from $tbl_users where contactno='$mobile_no'");
    $getuserid=mysql_fetch_object($checkmbsql);
    if(mysql_num_rows($checkmbsql)>0){
        $bydefualtiuserid=$getuserid->id;
    }   
    
    if(mysql_num_rows($checkmbsql)>0 && $getuserid->block==0){
        $message = array(
                "success" => "false",
                "error" => "Mobile number already exists, Please provide another one",
                "status"=>"Not send verification code"
            );
    }else{
        if ($mobile_no != "") {            
                $password=encryptIt($password);            
                $rdate=date('Y-m-d H:i:s');            

                
                    $form_data_user= array(
                        'email'             => '',
                        'password'          => '', 
                        'block'             => 1,              
                        'group_id'          =>  2,
                        'registered_date'   => $rdate,
                        'contactno'         => $mobile_no,
                        'verify_code'       => $getcode          
                         );
                    
                    if($bydefualtiuserid==0){
                        $where="";
                    }else{
                        $where="WHERE id='$bydefualtiuserid'";
                    }
                    
                    $add_edit_user1 =   $mysql->dbQueryRowsAffected($tbl_users, $form_data_user, $where);
                    $inserteduser=mysql_insert_id();
        
                    
                    if($bydefualtiuserid==0){
                        $form_data= array(              
                        'firstname'     => '',                    
                        'address'       => '',
                        'city'          => '',
                        'state'         => '',
                        'country'       => '',
                        'zipcode'       => '',
                        'contactno'     => $mobile_no,
                        'user_id'       => $inserteduser,
                        'verify_code'   => $getcode
                         );


                        $where="";
                    }else{
                        $form_data= array(              
                        'firstname'     => '',                    
                        'address'       => '',
                        'city'          => '',
                        'state'         => '',
                        'country'       => '',
                        'zipcode'       => '',
                        'contactno'     => $mobile_no,
                        'user_id'       => $bydefualtiuserid,
                        'verify_code'   => $getcode
                         );
                        $where="WHERE user_id='$bydefualtiuserid'";
                    }
                               
                    $add_edit_profile   =   $mysql->dbQueryRowsAffected($tbl_profile, $form_data, $where);
                    if($add_edit_profile>0){                        
                        
                        $message = array(
                        "success" => "true",
                        "error" => "null",
                        "status" => "Code Generated ",
                        "verify_code" =>$getcode
                        );
                    }else{
                        $message = array(
                            "success" => "false",
                            "error" => "Something is wrong to insert data, Please try again later.",
                            "status"=>"Not send verification code"
                        );
                    }
                        
        } else {

            $message = array(
                "success" => "false",
                "error" => "Please provide mobile number",
                "status"=>"Not send verification code"
            );
            
            
        }
    }
    echo json_encode(array('result' => $message));
}

//star lite point for customer
if(isset($_REQUEST['method']) && $_REQUEST['method']=='starlite_point'){

    $json       = json_decode(file_get_contents("php://input"));
    $userid     = $json->user_id;

    //select start lite point according to user
    $sql="SELECT sum(points) as totalpoint FROM $tbl_starlite_point WHERE user_id='$userid' and lottery_generated='0'";
    $exe=mysql_query($sql);
    $rowdata=mysql_fetch_object($exe);
    $total_point1=$rowdata->totalpoint;
    $total_point=$rowdata->totalpoint;
    //echo $total_point;die;
    //starlite award entry 
    $sqlaward="SELECT * from $tbl_starlite_award WHERE user_id='$userid' and lottery_generated='0' ORDER BY id DESC";
    $exeaward=mysql_query($sqlaward);
    $totalentry=mysql_num_rows($exeaward);

    //starlite entry points as admin assign
    $getpointvalues=mysql_fetch_object(mysql_query("select * from $tbl_starlite_category WHERE id='7'"));
    $awardentry_limit=$getpointvalues->points;
    $awardentry_point=$awardentry_limit;
    

    if($totalentry>0) {
        $achive=mysql_fetch_row(mysql_query("SELECT * from $tbl_starlite_award WHERE user_id='$userid' and lottery_generated='0' ORDER BY id DESC limit 0,1"));
         $total_point=$total_point-$achive['2'];
         $awardentry_point=$awardentry_limit+$achive['2'];
    }
    
    //echo $total_point;die; 
    
    while($total_point>=$awardentry_limit){


        $insert="INSERT INTO $tbl_starlite_award (user_id,achive_points) VALUES ('$userid',$awardentry_point)";
        mysql_query($insert);   

        $achive=mysql_fetch_row(mysql_query("SELECT * from $tbl_starlite_award WHERE user_id='$userid' and lottery_generated='0' ORDER BY id DESC"));
        $awardentry_point=$achive['2']+$awardentry_limit;        
            
        $total_point=$total_point-$awardentry_limit;
    }
        
    //select starlite award entry point with date and SAE id
    $sqlaward="SELECT * from $tbl_starlite_award WHERE user_id='$userid' and lottery_generated='0' ORDER BY id DESC";
    $exeaward=mysql_query($sqlaward);
    while($rowaward=mysql_fetch_object($exeaward)){
        $awardentry[]= array(
            'id' =>'SAE-'.$rowaward->id,
            'starlite_points' =>$rowaward->achive_points,
            'date'=>$rowaward->created
            );
    }

    //total count of sae entries
    $totalentry1=mysql_num_rows(mysql_query("SELECT * from $tbl_starlite_award  WHERE user_id='$userid' and lottery_generated='0' ORDER BY id DESC"));
    
    //push notification function for send push notification to user
    push_notification();

    $message = array(
            "success"       => "true",
            "error"         => "null",
            "total_point"   => "$total_point1",
            "status"        => "You have $totalentry1 entry for starlite award",
            "awards"        => $awardentry
        );
    echo json_encode(array('result' => $message));
}
?>
