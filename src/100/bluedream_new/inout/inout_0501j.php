<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . 'func/func_global.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . 'func/func_db.php';

	$caseBy = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['caseBy'])));
	if ($caseBy == "" || strtolower($_SERVER['REQUEST_METHOD']) != "post") {
		echo "
			<script language=\"javascript\">\n
				alert(\"[알림!] 잘못된 접근 입니다. 내용을 불러올수 없습니다.\");\n
			</script>\n
		";		
  		exit;	
	}

	/*******************************
	*	자료관리 > 위탁생 동아리활동
	*	@ author : 이근만
	*	@ since : 2020.12.21.
	*******************************/

	$db = doConnect();	// db connect
	
	$resOfSchInfo = $db->query("SELECT * FROM school_info WHERE LASTYN = 'Y' LIMIT 1;");
	$rowOfSchInfo = $resOfSchInfo->fetch_array();

	$ioGradeYear = $rowOfSchInfo['GRADEYEAR'];

	switch ($caseBy) {
		case "getClub" : 	// 현년도 재학생 동아리 목록
			$result = $db->query("SELECT * FROM member WHERE std_pyear = '" . $ioGradeYear . "' AND std_state = '재학' AND use_yn = 'Y' AND department2 <> '' AND department2 IS NOT NULL GROUP BY department2;");
			while ($row = $result->fetch_array()) {
				echo "<option value=\"" . $row['department2'] . "\">" . $row['department2'] . "</option>";
			}
		break;

		case "getList" : 	// 수상경력 조회
			$pHakgi = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pHakgi'])));
			$pUniqNo = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['uniqNo'])));
			$___tempArrayList = explode(",", $pUniqNo);

            for ($k = 0; $k < @sizeof($___tempArrayList); $k++) {
                if ($___tempArrayList[$k] != "") $tempArrayList[$___tempArrayList[$k]] = "TRUE";
            }
			
			$realCnt = 0;
			if (@sizeof($tempArrayList) > 0) {
				foreach ($tempArrayList AS $uniqKey => $uniqValue) {
					$rowCount = 0;
					$ptmt = $db->query("SELECT *, DATE_FORMAT(ioItem01, '%Y.%m.%d.') AS inputDate FROM school_data WHERE uniqKey = '" . $uniqKey . "' AND ioYear = '" . $ioGradeYear . "' AND ioHakgi = '" . $pHakgi . "' AND (ioDiv = '동아리활동' OR ioDiv = '동아리활동-학생부') ORDER BY ioDiv, ioItem01;");
					while ($rs = $ptmt->fetch_array()) {
						if ($rowCount == 0) {
?>
	<tr>
		<td><img style="cursor:pointer;" src="/images/<?= $ptmt->num_rows > 1? "nolines_plus" : "nolines_minus" ?>.gif" class="<?= $ptmt->num_rows > 1? "inout0501vExtendClass" : "" ?>" valid.blank="inout0501vExtendHiddenClass<?= $uniqKey ?>" align="absmiddle"></td>
		<td><?= $rs['sBan'] ?></td>
		<td><?= $rs['sBun'] ?></td>
		<td><?= $rs['sName'] ?></td>

		<td><input type="checkbox" class="inout0501vChoiceItemClass" valid.uKeys="<?= $uniqKey ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" value="<?= $rs['idx'] ?>"></td>
		<td><?= $rs['ioHakgi'] ?>학기</td>
<?php
							if ($rs['ioDiv'] == '동아리활동') {
?>
		<td><?= $rs['inputDate'] ?></td>
		<td><?= stripslashes($rs['ioItem02']) ?></td>
		<td style="text-align:left;"><?= $rs['ioItem06'] == 'Y'? '<span style="font-weight:bold; color:blue;">[봉사연계]</span> ' : '' ?><?= nl2br(stripslashes($rs['ioItem03'])) ?></td>
		<td><?= stripslashes($rs['ioItem04']) ?></td>
<?php
							} else {
?>
		<td colspan='3' style="text-align:left;"><span style="font-weight:bold;">【 학교생활기록부 반영기록 】 : </span><?= $rs['ioItem02'] != ''? "<br>" . nl2br(stripslashes($rs['ioItem02'])) : nl2br(stripslashes($rs['ioItem02'])) ?></td>
		<td><?= stripslashes($rs['ioItem01']) ?></td>
<?php
							}
?>
		<td><a class="button smallrounded blue input0501vModifyClasses" valid.type="<?= $rs['ioDiv'] == '동아리활동'? '1' : '2' ?>" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">수정</a> <a class="button smallrounded rosy input0501vRemoveClasses" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">삭제</a></td>
	</tr>
<?php
						} else {
?>
	<tr class="inout0501vExtendHiddenClass<?= $uniqKey ?>" style="display:none;">
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>

		<td><input type="checkbox" class="inout0501vChoiceItemClass" valid.uKeys="<?= $uniqKey ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" value="<?= $rs['idx'] ?>"></td>
		<td><?= $rs['ioHakgi'] ?>학기</td>
<?php
							if ($rs['ioDiv'] == '동아리활동') {
?>
		<td><?= $rs['inputDate'] ?></td>
		<td><?= stripslashes($rs['ioItem02']) ?></td>
		<td style="text-align:left;"><?= nl2br(stripslashes($rs['ioItem03'])) ?></td>
		<td><?= stripslashes($rs['ioItem04']) ?></td>
<?php
							} else {
?>
		<td colspan='3' style="text-align:left;"><span style="font-weight:bold;">【 학교생활기록부 반영기록 】 : </span><?= $rs['ioItem02'] != ''? "<br>" . nl2br(stripslashes($rs['ioItem02'])) : nl2br(stripslashes($rs['ioItem02'])) ?></td>
		<td><?= stripslashes($rs['ioItem01']) ?></td>
<?php
							}
?>
		<td><a class="button smallrounded blue input0501vModifyClasses" valid.type="<?= $rs['ioDiv'] == '동아리활동'? '1' : '2' ?>" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">수정</a> <a class="button smallrounded rosy input0501vRemoveClasses" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">삭제</a></td>
	</tr>
<?php
						}
						
						$rowCount++;	// 한 학생의 데이터 개수 카운트
						$realCnt++;	// 조회 대상 모든 학생의 데이터 카운트
					}
				}
			}

			if ($realCnt <= 0) {
?>
	<tr>
		<td colspan="11" style="text-align:center; height:50px;">:: 조회된 데이터가 없습니다. ::</td>
	</tr>
<?php
			}
	 	break;
	 	
		case "doSave" : 
			$pUniqNo = addslashes(htmlspecialchars($_REQUEST['inout0501vTarget'], ENT_QUOTES, 'ISO-8859-1'));	// 대상 학생, 구분자 쉼표.
			
			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501vItem00'], ENT_QUOTES, 'ISO-8859-1'));	// 학기
			
			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem01'], ENT_QUOTES, 'ISO-8859-1'));	// 일시
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem02'], ENT_QUOTES, 'ISO-8859-1'));	// 이수시간
			$pItem03 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem03'], ENT_QUOTES, 'ISO-8859-1'));	// 활동내용
			$pItem04 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem04'], ENT_QUOTES, 'ISO-8859-1'));	// 부서명

			$pItem05 = $_REQUEST['inout0501vItem05'];	// 교시 선택

			$pItem06 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem06'], ENT_QUOTES, 'ISO-8859-1'));	// 봉사연계 Y

			$pItem05new = '';
			for ($x = 0; $x < @sizeof($pItem05); $x++) {
				$pItem05new .= $pItem05[$x] . ($x == (@sizeof($pItem05) - 1)? "" : ",");
			}

			$pItem01 = str_replace("&amp;", "&", $pItem01);
			$pItem02 = str_replace("&amp;", "&", $pItem02);
			$pItem03 = str_replace("&amp;", "&", $pItem03);
			$pItem04 = str_replace("&amp;", "&", $pItem04);

			$___tempArrayList = explode(",", $pUniqNo);

            for ($k = 0; $k < @sizeof($___tempArrayList); $k++) {
                if ($___tempArrayList[$k] != "") $tempArrayList[$___tempArrayList[$k]] = "TRUE";
            }

			if (@sizeof($tempArrayList) > 0) {
				foreach ($tempArrayList AS $uniqKey => $uniqValue) {
					$ptmt = $db->query("SELECT * FROM member WHERE std_pyear = '" . $ioGradeYear . "' AND member_level = '2' AND std_unique = '" . $uniqKey . "' AND use_yn = 'Y' LIMIT 1;");
					$rs = $ptmt->fetch_array();

					$ptmt2 = $db->query("SELECT IFNULL(MAX(ioSort) + 1, 1) AS maxValue FROM school_data WHERE uniqKey = '" . $uniqKey . "' AND ioDiv = '동아리활동';");
					$rs2 = $ptmt2->fetch_array();

					$db->query("
						INSERT INTO school_data SET 
							uniqKey = '" . $uniqKey . "', sHak = '" . $rs['hak'] . "', sBan = '" . $rs['ban'] . "', sBun = '" . $rs['bun'] . "', sName = '" . $rs['name'] . "', 
							ioYear = '" . $ioGradeYear . "', ioDiv = '동아리활동', ioSort = '" . $rs2['maxValue'] . "', 
							ioItem01 = '" . $pItem01 . "', 
							ioItem02 = '" . $pItem02 . "', 
							ioItem03 = '" . $pItem03 . "', 
							ioItem04 = '" . $pItem04 . "', 
							ioItem06 = '" . $pItem06 . "', 
							ioApplyTime = '" . $pItem05new . "', 

							ioHakgi = '" . $pItemHakgi . "', 

							ioRegIden = '" . $sess_Code . "', 
							ioRegist = NOW(); 
					");
				}
			}
			
			echo "true";
		break;
		
		case "doSave2" : 
			$pUniqNo = addslashes(htmlspecialchars($_REQUEST['inout0501vTarget2'], ENT_QUOTES, 'ISO-8859-1'));	// 대상 학생, 구분자 쉼표.

			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501v2Item00'], ENT_QUOTES, 'ISO-8859-1'));	// 학기

			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501v2Item01'], ENT_QUOTES, 'ISO-8859-1'));	// 부서명
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501v2Item02'], ENT_QUOTES, 'ISO-8859-1'));	// 특기사항

			$pItem01 = str_replace("&amp;", "&", $pItem01);
			$pItem02 = str_replace("&amp;", "&", $pItem02);
			
			$___tempArrayList = explode(",", $pUniqNo);

            for ($k = 0; $k < @sizeof($___tempArrayList); $k++) {
                if ($___tempArrayList[$k] != "") $tempArrayList[$___tempArrayList[$k]] = "TRUE";
            }

			if (@sizeof($tempArrayList) > 0) {
				foreach ($tempArrayList AS $uniqKey => $uniqValue) {
					$ptmt = $db->query("SELECT * FROM member WHERE std_pyear = '" . $ioGradeYear . "' AND member_level = '2' AND std_unique = '" . $uniqKey . "' AND use_yn = 'Y' LIMIT 1;");
					$rs = $ptmt->fetch_array();

					$ptmt2 = $db->query("SELECT IFNULL(MAX(ioSort) + 1, 1) AS maxValue FROM school_data WHERE uniqKey = '" . $uniqKey . "' AND ioDiv = '동아리활동-학생부';");
					$rs2 = $ptmt2->fetch_array();

					$db->query("
						INSERT INTO school_data SET 
							uniqKey = '" . $uniqKey . "', sHak = '" . $rs['hak'] . "', sBan = '" . $rs['ban'] . "', sBun = '" . $rs['bun'] . "', sName = '" . $rs['name'] . "', 
							ioYear = '" . $ioGradeYear . "', ioDiv = '동아리활동-학생부', ioSort = '" . $rs2['maxValue'] . "', 
							ioItem01 = '" . $pItem01 . "', 
							ioItem02 = '" . $pItem02 . "', 

							ioHakgi = '" . $pItemHakgi . "', 

							ioRegIden = '" . $sess_Code . "', 
							ioRegist = NOW(); 
					");
				}
			}
			
			echo "true";
		break;
		
		case "doUpdate" : 
			$pUniqKey = addslashes(htmlspecialchars($_REQUEST['pUniqIdx'], ENT_QUOTES, 'ISO-8859-1'));	// 대상 학생, 구분자 쉼표.

			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem00'], ENT_QUOTES, 'ISO-8859-1'));	// 학기
			
			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem01'], ENT_QUOTES, 'ISO-8859-1'));	// 학년
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem02'], ENT_QUOTES, 'ISO-8859-1'));	// 활동영역 또는 주제
			$pItem03 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem03'], ENT_QUOTES, 'ISO-8859-1'));	// 활동내용
			$pItem04 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem04'], ENT_QUOTES, 'ISO-8859-1'));	// 활동내용

			$pItem05 = $_REQUEST['inout0501vDialogItem05'];	// 교시 선택

			$pItem06 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem06'], ENT_QUOTES, 'ISO-8859-1'));	// 봉사연계 Y

			$pItem05new = '';
			for ($x = 0; $x < @sizeof($pItem05); $x++) {
				$pItem05new .= $pItem05[$x] . ($x == (@sizeof($pItem05) - 1)? "" : ",");
			}
			
			$pItem01 = str_replace("&amp;", "&", $pItem01);
			$pItem02 = str_replace("&amp;", "&", $pItem02);
			$pItem03 = str_replace("&amp;", "&", $pItem03);
			$pItem04 = str_replace("&amp;", "&", $pItem04);
			
			$res = $db->query("
				UPDATE school_data SET 
					ioItem01 = '" . $pItem01 . "', 
					ioItem02 = '" . $pItem02 . "', 
					ioItem03 = '" . $pItem03 . "', 
					ioItem04 = '" . $pItem04 . "', 
					ioItem06 = '" . $pItem06 . "', 
					ioApplyTime = '" . $pItem05new . "', 
					
					ioHakgi = '" . $pItemHakgi . "', 

					ioRegIden = '" . $sess_Code . "', 
					ioModify = NOW()
				WHERE idx = '" . $pUniqKey . "' LIMIT 1;
			");

			echo "true";
		break;
		
		case "doUpdate2" : 
			$pUniqKey = addslashes(htmlspecialchars($_REQUEST['pUniqIdx'], ENT_QUOTES, 'ISO-8859-1'));	// 대상 학생, 구분자 쉼표.
			
			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501v2DialogItem00'], ENT_QUOTES, 'ISO-8859-1'));	// 학기
			
			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501v2DialogItem01'], ENT_QUOTES, 'ISO-8859-1'));	// 부서명
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501v2DialogItem02'], ENT_QUOTES, 'ISO-8859-1'));	// 특기사항

			$pItem01 = str_replace("&amp;", "&", $pItem01);
			$pItem02 = str_replace("&amp;", "&", $pItem02);
			
			$res = $db->query("
				UPDATE school_data SET 
					ioItem01 = '" . $pItem01 . "', 
					ioItem02 = '" . $pItem02 . "', 

					ioHakgi = '" . $pItemHakgi . "', 
					
					ioRegIden = '" . $sess_Code . "', 
					ioModify = NOW()
				WHERE idx = '" . $pUniqKey . "' LIMIT 1;
			");

			echo "true";
		break;
		
		case 'removeItem' :
			$pIdx = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pRemoveIdxs'])));
			$___tempArrayList = explode(",", $pIdx);

            for ($k = 0; $k < @sizeof($___tempArrayList); $k++) {
                if ($___tempArrayList[$k] != "") $tempArrayList[$___tempArrayList[$k]] = "TRUE";
            }

			$realCnt = 0;
			if (@sizeof($tempArrayList) > 0) {
				foreach ($tempArrayList AS $uniqKey => $uniqValue) {
					if ($res = $db->query("DELETE FROM school_data WHERE idx = '" . $uniqKey . "' LIMIT 1;")) $realCnt++;
				}
			}

            echo "true";
		break;
		
		case 'getDialog' : 
			$pIdx = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pDialogIdx'])));

			$result = $db->query("SELECT * FROM school_data WHERE idx = '" . $pIdx . "' AND ioYear = '" . $ioGradeYear . "' LIMIT 1;");
			$row = $result->fetch_array();
?>
<div style="width:100%; font-weight:bold;"><?= $row['sHak'] ?>학년 <?= $row['sBan'] ?>반 <?= $row['sBun'] ?>번 <?= $row['sName'] ?></div>
<form name="inout0501vDialogForm" id="inout0501vDialogForm" method="POST">

	<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types" style="width:100%; margin-top:8px;">
<?php
			if ($row['ioDiv'] == '동아리활동') {
?>
		<tr>
			<th style="width:120px;">학기</th>
			<td style="text-align:left;">
				<select id="inout0501vDialogItem00" name="inout0501vDialogItem00" class="input_GrayLine" style="width:70px;">
					<option value="<?= $row['ioHakgi'] ?>" selected><?= $row['ioHakgi'] ?>학기</option>
					<!--<option value="1" <?= $row['ioHakgi'] == '1'? 'selected' : '' ?>>1학기</option>
					<option value="2" <?= $row['ioHakgi'] == '2'? 'selected' : '' ?>>2학기</option>-->
				</select>
			</td>
		</tr>
		<tr>
			<th>일자</th>
			<td style="text-align:left;"><input type="text" id="inout0501vDialogItem01" name="inout0501vDialogItem01" valid.label="일자" placeholder="일자" class="input_GrayLineCenter" readonly style='background-color:#e3e3e3; width:85px; margin-right:3px;' value="<?= stripslashes($row['ioItem01']) ?>"></td>
		</tr>
		<tr>
			<th>이수시간<br>(교시 클릭!)</th>
			<td style="text-align:left;">
				<select id="inout0501vDialogItem05u" name="inout0501vDialogItem05u" class="input_GrayLine" multiple style="height:65px !important; width:60px;">
<?php
				$timeTable = explode(",",$row['ioApplyTime']);
				for ($i = 1; $i <= 7; $i++) {
					echo "<option value=\"" . $i . "\">" . $i . "교시</option>";
				}
			
				$tempArrayList = explode(",", $row['ioItem05']);
				$___tempArrayList = array();
				for ($x = 0; $x < @sizeof($tempArrayList); $x++) {
					if ($tempArrayList[$x] != "") $___tempArrayList[$tempArrayList[$x]] = 'TRUE';
				}
				
				@ksort($___tempArrayList);
?>
				</select>
				→
				<select id="inout0501vDialogItem05" name="inout0501vDialogItem05[]" class="input_GrayLine" multiple style="height:65px !important; width:60px;">
<?php
				if (@sizeof($___tempArrayList) > 0) {
					foreach ($___tempArrayList AS $k => $v) {
						echo "<option value=\"" . $k . "\">" . $k . "교시</option>";
					}
				}
				if (@sizeof($timeTable) > 0){
					foreach ($timeTable AS $k => $v){
?>
					<option value="<?=$v != '' ? $v : '' ?>"><?=$v != '' ? $v ."교시" : '' ?></option>	
<?php
						
					}
				}
					
?>
				</select>
				<div style="margin-top:1px;">
					<input type="text" id="inout0501vDialogItem02" name="inout0501vDialogItem02" valid.label="이수시간" class="input_GrayLineCenter isNumeric" readonly style="background-color:#e3e3e3; width:140px;" value="<?= stripslashes($row['ioItem02']) ?>">
				</div>
			</td>
		</tr>
		<tr>
			<th>활동내용</th>
			<td style="text-align:left;"><textarea id="inout0501vDialogItem03" name="inout0501vDialogItem03" valid.label="활동내용" placeholder="활동내용" class="input_GrayLine" style="height:68px; width:100%;"><?= stripslashes($row['ioItem03']) ?></textarea></td>
		</tr>
		<tr>
			<th>부서명</th>
			<td style="text-align:left;"><input type="text" id="inout0501vDialogItem04" name="inout0501vDialogItem04" valid.label="부서명" placeholder="부서명" class="input_GrayLineCenter" style="width:100%;" value="<?= stripslashes($row['ioItem04']) ?>"></td>
		</tr>
		<tr>
			<th>봉사연계</th>
			<td style="text-align:center;"><input type="checkbox" id="inout0501vDialogItem06" name="inout0501vDialogItem06" valid.label="봉사연계" style="width:20px; height:20px;" <?= $row['ioItem06'] == 'Y'? 'checked' : '' ?> value="Y"></td>
		</tr>
<?php
			} else if ($row['ioDiv'] == '동아리활동-학생부') {
?>
		<tr>
			<th style="width:120px;">학기</th>
			<td style="text-align:left;">
				<select id="inout0501v2DialogItem00" name="inout0501v2DialogItem00" class="input_GrayLine" style="width:70px;">
					<option value="1" <?= $row['ioHakgi'] == '1'? 'selected' : '' ?>>1학기</option>
					<option value="2" <?= $row['ioHakgi'] == '2'? 'selected' : '' ?>>2학기</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>부서명</th>
			<td style="text-align:left;"><input type="text" id="inout0501v2DialogItem01" name="inout0501v2DialogItem01" valid.label="부서명" placeholder="부서명" class="input_GrayLineCenter" style="width:100%;" value="<?= stripslashes($row['ioItem01']) ?>"></td>
		</tr>
		<tr>
			<th>특기사항<br>(<span id="counterOfDialogIO0501v">###</span>자 이내)</th>
			<td style="text-align:left;"><textarea id="inout0501v2DialogItem02" name="inout0501v2DialogItem02" valid.label="특기사항" placeholder="특기사항" class="input_GrayLine" stringMaxLength="250" style="height:68px; width:100%;"><?= stripslashes($row['ioItem02']) ?></textarea></td>
		</tr>
<?php
			}
?>
	</table>
</form>
<?php
		break;

	    default : break;
	}
?>