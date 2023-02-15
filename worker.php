<?php
// Start the session
session_start();
?>
<html>
<head>
<script>
if (window.location == window.parent.location){
	window.location.href = './worker.php?badluck=1';
}

function SendError(txt){
	var obj=parent.document.getElementById('Feedback_div');
	if (txt == " "){
		obj.innerHTML=txt;		
	}else{
		obj.innerHTML="<br>"+txt;		
	}
}
</script>
<?php

if (isset($_SESSION['rs'])) {
	if ($_SESSION['rs']==1){
		unset ($_SESSION['rs']);
		echo "<script>parent.window.location.href = './cpanel.php';</script>";
	}elseif($_SESSION['rs']==2){
		unset ($_SESSION['rs']);
		echo "<script>parent.window.location.href = './cpanel/myacc.php';</script>";		
	}
}
include "./config.php";
if (isset($_SESSION['UD1'])){
		if ($_SESSION['UD1'] != 1){
			die();
		}else{
			if (isset($_SESSION['UD2'])){
				if ($_SESSION['UD2'] != $AKey1){
					die();
				}
			}else{
				die();
			}
		}
}else{
	die();
}

if (isset($_GET['do'])){
	$do = $_GET['do'];
	if ($do=="login"){
	}elseif($do == "reg"){
	}elseif($do=="changeInfo"){
		if ($_SESSION['UD3'] != $AKey2){die();}
		if (isset($_SESSION['id'])){
			$d1 = $_GET['d1'];
			$d2 = $_GET['d2'];
			$d3 = $_GET['d3'];
			$d4 = $_GET['d4'];
			$d5 = $_GET['d5'];
			$d6 = $_GET['d6'];
			$d7 = $_GET['d7'];
			echo "<script>SendError(' ');</script>";
			//checkin
			$ListUsers=false;
			$expArr=explode("-", $d1);
			$CurUnam=StrToLower(Trim(stripslashes($expArr[0])));
			$CurUId=intval(StrToLower(Trim(stripslashes($expArr[1]))));
			$OldUnam=StrToLower(Trim(stripslashes($expArr[2])));
			$OldUId=intval(StrToLower(Trim(stripslashes($expArr[3]))));
			$counter[1]=count($expArr);
			$expArr=explode("-", $d2);
			$CurPw=Trim(stripslashes($expArr[0]));
			$NewPw1=Trim(stripslashes($expArr[1]));
			$NewPw2=Trim(stripslashes($expArr[2]));
			$counter[2]=count($expArr);
			$email=StrToLower(Trim(stripslashes($d3)));
			$rname=Trim(stripslashes($d4));
			$sex=intval(StrToLower(Trim(stripslashes($d5))));
			$bdate=Trim(stripslashes($d6));
			$expArr=explode("-", $d6);
			$counter[3]=count($expArr);
			$rank=intval(StrToLower(Trim(stripslashes($d7))));
			$changePw=false;
			$validNewPw=false;
			$passVer=true;
			$verifiedPw=false;
			$updatBDate=true;
			$newDateCheck=true;
			$un=$_SESSION['un'];
			$pw=$_SESSION['pw'];
			$uid=$_SESSION['id'];
			$ma=$_SESSION['ma'];
			
			$link = new mysqli($DB_Host, $DB_User, $DB_Password, $DB_Name);
			if ($link->connect_errno) {
				echo "<script>alert('Sorry, this website is experiencing problems (failed to make a MySQL connection)!');</script>";
				exit;
			}
			$admin=VerifyAdmin($link, $AdminId, $AdminPw);

			if (validate_Date($bdate)){
				if (($counter[1]==4)&&($counter[2]==3)&&($counter[3]==3)){
					if (((strlen($CurUnam)>4)&&(strlen($CurUnam)<21)&&(strlen($CurPw)>4)&&(strlen($CurPw)<21))||((strlen($CurUnam)>3)&&(strlen($CurUnam)<21)&&($admin)&&($CurPw=="")&&($uid!=$OldUId))){
						if (((ctype_alnum($CurUnam))&&(ctype_alnum($CurPw)))||((ctype_alnum($CurUnam))&&($CurPw=="")&&($admin)&&($uid!=$OldUId))){
							if (!(strlen($NewPw1)==0)){
								if ((strlen($NewPw1)>4)&&(strlen($NewPw1)<21)&&(strlen($NewPw2)>4)&&(strlen($NewPw2)<21)&&(ctype_alnum($NewPw1))&&(ctype_alnum($NewPw2))){
									if ($NewPw1==$NewPw2){
										$validNewPw=true;
										$changePw=true;
									}else{
										echo "<script>SendError('New password and password again must be same');</script>";
									}
								}else{
									echo "<script>SendError('New password & password again must be minimum 6 alphanumeric character');</script>";
								}
							}
						
							if ((($changePw)&& ($validNewPw)) or ($changePw===false)){
								if (validEmail($email)){
									if (($CurUId>15)&&($CurUId % 16 == 0)){	
										if ((($rank==0)||($rank==1))&&(($sex>=0)||($sex<3))){
											if ($PassType==1){
												$Salt1="0x".md5($OldUnam.$CurPw);
											}else if ($PassType==2){
												$Salt1=base64_encode(hash('md5',strtolower($OldUnam).$CurPw, true));
											}else if ($PassType==3){
												$Salt1="0x".md5($OldUnam.$CurPw);
											}
											if (!($admin)){$CurUnam=$OldUnam;$OldUId=$CurUId;}
											if (!($changePw)){
												$NewPw1=$CurPw;
											}
											if ($PassType==1){
												$Salt2="0x".md5($CurUnam.$NewPw1);
											}else if ($PassType==2){
												$Salt2=base64_encode(hash('md5',strtolower($CurUnam).$NewPw1, true));
											}else if ($PassType==3){
												$Salt2="0x".md5($CurUnam.$NewPw1);
											}
											$test2=0;
											if (($admin) && ($CurUId != $OldUId)){
												$test2=CountMysqlRows($link, 1, $CurUId);
											}
											$query = "SELECT ID, name, email, passwd, truename, birthday, gender FROM users WHERE ID=? AND name=?";
											$statement = $link->prepare($query);
											$statement->bind_param('is', $OldUId, $OldUnam);
											$statement->execute();
											$statement->bind_result($LID, $Lname, $Lmail, $LPw, $Lrname, $Lbday, $Lsex);
											$statement->store_result();
											$result = $statement->num_rows;
											$checkIdUsed=0;
											if (($admin) && ($CurUId != $OldUId)){
												$checkIdUsed=CountMysqlRows($link, 1, $CurUId);
											}
											if ($checkIdUsed==0){
												if ($result==1)	{
													while($statement->fetch()) {
													if (($admin) && ($uid != $OldUId)){
														$passVer=false;
														$verifiedPw=true;
													}
												
													if ($passVer){
														if ($PassType==1){
															if ($LPw==$Salt1){
																$verifiedPw=true;
															}else{
																echo "<script>SendError('Wrong username and password combination, don't cheat!');</script>";
															}
														}else if($PassType==2){
															if ($LPw==$Salt1){
																$verifiedPw=true;
															}else{
																echo "<script>SendError('Wrong username and password combination, don't cheat!');</script>";
															}
														}else if($PassType==3){	
															$LPw = addslashes($LPw);
															$rs=mysqli_query($link,"SELECT fn_varbintohexsubstring (1,'$LPw',1,0) AS result");
															$GetResult = mysqli_fetch_array($rs, MYSQLI_BOTH);
															$LDPw = $GetResult['result'];
															if ($LDPw==$Salt1){
																$verifiedPw=true;
															}else{
																echo "<script>SendError('Wrong username and password combination, don't cheat!');</script>";
															}														
														}
													}
													$expArr = explode("-", $bdate);
													if (($expArr[0] == "0000")||($expArr[1] == "00")||($expArr[2] == "00")){
														if ($bdate != "0000-00-00"){
															echo "<script>SendError('Wrong birth date, please select year, month and day!');</script>";
															$newDateCheck=false;
														}else{
															$updatBDate=false;
														}
													}
													
													if (($verifiedPw)&&($newDateCheck)){
														$Lrank=CountMysqlRows($link,5,$OldUId);
														$count7=0;
														$genderArr[0]="";
														$genderArr[1]="Male";
														$genderArr[2]="Female";
														$rankArr[0]="Member";
														$rankArr[1]="Game Master";

														//----------------------------------
														if ($Lmail != $email){
															$count7=1;
															if ($uid==$OldUId){$_SESSION['ma']=$email; $Lmail=$email;}
															echo"<script>parent.document.getElementById('AccInfoEm').innerHTML='".$email."';</script>";
														}
														if (($updatBDate) && ($newDateCheck)){
															$bdate=$bdate." 10:00:00";
															if ($Lbday != $bdate){
																$count7=1;
																$Lbday = $bdate;
																echo"<script>parent.document.getElementById('AccInfobd').innerHTML='".$bdate."';</script>";
															}
														}
														
														if ($Lrname != $rname){
															$count7=1;
															$Lrname = $rname;
															echo"<script>parent.document.getElementById('AccInfoRN').innerHTML='".$rname."';</script>";
														}
														if ($Lsex != $sex){
															$count7=1;
															$Lsex = $sex;
															echo"<script>parent.document.getElementById('AccInfoGe').innerHTML='".$genderArr[$sex]."';</script>";
															
														}
														if ($count7>0){
															$stmt = $link->prepare("UPDATE users SET email = ?, birthday = ?, truename = ?, gender = ? WHERE name=? AND ID=?");
															$stmt->bind_param('sssisi', $Lmail, $Lbday, $Lrname, $Lsex, $OldUnam, $OldUId);
															$stmt->execute(); 
															$stmt->close();
														}

														if ($rank != $Lrank){
															if (($rank==1)&&($Lrank==0)){
																$count7=1;
																//update member to gm
																$rs1=mysqli_query($link, "call addGM('$CurUId', '1')");
																echo"<script>parent.document.getElementById('AccInfoRa').innerHTML='".$rankArr[1]."';</script>";
															}elseif(($rank==0)&&($Lrank==1)){
																$count7=1;
																//downgade gm to member
																$stmt = $link->prepare("DELETE FROM auth WHERE userid = ?");
																$stmt->bind_param('i', $OldUId);
																$stmt->execute(); 
																$stmt->close();
																echo"<script>parent.document.getElementById('AccInfoRa').innerHTML='".$rankArr[0]."';</script>";
															}
															$ListUsers=true;
														}
														
														if ($CurUnam!=$OldUnam){
															$count7=1;
															if ($uid==$OldUId){$_SESSION['un']=$CurUnam;}
															//save username
															echo"<script>parent.document.getElementById('OldUnam').value='".$CurUnam."';</script>";
															$changePw=true;
															$stmt = $link->prepare("UPDATE users SET name = ? WHERE ID=?");
															$stmt->bind_param('si', $CurUnam, $OldUId);
															$stmt->execute(); 
															$stmt->close();
															echo"<script>parent.document.getElementById('AccInfoNa').innerHTML='".$CurUnam." [".$CurUId."]';</script>";
															$ListUsers=true;
														}
														
														if ($OldUId!=$CurUId){
															$count7=1;
															if ($uid==$OldUId){
																if ($admin){
																	$_SESSION['id']=$CurUId;
																	echo"<script>parent.document.getElementById('OldUId').value='".$CurUId."';</script>";
																	$filen='./config.php';
																	$fileno='./config_old.php';
																	$oldConfId = "AdminId=".$OldUId.";";
																	$newConfId = "AdminId=".$CurUId.";";
																	$str=file_get_contents($filen);
																	$str=str_replace($oldConfId, $newConfId, $str);
																	$oldConfId = 'AdminPw="'.$AdminPw.'";';
																	if ($PassType==1){
																		$newConfId = 'AdminPw="0x'.md5($CurUnam.$NewPw1).'";';
																	}elseif ($PassType==2){
																		$newConfId = 'AdminPw="'.base64_encode(hash('md5',strtolower($CurUnam).$NewPw1, true)).'";';
																	}elseif ($PassType==3){
																		$newConfId = 'AdminPw="'.md5($CurUnam.$NewPw1).'";';
																	}
																	
																	$str=str_replace($oldConfId, $newConfId, $str);
																	chmod($filen, 0777);
																	rename($filen, $fileno);
																	file_put_contents($filen, $str);
																	chmod($filen, 0755);
																}
															}
															//change id
															$stmt = $link->prepare("UPDATE users SET ID = ? WHERE ID=?");
															$stmt->bind_param('ii', $CurUId, $OldUId);
															$stmt->execute(); 
															$stmt->close();
															
															if (($admin) && ($CurUId != $OldUId) && ($rank == $Lrank) && ($Lrank==1)){
																//remove gm rank if old id was game account and re add to new id
																$stmt = $link->prepare("DELETE FROM auth WHERE userid = ?");
																$stmt->bind_param('i', $OldUId);
																$stmt->execute(); 
																$stmt->close();
																$rs1=mysqli_query($link, "call addGM('$CurUId', '1')");
															}
															$ListUsers=true;
														}
														
														if ($changePw){
															$count7=1;
															$_SESSION['pw']=$NewPw1;
															echo"<script>parent.document.getElementById('CurPwd').value='".$NewPw1."';</script>";
															echo"<script>parent.document.getElementById('AccInfoPw').innerHTML='".$NewPw1."';</script>";
															if ($PassType==3){
																mysqli_query ($link,"CALL changePasswd ('$CurUnam', $Salt2)");
															}else{
																mysqli_query ($link,"CALL changePasswd ('$CurUnam', '$Salt2')");
															}
															if (($admin)&&($uid==$CurUId)){
																$filen='./config.php';
																$fileno='./config_old.php';
																$str=file_get_contents($filen);
																$oldConfId = 'AdminPw="'.$AdminPw.'";';
																if ($PassType==1){
																	$newConfId = 'AdminPw="0x'.md5($CurUnam.$NewPw1).'";';
																}elseif ($PassType==2){
																	$newConfId = 'AdminPw="'.base64_encode(hash('md5',strtolower($CurUnam).$NewPw1, true)).'";';
																}elseif ($PassType==3){
																	$newConfId = 'AdminPw="'.md5($CurUnam.$NewPw1).'";';																	
																}
																
																$str=str_replace($oldConfId, $newConfId, $str);
																chmod($filen, 0777);
																rename($filen, $fileno);
																file_put_contents($filen, $str);
																chmod($filen, 0755);
															}
														}
														
														if ($count7>0){echo "<script>SendError('Done!');</script>";}else{echo "<script>SendError('Unchanged!');</script>";}
														if ($ListUsers !== false){ListUsers ($link, 1, "" );}
														//$_SESSION['rs']=2;
														//header("Location: ".$_SERVER['PHP_SELF']);
														//die();
													}else{
														echo "<script>SendError('Something went wrong! Maybe birthday not setted?');</script>";
													}
														
												}
												}else{
													echo "<script>SendError('Not exist this user with this username or id!');</script>";
												}
											}else{
												echo "<script>SendError('This user id is in use! Choose another!');</script>";
											}
											$statement->close();
										}else{
											echo "<script>SendError('Invalid data!');</script>";
										}
									}else{
										echo "<script>SendError('Incorrect id, your id must be mutiples of 16. Example: 16, 32, 48, 64, 80....');</script>";
									}
								}else{
									echo "<script>SendError('Incorrect email address, please type your email address!');</script>";
								}
							}else{
								echo "<script>SendError('Leave new password field blank or use a valid password with atleast 6 alphanumeric character!');</script>";
							}
						}else{
							echo "<script>SendError('Username and password must have alphanumeric characters!');</script>";
						}
					}else{
						
						echo "<script>SendError('Username and password must be minimum 6 character!');</script>";
					}
				}else{
					echo "<script>SendError('Invalid data');</script>";
				}
			}else{
				echo "<script>SendError('Invalid birth date data');</script>";
			}
		}
	}elseif($do=="loadId"){
		if ($_SESSION['UD3'] != $AKey2){die();}
		if (isset($_GET['id'])){
			$id=intval(trim($_GET['id']));
			if ($id > 15){
				$un=$_SESSION['un'];
				$pw=$_SESSION['pw'];
				$uid=$_SESSION['id'];
				$ma=$_SESSION['ma'];
				if (($uid==$AdminId)||($uid==$id)){
					$link = new mysqli($DB_Host, $DB_User, $DB_Password, $DB_Name);
					$Admin=VerifyAdmin($link, $AdminId, $AdminPw);
					if (($Admin)||($uid==$id)){
						$query = "SELECT ID, name, truename, email, birthday, creatime, gender, idnumber, VotePoint, VoteDates FROM users WHERE ID=?";
						$statement = $link->prepare($query);
						$statement->bind_param('i', $id);
						$statement->execute();
						$statement->bind_result($LID, $LName, $Lrname, $Lmail, $Lbday, $LRegDate, $Lsex, $LIpAddr, $LWebPoint, $LVC);
						$LAvatar="";
						$statement->store_result();
						$result = $statement->num_rows;
						$LastVotes = "";
						$WPoint = 0;
						if (!$result) {
							echo "<script>alert('User not exist');</script>";
							exit;
						}else{
							while($statement->fetch()) {
								$WPoint=$LWebPoint;
								$LastVotes=$LVC;
								$LbDateArr=explode(" ", $Lbday);
								$LbDateArr=explode("-", $LbDateArr[0]);
								$Lrank=CountMysqlRows ($link, 5, $id);
								$LPw="";
								if ($uid==$id){$LPw=$pw;}
								$genderArr[0]="";
								$genderArr[1]="Male";
								$genderArr[2]="Female";
								$rankArr[0]="Member";
								$rankArr[1]="Game Master";
								$LName_Ext=$LName." [".$id."]";
								echo"<script>
								parent.document.getElementById('AccInfoBanRow').style.display='none';  
								parent.document.getElementById('AccInfoZone').style.display='none';  
								parent.document.getElementById('AccInfoAv').innerHTML='';
								parent.document.getElementById('AccInfoNa').innerHTML='".$LName_Ext."';
								parent.document.getElementById('AccInfoRN').innerHTML='".$Lrname."';
								parent.document.getElementById('AccInfoPw').innerHTML='".$LPw."';
								parent.document.getElementById('AccInfoEm').innerHTML='".$Lmail."';
								parent.document.getElementById('AccInfoGe').innerHTML='".$genderArr[$Lsex]."';
								parent.document.getElementById('AccInfobd').innerHTML='".$Lbday."';
								parent.document.getElementById('AccInfoRa').innerHTML='".$rankArr[$Lrank]."';
								parent.document.getElementById('AccInfoRD').innerHTML='".$LRegDate."';
								parent.document.getElementById('AccInfoIp').innerHTML='".$LIpAddr."';
								parent.document.getElementById('AccInfoWP').innerHTML='".$LWebPoint."';
								parent.ExchRate='".$VoteExc."';
								parent.ExchPoint='".$LWebPoint."';
								parent.ExchMaxG='0';
								parent.document.getElementById('CurUnam').value='".$LName."';
								parent.document.getElementById('CurUId').value='".$id."';
								parent.document.getElementById('OldUnam').value='".$LName."';
								parent.document.getElementById('OldUId').value='".$id."';
								parent.document.getElementById('CurPwd').value='".$LPw."';
								parent.document.getElementById('NewPwd1').value='".$LPw."';
								parent.document.getElementById('NewPwd2').value='".$LPw."';
								parent.document.getElementById('Mail').value='".$Lmail."';
								parent.document.getElementById('RealName').value='".$Lrname."';
								var LGender=parseInt('".$Lsex."', 10);
								var LYear=parseInt('".$LbDateArr[0]."', 10);
								var LMonth=parseInt('".$LbDateArr[1]."', 10);
								var LDay=parseInt('".$LbDateArr[2]."', 10);
								var LRank=parseInt('".$Lrank."', 10);
								var cYear=parent.document.getElementById('dob-year').options[2].value;
								parent.document.getElementById('gender_male').checked=false;
								parent.document.getElementById('gender_female').checked=false;
								if (LGender==1){
									parent.document.getElementById('gender_male').checked=true;
								}else if (LGender==2){
									parent.document.getElementById('gender_female').checked=true;
								}
								if (LYear==1){LYear=0;}
								if (LMonth==1){LMonth=0;}
								if (LDay==1){LDay=0;}
								if (LYear>1){LYear=cYear-LYear+2;}
								if (LMonth>1){LMonth=LMonth+1;}
								if (LDay>1){LDay=LDay+1;}
								parent.document.getElementById('dob-year').selectedIndex=LYear;
								parent.document.getElementById('dob-month').selectedIndex=LMonth;
								parent.document.getElementById('dob-day').selectedIndex=LDay;
								parent.document.getElementById('mstat').selectedIndex = LRank;
								</script>";
							}   
						}
						$statement->close();

						if ($uid==$id){
							$unitPrice = intval($VoteExc / 100);
							echo"<script>
								parent.document.getElementById('PExchLink').style.display = 'none';
								parent.document.getElementById('ExchGMinRes').innerHTML = '1';
								parent.document.getElementById('ExchGMaxRes').innerHTML = '1';
								parent.document.getElementById('ExchPCost').innerHTML = '".$VoteExc."';
							</script>";	
							if (intval($VoteExc) > 0){
								$AvaiExch = intval($WPoint/$VoteExc);		//we have enough point for atleast 1 exchange
								if ($AvaiExch > 0){
									$ExchMin = 1;
									$ExchMax = $AvaiExch;
									echo"<script>
									parent.document.getElementById('PExchLink').style.display = 'block';
										parent.document.getElementById('ExchGMaxRes').innerHTML = '".$AvaiExch."';
										parent.ExchMaxG='".$ExchMax."';
									</script>";		
								}
							}
							if (($VoteButton==true)&&($VoteCount>0)&&($VoteInterval>0)){
								$VoteLastVotes = validDateStack($LastVotes, $VoteUrl);
								for ($i = 1; $i <= sizeof($VoteUrl); $i++) {
									if (strlen($VoteUrl[$i]) > 3){
										$secDiff = $VoteInterval*3600 - DateDifference ($VoteLastVotes, $i);
										echo"<script>
											parent.NewVoteTimer($secDiff, $i);
											</script>";
									}
								}
							}
							
						}
				
						//AccInfoLL - AccInfoBanRow AccInfoLA 
						//if online then zoneid =1 zonelocalid =17 - west banker - 26thouand stream
						//forbid 2 sec work
						//100 sign in 101 Forbid Talking 102 Forbid Trade Among Players 103 Forbid Selling
						
						$query = "SELECT lastlogin, zoneid, zonelocalid FROM point WHERE uid=?";
						$statement = $link->prepare($query);
						$statement->bind_param('i', $id);
						$statement->execute();
						$statement->bind_result($lastlog, $zoneid, $zonelid);
						$statement->store_result();
						$result = $statement->num_rows;
						if ($result>0) {
							while($statement->fetch()) {

								echo "<script>
								parent.document.getElementById('AccInfoLL').innerHTML='".$lastlog."';
								</script>";
								if ((intval($zoneid))>0){
									echo "<script>
									parent.document.getElementById('AccInfoStatus').innerHTML='<font color=\'#66ee00\'>Online</font>';
									parent.document.getElementById('AccInfoZId').innerHTML='Zone: ".$zoneid." - Location: ".$zonelid."';								
									parent.document.getElementById('AccInfoZone').style.display='block'; 	
									</script>";
								}else{
									echo "<script>
									parent.document.getElementById('AccInfoStatus').innerHTML='<font color=\'#ee0000\'>Offline</font>';
									</script>";
								}
							}
						}
						$statement->close();
						$query = "SELECT cash, fintime FROM usecashlog WHERE userid=?";
						$statement = $link->prepare($query);
						$statement->bind_param('i', $id);
						$statement->execute();
						$statement->bind_result($cash, $fintime);
						$statement->store_result();
						$result = $statement->num_rows;
						echo"<script>
						var table = parent.document.getElementById('GoldLogTable');
						var row;
						var cell;
						table.innerHTML = '';
						</script>";
						if (!$result) {
							echo "<script>
							row = table.insertRow(-1);
							cell = row.insertCell(0);
							cell.style.textAlign='center';
							cell.colSpan = '2';
							cell.innerHTML='<i>... You have no transaction history ...</i>';
							</script>";
						}else{
							echo "<script>
								row = table.insertRow(-1);
								cell = row.insertCell(0);
								cell.style.textAlign='center';
								cell.innerHTML='<b><u> Gold Amount</u></b>';
								cell = row.insertCell(1);
								cell.style.textAlign='center';
								cell.innerHTML='<b><u> When Reicived</u></b>';
								</script>";		
							while($statement->fetch()) {
								echo "<script>
								row = table.insertRow(-1);
								cell = row.insertCell(0);
								cell.innerHTML='<b>".$cash."</b>';
								cell = row.insertCell(1);
								cell.innerHTML='".$fintime."';
								</script>";								
							}
						}
						$statement->close();	
						//read characters
						echo"<script>
						table = parent.document.getElementById('CharList');
						table.innerHTML = '';
						</script>";
						include("./cpanel/packet_class.php");
						$CharCount=0;
						$GetUserRolesArg = new WritePacket();
						$GetUserRolesArg -> WriteUInt32(-1); // always
						$GetUserRolesArg -> WriteUInt32($id); // userid
						$GetUserRolesArg -> Pack(0xD49);//0xD49
						if ($GetUserRolesArg -> Send("localhost", 29400)){ // send to gamedbd
							//return;
							$GetUserRolesRes = new ReadPacket($GetUserRolesArg); // reading packet from stream
							$GetUserRolesRes -> ReadPacketInfo(); // read opcode and length
							$GetUserRolesRes -> ReadUInt32(); // always
							$GetUserRolesRes -> ReadUInt32(); // retcode
							$CharCount = $GetUserRolesRes -> ReadCUInt32();
														
							for ($i = 0; $i < $CharCount; $i++){
								$roleid = $GetUserRolesRes -> ReadUInt32();
								$rolename = $GetUserRolesRes -> ReadUString();
								
								$GetRoleBase = new WritePacket();
								$GetRoleBase -> WriteUInt32(-1); // always
								$GetRoleBase -> WriteUInt32($roleid); // userid
								$GetRoleBase -> Pack(0x1F43); // opcode  

								if (!$GetRoleBase -> Send("localhost", 29400)) // send to gamedbd
								return;

								$GetRoleBase_Re = new ReadPacket($GetRoleBase); // reading packet from stream
								$packetinfo = $GetRoleBase_Re -> ReadPacketInfo(); // read opcode and length
			
								$GetRoleBase_Re -> ReadUInt32(); // always
								$GetRoleBase_Re -> ReadUInt32(); // retcode
								$GetRoleBase_Re -> ReadUByte();
								$GetRoleBase_Re -> ReadUInt32();
								$GetRoleBase_Re -> ReadUString();
								$GetRoleBase_Re -> ReadUInt32();
								$roleCls = cls2class($GetRoleBase_Re -> ReadUInt32());
								$GetRoleBase_Re -> ReadUByte();
								$GetRoleBase_Re -> ReadOctets();
								$GetRoleBase_Re -> ReadOctets();
								$GetRoleBase_Re -> ReadUInt32();
								$GetRoleBase_Re -> ReadUByte();
								$roleDelTime = $GetRoleBase_Re -> ReadUInt32();
								$GetRoleBase_Re -> ReadUInt32();
								$roleLastLogin = $GetRoleBase_Re -> ReadUInt32();
								$forbidcount = $GetRoleBase_Re -> ReadCUInt32();
								for ($x = 0; $x < $forbidcount; $x++){
									$GetRoleBase_Re -> ReadUByte();
									$GetRoleBase_Re -> ReadUInt32();
									$GetRoleBase_Re -> ReadUInt32();
									$GetRoleBase_Re -> ReadUString();
								}
								$GetRoleBase_Re -> ReadOctets();
								$GetRoleBase_Re -> ReadUInt32();
								$GetRoleBase_Re -> ReadUInt32();
								$GetRoleBase_Re -> ReadOctets();
								$GetRoleBase_Re -> ReadUByte();
								$GetRoleBase_Re -> ReadUByte();
								$GetRoleBase_Re -> ReadUByte();
								$GetRoleBase_Re -> ReadUByte();
								$roleLevel = $GetRoleBase_Re -> ReadUInt32();
								$roleCulti = $GetRoleBase_Re -> ReadUInt32();
								$roleClass = $PWclass[$roleCls];

								if (($roleCulti > 19) && ($roleCulti<23)){
									$roleClass = $PWclsPath[1]." ".$roleClass;
								}elseif(($roleCulti>29)&&($roleCulti<33)){
									$roleClass = $PWclsPath[2]." ".$roleClass;
								}
								
								$roleClass = $roleClass." (".$roleLevel.")";
								if ($forbidcount > 0){
									$roleClass = $roleClass." [Ban]";
								}
								if ($roleDelTime > 0){
									$roleClass = $roleClass." [Del]";
								}								
								if (isset($PWclass[$roleCls])){
									if ($Admin != false){
										echo "<script>
										row = table.insertRow(-1);
										
										cell = row.insertCell(0);
										cell.innerHTML='<a href=\'javascript:void(0);\' title=\'Edit character\' onclick=\'alert($roleid);\' style=\'text-decoration:none;\'>$rolename [$roleid]</a>';
									
										
										cell = row.insertCell(1);
										cell.innerHTML='".$roleClass."';
										</script>";									
									}else{
										echo "<script>
										row = table.insertRow(-1);
										
										cell = row.insertCell(0);
										cell.innerHTML='".$rolename."';
										
										cell = row.insertCell(1);
										cell.innerHTML='".$roleClass."';
										</script>";										   
									}
								}
							}
						}
						
						echo "<script>parent.document.getElementById('AccInfoCI').innerHTML='".$CharCount."';</script>";
						
						
						if ($CharCount == 0){
							echo "<script>
							row = table.insertRow(-1);
							cell = row.insertCell(0);
							cell.style.textAlign='center';
							cell.colSpan = '2';
							cell.innerHTML='<i>... You have no character ...</i>';
							</script>";							
						}
						//--------------------------------

					}
				}
				mysqli_close($link);
			}else{
				echo "<script>alert('User id must be between n*16 where n > 0');</script>";
			}
		}
	}elseif($do=="logout"){

	}elseif ($do=="admintool"){

	}elseif($do=="VoteNCheck"){	
		if ($_SESSION['UD3'] != $AKey2){die();}
		if ((isset($_GET['id'])) && (isset($_SESSION['id']))){
			
			$vId = intval($_GET['id']);
			if (($vId <= count($VoteUrl))&&($vId > 0)){
				$VoteLink = $VoteUrl[$vId];
				if (strlen($VoteLink) > 3){
					
					$un=$_SESSION['un'];
					$pw=$_SESSION['pw'];
					$uid=$_SESSION['id'];
					$ma=$_SESSION['ma'];	
					$sepChar = ",";					
					$link = new mysqli($DB_Host, $DB_User, $DB_Password, $DB_Name);
					if ($link->connect_errno) {
						echo "<script>alert('Sorry, this website is experiencing problems (failed to make a MySQL connection)!');</script>";
						exit;
					}						
					$statement = $link->prepare("SELECT VotePoint, VoteDates FROM users WHERE name=? AND email=? AND ID=?");
					$statement->bind_param('ssi', $un, $ma, $uid);
					$statement->execute();
					$statement->bind_result($VPoint, $VDates);
					$statement->store_result();
					$count = $statement->num_rows;
					$Redir = "";
						if($count==1){
							while($statement->fetch()) {
								$VDates=validDateStack($VDates, $VoteUrl);
								$expArr = explode($sepChar, $VDates);
								$VoteIntSec = 3600*$VoteInterval;
								$TIME=date("Y-m-d H:i:s");
								if (count($expArr) < $vId){
									echo "<script>alert('Something wrong, the vote site not found!');</script>";
								}else{
									$secDiff = $VoteIntSec - DateDifference ($expArr[$vId-1], $vId);
									if ($secDiff < 0){
										if (($VPoint>($MaxWebPoint-$VoteReward))&&($VoteFor==1)){
											echo "<script>alert('You reeachedte maximal vote point, we cannot add more point, spend it on something!');</script>";							
											$Vpoint = $VPoint-$VoteReward;
										}
										//$VoteFor =1 point, =2 gold, we add gold or point
										$Vpoint = $VPoint+$VoteReward;
										if ($VoteFor == 1){
											$query = "UPDATE users SET VotePoint=$Vpoint WHERE ID=?";
											$stmt = $link->prepare($query);
											$stmt->bind_param('i', $uid);
											$stmt->execute(); 
											$stmt->close();			
											$VoteIntHour = 3600*$VoteInterval;
										}elseif ($VoteFor == 2){
											$nr0 = 0;
											$nr1 = 1;
											$stmt = $link->prepare("INSERT INTO usecashnow (userid, zoneid, sn, aid, point, cash, status, creatime) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
											$stmt->bind_param("iiiiiiis", $uid, $nr1, $nr0, $nr1, $nr0, $VoteReward, $nr1, $TIME);
											$stmt->execute(); 
											$stmt->close();											
										}
										if (($VoteFor == 1) or ($VoteFor == 2)){
											echo"<script>
											parent.NewVoteTimer($VoteIntSec, $vId);
											window.parent.location
											</script>";				
											$Redir = $VoteUrl[$vId];
											$expArr[$vId-1] = $TIME;
											$VDates=implode(",",$expArr);
											$query = "UPDATE users SET VoteDates=? WHERE ID=?";
											$stmt = $link->prepare($query);
											$stmt->bind_param('si', $VDates, $uid);
											$stmt->execute(); 
											$stmt->close();	
										}
									}else{
										$rHour = intval($secDiff / 3600);
										$rMin = intval(($secDiff % 3600)/60);
										$rSec = $secDiff % 60;
										echo "<script>alert('Need wait: $rHour:$rMin:$rSec !');</script>";
									}
								}
							}
							$statement->close();
						}else{
							echo "<script>alert('Try relog!');</script>";
							//nincs ilyen user
						}
					mysqli_close($link);
					if ($Redir != ""){
						echo"<script>window.parent.parent.location = '".$Redir."';</script>";						
					}
				}
			}else{
				echo "<script>alert('Invalid vote id!');</script>";
			}
		}
	}elseif($do=="ExchangePoints"){	
		if ($_SESSION['UD3'] != $AKey2){die();}
		if ((isset($_GET['amount'])) && (isset($_SESSION['id']))){
			
			$amount = intval($_GET['amount']);
			$uid = $_SESSION['id'];
			$link = new mysqli($DB_Host, $DB_User, $DB_Password, $DB_Name);
			if ($link->connect_errno) {
				echo "<script>alert('Sorry, this website is experiencing problems (failed to make a MySQL connection)!');</script>";
				exit;
			}						
			$statement = $link->prepare("SELECT VotePoint, creatime FROM users WHERE ID=?");
			$statement->bind_param('i', $uid);
			$statement->execute();
			$statement->bind_result($VPoint, $TIME);
			$statement->store_result();
			$count = $statement->num_rows;
			if($count==1){
				while($statement->fetch()) {
					$PCost = $amount * $VoteExc;
					if ($VPoint >= $PCost){
						$diff = $VPoint - $PCost;
						$query = "UPDATE users SET VotePoint=? WHERE ID=?";
						$stmt = $link->prepare($query);
						$stmt->bind_param('ii', $diff, $uid);
						$stmt->execute(); 
						$stmt->close();	
						$amount = $amount * 100; //100 silver = 1 gold
						$query="INSERT INTO usecashnow (userid, zoneid, sn, aid, point, cash, status, creatime) VALUES ('$uid', '1', '0', '1', '0', '$amount', '1', '$TIME') ON DUPLICATE KEY UPDATE cash = cash + $amount";
						$stmt = $link->prepare($query);
						$stmt->execute(); 
						$stmt->close();
						if ($diff < $VoteExc){
							echo "<script>
								parent.document.getElementById('ExchGMinRes').innerHTML = '0';
								parent.document.getElementById('ExchGMaxRes').innerHTML = '0';
								parent.document.getElementById('ExchGAmount').value = '0';
								parent.document.getElementById('ExchPCost').innerHTML = '0';
								parent.ExchMaxG='0';
							</script>";							
						}else{
							$MaxAvail = intval($diff / $VoteExc);
							echo "<script>
								parent.document.getElementById('ExchGMinRes').innerHTML = '1';
								parent.document.getElementById('ExchGMaxRes').innerHTML = '".$MaxAvail."';
								parent.document.getElementById('ExchGAmount').value = '1';
								parent.document.getElementById('ExchPCost').innerHTML = '".$VoteExc."';
								parent.ExchMaxG='".$MaxAvail."';
							</script>";								
						}
						echo "<script>
								parent.document.getElementById('AccInfoWP').innerHTML='".$diff."';
								parent.ExchPoint='".$diff."';
						</script>";
					}else{
						$diff = $PCost - $VPoint;
						echo "<script>alert('You dont have enough point, you need $PCost point !');</script>";
					}
					
				}
			}
			$statement->close();
			mysqli_close($link);

		}
	}elseif ($do=="UserFilter"){

	}
	echo'<script>window.location.href = "worker.php";</script>';
}
?>
</head>
<body>
<?php
function validDateStack($DateStack, &$VoteUrl){
	$sepChar = ",";
	$expDate = "2016-12-01 01:00:00";
	$cdatestack=0;
	$max = count($VoteUrl);
	$newStack="";
	if (strlen($DateStack) >= strlen($expDate)){
		if (strpos($DateStack, $sepChar) !== false) {
			$expArr = explode($sepChar, $DateStack);
			$sMax = count($expArr);
			if (validateDate($expArr[0])){
				$newStack = $expArr[0];
			}else{
				$newStack = $expDate;
			}				
			for ($i = 2; $i <= $max; $i++) {
				if ($i <= $sMax){
					$tmp=$expArr[($i-1)];
					if (validateDate($tmp)){
						$newStack = $newStack.$sepChar.$tmp;
					}else{
						$newStack = $newStack.$sepChar.$expDate;
					}
				}else{
					$newStack = $newStack.$sepChar.$expDate;
				}
			}		
		}else{
			if (validateDate($DateStack)){
				if ($max==1){
					return $DateStack;
				}else{
					$newStack = $DateStack;
					for ($i = 2; $i <= $max; $i++) {
						$newStack = $newStack.$sepChar.$expDate;
					}
				}
			}else{
				$newStack = $expDate;
				if ($max>1){
					for ($i = 2; $i <= $max; $i++) {
						$newStack = $newStack.$sepChar.$expDate;
					}		
				}				
			}			
		}
		
		return $newStack;
	}else{
		$newStack = $expDate;
		if ($max>1){
			for ($i = 2; $i <= $max; $i++) {
				$newStack = $newStack.$sepChar.$expDate;
			}		
		}
		return $newStack;
	}
}

function validateDate($date, $format = 'Y-m-d H:i:s'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function format_interval(DateInterval $interval) {
	$result = 0;
	if ($interval->y) { 
		$tmp=intval($interval->format("%y"));
		if ($tmp > 5){
			return (5*31536000);
		}else{
			$result = $result + $tmp*31536000; 
		}
	}
	if ($interval->m) { $result = $result + intval($interval->format("%m"))*2592000; }
	if ($interval->d) { $result = $result + intval($interval->format("%d"))*86400; }
	if ($interval->h) { $result = $result + intval($interval->format("%h"))*3600; }
	if ($interval->i) { $result = $result + intval($interval->format("%i"))*60; }
	if ($interval->s) { $result = $result + intval($interval->format("%s")); }
	return intval($result);
}

function DateDifference($DateStack, $Index){
	$CurDate = date("Y-m-d H:i:s");
	$sepChar = ",";
	$difference = 0;
	if (strlen($DateStack) < strlen($CurDate)){
		return 31536000;
	}elseif((strlen($DateStack) == 19)&&($Index==1)){
		$first_date = new DateTime($DateStack);
		$second_date = new DateTime($CurDate);
		$difference = $first_date->diff($second_date);
		$res_sec = format_interval($difference);
		return $res_sec;	
	}else{
		if (strpos($DateStack, $sepChar) !== false) {
			$expArr = explode($sepChar, $DateStack);
			if ($Index > count($expArr)){
				return 31536000;
 			}else{
				$first_date = new DateTime($expArr[($Index-1)]);
				$second_date = new DateTime($CurDate);
				$difference = $first_date->diff($second_date);
				$res_sec = format_interval($difference);
				return intval($res_sec);		
			}
		}else{
			return 31536000;
		}
	}
}	








function ValidId($id){
	$id=intval($id);
	if (($id>15)&&($id % 16 == 0)){
		return true;
	}else{
		return false;
	}
}










?>
</body>
</html>
