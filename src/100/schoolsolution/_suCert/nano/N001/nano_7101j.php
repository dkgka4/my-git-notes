<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_db.php");

	/**
	 * @ author : 이근만
	 * @ since : 2024.12.04.
	 * @ descript : 자료관리 > 방과후 수업 관리
	 */

	if ($sess_Iden == "" || $sess_Code == "") {
		echo "
			<script language=\"javascript\">\n
				alert(\"[알림!] 로그인해 주십시오.\");\n
				location.href = \"/logout.php\";\n
			</script>\n
		";
	}

	$db = doConnect();	 // DB connection

	$caseBy = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['caseBy'])));
	if ($caseBy == "" || strtolower($_SERVER['REQUEST_METHOD']) != "post") {
		echo "
			<script language=\"javascript\">\n
				alert(\"[알림!] 잘못된 접근 입니다. 내용을 불러올수 없습니다.\");\n
			</script>\n
		";		
  		exit;	
	}

	$yearSetYn = $db->query("SELECT GRADEYEAR FROM ecs_school WHERE LASTYN = 'Y' LIMIT 1;");	// 적용년도 확인
	$syrw = $yearSetYn->fetch_array();

	$nSelectYear = $syrw['GRADEYEAR'];

	switch ($caseBy) {
		case "getList" : 
			$pUniq = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pUniq'])));

			$teacherList = array();	// 최종입력자 가지고오기
			$results = $db->query("SELECT no, user_id, name FROM member WHERE (member_level = '1' OR member_level = '99') AND use_yn = 'Y';");
			while ($rows = $results->fetch_array()) {
				$teacherList[$rows['no']]['name'] = $rows['name'];
				$teacherList[$rows['no']]['user_id'] = $rows['user_id'];
			}
			
			$tempArrayList = explode(",", $pUniq);
			$stringArrayList = array();
			for ($i = 0; $i < sizeof($tempArrayList); $i++) {
				if ($tempArrayList[$i] != "") $stringArrayList[$tempArrayList[$i]] = "TRUE";
			}

			if ($pHak == '3') $pastGradeYear = $nSelectYear - 2;
			else if ($pHak == '2') $pastGradeYear = $nSelectYear - 1;
			else if ($pHak == '1') $pastGradeYear = $nSelectYear;

			$res1 = $db->query("SELECT * FROM new_cert_acknowledgment_mgr WHERE gYear = '" . $pastGradeYear . "' AND gTypeof = 'NEW방과후수업-산출기준설정' LIMIT 1;");
			$r1 = $res1->fetch_array();
			
			$nElemType11SetData = array();	// 방과후수업
			$nElemType11SetData[$r1['gTypeof']]['방과후수업_충족출석률'] 				= $r1['gCert1'] == ''? 0 : $r1['gCert1'];	// 방과후수업 충족출석률
			$nElemType11SetData[$r1['gTypeof']]['방과후수업_충족출석률_부여점수'] 		= $r1['gCert2'] == ''? 0 : $r1['gCert2'];	// 방과후수업 충족출석률 부여점수

			$tableViewCnt = 0;
			if (sizeof($stringArrayList) > 0) {
				foreach ($stringArrayList AS $uniqKey => $uniqValue) {
					$result = $db->query("
						SELECT gyeol, hak, ban, bun, name, std_unique FROM member
						WHERE member_level = '2' AND std_unique = '" . $uniqKey . "' AND std_pyear = '" . $syrw['GRADEYEAR'] . "' AND use_yn = 'Y' LIMIT 1;
					");
					$row = $result->fetch_array();

					$afterSchoolData = array();	// 방과후 수업 상세 정보 종합

					$vElemType11FinalData = array();	// 방과후 수업 최종 data

					$pstmt = $db->query("
						SELECT i_no, i_type, i_uniq, i_hakgwa, i_hak, i_ban, i_bun, i_name, 
							i_cert, let_totaltime, lec_time, lec_ratio, lec_Score, DATE_FORMAT( i_date, '%Y-%m-%d' ) AS i_date, i_write, i_year, modDate, DATE_FORMAT( regDate, '%Y-%m-%d' ) AS regDate 
						FROM new_cert_data WHERE i_type IN ('NEW방과후수업_출석률') AND i_uniq = '" . $uniqKey . "' ORDER BY regDate DESC;
					");
					while ($rw = $pstmt->fetch_array()) $afterSchoolData[] = $rw;
					
					for ($mCnt = 0; $mCnt < sizeof($afterSchoolData); $mCnt++) {
						$vElemType11FinalData['방과후수업_최종점수'] += ($afterSchoolData[$mCnt]['lec_ratio'] >= $nElemType11SetData['NEW방과후수업-산출기준설정']['방과후수업_충족출석률']? $nElemType11SetData['NEW방과후수업-산출기준설정']['방과후수업_충족출석률_부여점수'] : 0);
					}

					if (sizeof($afterSchoolData) > 0) {
?>
	<tr>
		<td style="padding-right:0px; padding-left:0px; text-align:center; vertical-align:top;"><img style="cursor:pointer;" src="/images/treeimg/nolines_plus.gif" class="nano7101vViewFgYnClass" valid.blank="nano7101vHiddenClass<?= $uniqKey ?>" align="absmiddle"></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['hak'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['gyeol'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['ban'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['bun'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['name'] ?></td>
		<td style="text-align:left; padding-left:2px; padding-right:2px; background-color:#e3e3e3;" colspan="3">
<?php
						echo '<div>[ <span style="font-weight:bold; color:red;">방과후 수업 종합</span> ] <span style="font-weight:bold; color:blue;">' . ($vElemType11FinalData['방과후수업_최종점수'] == ''? '' : $vElemType11FinalData['방과후수업_최종점수']) . '</span></div>';
?>
		</td>
	</tr>
<?php
						for ($mCnt = 0; $mCnt < sizeof($afterSchoolData); $mCnt++) {
							$viewString = '';
							if ($afterSchoolData[$mCnt]['i_type'] == 'NEW방과후수업_출석률') {
								$viewString .= '[ <span style="font-weight:bold; color:blue;">방과후 수업</span> ]';
								$viewString .= " " . $afterSchoolData[$mCnt]['i_cert'] . ($afterSchoolData[$mCnt]['lec_ratio'] != ''? " 【출석률" . $afterSchoolData[$mCnt]['lec_ratio'] . "%, " . ($afterSchoolData[$mCnt]['lec_time'] . "/" . $afterSchoolData[$mCnt]['let_totaltime']) . "】" . ($afterSchoolData[$mCnt]['lec_ratio'] >= $nElemType11SetData['NEW방과후수업-산출기준설정']['방과후수업_충족출석률']? " <span style='font-weight:bold;'>(" . $nElemType11SetData['NEW방과후수업-산출기준설정']['방과후수업_충족출석률_부여점수'] . "점)</span>" : "") : "");
							}
?>
	<tr class="nano7101vHiddenClass<?= $uniqKey ?>" style="display:none;">
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="text-align:left; padding-left:2px; padding-right:2px;"><?= $viewString ?></td>
		<td style="padding-right:0px; padding-left:0px;"><?= $afterSchoolData[$mCnt]['i_date'] ?></td>
		<td style="padding-right:0px; padding-left:0px;"><?= $teacherList[$afterSchoolData[$mCnt]['i_write']]['name'] ?></td>
	</tr>
<?php
							$tableViewCnt++;
						}
					}
				}
			}
			
			if ($tableViewCnt == 0) {
?>
	<tr>
		<td colspan="9" style="text-align:center; height:52px;">:: 등록된 데이터가 없습니다. ::</td>
	</tr>
<?php
			}
		break;

		case "getAfterSchoolDialog" : 	// 방과후 수업 데이터 가져오기
?>
			<div style="margin-bottom:5px;">■ 방과후 수업</div>
			<!--<div style="font-size:0.9em; margin-bottom:5px;">계획시간/이수시간 반영 : 계획시간 반영 시 강좌별 총 수강시간을 학생 이수시간으로 처리하여 출석률 100% 처리합니다.</div>-->

			<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type">
				<tr>
					<th>년도</th>
					<th>프로젝트명</th>
					<th>생성일</th>
					<th>강좌<br>수</th>
					<!--<th>방과후 수업<br>반영점수</th>-->
					<!--<th>계획시간<br>/이수시간 반영</th>-->
					<th>
						사용<br>유무
						<div style="height:3px;">&nbsp;</div>
						<input type="checkbox" style="width:18px; height:18px;" checked id="aftercheckClassAll">
					</th>
				</tr>
<?php
			$rowCount = 0;
			$datachk_query = $db->query("
				SELECT *, DATE_FORMAT(regdate, '%Y-%m-%d') AS regdates FROM freesemester_program 
				WHERE proYear IN ('" . $syrw['GRADEYEAR'] . "', '" . ($syrw['GRADEYEAR'] - 1) . "', '" . ($syrw['GRADEYEAR'] - 2) . "') ORDER BY regdate DESC;
			");
			while ($row = $datachk_query->fetch_assoc()) {
				$sub_query = $db->query("SELECT COUNT(*) AS cnt FROM freesemester_tch_list WHERE proCd = '" . $row['proIdx'] . "';");
				$subrow = $sub_query->fetch_assoc();
?>
			<tr>
				<td><?= $row['proYear'] ?></td>
				<td style="text-align:left; padding-left:3px; padding-right:3px;"><?= $row['proName'] ?></td>
				<td><?= $row['regdates'] ?></td>
				<td><?= $subrow['cnt'] ?></td>
				<!--<td>
					<input type="text" style="width:60px; text-align:center;" placeholder="부여점수" valid.key="<?= $row['proIdx'] ?>" value="0.75" class="aftercheckClass2 isNumeric">
				</td>-->
				<!--<td>
					<label for="aftercheck3_<?= $row['proIdx'] ?>_1"><input type="radio" style="width:18px; height:18px;" name="aftercheck3_<?= $row['proIdx'] ?>" id="aftercheck3_<?= $row['proIdx'] ?>_1" valid.key="<?= $row['proIdx'] ?>" value="P" class="aftercheckClass3"> 계획</label>
					<label for="aftercheck3_<?= $row['proIdx'] ?>_2"><input type="radio" style="width:18px; height:18px;" name="aftercheck3_<?= $row['proIdx'] ?>" id="aftercheck3_<?= $row['proIdx'] ?>_2" valid.key="<?= $row['proIdx'] ?>" value="" checked class="aftercheckClass3"> 이수</label>
				</td>-->
				<td>
					<input type="checkbox" style="width:18px; height:18px;" class="aftercheckClass" value="<?= $row['proIdx'] ?>" checked>
				</td>
			</tr>
<?php
				$rowCount++;
			}
			
			if ($rowCount == 0) {
?>
			<tr>
				<td colspan="5" style="height:32px; text-align:center;">방과후 수업 데이터가 없습니다.</td>
			</tr>
<?php
			}
?>
			</table>
<?php
		break;

		case "doAfterSchoolSave" : 	// 방과후 수업 데이터 가져오기
			if ($sess_Gbn != "T") {
				echo "권한이 없습니다.";
				exit;
			}

			$idxjoin = addslashes(iconv("UTF-8", "EUC-KR", urldecode($_REQUEST['idxjoin'])));

			$improcd = "";
			if ($idxjoin) {
				$idxjoinex = explode(",", $idxjoin);
				$improcd = implode("','", $idxjoinex);
			}

			$scoringKey = addslashes(iconv("UTF-8", "EUC-KR", urldecode($_REQUEST['scoringKey'])));   // 그룹에 부여한 점수

			$___scoringData = explode(';', $scoringKey);
			$scoringReqData = array();
			for ($s = 0; $s < @sizeof($___scoringData); $s++) {
			    if ($___scoringData[$s] != '') {
			        $___scoringSplitData = explode('^', $___scoringData[$s]);
			        if ($___scoringSplitData[0] != '' && $___scoringSplitData[1] != '') $scoringReqData[$___scoringSplitData[0]] = $___scoringSplitData[1];
			    }
			}

			$timeKey = addslashes(iconv("UTF-8", "EUC-KR", urldecode($_REQUEST['timeKey'])));   // 그룹에 계획/이수 시간

			$___timeData = explode(',', $timeKey);
			$timeReqData = array();
			for ($s = 0; $s < @sizeof($___timeData); $s++) {
			    if ($___timeData[$s] != '') $timeReqData[$___timeData[$s]] = 'TRUE';
			}

			// 강좌별 시간표 시간을 가져옴 (3개년도에서 가져오기 때문에 syear 조건은 빼버림
			$lecTimeArr = array();
			$___lecTimeArr = array();
			$___lecTimeArr2 = array();
			/*echo ("
				SELECT tIdx, proCd, COUNT(*) AS timesum FROM freesemester_personal_weekplan 
				WHERE proCd IN ('" . ($improcd ? $improcd : "no") . "') GROUP BY tIdx;
			");*/
			$timecheck_query = $db->query("
				SELECT tIdx, proCd, COUNT(*) AS timesum FROM freesemester_personal_weekplan 
				WHERE proCd IN ('" . ($improcd ? $improcd : "no") . "') GROUP BY tIdx;
			");
			while ($trow = $timecheck_query->fetch_assoc()) {
				$lecTimeArr[$trow['tIdx']] = $trow['timesum'];

				// echo $scoringReqData[$trow['proCd']] . '<br>';
				if ($scoringReqData[$trow['proCd']] != '') $___lecTimeArr[$trow['tIdx']] = $scoringReqData[$trow['proCd']];
				if ($timeReqData[$trow['proCd']] == 'TRUE') $___lecTimeArr2[$trow['tIdx']] = $timeReqData[$trow['proCd']];
			}

			$myAtt = array();

			// 학생 출석체크 데이터
			$stdAtt_query = $db->query("SELECT proLecidx, proCd, mIndex, psnweekCd, COUNT(*) AS cnt FROM freesemester_attendancelist WHERE proCd IN ('" . ($improcd ? $improcd : "no") . "') GROUP BY mIndex, proCd, proLecidx;");
			while ($atrow = $stdAtt_query->fetch_assoc()) $myAtt[$atrow['proLecidx']][$atrow['mIndex']] = $atrow['cnt'];

			$stdcheck_query = $db->query("
				SELECT proIdx, lectureCd, syear, proCd, lectureName, mIndex, pgyeol, phak, pban, pbun, pname, regdate FROM freesemester_std_list 
				WHERE cancelyn = 'N' AND proCd IN ('" . ($improcd ? $improcd : "no") . "');
			");
			$cnt = $stdcheck_query->num_rows;

			/*print_r($___lecTimeArr);
			exit;*/

			$__cnt = 0;
			if ($cnt > 0) {
				// lec_proCd 추가 : 모든데이터 삭제후 입력에서 선택한 프로젝트만 삭제후 다시 입력으로 수정
				if ($del = $db->query("
					DELETE FROM new_cert_data WHERE i_type = 'NEW방과후수업_출석률' AND i_detail = '방과후데이터' AND i_year = '" . $syrw['GRADEYEAR'] . "' AND lec_proCd IN ('" . ($improcd ? $improcd : "no") . "');
				")) {
					while ($row = $stdcheck_query->fetch_assoc()) {
						$lecplanTotalTime =($lecTimeArr[$row['lectureCd']] > 0 ? $lecTimeArr[$row['lectureCd']] : 0);	//강좌별 총 시간표 시간
						$lecStdTime = (($myAtt[$row['lectureCd']][$row['mIndex']]) > 0 ? ($myAtt[$row['lectureCd']][$row['mIndex']]) : 0);	//학생이 수강한 시간표 시간

						//백분률 (학생이수강한시간 / 수강강좌총시간) * 100
						$persent = 0;
						if ($lecStdTime > 0 && $lecplanTotalTime > 0) {
							$persent = @round((($lecStdTime/$lecplanTotalTime) * 100), 2);
						}

			// echo ($___lecTimeArr2[$row['lectureCd']] . '<br>');
			// exit;
						
						/*
						*	@@ 기본 방과후 관련 고유 코드 추가 @@
						*	lec_proCd : 프로그램 고유코드로 그룹 및 강좌 들을 많이 포함하고 있음
						*	i_cert_etc : lec_proCd안에 들어 있는 개별 강좌코드, 강좌 고유코드로 혹시나 나중을 위해 넣어둠
						*/
						$in = "
						INSERT INTO new_cert_data SET 
						i_type='NEW방과후수업_출석률',
						i_detail='방과후데이터',
						i_uniq='" . $row['mIndex'] . "',
						i_hakgwa='" . $row['pgyeol'] . "',
						i_hak='" . $row['phak'] . "',
						i_ban='" . $row['pban'] . "',
						i_bun='" . $row['pbun'] . "',
						i_name='" . $row['pname'] . "',
						i_cert='" . $row['lectureName'] . "(" . ($___lecTimeArr2[$row['lectureCd']] == 'TRUE'? $lecplanTotalTime : $lecStdTime) . "시간)',
						i_cert_etc='" . $row['lectureCd'] . "',

						lec_proCd='" . $row['proCd'] . "',
						let_totaltime='" . $lecplanTotalTime . "',						
						lec_time='" . ($___lecTimeArr2[$row['lectureCd']] == 'TRUE'? $lecplanTotalTime : $lecStdTime) . "',
						lec_ratio='" . ($___lecTimeArr2[$row['lectureCd']] == 'TRUE'? 100 : $persent) . "',
						lec_Score = '" . $___lecTimeArr[$row['lectureCd']] . "',
						i_acqu ='" . $row['phak'] . "',
						i_date='" . $row['regdate'] . "',
						i_write = '" . $sess_Code . "',
						i_year = '" . $syrw['GRADEYEAR'] . "',
						after_year = '" . $row['syear'] . "',
						regDate=NOW();";	//i_cert='" . $row['lectureName'] . "(" . $lecTimeArr[$row['lectureCd']] . "시간)',  . "(" . $lecStdTime . "시간)'

						// echo $in . '<br>';
						$ins = $db->query($in);	
						$__cnt++;
					}					
				}
			}
			
			echo $__cnt ."건 처리 하였습니다.";
			
		break;

		case "doAfterSchoolReset" : 
			if ($sess_Gbn != "T") {
				echo "권한이 없습니다.";
				exit;
			}
			
			$del = $db->query("DELETE FROM new_cert_data 
			WHERE i_type='NEW방과후수업_출석률' 
			AND i_detail='방과후데이터' 
			AND i_year='" . $syrw['GRADEYEAR'] . "';");
		break;
		
	    default : break;
	}
?>