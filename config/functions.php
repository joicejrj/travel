<?php 

	date_default_timezone_set($time_zone);

	$date = date("Y-m-d");

	$datetime = date("Y-m-d H:i:s");

	if (stristr($_SERVER['HTTP_HOST'], 'local') || (substr($_SERVER['HTTP_HOST'], 0, 7) == '192.168')) {
		$local = TRUE;
	} else {
		$local = FALSE;
	}

	$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
	include "db1.php";

	if (session_status() === PHP_SESSION_NONE) {
	    session_start();
	}

$site = new site();

class site {

	public function esc($string, $filters=""){

		$string = is_string($string) || is_numeric($string)?$string:"";

		$string = trim(stripslashes($string));

		preg_match("/strip_non_utf8/i", $filters)?$string = preg_replace('/[^\x00-\x7f\xA9\xAE\xA3\xA5]|(\&\#[0-9]{1,}\;)/', '', $string):0;

		preg_match("/strip_tags/i", $filters)?$string = strip_tags($string):0;

		preg_match("/html_encode/i", $filters)?$string = htmlentities($string, ENT_IGNORE):0;

		preg_match("/html_encode/i", $filters)?$string = str_replace("'", "&#39;", $string):0;

		preg_match("/filter_phone/i", $filters)?$string = preg_replace('/[^0-9\+\ \-\)\(]/', '', $string):0;

		return $string;

	}

	public function is_mail($a){

		return filter_var($a, FILTER_VALIDATE_EMAIL);

	}

	public function extn($fname){

	    $fname=explode('.',$fname);

	    return strtolower($fname[count($fname)-1]);

	}public function more($str = "", $len = 100){

		$str = html_entity_decode($str);

		$str = strip_tags($str);

		$str = strlen($str)>$len?substr($str,0,$len)."..":$str ;

		return $str;

	}

	public function str2url($name){

		$file_name = strtolower($name);

		$file_name = preg_replace('/[^0-9a-zA-Z]/',"-",$file_name);

		$file_name = preg_replace('/--+/',"-",$file_name);

		$file_name = preg_replace('/\-$|^\-/',"",$file_name);

		return $file_name;

	}

	public function randomstr($len=6, $chars="uln"){

		$pattern = "";

		$pattern .= preg_match("/n/", $chars)?"1234567890":"";

		$pattern .= preg_match("/l/", $chars)?"abcdefghijklmnopqrstuvwxyz":"";

		$pattern .= preg_match("/u/", $chars)?"ABCDEFGHIJKLMNOPQRSTUVWXYZ":"";

		$pattern .= preg_match("/s/", $chars)?"@#$&*-_+":"";

		$str = "";

		if(strlen($pattern)>0){

			while(strlen($str)<$len){

				$str .= $pattern[rand(0,strlen($pattern)-1)];

			}

		}

		return $str;

	}
	public function redirect($url='') {
		if($url!='') {
			echo '<script>window.location.href="'.$url.'";</script>';
			exit();
		}
		else {
			echo '<script>window.location.href=window.location.href;</script>';
			exit();
		}
	}
	public function back() {
		// Redirect back to the previous page
		header("Location: {$_SERVER['HTTP_REFERER']}");
		exit();
	}

	// for login access token
	public function generate_access_token($user_id,$role="agent") {
	    $payload = json_encode([
	        'uid' => $user_id,
	        'role' => $role,
	        'ts' => time()
	    ]);
	    return $this->my_encrypt($payload);
	}

	public function validate_access_token($token) {
	    global $access_token_expiry,$db; // e.g., 3600 seconds (1 hour)

	    $data = $this->my_decrypt($token); // or global if not in class
	    if (!$data) return false;

	    $payload = json_decode($data, true);
	    if (!isset($payload['uid']) || !isset($payload['ts'])) return false;

	    if(isset($payload['role'])) {
	    	if($payload['role']=='admin') {
	    		$getlu = $db->get('admins',array('id'=>$payload['uid'],'access_token'=>$token),'id');
	    		if(!$getlu) {
	    			return false;
	    		}
	    	}
	    	else if($payload['role']=='agent') {
	    		$getlu = $db->get('agents',array('id'=>$payload['uid'],'access_token'=>$token),'id');
	    		if(!$getlu) {
	    			return false;
	    		}
	    	}
	    }

	    // if ((time() - $payload['ts']) > $access_token_expiry) {
	    //     return false; // Token expired
	    // }

	    return $payload['uid']; // Token valid
	}

	// https://stackoverflow.com/questions/28155153/how-to-encrypt-decrypt-pdf-docx-files-in-php
	public function my_encrypt($data) {
	    global $encryption_key;
	    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
	    $iv = openssl_random_pseudo_bytes($iv_length);
	    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
	    $combined = $iv . $encrypted; // $iv . '::' . $encrypted;
	    return rtrim(strtr(base64_encode($combined), '+/', '-_'), '='); // URL safe base64
	}

	public function my_decrypt($data) {
	    global $encryption_key;
	    $data = strtr($data, '-_', '+/');
	    $data = base64_decode($data . str_repeat('=', (4 - strlen($data) % 4) % 4));
	    // $parts = explode('::', $data, 2);
	    // if (count($parts) !== 2) {
	    //     return false;
	    // }
	    // list($iv, $encrypted) = $parts;
	    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
	    $iv = substr($data, 0, $iv_length);
	    $encrypted = substr($data, $iv_length);
	    return openssl_decrypt($encrypted, 'aes-256-cbc', $encryption_key, 0, $iv);
	}

	public function write_file($filename,$content){

		$exist_content = "";

		if(file_exists($filename)){

			$exist_content = file_get_contents($filename);

		}

		$fp = fopen($filename, 'w');

		fwrite($fp, '<div style="overflow:hidden; font-family:arial; font-size:12px; padding-bottom:10px; margin-bottom:10px; border-bottom:1px dashed #ccc;">

		<div style="width:20%; float:left;">['.date("Y-m-d h:i:s A").']</div>

		<div style="width:79%; float:right;">'.$content.'</div></div>'.$exist_content);

		fclose($fp);

	}

	public function upload_file($fileupload, $folder="", $name="", $allowed=array(), $overwrite=1){

		$filename = $fileupload["name"];

		$rtn = 0;

		if($filename!=''){

			$ext    = $this->extn($filename);

			$folder = $folder!=''?('../'.preg_replace('/^\[|\]$/', '', $folder).'/'):'';

			$upload = empty($allowed) || in_array($ext, $allowed)?1:0;

			if($upload){

				$filename   = ($name==''?$this->randomstr(10, 'ul').date('ymdhis'):$this->str2url($name));

				$file       = $filename.'.'.$ext;

				if(!$overwrite){

					$file_index = 0;

					while(file_exists($folder.$file)){

						$file_index++;

						$file = $filename."-".$file_index.".".$ext;

					}

				}

				if(move_uploaded_file($fileupload["tmp_name"], $folder.$file)){

					$rtn = $file;

				}

			}



		}

		return $rtn;

	}

	public function format_multi_file($file){

		$file_list = array();

		if(isset($file["name"]) && is_array($file["name"])){

			foreach($file["name"] as $key=>$name){

				if($name!=""){

					$file_list[$key] = array(

						"name"=>$name, "tmp_name"=>$file["tmp_name"][$key], "type"=>$file["type"][$key], "size"=>$file["size"][$key], "error"=>$file["error"][$key]

					);

				}

			}

		}

		return $file_list;

	}

	public function create_thump($file, $folder, $name="random", $sizes = array()){

		$actualimg = $this->upload_img($file, $folder, $name, '1200');

		if(!empty($sizes) && $actualimg!=''){

			$folder = '../'.preg_replace('/^\[|\]$/', '', $folder).'/';

			foreach($sizes as $val){

				$newfile = $this->make_file_variation($folder.$actualimg, $val['width'].'x'.$val['height']);

				$proc = $this->resize_image($newfile, $val['width'], $val['height'], true);

			}



			return $actualimg;

		}

	}

	public function upload_img($file, $folder, $name="random", $width=300, $height="auto",$root="../", $thump=false, $top=false, $scale=false){

		$ret="";

		if(isset($file)){

			$ext = $this->extn($file['name']);

			$name = $name=="random"?$this->randomstr(40).date('ymdhis'):$name;

			if($file['tmp_name']!=""){

				$imgsize = getimagesize($file["tmp_name"]);

				if($imgsize !== false) {

					$wid = $imgsize[0];

					$hei = $imgsize[1];

					$dim_ok = ($width==$wid && $height==$hei); //||$thump==true;

					if($dim_ok) {

						$proc = $this->upload_file($file, $folder, $name, $allowed=array('jpg','jpeg','png','gif'));

						$ret = $proc;

					}

					else {

						$folder = $root.preg_replace('/^\[|\]$/', '', $folder).'/';

						$path = "$folder$name.$ext";

						$name = "$name.$ext";

						if(!is_dir($folder)){

							mkdir($folder);

						}

						if(copy($file['tmp_name'], $path)){

							$proc = $this->resize_image($path, $width, $height, $thump, $top, $scale);

							!$proc?$this->remove_file($path):'';

							if($proc){

								$ret = $name;

							}

						}

					}

				}

			}

		}

		return $ret;

	}

	public function resize_image($image, $width=300, $height="auto", $thump=false, $top=false, $scale=false){

		$rtn = false;

		if(file_exists($image) && !is_dir($image)){

			$ext = $this->extn($image);

			$cnc_func = "";

			$out_fnc = "";

			$out_qual = "";

			if($ext=="jpg"||$ext=="jpeg"){

				$cnc_func = "imagecreatefromjpeg";

				$out_fnc = "imagejpeg";

				$out_qual = 100;

			}else if($ext=="png"){

				$cnc_func = "imagecreatefrompng";

				$out_fnc = "imagepng";

				$out_qual = 1;

			}else if($ext=="gif"){

				$cnc_func = "imagecreatefromgif";

				$out_fnc = "imagegif";

			}

			if($cnc_func!=""){

				$source = call_user_func($cnc_func, $image);

				$source_width = imagesx($source);

				$source_height = imagesy($source);

				$destination_x = 0;

				$destination_y = 0;

				$destination_width = $source_width;

				$destination_height = $source_height;

				$range = $this->limit_range($source_width, $source_height, $width, $height, ($thump?($scale?"inner":"outer"):"normal"));

				$destination_width = $range->width;

				$destination_height = $range->height;

				if($thump){

					$destination_x = round((($destination_width-$width)/2))*-1;

					$destination_y = $top?0:round((($destination_height-$height)/2))*-1;

				}

				$canvas_width = $thump?$width:$destination_width;

				$canvas_height = $thump?$height:$destination_height;

				$newImg = imagecreatetruecolor($canvas_width, $canvas_height);

				## get source solor

				$fetchColor = imagecolorat($source, 0, 0);

				$rgb = imagecolorsforindex($source, $fetchColor);

				if($ext=="png"){

					imagealphablending($newImg, false);

					imagesavealpha($newImg,true);

					$transparent = imagecolorallocatealpha($newImg, $rgb["red"], $rgb["green"], $rgb["blue"], $rgb["alpha"]);

					imagefilledrectangle($newImg, 0, 0, $canvas_width, $canvas_height, $transparent);

				}else{

					$white_im = imagecreate($canvas_width, $canvas_height);

					imagecolorallocate($white_im, $rgb["red"], $rgb["green"], $rgb["blue"]);

					imagecopyresampled($newImg, $white_im, 0, 0, 0, 0, $canvas_width, $canvas_height, $source_width, $source_height);

				}

				imagecopyresampled($newImg, $source, $destination_x, $destination_y, 0, 0, $destination_width, $destination_height, $source_width, $source_height);

				if($ext=="gif") {

					call_user_func($out_fnc, $newImg, $image);

				}

				else {

					call_user_func($out_fnc, $newImg, $image, $out_qual);

				}

				$rtn = true;

			}

		}

		return $rtn;

	}

	public function limit_range($org_width, $org_height, $tgt_width, $tgt_height="auto", $mode="normal"){

		$response = new stdClass();

		$response->width = $org_width;

		$response->height = $org_height;

		$autoheight = $tgt_height=="auto";

		if($org_width>$tgt_width || (!$autoheight && $org_height>$tgt_height)){

			if($mode=="outer"){

				$base = $org_height<$org_width?$org_width/$tgt_width:$org_height/$tgt_height;

				$response->width = round($org_width/$base);

				$response->height = round($org_height/$base);

				if($response->width<$tgt_width){

					$base = $response->width/$tgt_height;

					$response->width = round($response->width/$base);

					$response->height = round($response->height/$base);

				}

				if($response->height<$tgt_height){

					$base = $response->height/$tgt_height;

					$response->width = round($response->width/$base);

					$response->height = round($response->height/$base);

				}

			}else{

				$base = max(($org_width/$tgt_width), (!$autoheight?$org_height/$tgt_height:0));

				$response->width = max(1, round($org_width/$base));

				$response->height = max(1, round($org_height/$base));

			}

		}

		return $response;

	}

	public function remove_file($file){

		$file!="" && is_file($file)?unlink($file):0;

	}

	public function remove_folder($dirPath) {

	    if (! is_dir($dirPath)) {

	        // throw new InvalidArgumentException("$dirPath must be a directory");

	    }

	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {

	        $dirPath .= '/';

	    }

	    $files = glob($dirPath . '*', GLOB_MARK);

	    foreach ($files as $file) {

	        if (is_dir($file)) {

	            self::remove_folder($file);

	        } else {

	            unlink($file);

	        }

	    }

	    rmdir($dirPath);

	}

	public function get_file_variation($file, $index){

		return preg_replace('/(.*)\.(.*)/i', "$1-$index.$2", $file);

	}

	public function make_file_variation($file, $index){

		$new_file = "";

		if(is_file($file)){

			$file_name = $this->get_file_variation($file, $index);

			if(copy($file, $file_name)){

				$new_file = $file_name;

			}

		}

		return $new_file;

	}

	// Converts seconds into mm:ss format
	public function getMin($seconds) {
	    $seconds = intval($seconds);
	    $minutes = floor($seconds / 60);
	    $remainingSeconds = $seconds % 60;
	    return sprintf("%02d:%02d", $minutes, $remainingSeconds);
	}

	public function time_elapsed_string($datetime, $full = false) {

	    $now = new DateTime;

	    $ago = new DateTime($datetime);

	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);

	    $diff->d -= $diff->w * 7;

	    // $string = array(

	    //     'y' => 'year',

	    //     'm' => 'month',

	    //     'w' => 'week',

	    //     'd' => 'day',

	    //     'h' => 'hour',

	    //     'i' => 'minute',

	    //     's' => 'second',

	    // );

	    $string = array(

	        'd' => 'day',

	        'h' => 'hour',

	        'i' => 'minute'

	    );

	    foreach ($string as $k => &$v) {

	        if ($diff->$k) {

	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');

	        } else {

	            unset($string[$k]);

	        }

	    }



	    if (!$full) $string = array_slice($string, 0, 1);

	    return $string ? implode(', ', $string) . ' ago' : 'Just now';

	}

	/**
	 * Outputs a JSON response and terminates the script.
	 *
	 * @param mixed $obj The data to be encoded into JSON format.
	 */
	public function json($obj) {
	    // Clears the output buffer to prevent unwanted output
	    ob_clean();

	    // Set the response content type to JSON
	    header("Content-Type: application/json");

	    // Convert the object to JSON and output it
	    echo json_encode($obj);

	    // Terminate the script execution to ensure no further output
	    exit;
	}

	public function msg($msg="An error occurred. Cannot perform this action", $status="error") {
    
	    // Store the message and status in the session
	    // This can be used later to show notifications to the user
	    $_SESSION['msg'] = array($msg, $status);

	}

	public function show_msg() {
    
	    // Check if there is a message stored in the session
	    if(isset($_SESSION['msg'])) {

	        // Retrieve the stored message and status
	        $arr = $_SESSION['msg'];

	        // Determine the Bootstrap alert class based on the message status
	        switch($arr[1]) {

	            case "success":
	                $type = 'alert-success'; // Green (Success)
	                break;

	            case "info":
	                $type = 'alert-primary'; // Blue (Info)
	                break;    

	            case "warning":
	                $type = 'alert-warning'; // Yellow (Warning)
	                break;    

	            default:
	                $type = 'alert-danger'; // Red (Error)
	                break;
	        }

	        // Display the message in an alert box
	        echo '<div class="alert '.$type.'" align="center">'.$arr[0].'</div>';

	        // Remove the message from the session after displaying it
	        unset($_SESSION['msg']);
	    }
	}

	public function send_sms($phone,$sms) {
		global $local,$db,$datetime,$enable_sms,$test_phones;
		if($local || in_array($phone, $test_phones)){
		    $db->insert('sms_logs',array('phone'=>$phone,'sms'=>"Test: ".$sms,'test'=>1,'timestamp'=>$datetime));
			return true;
		}
		else {
			if($enable_sms && $phone!='' && $sms!='') {
				$url = 'https://web.ukpbx.com/API/sms_apixxx.php';
		        $myvars = 'mobilenumber='.$phone.'&msg_text='.$sms;
		        
		        $ch = curl_init( $url );
		        curl_setopt( $ch, CURLOPT_POST, 1);
		        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
		        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		        curl_setopt( $ch, CURLOPT_HEADER, 0);
		        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		        
		        $response = curl_exec( $ch );
		        $resData = json_decode($response, true);
		        if(isset($resData)){
		            if( $resData['Response'] =='Success' ) {
		            	$db->insert('sms_logs',array('phone'=>$phone,'sms'=>$sms,'timestamp'=>$datetime));
						return true;
		            }
		        }
		    }
		    else return true;
		}
	}
	public function send_sms_with_url($phone,$sms) {
		global $local,$db,$datetime,$enable_sms,$test_phones;
		if($local || in_array($phone, $test_phones)){
		    $db->insert('sms_logs',array('phone'=>$phone,'sms'=>"Test: ".$sms,'test'=>1,'timestamp'=>$datetime));
			return true;
		}
		else {
			if($enable_sms && $phone!='' && $sms!='') {
				$smse = base64_encode($sms);
				$url = 'https://web.ukpbx.com/API/sms_api_encodexxx.php';
		        $myvars = 'mobilenumber='.$phone.'&msg_text='.$smse;
		        
		        $ch = curl_init( $url );
		        curl_setopt( $ch, CURLOPT_POST, 1);
		        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
		        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		        curl_setopt( $ch, CURLOPT_HEADER, 0);
		        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		        
		        $response = curl_exec( $ch );
		        $resData = json_decode($response, true);
		        if(isset($resData)){
		            if( $resData['Response'] =='Success' ) {
		            	$db->insert('sms_logs',array('phone'=>$phone,'sms'=>$sms,'timestamp'=>$datetime));
						return true;
		            }
		        }
		    }
		    else return true;
		}
	}

	public function send_mail($to, $subject,$tem_keywords=array(),$tem_values=array(),$template_file='email',$root='',$attach=array()){

		global $local,$db, $enable_email;


		if(!$enable_email) return false;

		if($to=='') return false;

		//testing
		// $to = "joicekurups@gmail.com";

		// $options = $db->options(array('mail_host','mail_user','mail_pass','mail_from','mail_port'));


		$title = "CRM Alerts";

		$template = $root."config/".$template_file.".html";

		$template = is_file($template)?@file_get_contents($template):"";

		$template = $template==false?"":$template;

		array_push($tem_keywords,"[subject]");

		array_push($tem_values, $subject);

		$template = str_replace($tem_keywords, $tem_values, $template);


		if($local){

			$this->write_file($root."mail.html", "<table cellpadding=\"7\" style=\"font-size:12px;\"><tr valign=\"top\"><td><strong>To</strong></td><td>$to".

			"</td></tr><tr valign=\"top\"></tr></table>".$template);

		}else{

			require_once($root."mail/class.phpmailer1.php");

			require_once($root."mail/class.smtp1.php");

			$mail = new PHPMailer\PHPMailer\PHPMailer();

			try {

				$mail->SMTPDebug = 0;

			    $mail->isSMTP();                                            

			    $mail->Host       = "mail.mediatel.com";                    

			    $mail->SMTPAuth   = true;                             

			    $mail->Username   = "crmalerts";                 

			    $mail->Password   = "1ksoR1PVA~1+";                        

			    $mail->SMTPSecure = 'ssl';                              

			    $mail->Port       = '465';  

			    $mail->setFrom("crmalerts@mediatel.com", $title);  

			    // $reply!=''?$mail->AddReplyTo($reply):'';

			    $mail->addAddress($to);			       

			    $mail->isHTML(true);                                  

			    $mail->Subject = $subject;

			    $mail->Body    = $template;

			    // $mail->AltBody = 'Body in plain text for non-HTML mail clients';

			    // **Add Attachment**
			    if(!empty($attach)) {
			    	foreach ($attach as $afile) {
			    		if($afile!='') {
    						$mail->addAttachment($afile); // File attachment
			    		}
			    	}
			    }

			    $rr = $mail->send();

			    return $rr; //true;

			} catch (Exception $e) {

			    return false;

			}

		}

	}

	// $_SESSION['person_name_admin'] = autologin admin session array
	// $_SESSION['person_id'] = agent id
	public function agent_log($log, $uid="", $utype="customer", $ltype="general") {
		global $db, $datetime;
		if(isset($_SESSION['person_id'])) {
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$logarr = array('agent_id' => $_SESSION['person_id'],'log' => $log,'type'=>$ltype,'ip'=> $user_ip,'timestamp' => $datetime);
			if($uid!="" && $utype!="") {
				$logarr[$utype.'_id'] = $uid;
			}
			if(isset($_SESSION['person_name_admin'])) {
				$logarr['admin'] = json_encode($_SESSION['person_name_admin']);
			}
		    $db->insert('people_logs',$logarr);
		}
	}

}

?>