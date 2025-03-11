<?php
	require($_SERVER['DOCUMENT_ROOT'] . "/khboard/inc/bbs_setting.php");
	@ksort($bbslistArray);
	
	$ploCode = (isset($_REQUEST['ploCode']) ? $_REQUEST['ploCode'] : ""); // 로컬프로그램 구분
	
	//echo $_SERVER['REMOTE_ADDR'];
?>
<style>
@media( max-width: 640px ) { 

    table.bbsresponsive-table {
        box-shadow: none;
    }
    table.bbsresponsive-table thead,table.bbsresponsive-table thead colgroup {
        display: none;
    }
    table.bbsresponsive-table th{
   		display: block  !important;
    }
	table.bbsresponsive-table th{
		border-top: none  !important;
		border-bottom: none  !important;
		width:100%  !important;
	}    
    
  
	
    table.bbsresponsive-table tr,
    table.bbsresponsive-table td {
        display: block;
    }
    
    table.bbsresponsive-table tr {
        
        margin-bottom: 0.5em;
        position: relative;
    }
    
    table.bbsresponsive-table td {
        border-top: none;
    }
    
    table.bbsresponsive-table td.organisationnumber {
        background: #f2f2f2;
       
        border-top: 1px solid #ddd;
    }
    
    table.bbsresponsive-table td.actions {
        background: none;
        border: none;
        position: absolute;
        right: 0;
        top: 0;
        vertical-align:top;
        margin-right:3px;
        padding-top:13px;
    }      
    
	.bbstable tbody tr:hover td {
	    background: none;
	}
	
    table.bbsresponsive1-table {
        box-shadow: none;
    }
    table.bbsresponsive1-table thead,table.bbsresponsive1-table thead colgroup {
        display: none;
    }
    table.bbsresponsive1-table th{
   		display: block  !important;
    }
	table.bbsresponsive1-table th{
		border-top: none  !important;
		border-bottom: none  !important;
		width:100%  !important;
	}    
	
	table.bbsresponsive1-table tr {
		background:#fff !important;
       
       
    }
    
    table.bbsresponsive1-table td {
         border: none;
         display: inline-block;
         height:auto;
    }	
    

    table.bbsresponsive1-table td.comma:after {
    	content:', '; 
    	 display: inline-block;
    }
    
    table.bbsresponsive1-table td.displaynone {
    	display: none;
    }
    
    table.bbsresponsive1-table td.displayblock {
    	display: block;
    	text-align:left;
    }
    
    table.bbsresponsive1-table td.displayblock a {
    	display: block;
    	font-size:15px;
    }
    
    table.bbsresponsive1-table td.actions {
        background: url(/khboard/img/icon_menus11.png) no-repeat !important;
        background-position: center right;
        width:36px;
        border: none;
        position: absolute;
        right: 0;
        top: 1;
        
        margin-right:18px;
        margin-top:-15px;
    }      
}


</style>
    
<?php
	//학교명칭 빼고 다 가림
	function maskSchoolName($schoolName) {
	    // 학교 이름에서 '학교'를 기준으로 앞부분만 별로 가리기
	    $position = strpos($schoolName, '학교');
	    if ($position !== false) {
	        $namePart = substr($schoolName, 0, $position); // '학교' 앞부분
	        $stars = str_repeat('*', mb_strlen($namePart)); // '*'로 앞부분 길이만큼 채우기
	        $maskedName = $stars . substr($schoolName, $position); // '*'로 변환한 부분과 '학교'를 결합
	        return $maskedName;
	    }
	    return $schoolName; // '학교'가 없으면 원래 이름 반환
	}
	
	//학교명칭 말고 전체 뒤두자리 빼고 가리기
	function maskText($text) {
	    // 텍스트 길이가 2보다 크면 처리
	    if (mb_strlen($text, 'UTF-8') > 2) {
	        // 뒤의 두 문자를 제외하고 나머지 문자들을 '*'로 마스킹
	        $maskedPart = str_repeat('*', mb_strlen($text, 'UTF-8') - 2); 
	        $visiblePart = mb_substr($text, -2, 2, 'UTF-8');  // 마지막 두 문자
	        return $maskedPart . $visiblePart; // '*'와 마지막 두 문자를 결합
	    }
	    // 텍스트 길이가 2 이하일 경우 원래 텍스트 반환
	    return $text;
	}


	function substrhan($str, $len, $footer='') { 
		if(strlen($str) <= $len) { 
		return $str; 
		} 
		else { 
		$len = $len - strlen($footer); 
		for($i=0; $i<$len; $i++) if(ord($str[$i])>127) $i++; 
		if($i > $len) $i-=2; 
		$str=substr($str,0,$i); 
		return $str.$footer; 
		} 
	} 
	
	$return_pageNumber = ($_REQUEST['pageNumber']-1);	
	if($_REQUEST['pageNumber']) $dfnos = ($return_pageNumber * $defPageTotal);
	else  $dfnos = 0;	
	
	/*
	 * 게시판 
	 */
	$bbsSQLto = "";
	if($bbs_user_level >= "99") { /* 관리자 권한으로 모든글 출력 */ }
	else {
		if($bbs_user_tspType == "T") {
			//선생님의 경우 공지대상이 지정이 없거나 공지 대상이 선생님을 포함한 경우
			//$bbsSQLto .= " AND ((infoyn = 'N') OR (info_type LIKE '%T%') or (memb_no = '" . $bbs_user_code . "')) ";		
		}
		else {
			//학생,학부모의 경우 공지대상 지정이 없거나 지정 옵션을 선택한
//			$bbsSQLto .= "";
			$bbsSQLto .= "
			AND (
				(infoyn = 'N') OR (
					info_type LIKE '%" . $bbs_user_tspType . "%' 
					AND (info_sex LIKE '%" . $bbs_user_sex . ($bbs_user_sex ? ",":"") . "%' OR info_sex='' OR info_sex IS NULL) 
					AND (info_hakgwa LIKE '%" . $bbs_user_hakgwa . ($bbs_user_hakgwa ? ",":"") . "%' OR info_hakgwa='' OR info_hakgwa IS NULL) 
					AND (info_hakyear LIKE '%" . $bbs_user_hakyear . ($bbs_user_hakyear ? ",":"") . "%' or info_hakyear='' OR info_hakyear IS NULL)
					AND (info_hakban LIKE '%" . $bbs_user_hakban . ($bbs_user_hakban ? ",":"") . "%' OR info_hakban='' OR info_hakban IS NULL)
					AND (info_dorm LIKE '%" . $dormchk . "%' OR info_dorm='' OR info_dorm IS NULL)
				)
			)";
		}	
	}
	
	
	$writelevel = explode(",",$settingOptArray['읽기권한']); //N,Y,Y,Y,N,N,Y,Y,Y,N,N,Y,N,N,N,N,Y
	//0 게시판에 글노출 여부
	if($writelevel[0] == "Y") 
	{
		//학생,학부모의 경우 공지대상 지정이 없거나 지정 옵션을 선택한
		$userLevel .= "
		AND (
			(infoyn = 'N') OR (
				info_type LIKE '%" . $bbs_user_tspType . "%' 
				AND (info_sex LIKE '%" . $bbs_user_sex . ($bbs_user_sex ? ",":"") . "%' OR info_sex='' OR info_sex IS NULL) 
				AND (info_hakgwa LIKE '%" . $bbs_user_hakgwa . ($bbs_user_hakgwa ? ",":"") . "%' OR info_hakgwa='' OR info_hakgwa IS NULL) 
				AND (info_hakyear LIKE '%" . $bbs_user_hakyear . ($bbs_user_hakyear ? ",":"") . "%' or info_hakyear='' OR info_hakyear IS NULL)
				AND (info_hakban LIKE '%" . $bbs_user_hakban . ($bbs_user_hakban ? ",":"") . "%' OR info_hakban='' OR info_hakban IS NULL)
				AND (info_dorm LIKE '%" . $dormchk . "%' OR info_dorm='' OR info_dorm IS NULL)
			)
		)";
	}
	
	
	
	//이름,제목,카테1,카테2,입력일
	$searchs = explode("^",$searchoption);
	if($searchs[0] == "Y") 
	{
		//if($searchs[1] == "Y") $bbsSQLto .= " AND subject LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
		
		if($searchs[1] == "Y" && $_REQUEST['searchtype'] == "17") $bbsSQLto .= " AND memb_name LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
		else if($searchs[2] == "Y" && $_REQUEST['searchtype'] == "18") $bbsSQLto .= " AND subject LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
		else if($searchs[3] == "Y" && $_REQUEST['searchtype'] == "19") $bbsSQLto .= " AND category LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
		else if($searchs[4] == "Y" && $_REQUEST['searchtype'] == "20") $bbsSQLto .= " AND category2 LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
		else if($searchs[5] == "Y" && $_REQUEST['searchtype'] == "24") $bbsSQLto .= " AND input_date LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
		else if($searchs[6] == "Y" && $_REQUEST['searchtype'] == "28") $bbsSQLto .= " AND memb_schoolnm LIKE '%" . ajax_rawurldecode($_REQUEST['bbs_list_search']) . "%' "; 		
	}
	
	/*
	*	공지사항
	*/
	$fixedbbsSQL = $db -> query("
	SELECT * FROM " . $board_database . "
	WHERE board_code='" . $board_code . "' 
	" . ($board_sub == "" ? "" : " AND board_subname='" . $board_sub . "' ") . "
	AND use_notice = '1' 
	" . $bbsSQLto . "
	" . $userLevel . " 
	ORDER BY reg_date DESC ");	
	
	$fixed_tr_html ="";
	while ($fix_r = $fixedbbsSQL -> fetch_array()) {

		//파라미터 설정
		$targeturl = md5('read');
		$idxcode = md5(trim($fix_r['newcode']));
		$q_param = $targeturl . $idxcode;	//최종처리
		
		//비밀글 제어 체크
		if($fix_r['pwd'])
		{
//			$aTaghref = "<a " . ($bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE" || $bbs_user_level >= "99" ? " href='?" . $add_url_param . "&q=" . $q_param ."' " : " href='#' class='pwdinfoclass' ") . "   java.val='" . ($q_param) . "' java.href='" . $add_url_param . "&q=" . $q_param ."' >";
//			$lockimg = "<img src='/khboard/img/ic_lock.gif' style='vertical-align:middle;width:16px;'>";
			$aTaghref = "<a href='?" . $add_url_param . "&q=" . $q_param ."'>";
			$lockimg = "";
		}
		else
		{
			$aTaghref = "<a href='?" . $add_url_param . "&q=" . $q_param ."'>";
			$lockimg = "";
		}
		
		$fixed_tr_html .= "<tr class='notice-title'>";
		
		if(sizeof($bbslistArray) > 0) {
			$cn=1;
	    	foreach ($bbslistArray as $key => $value) {
	    		foreach ($value as $ky => $val) {
	    			if($ky == "16") $fixed_tr_html .= "<td data-label='".$val."' class='displaynone'>" . $aTaghref . "<span style=''>공지</span></a></td>";			
	    			//제목
	    			else if($ky == "18") $fixed_tr_html .= "<td data-label='".$val."' class='displayblock' style='text-align:left;'>" . $aTaghref . "" . ($fix_r['category'] ? "[" . $fix_r['category'] . "]" : "") . "" . $fix_r[$fildlistArray[$ky]] . " " . $lockimg . "</a></td>";
	    			//다운로드 처리
	    			else if($ky == "25"){
	    				$fileupsu = 0;
						if($fix_r['file_name']) {
							$files = explode("^",$fix_r['file_name']);
							$dir = explode("^",$fix_r['file_dir']);
					    	$filetype = explode("^",$fix_r['file_type']);						
					    	$fileupsu = (is_array($files) && sizeof($files) > 0 ? sizeof($files) : 0);
						}
	    				
						if($subViewTypeArr[$ky] == "Y"){
							$fixed_tr_html .= "<td class='displaynone' data-label='".$val."'>" . ($fileupsu > 0 ? "<img src='/img/menu01/file-downroad-icon.png' style='width:35px;'>" : "") . "</td>";	"";
						} else {
							if($fileupsu > 1) $fixed_tr_html .= "<td  data-label='".$val."' class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "'>" . $aTaghref . "<img src='/img/menu01/file-downroad-icon.png' style='width:35px;'></a></td>";										
							else if($fileupsu == 1){
								$fixed_tr_html .= "
								<td  data-label='".$val."' class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "'>
									<a href='/khboard/json/khboard_download.php?dir=" . $dir[0] . "&name=" . rawurlencode($files[0]) . "'title='다운로드'>
									<img src='/img/menu01/file-downroad-icon.png' style='width:35px;'>
									</a>								
								</td>";										
							}
							else $fixed_tr_html .= "<td class='displaynone' data-label='".$val."'></td>";	"";
						}
						
	    			}

	    			
	    			    			
	    			else { 
	    				$fixed_tr_html .= "<td data-label='".$val."' " . ($ky=="22" ? " class='actions' " : " class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "' ") . ">" . $aTaghref . "" . ($ky == "24" ? substr($fix_r[$fildlistArray[$ky]],0,10) : $fix_r[$fildlistArray[$ky]]) . "</a></td>";			
	    			}
	    		}
	    		
	    		if($cn == 1){
					//유형별 게시판 카테고리 보기(유형별 게시판 분리, 카테고리 설정과 다름, 관리자 설정 확인)
					if($board_sub == "" && (is_array($bbsSubCodeArray[$board_code]) && sizeof($bbsSubCodeArray[$board_code]) > 0) && $settingOptArray['유형별게시판분리'] == "Y"){
						$fixed_tr_html .= "<td>" . ($bbsSubCodeArray[$board_code][$fix_r['board_subname']]['TITLE']) . "</td>";
					}		    			
	    		}
	    		
	    		$cn++;  
	    	}
		}
		
		$fixed_tr_html .= "</tr>";		
	}	

	
	
	$bbsSQL = "
	SELECT 
		*,(select count(*)  from " . $board_database . " where  board_code='" . $board_code . "' " . ($board_sub == "" ? "" : " AND board_subname='" . $board_sub . "' ") . " " . $bbsSQLto . " ) as cnt 
	FROM " . $board_database . "
	WHERE board_code='" . $board_code . "' 
	" . ($board_sub == "" ? "" : " AND board_subname='" . $board_sub . "' ") . "
	" . $bbsSQLto . "
	" . $userLevel . "";
	
	$bbsSQL .= "
	ORDER BY input_date DESC ";
	

	if($defPageTotal != "") { 
		$bbsSQL .= " LIMIT " . (($return_pageNumber) > 0 ? ($defPageTotal*$return_pageNumber) : 0) . "," . $defPageTotal . ";";
	}
	
	// echo $bbsSQL;
	$rst = $db->query($bbsSQL);
	
	$tr_html ="";
	$mobileHtml = "";
	$i=0;
	while($row = $rst-> fetch_array())
	{
		if($i==0){
			$cntA = $row['cnt'] - $dfnos;
			$toCnt = $row['cnt'];
		}
		
		$exp = explode("</p>",$row['contents']);
		//$imgprt = explode("</span>",$row['contents']);

		if(preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $row['contents'], $match) == 0) {  
			//본문에 이미지가 없는 경우 업로드 파일이라도 있는지 조회후 있으면 이미지 보여주고 아니면 NO출력
			if($row['file_name'] != "")
			{
				$fileN = explode("^",$row['fileExt']);
				$fileDIR = explode("^",$row['file_dir']);
				$fileNM = explode("^",$row['file_name']);
	
				foreach($fileN AS $k => $value)
				{
					$filepath = $_SERVER['DOCUMENT_ROOT'] . "/khboard/upload_file/" . $fileDIR[$k] . "/" . $fileNM[$k];
					if($value=="jpg" || $value=="JPG" || $value=="gif" || $value=="GIF" || $value=="jpeg" || $value=="JPGE" || $value=="png" || $value=="PNG"){
						if(file_exists($filepath))
						{
							$NewIMGSRC = "<img src='/khboard/upload_file/" . $fileDIR[$k] . "/" . $fileNM[$k] . ".thumb_500' style='width:100px;height:100px;' alt='이미지'>";
						}else{
							$NewIMGSRC = "<img src='/khboard/img/noimage.gif' style='width:100px;height:100px;' alt='이미지'>";
						}					
					}
					else
					{
						if(file_exists($filepath))
						{
							$a_option ="class='media {width:150, height:120}' href='/khboard/upload_file/" . $fileDIR[$k] . "/" . $fileNM[$k] . "'";
							$NewIMGSRC = "<img src='/khboard/img/noimage.gif' style='width:100px;height:100px;' alt='이미지'>";
						}else{
							$a_option ="";
							$NewIMGSRC = "<img src='/khboard/img/noimage.gif' style='width:100px;height:100px;' alt='이미지'>";
						}					
					}
				}
				
			}else
			{
				$NewIMGSRC = "<img src='/khboard/img/noimage.gif' style='width:100px;height:100px;' alt='이미지'>";
			}		
		} else {  
			// "본문에 이미지가 포함";  
			preg_match_all('/src=\"(.[^"]+)"/i', $row['contents'], $src_location);
			//print_r($src_location);
			if(($src_location[0][0]) == ""){
				$NewIMGSRC = "<img src='/khboard/img/noimage.gif' style='width:100px;height:100px;' alt='이미지'>";
			}
			else{
				$NewIMGSRC = '<img ' . ($src_location[0][0]) . ' style="width:100px;height:100px;" alt="이미지">';
			}
		}  
	
		$titlenew = "";
		$ii=0;
		$mm=0;
		for($i=0; $i < 20; $i++)
		{
			$targethtml = trim(strip_tags($exp[$i]));
			if(@preg_match("/날짜 :/i", $targethtml) || @preg_match("/장소 :/i", $targethtml) || @preg_match("/시간 :/i", $targethtml))
			{
				$titlenew .= "<div style='margin-top:5px;'>".substrhan(strip_tags($exp[$i]),66,"...") . "</div>"; 	
				$mm++;				
			}
		}	
		
		if($mm == 0){
			$titlenew = "";
		}		
		
		
		//파라미터 설정
		$targeturl = md5('read');
		$idxcode = md5(trim($row['newcode']));
		$q_param = $targeturl . $idxcode;	//최종처리
		
		//비밀글 제어 체크
		if($row['pwd'])
		{
			$aTaghref = "<a " . ($bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE" || $bbs_user_level >= "99" ? " href='?" . $add_url_param . "&q=" . $q_param ."' " : " href='#' class='pwdinfoclass' ") . "   java.val='" . ($q_param) . "' java.href='" . $add_url_param . "&q=" . $q_param ."' >";
			$lockimg = "<img src='/khboard/img/ic_lock.gif' style='vertical-align:middle;width:16px;'>";
		}
		else
		{
			$aTaghref = "<a href='?" . $add_url_param . "&q=" . $q_param ."' style='display:block;'>";
			$lockimg = "";
		}
		
		$tr_html .= "<tr>";
		
		if(sizeof($bbslistArray) > 0) {
			$cn=1;
	    	foreach ($bbslistArray as $key => $value) {
	    		foreach ($value as $ky => $val) {
	    			//순번
	    			if($ky == "16") $tr_html .= "<td class='displaynone' data-label='".$val."'>" . $aTaghref . "" . ($cntA--) ."</a></td>";			
	    			
	    			//제목
	    			else if($ky == "18") $tr_html .= "<td class='displayblock' style='text-align:left;' data-label='".$val."'>" . $aTaghref . "" . ($row['category'] ? "[" . $row['category'] . "]" : "") . "" . $row[$fildlistArray[$ky]] . " " . $lockimg . "</a></td>";
	    			
	    			//다운로드 처리
	    			else if($ky == "25"){
	    				$fileupsu = 0;
						if($row['file_name']) {
							$files = explode("^",$row['file_name']);
							$dir = explode("^",$row['file_dir']);
					    	$filetype = explode("^",$row['file_type']);						
					    	$fileupsu = (is_array($files) && sizeof($files) > 0 ? sizeof($files) : 0);
						}
	    				
						if($subViewTypeArr[$ky] == "Y"){
							$tr_html .= "<td class='displaynone' data-label='".$val."'>" . ($fileupsu > 0 ? "<img src='/img/menu01/file-downroad-icon.png' style='width:35px;'>" : "") . "</td>";	"";
						} else {
							if($fileupsu > 1) $tr_html .= "<td  data-label='".$val."' class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "'>" . $aTaghref . "<img src='/img/menu01/file-downroad-icon.png' style='width:35px;'></a></td>";										
							else if($fileupsu == 1){
								$tr_html .= "
								<td  data-label='".$val."' class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "'>
									<a href='/khboard/json/khboard_download.php?dir=" . $dir[0] . "&name=" . rawurlencode($files[0]) . "'title='다운로드'>
									<img src='/img/menu01/file-downroad-icon.png' style='width:35px;'>
									</a>								
								</td>";										
							}
							else $tr_html .= "<td class='displaynone' data-label='".$val."'></td>";	"";
						}
						
	    			}
					//학교명
					else if($ky == "28") {
						$tr_html .= "<td  data-label='".$val."' class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "' >" . $aTaghref . "" . ($subViewTypeArr[$ky] == "Y" ?maskText($row[$fildlistArray[$ky]]) : $row[$fildlistArray[$ky]]) . "</a></td>";			
					}
					//답변상태
					else if($ky == "29") {
						if(trim($row[$fildlistArray[$ky]]) == "답변완료") $vvvv = "<span style='color:#2A62D2;'>" . $row[$fildlistArray[$ky]] . "</span>";
						else $vvvv = $row[$fildlistArray[$ky]];

						$tr_html .= "<td  data-label='".$val."' class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "' >" . $aTaghref . "" . ($vvvv) . "</a></td>";			
					}	    	
					
					//22 조회,24입력일
	    			else { 
	    				$tr_html .= "<td  data-label='".$val."' " . ($ky=="22" ? " class='actions' " : " class='" . (sizeof($bbslistArray) == $cn ? "notcomma":"comma") . "' ") . ">" . $aTaghref . "" . ($ky == "24" ? substr($row[$fildlistArray[$ky]],0,10) : $row[$fildlistArray[$ky]]) . "</a></td>";			
	    			}
	    		}
	    		
	    		if($cn == 1){
					//유형별 게시판 카테고리 보기(유형별 게시판 분리, 카테고리 설정과 다름, 관리자 설정 확인)
					if($board_sub == "" && (is_array($bbsSubCodeArray[$board_code]) && sizeof($bbsSubCodeArray[$board_code]) > 0) && $settingOptArray['유형별게시판분리'] == "Y"){
						$tr_html .= "<td>" . ($bbsSubCodeArray[$board_code][$row['board_subname']]['TITLE']) . "</td>";
					}		    			
	    		}	    		
	    		
	    		$cn++;  
	    	}
		}
		
		$tr_html .= "</tr>";
			
		$i++;
	}
	
	$avTno = (@round($toCnt / $defPageTotal,2) ? @round($toCnt / $defPageTotal,2) : 0);
	$toCntS = ($avTno ? ceil($avTno) : 0);	
	
//echo $toCnt."<>".$defPageTotal."...".$avTno."..".$toCntS;
?>

<!-- 삭제 하지 마세요 -->
<input type='hidden' name='pageAllcnt' id='pageAllcnt' value='<?= ($toCntS ? $toCntS : 0) ?>'><!-- 총 페이지 수 -->
<div style="width:100%;margin:0 auto;">

<!-- 검색 창 -->
<?php
if($searchs[0] == "Y")
{
	$viewYn = "N";
	if($searchs[7] == "Y"){
		if($bbs_user_level >= 99 || $bbsIpAuthArray[$_SERVER['REMOTE_ADDR']] == "TRUE") $viewYn = "Y";
	}
	else  $viewYn = "Y";
	
	if($viewYn == "Y"){	
		
?>
	<div style="text-align:right;margin-bottom:10px;">
		<table align="right" class="bbs-search-tablebox" summary="검색창 출력">
			<tr>
				<td width="100" style="text-align:right;padding-right:10px;">
					<select id="searchoptions" name="searchoptions" class="bbs-search-options">
						<option value="">검색조건</option>
	<?php
						if($searchs[1] == "Y") echo "<option value='17' " . ($_REQUEST['searchtype'] == "17" ? " SELECTED " : "") . ">" . ($bbssearchArray['17']) . "</option>";
						if($searchs[6] == "Y") echo "<option value='28' " . ($_REQUEST['searchtype'] == "28" ? " SELECTED " : "") . ">" . ($bbssearchArray['28']) . "</option>";
						if($searchs[2] == "Y") echo "<option value='18' " . ($_REQUEST['searchtype'] == "18" ? " SELECTED " : "") . ">" . ($bbssearchArray['18']) . "</option>";
						if($searchs[3] == "Y") echo "<option value='19' " . ($_REQUEST['searchtype'] == "19" ? " SELECTED " : "") . ">" . ($bbssearchArray['19']) . "</option>";
						if($searchs[4] == "Y") echo "<option value='20' " . ($_REQUEST['searchtype'] == "20" ? " SELECTED " : "") . ">" . ($bbssearchArray['20']) . "</option>";
						if($searchs[5] == "Y") echo "<option value='24' " . ($_REQUEST['searchtype'] == "24" ? " SELECTED " : "") . ">" . ($bbssearchArray['24']) . "</option>";
	?>
					</select>
				</td>
				<th width="150" style="text-align:right;padding-right:30px;"><input type="text" class="bbs-search-input" id="bbs_list_search" placeholder=" 검색어" value="<?= ajax_rawurldecode($_REQUEST['bbs_list_search']) ?>" title="검색어"></th>
				<td width="100"><div class="bbs-search-btn" id="bbs_list_search_button">검색</div></td>
			</tr>
		</table>
		<div style="clear:both;"></div>
	</div>
<?php
	}
}
?>

	<div style="position:relative;">
		<?= (ReadAuth() == "Y" ? "" : "<div style='position:absolute;top:0;bottom:0;left:0;right:0;' class='authcheckDiv'></div>")?>
		<table class="bbstable bbsresponsive<?= ($tr_html ? '1' : "") ?>-table bbslayout" cellpadding="0"  cellspacing="0">
		    <thead>
			  	<tr>
<?php
			  	if(sizeof($bbslistArray) > 0) {
			  		$mNo=1;
					foreach ($bbslistArray as $key => $value){
						foreach ($value as $ky => $val) {
							echo "<th style='text-align:center;'>" . $val . "</th>";
						}
						
						//유형별 게시판 카테고리 보기(유형별 게시판 분리, 카테고리 설정과 다름, 관리자 설정 확인)
						if($mNo == 1){
							if($board_sub == "" && (is_array($bbsSubCodeArray[$board_code]) && sizeof($bbsSubCodeArray[$board_code]) > 0) && $settingOptArray['유형별게시판분리'] == "Y"){
								echo "<th style='text-align:center;'>구분</th>";
							}
						}
						
						$mNo++;
					}
			  	}
?>
			    </tr>
		    </thead>
		    <tbody>
		    	<?= $fixed_tr_html ?>
		    	<?= ($tr_html ? $tr_html : "<tr><td style='text-align:center;line-height:50px;' colspan='" . (sizeof($bbslistArray)) . "'>등록된 글이 없습니다.</td></tr>") ?>
		    </tbody>
		</table>
	</div>
	
	<!-- 페이징 영역 --> 
	<div>
		<table style='width:100%;margin-top:10px;' summary="페이징 영역">
			<tr>
				<th colspan="2" valign="top" align="left" style='width:90%'>
					<div class="pagination-wrapper-left">
						<div id='pageingDiv' ></div>
					</div>		
				</th>
			</tr>
			<tr>
				<td style="text-align:left;">
<?php
		if($bbs_user_level >= "99" && $settingOptArray['데이터다운로드'] == "Y"){ 
			echo "<div class='bbs_mbutton bbs_bg_gray' id='excel_down' style='width:100px;'><span style='color:#fff;'>다운로드</span></div>";
		}
?>
				</td>
<?php
		//글쓰기 권한 및 로그인 상태 체크  관리자 , 비회원
		if(WriteAuth() == "Y") {
			echo "<td valign='top' align='right' style='width:10%'><a href='?".$add_url_param."&q=".md5('write')."'><div class='bbs_mbutton bbs_bg_blue bbswrite' id='bbswrite'><span style='color:#fff;'>글쓰기</span></div></a></td>";
		}
		
?>
			</tr>
		</table>
	</div>	
</div>


<!--비밀번호 모달창-->
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


<?php
	if ($ploCode == "sn") {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_sn_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_sn_comm.css" />
<link rel="stylesheet" href="/khboard_local/js/simplePagination/css/simplePagination_sn.css">
<?php
	} else if ($ploCode == "jung" || $ploCode == "high") {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_high_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_high_comm.css" />
<link rel="stylesheet" href="/khboard_local/js/simplePagination/css/simplePagination_high.css">
<?php
	} else {
?>
<link rel="stylesheet" href="/khboard_local/css/khboard_button.css" />
<link rel="stylesheet" href="/khboard_local/css/khboard_comm.css" />
<link rel="stylesheet" href="/khboard_local/js/simplePagination/css/simplePagination.css" />
<?php
	}
?>

<link rel="stylesheet" href="/khboard_local/js/bbsmodal/css/jquery.modal.css">
<script  src="/khboard_local/js/bbsmodal/js/jquery.modal.js" type="text/javascript"  /></script>
<link rel="stylesheet" href="/khboard_local/css/khboard_modal.css">
<script src="/khboard_local/js/js_list.js?k=<?= $scrcode ?>&j=<?=md5($board_code)?>&vv=<?=time()?>" type="text/javascript" /></script>

<script  src="/khboard_local/js/simplePagination/jquery.simplePagination.js" type="text/javascript"  /></script>

<script>

$(document).ready(function(){

	//검색하기 searchoptions
	$("#bbs_list_search_button").unbind().click(function(){
		location.href="?bbscaseBy=list&<?=$add_url_param?>&searchtype=" + $("#searchoptions option:selected").val() + "&bbs_list_search=" + encodeURIComponent($("#bbs_list_search").val());
	});
	
	var pageAllcnt = ($("#pageAllcnt").val() ? parseInt($("#pageAllcnt").val()) : 0);

	if(pageAllcnt > 1)
	{
		$('#pageingDiv').pagination({
			items: 1,   // Total number of items that will be used to calculate the pages.
			itemsOnPage: 1, // Number of items displayed on each page.
			pages:pageAllcnt,   // If specified, items and itemsOnPage will not be used to calculate the number of pages.
			maxVisible: 3, 
			displayedPages:10, // How many page numbers should be visible while navigating. Minimum allowed: 3 (previous, current & next)
			//edges:2,    // How many page numbers are visible at the beginning/ending of the pagination.
			currentPage: <?= ($_REQUEST['pageNumber'] ? $_REQUEST['pageNumber'] : 1)?>, // Which page will be selected immediately after init.
			hrefTextPrefix: "#page-", // A string used to build the href attribute, added before the page number.
			hrefTextSuffix: '', // Another string used to build the href attribute, added after the page number.
			prevText: "이전", // Text to be display on the previous button.
			nextText: "다음", // Text to be display on the next button.
			cssStyle: "light-theme", // The class of the CSS theme.
			selectOnClick: true,
			onPageClick: function(pageNumber, event) {
				var param = "?<?=$add_url_param?>&q=<?=md5('list')?>&pageNumber=" + pageNumber;
				if($("#searchoptions").length > 0) param += "&searchtype=" + $("#searchoptions option:selected").val();
				if($("#bbs_list_search").length > 0)  param += "&bbs_list_search=" + $("#bbs_list_search").val();
				location.href = param;
			}			
		});
	}


	//글쓰기(팝업창 생성)
	$(".bbswrite").unbind().click(function(){
		location.href="?<?=$add_url_param?>&q=<?=md5('write')?>";
	});

	
	
	<?php if($bbs_user_level >= "99" && $settingOptArray['데이터다운로드'] == "Y"){ ?>
	$("#excel_down").unbind().click(function(){
		location.href="/khboard/json/exceldown.php?target=<?=$scrcode?>";
	});
	<?php } ?>		
	
});

</script>


<div id="toast-container"></div>

<script>
// script.js
$(document).ready(function() {

    // 토스트 알림을 화면에 표시하는 함수
    function showToast(message) {
        var toast = $('<div class="toast"></div>').text(message);
        $('#toast-container').append(toast);

        // 알림이 나타난 후 3초 뒤에 사라지게 만듦
        setTimeout(function() {
            toast.css('animation', 'toast-hide 0.5s forwards');
        }, 1000);

        // 애니메이션이 끝난 후 DOM에서 삭제
        setTimeout(function() {
            toast.remove();
        }, 1500);
    }

    // 버튼 클릭 시 토스트 알림을 표시
    $('#show-toast').click(function() {
        showToast('토스트 알림입니다!');
    });
    
    $(".authcheckDiv").unbind().click(function(){
    	showToast('읽기 권한이 없습니다.');
    });

});

</script>
