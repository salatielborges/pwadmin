<?php 
session_start();
include "../config.php";
include "../basefunc.php";
$header_arr = array("error" => "Unknown Error!", "reloaduserlist" => "0", "success" => "", "reloaduserdata" => "0");
$user_arr = array();
SessionVerification();
if ($_SESSION['UD3'] != $AKey2){die();}
$data = json_decode(file_get_contents('php://input'), true);
if (($data)&&(isset($_SESSION['un']))) {
	$atool=intval($data["tool"]);
	$un=$_SESSION['un'];
	$pw=$_SESSION['pw'];
	$uid=$_SESSION['id'];
	$ma=$_SESSION['ma'];
	if (($uid==$AdminId)&&($atool>0)){
		$link = new mysqli($DB_Host, $DB_User, $DB_Password, $DB_Name);
		$Admin=VerifyAdmin($link, $un, $pw, $uid, $ma);
		if ($Admin!==false){
			if (isset($data["id"])){$id=$data["id"];}
			if (isset($data["amount"])){$amount=$data["amount"];}
			if (isset($data["day"])){$days=$data["day"];}
			if ($atool==2){
				if ($amount>0){
					$TIME=date("Y-m-d H:i:s");
					$query="INSERT INTO usecashnow (userid, zoneid, sn, aid, point, cash, status, creatime) VALUES ('$id', '1', '0', '1', '0', '$amount', '1', '$TIME') ON DUPLICATE KEY UPDATE cash = cash + $amount";
					$stmt = $link->prepare($query);
					$stmt->execute(); 
					$stmt->close();
					$header_arr["reloaduserdata"]="1";
					$header_arr["success"]="Gold added to account";
				}else{
					$header_arr["error"]="Amount is 0";
				}
			}elseif ($atool==3){
				if ($amount>0){
					$query = "UPDATE users SET VotePoint = VotePoint+$amount WHERE ID=?";
					$stmt = $link->prepare($query);
					$stmt->bind_param('i', $id);
					$stmt->execute(); 
					$stmt->close();		
					$header_arr["reloaduserdata"]="1";
					$header_arr["success"]="Point added to account";					
				}else{
					$header_arr["error"]="Amount is 0";
				}
			}elseif ($atool==4){
				$GMr=CountMysqlRows($link,5,$id);
				if ($GMr==0){
					mysqli_query($link, "call addGM('".$id."', '1')");
					$header_arr["success"]="Account became GM account";
					$header_arr["reloaduserdata"]="1";
					$header_arr["reloaduserlist"]="1";
				}else{
					$header_arr["error"]="Already GM account";
				}					
			}elseif ($atool==5){
				$stmt = $link->prepare("DELETE FROM auth WHERE userid = ?");
				$stmt->bind_param('i', $id);
				$stmt->execute(); 
				$stmt->close();		
				$header_arr["reloaduserdata"]="1";
				$header_arr["reloaduserlist"]="1";				
				$header_arr["success"]="Account is normal account";				
			}elseif ($atool==6){
				$BannedId = intval($data["targetid"]);
				$banType = intval($data["bantype"]);
				$GMId = intval($data["gmid"]);
				$Duration = intval($data["bandur"]);
				$Reason = $data["banreason"];
				if (($BannedId > 0)&&($banType > 0)&&($banType < 5)){
					if ($Duration < 5){$Duration=5;}
					include("../php/packet_class.php");
					$Packet = new WritePacket();
					$Packet -> WriteUInt32($GMId); // gmroleid ex. -1
					$Packet -> WriteUInt32(0); // ssid
					$Packet -> WriteUInt32($BannedId); // ID role/account ex. 16
					$Packet -> WriteUInt32($Duration); // Time ex. 3600
					$Packet -> WriteUString($Reason); //Reason
					switch($banType){
						case 1:
							$Packet -> Pack(0x162); //Ban account
							break;
						case 2:
							$Packet -> Pack(0x164); //Ban chat account
							break;
						case 3:
							$Packet -> Pack(0x16A); //Ban chat role
							break;
						case 4:
							$Packet -> Pack(0x168); //Ban role
							break;
						default:
							return;
						}
					$Packet -> Send("localhost", 29100);
					$header_arr["success"]="Ban action executed";	
					$header_arr["reloaduserdata"]="1";
				}else{
					$header_arr["error"]="Use the correct settings!";
				}
			//tool 7 removed
			}elseif ($atool==8){
				if ($id != $AdminId){
					DeleteUserAccount ($link, $id);
					$header_arr["success"]="Account deleted";
					$header_arr["reloaduserlist"]="1";					
				}				
			}elseif ($atool==9){
				if (($days > 0)&&($days<36500)){
					$header_arr["reloaduserlist"]=$days;
					$c=0;
					$query="SELECT uid FROM point WHERE lastlogin < DATE_SUB(NOW(), INTERVAL {$days} DAY) AND uid <> {$AdminId}";
					$statement = $link->prepare($query);
					$statement->execute();
					$statement->bind_result($id1);
					$statement->store_result();
					$result = $statement->num_rows;
					$c=$c+$result;
					if ($result<1) {
						$header_arr["error"]="Do not exist user with that much inactive day since last login date!";
					}else{
						while($statement->fetch()) {
							DeleteUserAccount ($link, $id1);
							$header_arr["reloaduserlist"]="1";
						}   
					}
					
					$header_arr["reloaduserlist"]=$days;
					$statement->close();
					
					
					$query="SELECT ID FROM users WHERE ID <> {$AdminId} AND creatime < DATE_SUB(NOW(), INTERVAL {$days} DAY) AND (NOT EXISTS (SELECT null FROM point WHERE users.ID = point.uid))";
					$statement = $link->prepare($query);
					$statement->execute();
					$statement->bind_result($id1);
					$statement->store_result();
					$result = $statement->num_rows;
					$c=$c+$result;
					if (!$result) {
						$header_arr["error"]="Do not exist user with that much inactive day since last login date!";
					}else{
						while($statement->fetch()) {
							DeleteUserAccount ($link, $id1);
							$header_arr["reloaduserlist"]="1";
						}   
					}
					$statement->close();
					
					
					$header_arr["success"]=$c." user deleted";
				}else{
					$header_arr["error"]="Day must be between 0 and 36500!";
				}
				
			}elseif ($atool==10){
				if ((($days > -1)&&($days<36500))&&(($amount > 0)&&($amount<9999999))){
					if ($days==0){
						$query="SELECT uid FROM point WHERE zoneid IS NOT NULL";
					}else{
						$query="SELECT uid FROM point WHERE lastlogin >= ( CURDATE() - INTERVAL {$days} DAY )";
					}
					$statement = $link->prepare($query);
					$statement->execute();
					$statement->bind_result($id1);
					$statement->store_result();
					$result = $statement->num_rows;
					if (!$result) {
						$header_arr["error"]="Do not exist user who was online in last ".$days." day!";
					}else{
						$nr0 = 0;
						$nr1 = 1;
						$TIME=date("Y-m-d H:i:s");
						while($statement->fetch()) {
							$query="INSERT INTO usecashnow (userid, zoneid, sn, aid, point, cash, status, creatime) VALUES ('$id1', '1', '0', '1', '0', '$amount', '1', '$TIME') ON DUPLICATE KEY UPDATE cash = cash + $amount";
							$stmt = $link->prepare($query);
							$stmt->execute(); 
							$stmt->close();
						}   
						$header_arr["success"]=$result." reicived gold";
						$header_arr["reloaduserdata"]="1";
					}
					$statement->close();											
				}				
			}elseif ($atool==11){
				if ((($days > -1)&&($days<36500))&&(($amount > 0)&&($amount<9999999))){
					if ($days==0){
						$query="SELECT uid FROM point WHERE zoneid IS NOT NULL";
					}else{
						$query="SELECT uid FROM point WHERE lastlogin >= ( CURDATE() - INTERVAL $days DAY )";
					}
					$statement = $link->prepare($query);
					$statement->execute();
					$statement->bind_result($id1);
					$statement->store_result();
					$result = $statement->num_rows;
					if (!$result) {
						$header_arr["error"]="Do not exist user who was online in last ".$days." day!";
					}else{
						while($statement->fetch()) {
							$query = "UPDATE users SET VotePoint=VotePoint+$amount WHERE ID=?";
							$stmt = $link->prepare($query);
							$stmt->bind_param('i', $id1);
							$stmt->execute(); 
							$stmt->close();
						}   
						$header_arr["success"]=$result." reicived point";
						$header_arr["reloaduserdata"]="1";
					}
					$statement->close();
				}				
			}elseif ($atool==12){
				$filen='../config.php';
				$fileno='../config_old.php';
				$str=file_get_contents($filen);
				$oldConfId = $AKey1;
				$newConfId = base64_encode(md5(time()."This is admin reset key"));
				$str=str_replace($oldConfId, $newConfId, $str);
				$oldConfId = $AKey2;
				$newConfId = base64_encode(md5(time()."Secondary reset alot better!"));
				$str=str_replace($oldConfId, $newConfId, $str);	
				chmod($filen, 0777);
				rename($filen, $fileno);
				file_put_contents($filen, $str);
				chmod($filen, 0755);	
				unset ($_SESSION['un']);
				unset ($_SESSION['pw']);
				unset ($_SESSION['id']);
				unset ($_SESSION['ma']);
				if (isset($_SESSION['t'])){
					unset ($_SESSION['t']);
				}
				if (isset($_SESSION['UD1'])){
					unset ($_SESSION['UD1']);
				}
				if (isset($_SESSION['UD2'])){
					unset ($_SESSION['UD2']);
				}
				if (isset($_SESSION['UD3'])){
					unset ($_SESSION['UD3']);
				}
				$header_arr["error"]="Please relog!";			
			}elseif ($atool==13){
				/*
							if ((isset($_GET['sn']))&&(isset($_GET['al']))&&(isset($_GET['ri']))&&(isset($_GET['rs']))&&(isset($_GET['sg']))&&(isset($_GET['sp']))&&(isset($_GET['mp']))&&(isset($_GET['mi']))&&(isset($_GET['wc']))&&(isset($_GET['db']))&&(isset($_GET['fb']))&&(isset($_GET['fu']))&&(isset($_GET['vg']))&&(isset($_GET['ve']))&&(isset($_GET['vt']))&&(isset($_GET['vf']))&&(isset($_GET['vr']))&&(isset($_GET['mh']))&&(isset($_GET['dn']))&&(isset($_GET['dp']))&&(isset($_GET['mu']))&&(isset($_GET['pi']))&&(isset($_GET['li']))&&(isset($_GET['su']))&&(isset($_GET['sj']))&&(isset($_GET['sr']))&&(isset($_GET['pt']))&&(isset($_GET['as']))&&(isset($_GET['wl']))&&(isset($_GET['wd']))&&(isset($_GET['sd']))){
								$bool[0]="false";
								$bool[1]="true";
								$SN=htmlspecialchars(trim($_GET['sn']));
								$AL=intval(trim($_GET['al']));
								$AR=intval(trim($_GET['ar']));
								$RI=intval(trim($_GET['ri']));
								$RS=intval(trim($_GET['rs']));
								$SG=intval(trim($_GET['sg']));
								$SP=intval(trim($_GET['sp']));
								$MP=intval(trim($_GET['mp']));
								$MI=intval(trim($_GET['mi']));
								$WC=intval(trim($_GET['wc']));
								$WS=intval(trim($_GET['ws']));
								$WD=intval(trim($_GET['wd']));
								$WL=intval(trim($_GET['wl']));
								$DB=intval(trim($_GET['db']));
								$FB=intval(trim($_GET['fb']));
								$VG=intval(trim($_GET['vg']));
								$VE=intval(trim($_GET['ve']));
								$VT=intval(trim($_GET['vt']));
								$VF=intval(trim($_GET['vf']));
								$VR=intval(trim($_GET['vr']));	
								$SD=intval(trim($_GET['sd']));										
								$FU=trim($_GET['fu']);
								$MH=trim($_GET['mh']);
								$DN=trim($_GET['dn']);
								$DP=trim($_GET['dp']);
								$MU=trim($_GET['mu']);
								$PI=trim($_GET['pi']);
								$LI=trim($_GET['li']);
								$SU=trim($_GET['su']);
								$SJ=trim($_GET['sj']);
								$SR=trim($_GET['sr']);
								$PT=trim($_GET['pt']);
								$AS=trim($_GET['as']);
								$FU=trim($_GET['fu']);
								
								if ((filter_var($FU, FILTER_VALIDATE_URL))&&($FU!="")){
									$filen='./config.php';
									$fileno='./config_old.php';
									$str=file_get_contents($filen);
									$str=str_replace('ServerName="'.$ServerName.'";', 'ServerName="'.$SN.'";', $str);
									$str=str_replace('LoginEnabled='.BoolToSting($LoginEnabled).';', 'LoginEnabled='.$bool[$AL].';', $str);									
									$str=str_replace('RegisEnabled='.BoolToSting($RegisEnabled).';', 'RegisEnabled='.$bool[$AR].';', $str);									
									$str=str_replace('IPRegLimit='.$IPRegLimit.';', 'IPRegLimit='.$RI.';', $str);									
									$str=str_replace('SRegLimit='.$SRegLimit.';', 'SRegLimit='.$RS.';', $str);									
									$str=str_replace('StartGold='.$StartGold.';', 'StartGold='.$SG.';', $str);									
									$str=str_replace('StartPoint='.$StartPoint.';', 'StartPoint='.$SP.';', $str);									
									$str=str_replace('MaxWebPoint='.$MaxWebPoint.';', 'MaxWebPoint='.$MP.';', $str);
									$str=str_replace('ItemIdLimit='.$ItemIdLimit.';', 'ItemIdLimit='.$MI.';', $str);									
									$str=str_replace('WebShop='.BoolToSting($WebShop).';', 'WebShop='.$bool[$WS].';', $str);			
									$str=str_replace('WebShopLog='.BoolToSting($WebShopLog).';', 'WebShopLog='.$bool[$WL].';', $str);			
									$str=str_replace('WShopLogDel='.$WShopLogDel.';', 'WShopLogDel='.$WD.';', $str);			
									$str=str_replace('WShopDB='.$WShopDB.';', 'WShopDB='.$SD.';', $str);			
									$str=str_replace('ControlPanel='.BoolToSting($ControlPanel).';', 'ControlPanel='.$bool[$WC].';', $str);									
									$str=str_replace('Donation='.BoolToSting($Donation).';', 'Donation='.$bool[$DB].';', $str);									
									$str=str_replace('Forum='.BoolToSting($Forum).';', 'Forum='.$bool[$FB].';', $str);									
									$str=str_replace('ForumUrl="'.$ForumUrl.'";', 'ForumUrl="'.$FU.'";', $str);	
									$str=str_replace('VoteButton='.BoolToSting($VoteButton).';', 'VoteButton='.$bool[$VE].';', $str);									
									$str=str_replace('PointExc='.$PointExc.';', 'PointExc='.$VG.';', $str);									
									$str=str_replace('VoteInterval='.$VoteInterval.';', 'VoteInterval='.$VT.';', $str);									
									$str=str_replace('VoteFor='.$VoteFor.';', 'VoteFor='.$VF.';', $str);									
									$str=str_replace('VoteReward='.$VoteReward.';', 'VoteReward='.$VR.';', $str);		
									$str=str_replace('DB_Host="'.$DB_Host.'";', 'DB_Host="'.$MH.'";', $str);									
									$str=str_replace('DB_User="'.$DB_User.'";', 'DB_User="'.$MU.'";', $str);									
									$str=str_replace('DB_Name="'.$DB_Name.'";', 'DB_Name="'.$DN.'";', $str);									
									$str=str_replace('DB_Password="'.$DB_Password.'";', 'DB_Password="'.$DP.'";', $str);									
									$str=str_replace('SSH_User="'.$SSH_User.'";', 'SSH_User="'.$SU.'";', $str);									
									$str=str_replace('SSH_Password="'.$SSH_Password.'";', 'SSH_Password="'.$SJ.'";', $str);									
									$str=str_replace('ServerIP="'.$ServerIP.'";', 'ServerIP="'.$PI.'";', $str);									
									$str=str_replace('LanIP="'.$LanIP.'";', 'LanIP="'.$LI.'";', $str);									
									$str=str_replace('DB_Host="'.$DB_Host.'";', 'DB_Host="'.$MH.'";', $str);									
									$str=str_replace('ServerPath="'.$ServerPath.'";', 'ServerPath="'.$SR.'";', $str);									
									$str=str_replace('PassType='.$PassType.';', 'PassType="'.$PT.'";', $str);									
									$tmpArr=explode("#", $AS);
									for ($i = 1; $i <= 9; $i++){
										$str=str_replace('ServerFile['.$i.']="'.$ServerFile[$i].'";', 'ServerFile['.$i.']="'.$tmpArr[$i].'";', $str);
									}
									chmod($filen, 0777);
									$fperm=substr(sprintf('%o', fileperms($filen)), -4);
									rename($filen, $fileno);
									file_put_contents($filen, $str);
									chmod($filen, 0755);	
									if ($fperm=="0777"){
										echo "<script>
										alert('Settings saved to config file!');
										parent.location.href=parent.location.href;
										</script>";
									}else{
										echo "<script>alert('Unable save because not have permission for config file!');</script>";
									}
									
								}else{
									echo "<script>alert('Invalid forum url!');</script>";
								}
							}
				*/							
			}
		}
		mysqli_close($link);
	}else{
		$header_arr["error"]="No permission for load this data!";
	}
}
if ($header_arr["success"]!=""){$header_arr["error"]="";}
$return_arr = array();
$return_arr[0]=$header_arr;
$return_arr[1]=$user_arr;
echo json_encode($return_arr);	

function DeleteUserAccount ($con, $uid){
	$uid=intval($uid);
	$stmt = $con->prepare("DELETE FROM users WHERE ID = ?");
	$stmt->bind_param('i', $uid);
	$stmt->execute(); 
	$stmt->close();
	$stmt = $con->prepare("DELETE FROM auth WHERE userid = ?");
	$stmt->bind_param('i', $uid);
	$stmt->execute(); 
	$stmt->close();
	$stmt = $con->prepare("DELETE FROM point WHERE uid = ?");
	$stmt->bind_param('i', $uid);
	$stmt->execute(); 
	$stmt->close();
	$stmt = $con->prepare("DELETE FROM usecashnow WHERE userid = ?");
	$stmt->bind_param('i', $uid);
	$stmt->execute(); 
	$stmt->close();
	$stmt = $con->prepare("DELETE FROM usecashlog WHERE userid = ?");
	$stmt->bind_param('i', $uid);
	$stmt->execute(); 
	$stmt->close();
	$stmt = $con->prepare("DELETE FROM forbid WHERE userid = ?");
	$stmt->bind_param('i', $uid);
	$stmt->execute(); 
	$stmt->close();
}
?>
