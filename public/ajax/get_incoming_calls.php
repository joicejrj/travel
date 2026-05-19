<?php
ini_set('display_errors',1);

session_start();
require_once __DIR__ . '/../../config/db.php';        // must provide $mysqli
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';

$rtn = array('success'=>0);
if(isset($_POST['calls'])){
   $date15min = date("Y-m-d H:i:s",strtotime("-15 minutes",strtotime($datetime)));
   $ucond = array('#all'=>1,'#srt'=>'date_added desc','#cus'=>"(date_added>='".$date15min."')",'#limit'=>5);
   $ulist = $db->get('tbl_incoming_calls',$ucond,'number,source');
   if(!empty($ulist->data)) {
      $rtn['success'] = 1;
      // $unoa = array();
      // foreach ($ulist->data as $key => $uno) {
      //    $unoa[$key]['number'] = $uno->number;
      // }
      // var_dump($ulist->data);
      // $unoa = array_unique($unoa);
      $unoa = (array)$ulist->data;
      //print_r($unoa);
      // $unoa = array_unique($uarr);
      $unoa = array_values(array_map("unserialize", array_unique(array_map("serialize", $unoa))));
      // var_dump($unoa);
      $rtn['nos'] = $unoa; //(array)$ulist->data;
      // $rtn['nos'] = (array)$ulist->data;
   }
}
$site->json($rtn);
?>