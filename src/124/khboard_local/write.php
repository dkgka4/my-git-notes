<?php
	require($_SERVER['DOCUMENT_ROOT'] . "/khboard/inc/bbs_setting.php");
	
	//$md5_listtarget = substr($_REQUEST['q'],0,32);
	//$md5_bcode = substr($_REQUEST['q'],32,64);
	
	$md5_listtarget = substr($_REQUEST['q'],0,32);
	$md5_bcode = substr($_REQUEST['q'],32,32);
	$secretmodyn= substr($_REQUEST['q'],64,1);	//비밀번호 입력 성공시 수정 삭제 권한을 줌	
	$ploCode = (isset($_REQUEST['ploCode']) ? $_REQUEST['ploCode'] : "");
	
	
	$bbscasBy = "";
	if($md5_listtarget == md5('read')) $bbscasBy = "read";
	else  $bbscasBy = "list";
	
	//sms전송번호 설정으로 처리 phoneNumber,ban -- 미사용 테이블 재사용
//	$smsNumberResult = $db->query("select phoneNumber,ban from SMStelList;");
	$smsListArr = array(); //메뉴별 지정번호 배열(상벌점,취업관리,기숙사 등)
//	while ($row_smsnum = $smsNumberResult->fetch_array()){
//		if($row_smsnum['ban']) $smsListArr[$row_smsnum['ban']] = $row_smsnum['phoneNumber'];
//	}		


	// editor적용 
	$editorApllyBolArray =  array(
		"sn" => false,
		"high" => true,
		"jung" => true,
		"univ" => false
	);
	
	
//print_r($settingOptArray);

  	if(WriteAuth() != "Y") {
  		echo "
  		<div style='width:100%;text-align:center;padding:10% 0;'>
			<svg xmlns='http://www.w3.org/2000/svg' height='40px' viewBox='0 -960 960 960' width='40px' fill='#bf0000' style='width:80px;height:80px;'><path d='m40-120 440-760 440 760H40Zm115.33-66.67h649.34L480-746.67l-324.67 560ZM482.78-238q14.22 0 23.72-9.62 9.5-9.61 9.5-23.83 0-14.22-9.62-23.72-9.61-9.5-23.83-9.5-14.22 0-23.72 9.62-9.5 9.62-9.5 23.83 0 14.22 9.62 23.72 9.62 9.5 23.83 9.5Zm-33.45-114H516v-216h-66.67v216ZM480-466.67Z'/></svg>
  			<div>쓰기 권한이 없습니다.</div>
  		</div>";
  		exit;
  	}
  	
?>                                                                               



<style>
#button{
  display:block;
  margin:20px auto;
  padding:10px 30px;
  background-color:#eee;
  border:solid #ccc 1px;
  cursor: pointer;
}
#upload_overlay{	
  position: absolute;
  top: 0;
  bottom:0;
  left:0;right:0;
  z-index: 100;
  width: 100%;
  height:100%;
  display: none;
  background: rgba(0,0,0,0.6);
}
.upload-cv-spinner {
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;  
}
.upload-spinner {
  width: 40px;
  height: 40px;
  border: 4px #ddd solid;
  border-top: 4px #2e93e6 solid;
  border-radius: 50%;
  animation: sp-anime 0.8s infinite linear;
}
@keyframes sp-anime {
  100% { 
    transform: rotate(360deg); 
  }
}
.is-hide{
  display:none;
}
</style>
<div id="upload_overlay">
  <div class="upload-cv-spinner">
    <div class="upload-spinner"></div>
    <div style='color:#fff;'>업로드중</div>
  </div>
</div>


<?php
	if ($ploCode == "sn") {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_sn_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_sn_comm.css" />
<?php
	} else if ($ploCode == "jung" || $ploCode == "high") {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_high_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_high_comm.css" />
<?php
	} else if ($ploCode == "univ") {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_univ_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_univ_comm.css" />
<?php
	} else {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_comm.css" />
<?php
	}
?>

<div style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.6);z-index:9999;display:none;" id="saveloading">
	<div class="khloading-container">
	    <div class="khloading"></div>
	    <div id="khloading-text">저장중..</div>
	</div>
</div>


<form name='editor_frm' id='editor_frm' enctype = "multipart/form-data" style="margin:0 auto;">
	<input type='hidden' name="board_code" id="board_code" value='<?= $board_code ?>'>	
	<input type='hidden' name="board_sub" id="board_sub" value='<?= $board_sub ?>'>	
	<input type="hidden" id="lohref" value="q=<?= md5('list')?>&<?=$add_url_param?>">
	<div>
	<table style='width:100%; margin-bottom:10px;' class="bbstable" summary="내용입력" cellpadding="0" cellspacing="0">
<?php
//유형별 게시판 카테고리 보기(유형별 게시판 분리, 카테고리 설정과 다름, 관리자 설정 확인)
if($board_sub == "" && (is_array($bbsSubCodeArray[$board_code]) && sizeof($bbsSubCodeArray[$board_code]) > 0) && $settingOptArray['유형별게시판분리'] == "Y"){
	$ctgOption="";
	foreach ($bbsSubCodeArray[$board_code] as $kkey => $kvalue) {
		$ctgOption .="<option value='" . $kvalue['CODE'] . "'>" . $kvalue['TITLE'] . "</option>";
	}
	echo "
	<tr>
		<th style='text-align:center;color:red;'>구분 *</th>
		<td style='text-align:left;'>
			<select name='typeCategoryAdd'>
				" . $ctgOption . "
			</select>
			<div style='color:#bf0000;font-size:14px'>- 문의할 대상을 꼭 확인해 주세요.</div>
		</td>
	</tr>
	";
}


	@ksort($bbswriteArray);
	foreach ($bbswriteArray as $key => $value) {
		foreach ($value as $ky => $val) {
			//echo $ky."<br>";
			//글쓰기시 답변상태는 나타나지 않습니다.
			if($ky == "29") continue;
			
			echo "<tr>";
			if($ky != "21") echo "<th style='text-align:center; width:150px;'>" . $val['이름'] . "" . ($val['필수'] == "Y" ? "<span style='color:red;'> *</span>":"") . "</th>"; //내용이 아니면
			
			//장문입력 받기
			if($val['타입'] == "TL") {
				echo "<td><textarea style='width:100%;height:70px;border:1px solid #ddd;' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='textarea' valid.text='" . $val['이름'] . "'></textarea></td>";
			}
			//라디오 타입 입력 받기
			else if($val['타입'] == "RR") {
				$radio_exp = explode(",",$val['타입값']);
				echo "<td style='text-align:left;'>";
				foreach ($radio_exp as $k => $v) echo "<input type='radio' id='data_" . $ky . "' name='data_" . $ky . "' value='" . $v . "'  " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='radio' valid.text='" . $val['이름'] . "'> " . $v . " ";
				echo "</td>";
			}
			//셀렉트 타입 입력 받기
			else if($val['타입'] == "RS") {
				$selectbox_exp = explode(",",$val['타입값']);
				$option = "";
				foreach ($selectbox_exp as $k => $v) $option .= "<option  value='" . $v . "'>" . $v . "</option>";
				echo "<td style='text-align:left;'>";
				echo "<select style='width:100%;border:1px solid #ddd;' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='select' valid.text='" . $val['이름'] . "'>" . $option . "</select>";
				echo "</td>";
				
			}
			//체크박스 타입 입력 받기
			else if($val['타입'] == "CC") {
				$checkbox_exp = explode(",",$val['타입값']);
				
			}
			else{
				//TS
				//메인글쓰기
				if($ky == "17"){
					echo "
					<td>
						<input type='text' value='" . $sess_Name . "' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass textInputClass' " : " class='textInputClass ") . " valid.inputtype='text' valid.text='" . $val['이름'] . "' style='font-size:16px;padding:7px;border:1px solid #ddd;'>
					</td>";
				}
				else if($ky == "21"){
					
					$fileaddhtml = "";
					if($bbswriteArray['25']['글쓰기'] == "N") 
					{
						$fileaddhtml = "
						<div style='text-align:left;'>
							<div style='width:100%; margin-top:6px; text-align:left; display:n1one;' id='uploadTxtshow'>업로드 : 
								<span id='showuploadList' style='width:100%;'></span>
							</div>
						</div>";
					}
										
					echo "
					<th style='text-align:center;'>글입력</th>
					<td style='padding:0px;'>
						<input type='hidden' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='editor' valid.text='" . $val['이름'] . "'>
						<div style='position:relative;z-index:999;'>
							<textarea  id='data_" . $ky . "'  name='data_" . $ky . "' style='border:1px solid; border-color:#cccccc; padding:2px; width:100%; height:auto; margin:0px;' title='본문내용입력'></textarea>
						</div>
						" . $fileaddhtml . "
					</td>";
				}
				//파일 업로드
				else if($ky == "25") {
					echo "
					<td style='text-align:left;'>
						<input type='hidden' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='etcfile' valid.text='" . $val['이름'] . "'>
						<div class='bbs_mbutton' onclick='fileImg_upload(1)' style='width:130px;border:1px solid #aaa;'>
							<svg xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 -960 960 960' width='24px' style='vertical-align:middle;' fill='#5f6368'><path d='M440-320v-326L336-542l-56-58 200-200 200 200-56 58-104-104v326h-80ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z'/></svg> 
							파일업로드
						</div>
						<div style='width:100%; margin-top:6px; text-align:left; display:none;' id='uploadTxtshow'>업로드 : 
							<span id='showuploadList' style='width:100%;'></span>
						</div>
					</td>";
					
					
				}
				//답변상태, 등록시 사용할수 없음
				else if($ky == "29") {
					//답변상태는 select box 형식이라 위에서 제어함
				}
				//18 제목
				else echo "<td><input type='text' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass textInputClass " . ($ky == 18 ? " class='datam_18_class textInputClass' " : " class='textInputClass' ") . "' " : ($ky == 18 ? " class='datam_18_class textInputClass' " : " class='textInputClass' ")) . " valid.inputtype='text' valid.text='" . $val['이름'] . "' style='font-size:16px;padding:7px;border:1px solid #ddd;' " . ($ky == "27" ? " placeholder=' 핸드폰 번호 입력 시, 더 빠른 응대 및 카톡 알림이 전송됩니다.' " : "") . "></td>";
			}
			
			echo "</tr>";
			
		}
	}
	
	
	if($settingOptArray['비밀글사용'] == "Y" || $settingOptArray['비밀글사용'] == "A")
	{
		echo "
		<tr>
			<th style='width:140px;text-align:center;'>비밀번호" . ($settingOptArray['비밀글사용'] == "A" ? "<span style='color:red;'> *</span>":""). "</th>
			<td style='text-align:left;'>
				<input type='password' name='bbs_pw' id='bbs_pw' placeholder='비밀번호를 입력 바랍니다.' " . ($settingOptArray['비밀글사용'] == "A" ? " class='checkvalueClass localInput' " : "class='localInput'") . " valid.inputtype='text' valid.text='비밀번호'>
				<div id='passwordMessage' style='color:#bf0000;font-size:12px;'></div> <!-- 메시지를 표시할 영역 --> 
			</td>			
		</tr>		
		";
	}
	
	if($settingOptArray['공지사용'] == "Y" && ($bbs_user_level >= 99 || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE")) {
		echo "
		<tr>
			<th style='width:140px;text-align:center;'>공지글</th>
			<td style='text-align:left;'>
				<label><input type='checkbox' style='width:20px;height:20px;' name='use_notice' id='use_notice' value='1'> 공지사용</label> 
			</td>			
		</tr>";
	}
?>

		<tr>
			<th style="text-align:center;">보안문자<span style='color:red;'> *</span></th>
			<td style="text-align:left;">
<?php
				$captchacode = (time()*1234);//md5(date('h:i:s'));
				$rcode= (rand(1,9));
				$capCode = $captchacode.$rcode;
				
				$cap = substr($capCode, 0, -1);  // 끝에서 한 문자 제외한 문자열	// 뒤에서 한 자리를 제외한 문자열을 구하기
				$c = substr($capCode, -1);  // 음수 인덱스로 뒤에서 첫 번째 문자 가져오기 // 뒤에서 한 자리를 구하기 (마지막 문자)
				$text = strtoupper(substr($cap,$c,4));				
?>			
				<img id="captcha-image" src="/khboard/json/captcha2.php?cap=<?= $capCode?>" alt="CAPTCHA Image"  style='vertical-align:middle;width:100px;'>
				<img src="/khboard/ckeditor5/refresh.png" style="width:20px;cursor:pointer;vertical-align:middle;"  id="refresh-captcha">
				<input type="hidden" id="ccCode" name="ccCode" value="<?= $capCode?>" >
				<input type="text" id="captchatext" name="captchatext"  style="height:32px;max-width:120px;display:inline-block;border:1px solid #ddd;vertical-align:middle;font-size:16px;padding:7px;" value="<?=($bbs_user_level >= 99 || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE" ? $text : "")?>">
			</td>			
		</tr>
		
		
	</table>
	
	</div>
	
	<?php
	//푸시 전송 옵션
	if(($admOptArray['알림전송']['사용유무']) == "Y" && ($bbs_user_level >= 99 || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE")) 
	{
		$admwriteuserText = "
		<div style='margin-top:15px;margin-bottom:3px;'><i class='far fa-comment-alt'></i> 알림 전송설정(메세지 발송)</div>
		<div>
		<table class='bbstable'>		
			<tr>
				<th style='text-align:center;width:70px;'>알림</th>
				<td style='text-align:left;'>
					<div style='padding-bottom:7px;border-bottom:1px dotted #ddd;margin-bottom:7px;'>
						<input type='text' class='localInput' id='addTelFly' name='addTelFly' placeholder='예)0101234567,01014541578,0102484487'>
						<div style='font-size:14px;color:#bf0000;'>- 받는이의 연락처를 입력해주세요</div>
					</div>
					<div>
						<label><input type='radio' name='smsPushType' style='width:20px;height:20px;' value=''> 게시글만 등록(알림없음)</label>
						<label><input type='radio' name='smsPushType' style='width:20px;height:20px;' value='C' checked> 카카오톡 전송(실패시 LMS)</label>
					</div>
				</td>
			</tr>
		</table>
		</div>";
		
		echo $admwriteuserText;	
	}
	
	
	if(($admOptArray['읽음확인문답']['사용유무']) == "Y") {
		echo "
		<div style='font-size:0.8em;color:red;'>※ 질문답 입력시 사용자가 답을 입력해야 게시물 읽음 상태로 됩니다.</div>
		<div style='margin-top:10px;' id='question_answer'>
			<div class='qusanClass'>
				<table style='width:100%;' cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td valign='top' style='text-align:left;'>
							<textarea style='height:30px;width:100%;' name='question_1' id='question_1' placeholder=' 확인질문'></textarea>
						</td>
						<td width='100' >
							<input type='text' style='margin-left:5px;height:30px;width:100%;' name='answer_1' id='answer_1' placeholder=' 확인정답'>
						</td>
						<td width='50'>
							<div id='qusAdd' style='width:45px;margin-left:5px;background:#EBEBEB;color:#676767;vertical-align:middle;cursor:pointer;height:30px;line-height:30px;text-align:center;font-size:11px;font-weight:bold;'>추가+</div>
						</td>
					</tr>	
				</table>
			</div>
		</div>";
	}

	 
	?>
</form>	

<div style='margin-top:10px;text-align:right;'>
	<div class="localAlign">
		<a href="?bbscaseBy=list&<?= $add_url_param ?>" title="목록이동" style="display:inline;"><div class='bbs_mbutton bbs_bg_blue localinLine' style="width:100px;"><span style="color:#fff;">목록이동</span></div></a>
	</div>
	<div class="localAlign">
		<div class='bbs_mbutton bbs_bg_red localinLine' id="bbs_save" style="width:100px;"><span style="color:#fff;">등록하기</span></div>
	</div>	
</div>


<div id="editor"></div>

<div class="modal-overlay" style="display:none;">
	<div class="modal-bg"></div> <!-- 투명 배경을 위한 추가 div -->
	<div class="modal-content">
		<div class="modal-header">모달 타이틀</div>
		<div class="modal-body"></div>
		<div class="modal-footer"></div>
	</div>
</div>
<!--bljung, blhigh 랑 univ, sn은 css가 다르게 먹음-->
<input type="hidden" id="loGubn" value="<?= $ploCode ?>" />

<!--스마트에디터--> 

<?php
	if ($editorApllyBolArray[$ploCode]) {
?>
<script type="text/javascript" src="/js/smarteditor2/js/service/HuskyEZCreator.js"></script>
<script type="text/javascript">
	var oEditors = [];
	localApplyBol = "TRUE";
	nhn.husky.EZCreator.createInIFrame({
		oAppRef: oEditors,
		elPlaceHolder: "data_21",
		sSkinURI: "/js/smarteditor2/SmartEditor2Skin.html",
		fCreator: "createSEditor2",
		htParams : // 이 페이지에서 나가기 없애는 구문
			{
				fOnBeforeUnload : function()
				{ }
			}
	});
</script>
<?php
	} 
?>

<?php
	if ($ploCode == "univ") {
?>
<link rel="stylesheet" href="/khboard_local/js/bbsmodal/css/jquery.modal.univ.css">
<script  src="/khboard_local/js/bbsmodal/js/jquery.modal_univ.js" type="text/javascript"  /></script>
<?php
	} else {
?>
<link rel="stylesheet" href="/khboard_local/js/bbsmodal/css/jquery.modal.css">
<script  src="/khboard_local/js/bbsmodal/js/jquery.modal.js" type="text/javascript"  /></script>
<?php
	}
?>



<!--<script src="/khboard/js/js_common.js?k=<?= $scrcode ?>&j=<?=md5($board_code)?>&a=<?= time()?>" type="text/javascript" /></script>-->
<!--파일업로드 및 비밀번호 입력때문에 -->
<link rel="stylesheet" href="/khboard_local/css/khboard_modal.css">
<script src="/khboard_local/js/js_write.js?k=<?= $scrcode ?>&j=<?=md5($board_code)?>&a=<?= time()?>" type="text/javascript" /></script>
<script>
var localApplyBol = "";
$(document).ready(function(){
	$("#smspushpreview").unbind().click(function(){
		var datam_18_class = $(".datam_18_class").val();
		var text = "";
		//text += "학생 역량 강화 시스템 발송<br>";
		//text += "#{학교명}<br>";
		//text += "#{학생/학부모 학교생활}<br><br>";
		text += "<div style='color:#000;font-weight:bold;'>※ 본 내용은 카카오톡 템플릿 검수가 완료된 내용으로 제목입력에 대한 내용만 발송 가능합니다.</div><br>";
		text += "안녕하십니까?<br>";
		text += "<?=$solution_school_Name?> 입니다.<br>";
		text += "학교생활 활동 내용을 안내해 드립니다<br>";
		text += "자세한 사항은 하단에 있는 연결 주소(URL)를 클릭하여 확인 할수 있습니다.<br>";
		text += "바쁘시더라도 잠시 시간을 내시어 확인 부탁드립니다. <br><br>";
		
		text += (datam_18_class ? datam_18_class : "{작성된 제목 내용이 없습니다. 제목이 출력됩니다.}")+"<br><br>";
		text += "확인 URL : https://<?= ($serverHostNm) ?>.meistergo.co.kr?pg=A01&q=efb2a684e4afb7d55e6147fbe5a332ee<br><br>";
		
		text += "○ 문의전화 : <?= ($smsListArr['phoenfirst'] ? $smsListArr['phoenfirst'] : $_sender) ?><br><br>";
		text += "* 본 메세지는 <?=$solution_school_Name?> 재학생/학부모/교직원 대상으로 발송됩니다.";
		
		bbsmodal({ type: 'alert', title: $("#do_serverHostName").val(), theme: 'x',text: text});
	});
});
</script>
<style>
.cke_editable p {margin : 0}
	
.cke_button__fileupload_icon{ 
	width:70px !important;
	background-size:100% !important;
	background-repeat: no-repeat;
	background-position: center center;
}

.filebox label {
	margin-top: 10px;
    display: inline-block;
    padding: 1em .75em;
    color: #999;
    font-size: inherit;
    line-height: normal;
    vertical-align: middle;
    background-color: #fdfdfd;
    cursor: pointer;
    border: 1px solid #ebebeb;
    border-bottom-color: #e2e2e2;
    border-radius: .25em;
    width:90%;
    max-width:90%;
}
 
.filebox input[type="file"] {  /* 파일 필드 숨기기 */
    position: absolute;
    width: 0px;
    height: 0px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip:rect(0,0,0,0);
    border: 0;
}

.filebox #bbs_file_upload {  /* 파일 필드 숨기기 */
    position: absolute;
    width: 0px;
    height: 0px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip:rect(0,0,0,0);
    border: 0;
}
</style>



