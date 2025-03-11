<?php
	require($_SERVER['DOCUMENT_ROOT'] . "/khboard/inc/bbs_setting.php");
	
	$ploCode = (isset($_REQUEST['ploCode']) ? $_REQUEST['ploCode'] : ""); // 로컬프로그램 구분
	
	$md5_listtarget = substr($_REQUEST['q'],0,32);
	$md5_bcode = substr($_REQUEST['q'],32,32);
	$secretmodyn= substr($_REQUEST['q'],64,1);	//비밀번호 입력 성공시 수정 삭제 권한을 줌
	
	//echo $md5_listtarget.".....".$md5_bcode.".....".$secretmodyn;
	//ecae13117d6f0584c25a9da6c8f8415e.....e19fcc3ea335406d5a9c762f54983edc
	
	$bbscasBy = "";
	if($md5_listtarget == md5('read')) $bbscasBy = "read";
	else  $bbscasBy = "list";
	
	// 개별페이지 실행방지
  	if ( defined("_KHBOARD_") == false ) {
  		echo "<div style='margin-top:150px;text-align:center;font-size:25px;font-weight:bold;color:red;'>접근 권한이 없습니다.</div>";
  		echo  "<div style='margin-top:10px;text-align:center;font-size:25px;font-weight:bold;'>ⓧ</div>";
  		exit;	
  	}
  	
  	if ( $bbscasBy != "read") {
  		echo "<div style='margin-top:150px;text-align:center;font-size:25px;font-weight:bold;color:red;'>해당 게시물을 찾을수 없습니다.</div>";
  		echo  "<div style='margin-top:10px;text-align:center;font-size:25px;font-weight:bold;'>ⓧ</div>";
  		exit;	
  	}
  	
  	if(ReadAuth() != "Y") {
  		echo "
  		<div style='width:100%;text-align:center;padding:10% 0;'>
			<svg xmlns='http://www.w3.org/2000/svg' height='40px' viewBox='0 -960 960 960' width='40px' fill='#bf0000' style='width:80px;height:80px;'><path d='m40-120 440-760 440 760H40Zm115.33-66.67h649.34L480-746.67l-324.67 560ZM482.78-238q14.22 0 23.72-9.62 9.5-9.61 9.5-23.83 0-14.22-9.62-23.72-9.61-9.5-23.83-9.5-14.22 0-23.72 9.62-9.5 9.62-9.5 23.83 0 14.22 9.62 23.72 9.62 9.5 23.83 9.5Zm-33.45-114H516v-216h-66.67v216ZM480-466.67Z'/></svg>
  			<div>읽기 권한이 없습니다.</div>
  		</div>";
  		exit;
  	}
  	
	// editor적용 
	$editorApllyBolArray =  array(
		"sn" => false,
		"high" => true,
		"jung" => true,
		"univ" => true
	);
	
	/*
	 * 글 상세 보기
	 * 댓글달기 기능
	 * 댓글의 댓글 또는 답글 달기 기능
	 * 해당글 수정,삭제 이동 및 댓글 삭제 기능
	 * */

	$bcode = $md5_bcode; //$_REQUEST['bcode']; //고유번호
	
	/*
	 * 게시판 내용 읽어오기
	 */
	$bbsSQL = "
	SELECT * 
	FROM " . $board_database . "
	WHERE md5(newcode)='" . $bcode . "';";
	$rst = $db->query($bbsSQL);
	$row = $rst-> fetch_array();  
	
	if($row['idx'] == "") {
  		echo "<div style='margin-top:150px;text-align:center;font-size:16px;font-weight:bold;color:red;'>해당 게시물을 찾을수 없습니다.</div>";
  		echo  "
  		<div style='margin-top:10px;text-align:center;font-size:14px;font-weight:bold;'>
  			<a href='/' title='메인으로 이동'><div class='bbs_mbutton bbs_bg_red' style='width:160px;'><span style='color:#fff;'>메인페이지 이동</span></div></a>
  		</div>";
  		exit;
	}
	else
	{
		//카운트 1씩 증가 시켜 주기	
		$db ->query("UPDATE " . $board_database . " SET hits=(hits+1) WHERE  md5(newcode)='" . $bcode . "';");
	}
	
	/*
	* 공지 내용 확인 체크
	*/
	$confirm_rst = $db -> query("SELECT eq FROM khbbs_confirm_ok_" . $board_code . " WHERE org_newcode='" . $bcode . "' AND memb_no='" . $bbs_user_code . "'  AND userType ='" . $bbs_user_tspType . "';");
	$crow = $confirm_rst -> fetch_array();	
	
	
	/*
	 * 이전,다음글 정보 및 타이틀 가져오기
	 * */
	$nextw = "SELECT t2.newtype, t2.idx,t1.newcode, t1.subject
	FROM " . $board_database . " t1 INNER JOIN
	(
	  SELECT 'prev_row' AS newtype, MAX(idx) AS idx,newcode FROM " . $board_database . " WHERE board_code='" . $board_code . "' " . ($board_sub == "" ? "" : " AND board_subname='" . $board_sub . "' ") . " " . ($bbs_user_level == "999" || $bbs_user_level == "99" ? "" : " AND (pwd IS null OR pwd='') ") . " AND idx < " . ($row['idx'] == "" ? 0 : $row['idx']) . "
	  UNION ALL
	  SELECT 'next_row' AS newtype, MIN(idx) AS idx,newcode FROM " . $board_database . " WHERE board_code='" . $board_code . "' " . ($board_sub == "" ? "" : " AND board_subname='" . $board_sub . "' ") . " " . ($bbs_user_level == "999" || $bbs_user_level == "99" ? "" : " AND (pwd IS null OR pwd='') ") . " AND idx > " . ($row['idx'] == "" ? 0 : $row['idx']) . "
	) t2 ON t1.idx = t2.idx; ";
	
	$next_rst = $db->query($nextw);
	
	$xxArray = array();
	while($rw = $next_rst-> fetch_array()) {
		$xxArray[$rw['newtype']]['idx'] = $rw['idx'];
		$xxArray[$rw['newtype']]['newcode'] = $rw['newcode'];
		$xxArray[$rw['newtype']]['subject'] = $rw['subject'];
	}	
	
	function readyContent($contents) {
		/*$contents = str_replace("&lt;","<",$contents);
		$contents = str_replace("&gt;",">",$contents);
		$contents = str_replace("&amp;","&",$contents);
		$contents = str_replace("&quot;","'",$contents);
		$contents = str_replace("&#039;","'",$contents);
		$contents = str_replace("\n","", $contents);*/
	//	$contents = htmlspecialchars_decode(stripslashes($contents)); 
	//	$contents=nl2br($contents); 
		
		$contents = htmlspecialchars_decode(($contents)); 
		$contents = str_replace("<script>","&lt;script&gt;",$contents);
		$contents = str_replace("</script>","&lt;/script&gt;",$contents);		
		$contents = str_replace("<html","&lt;html",$contents);
		$contents = str_replace("</html","&lt;/html",$contents);
		$contents = str_replace("<body","&lt;body",$contents);
		$contents = str_replace("</body","&lt;/body",$contents);
		$contents = str_replace("<form","&lt;form",$contents);
		$contents = str_replace("</form","&lt;/form",$contents);
			
		return $contents;
	}
	
	function bbsrtn_mobile_chk() {
	    $ary_m = array("iPhone","iPod","IPad","Android","Blackberry","SymbianOS|SCH-M\d+","Opera Mini","Windows CE","Nokia","Sony","Samsung","LGTelecom","SKT","Mobile","Phone");
	
	    for($i=0; $i<count($ary_m); $i++){
	        if(preg_match("/$ary_m[$i]/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
	            return $ary_m[$i];
	            break;
	        }
	    }
	    return "PC";
	}
	
	$chk_m = bbsrtn_mobile_chk(); 		
	
?>

<style>
	.star_rating {font-size:0; letter-spacing:-4px;}
	.star_rating a {
	    font-size:22px;
	    letter-spacing:0;
	    display:inline-block;
	    margin-left:5px;
	    color:#ccc;
	    text-decoration:none;
	}
	.star_rating a:first-child {margin-left:0;}
	.star_rating a.on {color:#000;}	
	

	/* 내용수정*/	
	#contentText {text-align: justify; color: rgb(51, 51, 51); line-height: 1.5; font-size: 15px;padding:10px;}
	
	#contentText > table { 
		width:100% !important; 
		font-size:14px;
	}
	
	#contentText img { max-width:100%;height:auto; }
	#contentText ol {line-height: 1.3; list-style-type: square; padding-left:30px;}
	#contentText ol li {margin:5px 0px}
	#contentText ol ol {font-size: 14px; list-style-type: circle; padding-left:16px}
	#contentText ol ol li {margin:4px 0px}
	
	#contentText h1 {font-weight:bold; font-size:18px}
	#contentText h2 {font-weight:bold; font-size:16px}
	#contentText h3 {font-weight:bold; font-size:15px}
	#contentText h4 {font-weight:bold; font-size:14px}	
	
	.ck-voice-label{display:none !important;}
</style>

<script>
$(document).ready(function(){
	/*
	* 페이지 실행 후 
	*/
	//본문에 이미지가 포함되어 있을경우 사이즈 조절
	var contentTextv = $("#contentText");
	if(contentTextv.length > 0) {
		if(contentTextv.find("img").length > 0)
		{
			contentTextv.find("img").each(function(){
				$(this).css({"width":"100% ","max-width":$(this).width() +"px","height":"auto"});
			});
		}
		
		contentTextv.find("table").addClass('responsive-table').find("tr:eq(0)").hide();
	}
	


}); //ready end;
</script>
<!-- 가져올 추가 데이터-->
<input type="hidden" id="infoyn" value="<?= $row['infoyn'] ?>">
<input type="hidden" id="sb_board_code" value="<?= $board_code?>">
<input type="hidden" id="sess_wr_gbn" value="<?= $bbs_user_tspType ?>">
<input type="hidden" id="newbcode" value="<?= $bcode ?>">
<input type="hidden" id="addparameter" value="<?= $add_url_param ?>">


<?php
//if ($_SERVER['REMOTE_ADDR'] == "61.85.146.3") {
//	ECHO ($admOptArray['읽음인원확인']['사용유무'])."....".$bbs_emhk.".....".$bbs_user_tspType."<BR>";
//	print_r($levWr);
//}
?>

<?php



	//읽음확인 유무 보일경우
	if(($admOptArray['읽음인원확인']['사용유무']) == "Y") 
	{
		$rd_res = $db -> query("select userType,count(*) as tcount from khbbs_confirm_ok_" . $board_code . " 
		where memb_no <> '' 
		and org_newcode='" . $bcode . "'
		group by userType;");
		$rdTmp = array();
		while ($rdrow = $rd_res -> fetch_array()) {
			$rdTmp[$rdrow['userType']] = $rdrow['tcount'];
		}
		
		
// infoyn='Y',info_type,T,S,		
		
		$rdList = "";
		if($row['infoyn'] == "Y") 
		{
			$infoynexp = explode(",",$row['info_type']);
			$wtmp = array();
			if(sizeof($infoynexp) > 0) {
				foreach ($infoynexp as $key => $value) $wtmp[$value] = "true";
			}
			
			if($wtmp["T"] == "true") $rdList .= " <span style='cursor:pointer;' " . ($sess_Gbn == "T" ? " class='writeuserlist' valid.gtype='T' valid.bcode='" . $bcode . "' valid.bordercode='" . $board_code . "' " : "") . ">교사(" . $rdTmp['T'] . ")</span>,";
			if($wtmp["S"] == "true") $rdList .= " <span style='cursor:pointer;' " . ($sess_Gbn == "T" ? " class='writeuserlist' valid.gtype='S' valid.bcode='" . $bcode . "' valid.bordercode='" . $board_code . "' " : "") . ">학생(" . $rdTmp['S'] . ")</span>,";
			if($wtmp["P"] == "true") $rdList .= " <span style='cursor:pointer;' " . ($sess_Gbn == "T" ? " class='writeuserlist' valid.gtype='P' valid.bcode='" . $bcode . "' valid.bordercode='" . $board_code . "' " : "") . ">학부모(" . $rdTmp['P'] . ")</span>,";			
		} 
		else 
		{
			if($rdTmp['T'] > 0) $rdList .= " <span style='cursor:pointer;' " . ($sess_Gbn == "T" ? " class='writeuserlist' valid.gtype='T' valid.bcode='" . $bcode . "' valid.bordercode='" . $board_code . "' " : "") . ">교사(" . $rdTmp['T'] . ")</span>,";
			if($rdTmp['S'] > 0) $rdList .= " <span style='cursor:pointer;' " . ($sess_Gbn == "T" ? " class='writeuserlist' valid.gtype='S' valid.bcode='" . $bcode . "' valid.bordercode='" . $board_code . "' " : "") . ">학생(" . $rdTmp['S'] . ")</span>,";
			if($rdTmp['P'] > 0) $rdList .= " <span style='cursor:pointer;' " . ($sess_Gbn == "T" ? " class='writeuserlist' valid.gtype='P' valid.bcode='" . $bcode . "' valid.bordercode='" . $board_code . "' " : "") . ">학부모(" . $rdTmp['P'] . ")</span>,";
		}
		
		if($rdList) {
			echo "<div style='text-align:right;margin-bottom:3px;font-size:0.8em;vertical-align:middle;'>
			<i class='fab fa-readme' style='font-size:1em;vertical-align:middle;'> 읽음 : </i> 
			" . substr($rdList,0,-1) . "
			</div>";
		}
	}
?>
<div id="AGASEGAEGAWEGWAG"></div>
<div>
<table border="0" cellpadding="0" cellspacing="0" style='width:100%; margin-bottom:10px;' class="bbstable" summary="내용입력">
<?php
//유형별 게시판 카테고리 보기(유형별 게시판 분리, 카테고리 설정과 다름, 관리자 설정 확인)
if($board_sub == "" && (is_array($bbsSubCodeArray[$board_code]) && sizeof($bbsSubCodeArray[$board_code]) > 0) && $settingOptArray['유형별게시판분리'] == "Y"){
	echo "
	<tr>
		<th style='text-align:center;'>구분</th>
		<td style='text-align:left;'>" . ($bbsSubCodeArray[$board_code][$row['board_subname']]['TITLE']) . "</td>
	</tr>
	";
}


//$sess_applogin = "Y";
//$chk_m="iPhone";
	if(sizeof($bbsreadArray) > 0) {
		@ksort($bbsreadArray);
		foreach ($bbsreadArray as $key => $value) {
			foreach ($value as $ky => $val) {
				if($ky == "25") {
					if($row['file_name']) {
						$files = explode("^",$row['file_name']);
						$dir = explode("^",$row['file_dir']);
				    	$filetype = explode("^",$row['file_type']);						
						
						$filelist = "";
						for($i=0; $i < sizeof($files); $i++)
						{
							$name = explode(".",$files[$i]);
							if($name[0] != "") {
							    /*if($chk_m != "PC")
							    {
							    	
							    	if($sess_applogin == "Y") {
								    	if($chk_m == "iPhone" || $chk_m == "iPod" || $chk_m == "IPad") {
								    		//$filelist .= "<a href='/khboard/json/khboard_download.php?dir=" . $dir[$i] . "&name=" . rawurlencode(iconv("euc-kr","utf-8",$files[$i])) . "' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;' title='다운로드'>" . $name[0] . "</a>&nbsp;";
											//$filelist .= "<a href='/khboard/json/app_download_link.php?dir=" . $dir[$i] . "&filetype=" . $filetype[$i] . "&name=" . rawurlencode(iconv("euc-kr","utf-8",$files[$i])) . "&downloadurl=/khboard/upload_file/" . $dir[$i] . "'  class='filedown1class' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;'>" . $name[0] . "</a>&nbsp;";
											$filelist .= "<a href='/khboard/json/khboard_download.php?dir=" . $dir[$i] . "&name=" . rawurlencode(iconv("euc-kr","utf-8",$files[$i])) . "' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;' title='다운로드'>" . $name[0] . "</a>&nbsp;";
								    	}
								    	else {
								    		$filelist .= "<a href='/khboard/json/app_download_link.php?dir=" . $dir[$i] . "&filetype=" . $filetype[$i] . "&name=" . rawurlencode(iconv("euc-kr","utf-8",$files[$i])) . "&downloadurl=/khboard/upload_file/" . $dir[$i] . "'  
											 valid.target='download'
											 valid.gbn='khboard/upload_file/" . $dir[$i] . "'
											 valid.val='" . iconv("euc-kr","utf-8",rawurlencode($files[$i])) . "'
											 class='blankurlClass' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;'>" . $name[0] . "1111</a>&nbsp;";
								    	}
							    	}
							    	else
							    	{
							    		$filelist .= "<a 
										 valid.target='download'
										 valid.gbn='" . ("khboard/upload_file/" . $dir[$i] . "/" . rawurlencode($files[$i])) . "'
										 valid.val='" . ($files[$i]) . "'
										 class='blankurlClass'							    		
							    		href='/khboard/json/khboard_download.php?dir=" . $dir[$i] . "&name=" . rawurlencode(iconv("euc-kr","utf-8",$files[$i])) . "' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;' title='다운로드'>" . $name[0] . "</a>&nbsp;";
							    	}
							    }
					    		else
					    		{
					    			$filelist .= "<a href='/khboard/json/khboard_download.php?dir=" . $dir[$i] . "&name=" . rawurlencode(iconv("euc-kr","utf-8",$files[$i])) . "' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;' title='다운로드'>" . $name[0] . "</a>&nbsp;";
					    		}*/
					    		
					    		$filelist .= "<a href='/khboard/json/khboard_download.php?dir=" . $dir[$i] . "&name=" . rawurlencode($files[$i]) . "' style='border:1px solid #cccccc;padding:3px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:11px;color:#4e4e4e;' title='다운로드'>" . $name[0] . "</a>&nbsp;";
					    		$filelist .= (($i+1) == sizeof($files) ? "" : ", ");
							}
						}						
						echo "
						<tr>
							<th style='width:130px;text-align:center;'>" . $val['이름'] . "" . ($val['필수'] == "Y" ? "*":"") . "</th>
							<td style='text-align:left;'>" . $filelist . "</td>
						</tr>";							
					}
					else {
						echo "
						<tr>
							<th style='width:130px;text-align:center;'>" . $val['이름'] . "" . ($val['필수'] == "Y" ? "*":"") . "</th>
							<td style='text-align:left;'>업로드된 파일이 없습니다.</td>
						</tr>";							
					}
				}
				//답변상태
				else if($ky == "29") 
				{
						echo "
						<tr>
							<th style='width:130px;text-align:center;'>" . $val['이름'] . "" . ($val['필수'] == "Y" ? "*":"") . "</th>
							<td style='text-align:left;'>
								" . readyContent($row[$fildlistArray[$ky]]) . "
								" . ($bbs_user_level >= "99" || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE" ? "<div style='background:#bf0000;color:#fff;border-radius:1px;margin-left:10px;display:inline;font-size:14px;width:150px;text-align:center;padding:3px 8px;cursor:pointer;' class='masterchangeBtn'  valid.tel='" . $row[$fildlistArray['27']] . "' valid.idx='" . $bcode . "'>관리자 상태변경&전송</div>" : "") . "
							</td>
						</tr>";	
						
						
				}
				else
				{
					if($ky == "21") {
						//내용
						echo "
						<tr>
							<td style='text-align:left;' colspan='2'><div id='contentText'>" . readyContent($row[$fildlistArray[$ky]]) . "</div></td>
						</tr>";	    									
					}
					else
					{
						echo "
						<tr>
							<th style='width:210px;text-align:center;'>" . $val['이름'] . "" . ($val['필수'] == "Y" ? "*":"") . "</th>
							<td style='text-align:left; width:1000px;'>" . readyContent($row[$fildlistArray[$ky]]) . "</td>
						</tr>";	    			
					}
				}
			}
		}
	}
?>
</table>
</div>

<div id="qagegwagwa"></div>

	<?php
		//공지 내용숙지 문제를 사용할 경우(학생만 해당) 읽음 여부 판단
		if($bbs_user_tspType == "S" && (($row['question1'] && $row['answer1']) || ($row['question2'] && $row['answer2']) || ($row['question3'] && $row['answer3']))) {
			if($crow['eq'] == "") {
	?>
	<!-- 공지확인 시작 -->
	<div style="background:#F4FAFF;margin-top:5px;text-align:left;padding:0 10px;" id='bssinchk'>
	<?php
		if($row['question1'] && $row['answer1']) {
	?>
		<div style="color:#4e4e4e;font-weight:bold;padding-top:15px;padding-bottom:3px;"><?= $row['question1']?></div>
		<div><input type="text" valid.answer1='<?= $row['answer1']?>' name="ans_1" id="ans_1" style="width:100%;height:25px;border:1px solid #E0F0FF;" placeholder=" 정답입력"></div>
	<?php
		}
		
		if($row['question2'] && $row['answer2']) {
	?>
		<div style="color:#4e4e4e;font-weight:bold;padding-top:15px;padding-bottom:3px;"><?= $row['question2']?></div>
		<div><input type="text" valid.answer2='<?= $row['answer2']?>' name="ans_2" id="ans_2" style="width:100%;height:25px;border:1px solid #E0F0FF;" placeholder=" 정답입력"></div>	
	<?php
		}
		
		if($row['question3'] && $row['answer3']) {
	?>
		<div style="color:#4e4e4e;font-weight:bold;padding-top:15px;padding-bottom:3px;"><?= $row['question3']?></div>
		<div><input type="text" valid.answer3='<?= $row['answer3']?>' name="ans_3" id="ans_3" style="width:100%;height:25px;border:1px solid #E0F0FF;" placeholder=" 정답입력"></div>	
	<?php
		}
	?>
		<div style="text-align:center;width:100%;padding-top:15px;padding-bottom:15px;">
			<div class="bbs_mbutton bbs_bg_gray" id="queans_submit" style="width:180px;"><span style="color:#fff;">공지 내용 숙지 확인</span></div>
		</div>
	</div>
	<!-- 공지확인 끝 -->
	<?php
			} else {
				echo "<div style='margin:0 auto;margin-top:10px;border:1px solid #ccc;width:210px;text-align:center;padding:10px;border-radius:3px;'>내용 확인 완료 상태입니다</div>";
			}
		} 
		else 
		{
			//일반글 내용확인 저장하기 -> 확인글에 포함이 안되어 있을경우 입력
			if($crow['eq'] == "") {
				if($sess_Code) { //로그인 상태일 경우만 해당
		 			$membIP = $_SERVER['REMOTE_ADDR'];	
		 			$insert_sql ="
						INSERT INTO khbbs_confirm_ok_" . $board_code . " SET 
						board_code = '" . $board_code . "', 
						userType = '" . $bbs_user_tspType . "', 
						org_idx = '" . $row['idx'] . "', 
						org_newcode = '" . $bcode . "', 
						memb_no = '" . $bbs_user_code . "', 
						memb_id = '" . $bbs_user_id . "', 
						memb_name = '" . $bbs_user_name . "', 
						sHakgwa = '" . $sess_Hakgwa . "', 
						sHak = '" . $sess_Hak . "', 
						sBan = '" . $sess_Ban . "', 
						sBun = '" . $sess_Bun . "', 
						memb_ip = '" . $membIP . "', 
						mIndex = '" . $bbs_user_uniqkey . "',
						direct = 'Y',
						regDate = NOW();";
		 			
		 			$rst = $db -> query($insert_sql);					
				}
			}
		}
		
		$dabCommentHtml = "";
		if($commentAllReadYn == "Y") {
	 		$dabcheck_query = $db -> query("SELECT * FROM khbbs_comment_" . $board_code . " WHERE org_newcode='" . ($row['newcode']) . "' and cm_no='답글';");
	 		$dabrow = $dabcheck_query -> fetch_array();			
	 		
	 		$dabCommentHtml .= "
	 		<div style='border:1px solid #aaa;border-top:2px solid #aaa;padding:10px;' id='newAnswerDiv'>
	 			" . ($dabrow['comment'] ? "<div  style='margin-bottom:10px;border-bottom:1px solid #ddd;padding-bottom:10px;font-size:16px;'>
	 			관리자 (" . $dabrow['input_date'] . ")</div>
	 			<div id='answerPrint'>".nl2br(readyContent($dabrow['comment']))."</div>" : "답글이 없습니다.") . "
	 		</div>";
	 		
			$files = explode("^",$dabrow['file_name']);
			$dir = explode("^",$dabrow['file_dir']);
	    	$filetype = explode("^",$dabrow['file_type']);						
			
			$filelist = "";
			for($i=0; $i < sizeof($files); $i++)
			{
				$name = explode(".",$files[$i]);
				if($name[0] != "") {
					$filelist .= "
					<a href='/khboard/json/khboard_download.php?vmode=comment&dir=" . $dir[$i] . "&name=" . rawurlencode($files[$i]) . "' style='border:1px solid #cccccc;padding:3px 10px;border-radius: 5px;padding-top:5px;background:#f2f2f2;font-size:14px;color:#4e4e4e;' title='다운로드'>
					<i class='fas fa-download'></i> " . $name[0] . "
					</a>&nbsp;";
		    		$filelist .= (($i+1) == sizeof($files) ? "" : ", ");
				}
			}

			if($filelist) {
				$dabCommentHtml .= "<div style='margin-top:5px;border:1px solid #ccc;padding:10px;background:#f9f9f9;border-top:2px solid #aaa;' id='comment_download'>다운로드 : " . $filelist . "</div>";	
			}
		}
?>
	<div style='width:100%;margin-top:10px;'>
		<div style='text-align:left;'>
<?php 
			$prevIdx = ($xxArray['prev_row']['newcode'] == "" ? "onclick='do_prnx(\"prev\");' style='cursor:pointer;' " : "href='?" . $add_url_param . "&q=" . $md5_listtarget.md5($xxArray['prev_row']['newcode']) . "' class='loadingclsgo' "); 
			$nextIdx = ($xxArray['next_row']['newcode'] == "" ? "onclick='do_prnx(\"next\");' style='cursor:pointer;' '" : "href='?" . $add_url_param . "&q=" . $md5_listtarget.md5($xxArray['next_row']['newcode']) . "' class='loadingclsgo' ");
?>
				<div class="localAlign">		
					<a href="?<?= $add_url_param ?>" class="loadingclsgo" title="목록"><div class="bbs_mbutton bbs_bg_blue" style="width:65px;"><span style="color:#fff;">목록</span></div></a>
				</div>
				<div class="localAlign">
					<a <?= $prevIdx ?> title='<?= ($xxArray['prev_row']['subject']) ?>'><div class="bbs_mbutton bbs_bg_gray" style="width:65px;"><span style="color:#fff;">이전</span></div></a>
				</div>
				<div class="localAlign">
					<a <?= $nextIdx ?> title='<?= ($xxArray['next_row']['subject']) ?>'><div class="bbs_mbutton bbs_bg_gray" style="width:65px;"><span style="color:#fff;">다음</span></div></a>
				</div>
		 
		
		<?php
			if($commentmasterYn == "Y") {
				echo "<div class='localDivAlign'><span id='masterDab' java.val='" . (md5('update').$bcode) . "'><div class='bbs_mbutton bbs_bg_green localinLine' style='width:90px;'><span style='color:#fff;'>" . ($dabrow['comment'] ? "답글수정" : "답글쓰기") . "</span></div></span></div>";
			}
		
			//echo $bbs_user_ip;
		
			$idxinfo = "N";
			if($row['memb_no'] && ($bbs_user_code == $row['memb_no'])) $idxinfo = "Y";	//등록자 고유 코드가 같은경우
			else if($bbs_user_level >= "99") $idxinfo = "Y";							//관리자 권한인 경우
			else if($bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE") $idxinfo = "Y";			//지정된 아이피라면..
			else $idxinfo = "N";			

			if($idxinfo == "Y" || $secretmodyn == "y") 
			{
				if($row['pwd']){
					// 비밀번호 글일 경우	
					if($bbs_user_level >= "99" || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE") {
						echo "
							<div class='localDivAlign'>
								<span id='updateIdxshownopwd' java.val='" . (md5('update').$bcode) . "' java.cl='" . $idxinfo . "'><div class='bbs_mbutton bbs_bg_red localinLine' style='width:65px;'><span style='color:#fff;'>수정</span></div></span>
							</div>
							<div class='localDivAlign'>
								<span id='deleteIdxshownopwd' java.val='" . (md5('update').$bcode) . "' java.cl='" . $idxinfo . "'><div class='bbs_mbutton bbs_bg_red localinLine' style='width:65px;'><span style='color:#fff;'>삭제</span></div></span>
							</div>";
					} 
					else 
					{
						echo "
							<div class='localDivAlign'>
								<span id='updateIdxshow' java.val='" . (md5('update').$bcode) . "'><div class='bbs_mbutton bbs_bg_red localinLine' style='width:65px;'><span style='color:#fff;'>수정</span></div></span>
							</div>
							<div class='localDivAlign'>
								<span id='deleteIdxshow' java.val='" . (md5('update').$bcode) . "'><div class='bbs_mbutton bbs_bg_red localinLine' style='width:65px;'><span style='color:#fff;'>삭제</span></div></span>
							</div>";
					}
				}else{
					echo "
							<div class='localDivAlign'>
								<span id='updateIdxshownopwd' java.val='" . (md5('update').$bcode) . "' java.cl='" . $idxinfo . "'><div class='bbs_mbutton bbs_bg_red localinLine' style='width:65px;'><span style='color:#fff;'>수정</span></div></span>
							</div>
							<div class='localDivAlign'>
								<span id='deleteIdxshownopwd' java.val='" . (md5('update').$bcode) . "' java.cl='" . $idxinfo . "'><div class='bbs_mbutton bbs_bg_red localinLine' style='width:65px;'><span style='color:#fff;'>삭제</span></div></span>
							</div>";
					
					
					
				}
			}
?>		
		</div>
	</div>
<?php

	if($dabCommentHtml)
	{			
		echo "<div style='margin-top:25px;'><div style='border:2px solid #2A62D2;background:#F5F5F5;border-radius:10px;padding:20px;margin:15px auto;font-size:18px;text-align:center;color:#2A62D2;font-weight:bold;'>답글 내용 확인</div>".$dabCommentHtml."<div>";
?>
			<script>
			$(document).ready(function(){
				/*
				* 페이지 실행 후 
				*/
				//본문에 이미지가 포함되어 있을경우 사이즈 조절
				var contentTextv = $("#answerPrint");
				if(contentTextv.length > 0) {
					if(contentTextv.find("img").length > 0)
					{
						contentTextv.find("img").each(function(){
							$(this).css({"width":"100% ","max-width":$(this).width() +"px","height":"auto"});
						});
					}
					
					//contentTextv.find("table").addClass('responsive-table').find("tr:eq(0)").hide();
				}
			
			}); //ready end;
			</script>
<?php			
	}
?>	
	
	
	
<?php
	$commentFrm = "
	<div style='clear:both;'></div>
	<div style='margin-top:15px;margin-bottom:7px;'>
		<form id='commFrm' name='commFrm' method='POST'>
			" . ($settingOptArray['만족도출력'] == "TRUE" ? "<div style='text-align:right;margin-bottom:5px;'><p class='star_rating'><a class='on'>☆</a><a class='on'>☆</a><a class='on'>☆</a><a>☆</a><a>☆</a></p></div>" : "") . "
			<input type='hidden' name='starjum' id='starjum' value='3'>
			<input type='hidden' name='idx' id='idx' value='" . $row['idx'] . "'>
			<input type='hidden' name='newcode' id='newcode' value='" . $row['newcode'] . "'>
			<input type='hidden' name='board_code' id='board_code' value='" . $board_code . "' />
			<input type='hidden' name='board_name' id='board_name' value='" . $board_name . "' />
			
			<input type='hidden' name='newcaptchacode' id='newcaptchacode' />
			<input type='hidden' name='mycaptchacode' id='mycaptchacode' />
			<input type='hidden' name='mycommpwd' id='mycommpwd' />
			<table style='width:100%;' cellpadding='0' cellspacing='0' border='0' summary='댓글쓰기'>
				<tr>
					<th valign='top' style='text-align:left;'>
						<input type='text' style='height:30px;width:100%;border-radius:0px;' name='retext' id='retext' placeholder=' 댓글을 넘겨주세요.' title='댓글입력창'>
						<input type='text' style='display:none;' title='input박스 1개시 서브밋 방지'>
					</th>
					<td width='70' style='background:#DDE1E2;'>
						<div id='" . ($levCo['로그인필수'] == "Y" ? ($sess_Code ? "commsubmit" : "commsubmitre") : "commsubmit") . "' style='border-radius:0px;vertical-align:middle;color:#6E6E6E;cursor:pointer;height:30px;line-height:30px;text-align:center;font-size:12px;font-weight:bold;'>댓글달기</div>
					</td>
				</tr>
			</table>
		</form>
	</div>		
	<div style='margin-top:3px;text-align:left;'>
		<div id='comm_text_list'></div>
	</div>";

	if($commUserYn == "Y" && ($bbs_user_level >= "99" || $levCo['A'] == "Y" || $levCo[$bbs_user_tspType] == "Y")) {	
		if($levCo['로그인필수'] == "Y") {
			if($sess_Code) echo $commentFrm;
		}
		else echo $commentFrm;
		
	}
?>	
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

<?php
	if ($ploCode == "univ") {
?>
<link rel="stylesheet" href="/khboard_local/js/bbsmodal/css/jquery.modal.univ.css">
<script  src="/khboard_local/js/bbsmodal/js/jquery.modal_univ.js" type="text/javascript"  /></script>

<script src="/khboard_local/js/toast/jquery.toast.univ.js" type="text/javascript" /></script>
<link rel="stylesheet" href="/khboard_local/js/toast/jquery.toast.univ.css" />
<?php
	} else {
?>
<link rel="stylesheet" href="/khboard_local/js/bbsmodal/css/jquery.modal.css">
<script  src="/khboard_local/js/bbsmodal/js/jquery.modal.js" type="text/javascript"  /></script>

<script src="/khboard_local/js/toast/jquery.toast.js" type="text/javascript" /></script>
<link rel="stylesheet" href="/khboard_local/js/toast/jquery.toast.css" />
<?php
	}
?>

<script src="/khboard/js/js_common.js?k=<?= $scrcode ?>&j=<?=md5($board_code)?>&a=<?= time()?>" type="text/javascript" /></script>
<script src="/khboard_local/js/js_read.js?k=<?= $scrcode ?>&j=<?=md5($board_code)?>&a=<?=time()?>" type="text/javascript" /></script>

<script>
$(document).ready(function(){
<?php 
	if($commUserYn == "Y") { 
?>
		commentList();
<?php
	}
?>	
});
</script>
<style>
.ck-dialog__body {
    z-index: 9999;  /* 이 값을 높여서 다이얼로그가 다른 요소 위에 오도록 설정 */
}

.ck-editor__editable {
    z-index: 9999;  /* CKEditor의 편집 영역이 다른 요소 위에 오도록 설정 */
}
</style>

<!--<link rel="stylesheet" href="/khboard/ckeditor5/ckeditor5.css">
<script type="importmap">
{
	"imports": {
		"ckeditor5": "/khboard/ckeditor5/ckeditor5.js",
		"ckeditor5/": "/khboard/ckeditor5/"
	}
}
</script>-->


<script type="text/javascript">
//    import { initializeCKEditor } from '/khboard/ckeditor5/khcommon.js';

    $(document).ready(function() {
    
		$("#masterDab").unbind().on("click", function(){
			$("#masterDab,#comment_download").hide(); //답글쓰기 버튼 안보임 처리, 답글 다운로드 안보임
			var idx = $(this).attr("java.val");
			var phpcd = "<?= md5($board_code) ?>";
			var phlocal = "<?= $ploCode ?>";
			var localApplyBol = "<?= ($editorApllyBolArray[$ploCode] ? "TRUE" : "FALSE") ?>";
			
			$.post("/khboard_local/json/khboard_json.php","phpcd=" +phpcd + "&caseBy=masterLocalDabFrm&idx="+idx + "&ploCode=" + phlocal,function(vv){
				$("#newAnswerDiv").html(vv);
				
				$("#toAnswerClose").unbind().click(function(){location.reload();})	;
				$("#toAnswerBtn").unbind().click(function(){
					if ($("#answerWrHideiframe").length < 1) {
				        var $io = $('<iframe name="answerWrHideiframe" id="answerWrHideiframe" src="" />');
				        $io.css({ position: 'absolute', top: '-10000px', left: '-10000px',width:'0px',height:'0px' });		
				        $($io).appendTo("body");
					}
					
					if (localApplyBol == "TRUE") { // 로컬프로그램에 에디터가 적용되어있다면
						//스마트 에디터 값을 텍스트컨텐츠로 전달
						oEditors.getById["answerText"].exec("UPDATE_CONTENTS_FIELD", []);  
				   		
						var smartEditorCont = document.getElementById("answerText").value;
						
						if ($.trim(smartEditorCont) == "") $("#answerText").val("");
				    		
					}					
					var content = $("#answerText").val();
					if (content === '<p>&nbsp;</p>' || $.trim(content) === '') {
//						editor.setData('');
						$("#answerText").empty();
						if($("#commentwrYn").val() == "Y") {
							bbsmodal({ type: 'confirm',theme:"alert", size:"small",text:"답변 글이 없습니다.<br>삭제 하시겠습니까?", buttonText: {
									yes: "&nbsp;&nbsp;&nbsp; 삭제하기 &nbsp;&nbsp;&nbsp;",
									cancel: "&nbsp;&nbsp;&nbsp; 취 소 &nbsp;&nbsp;&nbsp;"
								},
								callback: function(result) {
									if(result == true) {
										$.post("/khboard/json/khboard_json.php","phpcd=" +phpcd + "&caseBy=commentMasterDel&eq="+idx,function(o){
											location.reload();
										});
									}
								}
							});
							return;
						}
						
					}	
					
					/*
					*	저장하기 부분
					*/
					var frm = $("#answerWriteFrm");
					frm.attr("method", "post"); 
					frm.attr("action", "/khboard/json/khboard_json.php");
					frm.attr("target", "answerWrHideiframe");
					//frm.attr("accept-charset","utf-8");
					frm.submit();
			
					var mmsmsm = $("#answerWrHideiframe")
					.on("load",function(){
						var iframeContents = $(this).contents().find('body').html();
			//alert(iframeContents);
						bbsmodal({ type: 'alert',size:"small",theme: 'alert',text: iframeContents,callback:function(){location.reload();} });		
						$("#answerWrHideiframe").remove();
						$(this).unbind('load');
						return false;
					});						
					
					/*
					$.post("/khboard/json/khboard_json.php","phpcd=" +phpcd + "&caseBy=commentWrFrm&idx="+idx,function(vhtml){
						bbsmodal({ type: 'confirm',theme:"alert", size:"small",text:vhtml, buttonText: {
								yes: "&nbsp;&nbsp;&nbsp; 저장하기 &nbsp;&nbsp;&nbsp;",
								cancel: "&nbsp;&nbsp;&nbsp; 취 소 &nbsp;&nbsp;&nbsp;"
							},
							callback: function(result) {
								if(result == true) {
									let youTelnumber = ($("#youTelnumber").length > 0 ? $("#youTelnumber").val() : "");
									let dabstat = ($("#dabstat").length > 0 ? ($("#dabstat").attr("type") == "text" ? $("#dabstat").val() : $("#dabstat option:selected").val()) : "");
									let copyboard = ($("#copyboard").length > 0 ? $("#copyboard").val() : "");

									$("#answer_youTelnumber").val(youTelnumber);
									$("#answer_dabstat").val(dabstat);
									$("#answer_copyboard").val(copyboard);

									
									var frm = $("#answerWriteFrm");
									frm.attr("method", "post"); 
									frm.attr("action", "/khboard/json/khboard_json.php");
									frm.attr("target", "answerWrHideiframe");
									//frm.attr("accept-charset","utf-8");
									frm.submit();
							
									var mmsmsm = $("#answerWrHideiframe")
									.on("load",function(){
										var iframeContents = $(this).contents().find('body').html();
							
										bbsmodal({ type: 'alert',size:"small",theme: 'alert',text: iframeContents,callback:function(){location.reload();} });		
										$("#answerWrHideiframe").remove();
										$(this).unbind('load');
										return false;
									});	
								}
							}
						});	

						
						$("#reviewpopup").unbind().click(function(){
							var viewmessage = "여기에 동적 미리보기 내용을 넣을 수 있습니다."; // 동적 내용
							var popupHTML = $("<div id='popupOverlay'><div id='popupContent'><button class='close-btn' id='closePopup'>X</button><h2>발신 미리보기</h2><p>" + viewmessage + "</p></div></div>");// 동적으로 팝업 요소 생성 후 DOM에 추가
							$("body").append(popupHTML);// DOM에 추가
							
							// 팝업 플러그인 적용
							popupHTML.popup();							 
						});
											
					});
					*/
			
					
									
					

				});
				
				
				
				/*bbsmodal({ type: 'confirm', title: "답글 쓰기", size:"large",text: vv, buttonText: {
						yes: "&nbsp;&nbsp;&nbsp; 저장하기 &nbsp;&nbsp;&nbsp;",
						cancel: "&nbsp;&nbsp;&nbsp; 취 소 &nbsp;&nbsp;&nbsp;"
					},
					callback: function(result) {
						if(result == true) {
	
							if ($("#answerWrHideiframe").length < 1) {
						        var $io = $('<iframe name="answerWrHideiframe" id="answerWrHideiframe" src="" />');
						        $io.css({ position: 'absolute', top: '-10000px', left: '-10000px',width:'0px',height:'0px' });		
						        $($io).appendTo("body");
							}
							
							var frm = $("#answerWriteFrm");
							frm.attr("method", "post"); 
							frm.attr("action", "/khboard/json/khboard_json.php");
							frm.attr("target", "answerWrHideiframe");
							//frm.attr("accept-charset","utf-8");
							frm.submit();
					
							var mmsmsm = $("#answerWrHideiframe")
							.on("load",function(){
								var iframeContents = $(this).contents().find('body').html();
					
								bbsmodal({ type: 'alert',size:"small",theme: 'alert',text: iframeContents,callback:function(){location.reload();} });		
								$("#answerWrHideiframe").remove();
								$(this).unbind('load');
								return false;
							});	
						}
					}
				});	*/
				
	            /*$('<div>'+vv+'</div>').dialog({
	                modal: true,   // 모달로 표시
	                width: 600,    // Dialog의 너비
	                height: 400,   // Dialog의 높이
	             
	                buttons: {
	                    "닫기": function() {
	                        $(this).dialog("close");
	                    },
	                    "답글달기": function() {
	                    	
							if ($("#answerWrHideiframe").length < 1) {
						        var $io = $('<iframe name="answerWrHideiframe" id="answerWrHideiframe" src="" />');
						        $io.css({ position: 'absolute', top: '-10000px', left: '-10000px',width:'0px',height:'0px' });		
						        $($io).appendTo("body");
							}
							
							var frm = $("#answerWriteFrm");
							frm.attr("method", "post"); 
							frm.attr("action", "/khboard/json/khboard_json.php");
							frm.attr("target", "answerWrHideiframe");
							//frm.attr("accept-charset","utf-8");
							frm.submit();
					
							var mmsmsm = $("#answerWrHideiframe")
							.on("load",function(){
								var iframeContents = $(this).contents().find('body').html();
					
								bbsmodal({ type: 'alert',size:"small",theme: 'alert',text: iframeContents,callback:function(){location.reload();} });		
								$("#answerWrHideiframe").remove();
								$(this).unbind('load');
								return false;
							});			
	
							                   	
	                        $(this).dialog("close");
	                    }
	                }
	            });	*/
				
				
//	            var editorId = vv.editorId || '#answerText';  // 응답 데이터에서 editorId를 가져오거나 기본값으로 #data_21 설정
//	
//	            // 3. CKEditor 초기화 후 editor 객체를 전역에서 사용할 수 있도록
//	            initializeCKEditor(editorId, '')  // editorId를 파라미터로 전달
//	                .then(editor => {
//	                    window.editor = editor;  // 전역적으로 editor 객체 저장
//	                    console.log('Editor initialized successfully:', editor);
//	                })
//	                .catch(error => {
//	                    console.error('Failed to initialize editor:', error);
//	                });		

			});
			
		});    	
    	
    	
		
    });	//ready end;
    
    
    

</script>

<style>
    /* 미리보기 팝업 스타일 */
    #popupOverlay {
        
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7); /* 반투명 배경 */
        z-index: 1000; /* 팝업이 다른 요소들 위에 뜨도록 */
    }

    #popupContent {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        width: 80%;
        max-width: 500px;
        text-align: center;
    }

    /* 닫기 버튼 스타일 */
    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #ff0000;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        font-size: 18px;
        cursor: pointer;
    }
</style>
<script>
        // 팝업 플러그인 정의
        (function($) {
            $.fn.popup = function(options) {
                // 기본 설정
                var settings = $.extend({
                    trigger: this,  // 팝업을 띄우기 위한 트리거 요소
                    overlay: $("#popupOverlay"),  // 팝업 오버레이
                    content: $("#popupContent"),  // 팝업 콘텐츠
                    closeBtn: $("#closePopup")  // 닫기 버튼
                }, options);

                // 팝업 열기
                function openPopup() {
                    settings.overlay.fadeIn();  // 팝업을 화면에 표시
                }

                // 팝업 닫기
                function closePopup() {
                	
                    settings.overlay.fadeOut(); // 팝업을 숨긴다
                    $("#popupOverlay").remove();
                }

                // 트리거 요소 클릭 시 팝업 열기
                settings.trigger.click(function() {
                    openPopup();
                });

                // 닫기 버튼 클릭 시 팝업 닫기
                settings.closeBtn.click(function() {
                    closePopup();
                });

                // 오버레이 배경 클릭 시 팝업 닫기
                settings.overlay.click(function(event) {
                    if ($(event.target).is(settings.overlay)) {
                        closePopup();
                    }
                });

                // return this to allow chaining
                return this;
            };
        })(jQuery);

    </script>    