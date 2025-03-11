<?php 
	require($_SERVER['DOCUMENT_ROOT'] . "/khboard/inc/bbs_setting.php");
	
//	$md5_listtarget = substr($_REQUEST['q'],0,32);
//	$md5_bcode = substr($_REQUEST['q'],32,64);
	$md5_listtarget = substr($_REQUEST['q'],0,32);
	$md5_bcode = substr($_REQUEST['q'],32,32);
	$secretmodyn= substr($_REQUEST['q'],64,1);	//비밀번호 입력 성공시 수정 삭제 권한을 줌
	$ploCode = (isset($_REQUEST['ploCode']) ? $_REQUEST['ploCode'] : "");
	
//	$bbscasBy = "";
//	if($md5_listtarget == md5('read')) $bbscasBy = "read";
//	else  $bbscasBy = "list";

	// editor적용 
	$editorApllyBolArray =  array(
		"sn" => false,
		"high" => true,
		"jung" => true,
		"univ" => false
	);
	
	$idx = $md5_bcode; //rawurldecode(iconv("utf-8", "euc-kr", $_REQUEST['upcode']));	//고유번호

	$rst = $db->query("SELECT * FROM " . $board_database . " WHERE md5(newcode)='" . $idx . "';");	// 게시판  
	$row = $rst->fetch_array();

	$rfileDir = explode("^", $row['file_dir']);
	$rfileNm = explode("^", $row['file_name']);
	$rfileSiz = explode("^", $row['file_size']);
	$rfileTpy = explode("^", $row['file_type']);
	$rfileWid = explode("^", $row['file_width']);
	$rfileHei = explode("^", $row['file_height']);
	$rfileExt = explode("^", $row['fileExt']);
	$rfileNum = explode("^", $row['file_num']);

	$filespan = "";
	if ($row['file_dir'] != "") {
		foreach($rfileDir AS $key => $value) {
			$filespan .= "
				<span style='cursor:pointer;' class='fileDelclass' id='fileDdeloff_" . $key . "' java.val='".$key."^".($rfileDir[$key]."^".($rfileNm[$key])."^".$rfileSiz[$key]."^".$rfileTpy[$key]."^".$rfileWid[$key]."^".$rfileHei[$key]."^".$rfileExt[$key]."^".$rfileNum[$key]) . "'>
					" . $rfileNm[$key] . " ⓧ
					<input type='hidden' id='fileDsave_" . $key . "' name='fileDsaveoff_" . $key . "' value='".$key."^".($rfileDir[$key]."^".($rfileNm[$key])."^".$rfileSiz[$key]."^".$rfileTpy[$key]."^".$rfileWid[$key]."^".$rfileHei[$key]."^".$rfileExt[$key]."^".$rfileNum[$key]) . "'>
				</span> 
			";
		}
	}
?>

<form name='editor_frm' id='editor_frm' enctype = "multipart/form-data">
	<input type='hidden' name='idxno' id='idxno' value='<?= $row['newcode'] ?>'>
	<input type='hidden' name="board_sub" id="board_sub" value='<?= $board_sub ?>'>	
	<input type='hidden' name="board_code" id="board_code" value='<?= $row['board_code'] ?>'>
	<div id="filedel_list"></div>
	
	
	
	<table border="0" cellpadding="0" cellspacing="0" style='width:100%; margin-bottom:10px;' class="bbstable" summary="내용입력">
<?php
	@ksort($bbswriteArray);
	foreach ($bbswriteArray as $key => $value) {
		foreach ($value as $ky => $val) {
			//echo $val."<br>";
			$myvalue = $row[$fildlistArray[$ky]];
			echo "
			<tr>
				<th style='width:100px;text-align:center;'>" . $val['이름'] . "" . ($val['필수'] == "Y" ? "*":"") . "</th>";
			
			if($val['타입'] == "TL") {
				echo "<td><textarea style='width:100%;height:70px;border:1px solid #ddd;' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='textarea' valid.text='" . $val['이름'] . "'>" . $myvalue . "</textarea></td>";
			}
			else if($val['타입'] == "RR") {
				$radio_exp = explode(",",$val['타입값']);
				echo "<td style='text-align:left;'>";
				foreach ($radio_exp as $k => $v) echo "<input type='radio' id='data_" . $ky . "' name='data_" . $ky . "' value='" . $v . "' " . ($myvalue == $v ? " checked " : "") . "  " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='radio' valid.text='" . $val['이름'] . "'> " . $v . " ";
				echo "</td>";
			}
			else if($val['타입'] == "RS") {
				if($ky == "29")
				{
					//답변상태	
					$selectbox_exp = explode(",",$val['타입값']);
					$option = "";
					foreach ($selectbox_exp as $k => $v) $option .= "<option  value='" . $v . "'  " . ($myvalue == $v ? " selected " : "") . ">" . $v . "</option>";
					
					echo "
					<td style='text-align:left;'>
						<div style='position:relative;'>
						
						" . ($bbs_user_level >= "99" || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE" ? $myvalue . "<div style='background:#bf0000;color:#fff;border-radius:10px;margin-left:10px;display:inline;border:1px solid #ccc;font-size:14px;width:150px;text-align:center;padding:3px 8px;cursor:pointer;' class='masterchangeBtn'  valid.tel='" . $row[$fildlistArray['27']] . "' valid.idx='" . $idx . "'>관리자 상태변경&전송</div>" : "") . "						
						
						<!--
							<div style='padding-right:90px;'>" . $myvalue . "							
							<select style='width:100%;border:1px solid #ddd;' id='data_" . $ky . "' name='data_" . $ky . "' class='master_dabchange " . ($val['필수'] == "Y" ? " checkvalueClass " : "") . "' valid.inputtype='select' valid.text='" . $val['이름'] . "'>" . $option . "</select>
							</div>
							<div style='position:absolute;top:0;right:0;'>
								<div style='border:1px solid #ccc;width:80px;text-align:center;padding:5px;cursor:pointer;' class='masterchangeBtn' valid.tel='" . $row[$fildlistArray['27']] . "' valid.idx='" . $idx . "'>전송하기</div>
							</div>
						-->	
						</div>
					</td>";						
				}
				else
				{
					$selectbox_exp = explode(",",$val['타입값']);
					$option = "";
					foreach ($selectbox_exp as $k => $v) $option .= "<option  value='" . $v . "'  " . ($myvalue == $v ? " selected " : "") . ">" . $v . "</option>";
					
					echo "
					<td style='text-align:left;'>
					<select style='width:100%;border:1px solid #ddd;' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='select' valid.text='" . $val['이름'] . "'>" . $option . "</select>
					</td>";					
				}
			}
			else if($val['타입'] == "CC") {
				$checkbox_exp = explode(",",$val['타입값']);
				echo "<td style='text-align:left;'>";
				foreach ($checkbox_exp as $k => $v) echo "<input type='radio' id='data_" . $ky . "' name='data_" . $ky . "' value='" . $v . "' " . ($myvalue == $v ? " checked " : "") . "  " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='radio' valid.text='" . $val['이름'] . "'> " . $v . " ";
				echo "</td>";
			}
			else{
				if($ky == "21"){
					
					$fileaddhtml = "";
					if($bbswriteArray['25']['글쓰기'] == "N") 
					{
						$fileaddhtml = "
						<div style='text-align:left;'>
							<div style='width:100%; margin-top:6px; text-align:left;" . ($filespan ? "" : "display:none;"). " ' id='uploadTxtshow'>업로드 : 
								<span id='showuploadList' style='width:100%;'>" . $filespan . "</span>
							</div>
						</div>";
					}
										
					echo "
					<td>
						<input type='hidden' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='editor' valid.text='" . $val['이름'] . "'>
						<div>
							<textarea  id='data_" . $ky . "' name='data_" . $ky . "'  style='border:1px solid; border-color:#cccccc; padding:2px; width:99%; height:auto; margin:0px;' title='본문내용입력'>" . $myvalue . "</textarea>
						</div>
						" . $fileaddhtml . "
					</td>";
					
					
				}
				else if($ky == "25") {
					echo "
					<td style='text-align:left;'>
						<input type='hidden' " . ($val['필수'] == "Y" ? " class='checkvalueClass' " : "") . " valid.inputtype='etcfile' valid.text='" . $val['이름'] . "'>
						<div class='bbs_mbutton' onclick='fileImg_upload(1)' style='width:130px;'>
							<svg xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 -960 960 960' width='24px' style='vertical-align:middle;' fill='#5f6368'><path d='M440-320v-326L336-542l-56-58 200-200 200 200-56 58-104-104v326h-80ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z'/></svg> 
							파일업로드
						</div>
						<div style='width:100%; margin-top:6px; text-align:left;" . ($filespan ? "" : "display:none;"). "' id='uploadTxtshow'>업로드 : 
							<span id='showuploadList' style='width:100%;'>" . $filespan . "</span>
						</div>
					</td>";
				}
				else echo "<td><input type='text' id='data_" . $ky . "' name='data_" . $ky . "' " . ($val['필수'] == "Y" ? " class='checkvalueClass textInputClass' " : " class='textInputClass' ") . " valid.inputtype='text' valid.text='" . $val['이름'] . "' value='" . $myvalue . "' style='font-size:16px;padding:7px;border:1px solid #ddd;'></td>";
			}
			
			echo "</tr>";
			
		}
	}
	
	if($settingOptArray['비밀글사용'] == "Y" || $settingOptArray['비밀글사용'] == "A")
	{
		echo "
		<tr>
			<th style='width:100px;text-align:center;'>비밀번호" . ($settingOptArray['비밀글사용'] == "A" ? "*":""). "</th>
			<td style='text-align:left;'>
				<input type='password' name='bbs_pw' id='bbs_pw' placeholder='비밀번호를 입력 바랍니다.' " . ($settingOptArray['비밀글사용'] == "A" ? " class='checkvalueClass' " : "") . " valid.inputtype='text' valid.text='새로운 비밀번호' style='width:97%;font-size:16px;padding:7px;border:1px solid #ddd;'> 
				<div id='passwordMessage' style='color:#bf0000;font-size:12px;'></div> <!-- 메시지를 표시할 영역 -->
			</td>			
		</tr>		
		";
	}
	
	if($settingOptArray['공지사용'] == "Y") {
		echo "
		<tr>
			<th style='width:100px;text-align:center;'>공지글</th>
			<td style='text-align:left;'>
				<label><input type='checkbox' style='width:20px;height:20px;' name='use_notice' id='use_notice' value='1' " . ($row['use_notice'] == "1" ? " checked " : "") . "> 공지사용</label> 
			</td>			
		</tr>";
	}	
?>
	
	</table>	
	<input type="hidden" id="lohref" value="q=<?= md5('list')?>&<?=$add_url_param?>">	
</form>




<div style='margin-top:10px; display:inline;'>	
	<a href="?<?= $add_url_param ?>" title="목록"><div class="bbs_mbutton bbs_bg_blue locaULine"><span style="color:#fff;">목록</span></div></a>
	<span onclick="is_bbsSave()" style='cursor:pointer;margin-top:10px;'><div class="bbs_mbutton bbs_bg_red locaULine" ><span style="color:#fff;">변경</span></div></span>
</div>	

<!--<link rel="stylesheet" href="/css/reset.css" />-->
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

<div style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.6);z-index:9999;display:none;" id="saveloading">
	<div class="khloading-container">
	    <div class="khloading"></div>
	    <div id="khloading-text">저장중..</div>
	</div>
</div>


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


<!--파일업로드 및 비밀번호 입력때문에 -->
<link rel="stylesheet" href="/khboard_local/css/khboard_modal.css">
<script src="/khboard/js/js_common.js?v=<?=time();?>" type="text/javascript" /></script>
<script src="/khboard_local/js/js_update.js?k=<?= $scrcode ?>&j=<?=md5($board_code)?>" type="text/javascript" /></script>

<!--스마트에디터--> 
<?php
	if ($editorApllyBolArray[$ploCode]) {
?>
<script type="text/javascript" src="/js/smarteditor2/js/service/HuskyEZCreator.js"></script>
<script type="text/javascript">
	var oEditors = [];
	var localApplyBol = "";
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
	} else {
?>
<script type="text/javascript">
	var localApplyBol = "";
</script>
<?php
	}
?>

<style>

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