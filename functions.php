<?php
/*
 * **************************************************************
 ****************  MCS EXPT3 ******************************
 ***************************************************************/
 /* Designed & Developed by
 /*                                    - Shiburaj Pappu
 /* ************  S.P.I.T *************************************
 /* ********** M.E (EXTC) Sem-II *****************************
 */
?>
<?php
include_once('config.inc.php');
include_once('Database.class.php');
define("MSCTBL","msc");
$db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    //connect to the server
    $db->connect(); 

if($_GET['action']=='register' && !empty($_POST['MSISDN']) && !empty($_POST['IMSI'])){
    $preMSISDN = $_POST['MSISDN'][0].$_POST['MSISDN'][1].$_POST['MSISDN'][2].$_POST['MSISDN'][3];
    $data['TMSI'] = "91".$preMSISDN.rand('199999','999999');
    $sql = "SELECT * FROM ".MSCTBL." WHERE `MSISDN` = '".$_POST['MSISDN']."'";
    $row = $db->query_first($sql);
    if(empty($row)){
        $error_dat['status'] = "error";
        $error_dat['reason'] = "Registration Failed...";
    }else{
        $db->query_update(MSCTBL,$data," `MSISDN` = '".$_POST['MSISDN']."' ");
        $error_dat['regdata'] = $row;
        $error_dat['regdata']['TMSI'] = $data['TMSI'];
        $error_dat['status'] = "success";
        $error_dat['reason'] = "Registration Complete...";
    }
    sleep(3);
    echo json_encode($error_dat);
}elseif($_GET['action']=='makesim' && !empty($_POST['MSISDN'])){
    $preMSISDN = $_POST['MSISDN'][0].$_POST['MSISDN'][1].$_POST['MSISDN'][2].$_POST['MSISDN'][3];
    $data['IMSI'] = "91".$preMSISDN.rand('199999','999999');
    $data['IMEI'] = "956647"."894467".rand('199999','999999');
    $data['Ki'] = genRandKey(128);
    $data['NSP'] = getNSP($preMSISDN);
    $sql = "SELECT * FROM ".MSCTBL." WHERE `MSISDN` = '".$_POST['MSISDN']."'";
    $row = $db->query_first($sql);
    if(empty($row)){
        $data['MSISDN'] = $_POST['MSISDN'];
        $db->query_insert(MSCTBL,$data);
    }else{
        $db->query_update(MSCTBL,$data," `MSISDN` = '".$_POST['MSISDN']."' ");
        $data['MSISDN'] = $_POST['MSISDN'];
    }
    echo json_encode($data);
}elseif($_GET['action']=='authenticate' && !empty($_POST['MSISDN']) && !empty($_POST['TMSI'])){
    $sql = "SELECT * FROM ".MSCTBL." WHERE `MSISDN` = '".$_POST['MSISDN']."' AND `TMSI` = '".$_POST['TMSI']."'";
    $row = $db->query_first($sql);
    if(empty($row)){
        $error_dat['status'] = "error";
        $error_dat['reason'] = "Device Not Registered...";
    }else{
        $error_dat['RAND'] = genRandKey(128);
        $error_dat['RES'] = resGen($error_dat['RAND'],$row['Ki'],128);
        $error_dat['status'] = "success";
        $error_dat['reason'] = "RAND Generation Complete...";
    }
    sleep(3);
    echo json_encode($error_dat);
}elseif($_GET['action']=='gensres' && !empty($_POST['RAND']) && !empty($_POST['Ki'])){
    $error_dat['SRES'] = resGen($_POST['RAND'],$_POST['Ki'],128);
    $error_dat['status'] = "success";
    $error_dat['reason'] = "RAND Generation Complete...";
    sleep(3);
    echo json_encode($error_dat);
}elseif($_GET['action']=='sendsres' && !empty($_POST['SRES']) && !empty($_POST['RES'])){
    if($_POST['SRES']==$_POST['RES']){
        $error_dat['status'] = "success";
        $error_dat['reason'] = "Authentication Successfull...";
    }else{
        $error_dat['status'] = "error";
        $error_dat['reason'] = "Authentication Failed...";
    }
    
    sleep(3);
    echo json_encode($error_dat);
}


function genRandKey($length=128){
    $count = round($length/8);
    $binData = "";
    for($i=1;$i<=$count;$i++){
        $binData .= dechex(rand('128','255'))."-";    
    }
    $binData = trim($binData,"-");
    return $binData;
}

function getNSP($preMSISDN){
    switch($preMSISDN){
        case "9209":
        return "Tata Docomo";
        break;
        case "9967":
        return "Airtel";
        break;
        case "9209":
        return "Tata Indicom";
        break;
        case "9773":
        return "Aircel";
        break;
        case "8087":
        return "Tata Docomo";
        break;
        default:
        return "Other";
    }
}

function resGen($rand,$ki,$length=128){
    $count = round($length/16);
    $rand_arr = explode("-",$rand);
    $ki_arr = explode("-",$ki);
    for($i=0;$i<$count;$i++){
        $left = hexdec($rand_arr[$i]) ^ hexdec($ki_arr[$i+$count]);
        $right = hexdec($ki_arr[$i]) ^ hexdec($rand_arr[$i+$count]);
        $res64[$i] = $left ^ $right;
    }
    $count2 = round($count/2);
    for($i=0;$i<$count2;$i++){
        $res32[$i] = dechex($res64[$i] ^ $res64[$count2+$i]); 
    }
    return implode('-',$res32);
}
?>