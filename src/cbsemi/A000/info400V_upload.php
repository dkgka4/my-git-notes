<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "/func/func_global.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "/func/func_db.php";

	include_once($_SERVER['DOCUMENT_ROOT'] . '/inc/lib.inc'); // 공통 라이브러리

	/* 학생 학생부 성적 업로드 */
	$db = doConnect();	 // DB connection

	@header("Content-Type: text/html; charset=euc-kr");

	require_once $_SERVER['DOCUMENT_ROOT'] . '/func/func_reader.php';
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('euc-kr');
	$data->read($_FILES['Filename400V']['tmp_name']);
	error_reporting(E_ALL ^ E_NOTICE);
	
	
	$itemCodeArray = array('학생개인번호' => 'ONLYNUMBER','개인번호' => 'ONLYNUMBER', '성명(한자)' => 'CNAME', '성별' => 'GENDER', '생년월일' => 'BIRTHDAY', '우편번호' => 'ADDRNUMBER', '주소' => 'ADDR', '세부전공' => 'DETAIL', '복수전공' => 'DOUBLE', '부전공' => 'SUBTITLE', '비고' => 'ETC');

	/* 해당년도 학적을 가져온다 */
	$yearSetYn = @$db->query("SELECT GRADEYEAR FROM `ecs_school` WHERE LASTYN='Y';");
	$syrw = @$yearSetYn->fetch_array();

	// 엑셀에 들어있는 학년 찾기
	$excelHaksearch = explode(" ", $data->sheets[0]['cells'][3][1]);
	foreach($excelHaksearch AS $key => $value){
		$val = explode("학년", $value);
		if (eregi("학년", $value) && strlen($val[0]) == 1) {
			$grade = $val[0];	// 학년 추출
		}
	}

	// 엑셀이 들어있는 학과 찾기
	//2013학년도 금융회계과 3학년 01반 
	$excelHaksearch =explode(" " , trim($data->sheets[0]['cells'][3][1]));
	foreach($excelHaksearch AS $key => $value){
		if(eregi("과",$value) || eregi("일반",$value)){
			$gyeol = trim($value);	// 학과 추출
		}
	}
	
	if($gyeol == ""){
		echo "
			<script type='text/javascript'>\n
				alert('[알림!] 엑셀폼을 확인하세요.');\n
			</script>\n
			";
		exit;
	}
	
	if ($syrw['GRADEYEAR'] != '' && $grade != '') {
		
		$selrst = @$db->query("SELECT std_unique, gyeol, name FROM member WHERE std_pyear='" . $syrw['GRADEYEAR'] . "' AND hak='" . $grade . "';");	// 처리년도가 저장 되어있을경우 인서트가 가능

		$tmpMyinfo = array();
		while($row = @$selrst->fetch_array()) $tmpMyinfo[$row[std_unique]][$row[name]] = "true";

		$studentInfoArray = array();	$itemInfoArray = array();	$distinctArray = array();
		for ($i = 0; $i <= $data->sheets[0]['numRows']; $i++) {
			/*$tmpBan = explode(" " , trim($data->sheets[0]['cells'][3][1]));
			$tmpBanName = $tmpBan[sizeof($tmpBan) -1];*/
			
			// $excelBan = @intval(preg_replace("/[^0-9]*/s", "", $tmpBanName));	// 반
			$excelBan = $data->sheets[0]['cells'][$i][1];	// 반
			$excelBun = $data->sheets[0]['cells'][$i][2];	// 번호
			$excelName = $data->sheets[0]['cells'][$i][3];	// 성명
			
			
			if ($excelBan != '' && $excelBun != '' && $excelName != '') {
				// echo "$excelBan/$excelBun/$excelName<br>";
				if ($excelBun == '번호' && $excelName == '성명') {
					$itemInfoArray = array();
					for ($j = 3; $j <= 13; $j++) {	// 4 ~ 7 열에 있는 정보만 조회
						if ($data->sheets[0]['cells'][$i][$j] != '') $itemInfoArray[$data->sheets[0]['cells'][$i][$j]] = $j;
					}
				}
				
				/*print_r($itemInfoArray);
				echo "<br><br>";*/
				$itemInfoArray = sizeof($itemInfoArray) > 0? $itemInfoArray : array();	// 데이터가 있다면 그대로 없다면 초기화
				if ($excelBan != '반' && $excelBun != '번호' && $excelName != '성명' && sizeof($itemInfoArray) > 0) {	// 이런 항목이 있다면 넣지 말고
					foreach ($itemInfoArray AS $key => $value) {
						// echo $itemCodeArray[$key] . "<br>";
						$studentInfoArray[(int)$excelBan][(int)$excelBun][$excelName][$itemCodeArray[$key]] = $data->sheets[0]['cells'][$i][$value];
						if ($itemCodeArray[$key] == 'ONLYNUMBER') $distinctArray['s' . $data->sheets[0]['cells'][$i][$value]] = 'TRUE';	// 중복아이디 필터링 기능
					}
				}
			}
		}

		$multiQyery = "";	// GENDER
		if (sizeof($studentInfoArray) > 0) {	// 학생 자료가 있다면
			foreach ($studentInfoArray AS $iBanKey => $iBanValue) {
				foreach ($studentInfoArray[$iBanKey] AS $iBunKey => $iBunValue) {
					foreach ($studentInfoArray[$iBanKey][$iBunKey] AS $iNameKey => $iNameValue) {
						if ($tmpMyinfo[$studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']][$iNameKey] == 'true') {	// 업데이트
							$multiQyery .= "
								UPDATE member SET member_level = '2', std_state = '재학', std_pyear = '" . $syrw['GRADEYEAR'] . "',
									name = '" . $iNameKey . "', password = PASSWORD('" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "'),
									sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '남성'? '1' : '2') . "',
									gyeol = '" . $gyeol . "', hak = '" . $grade . "', ban = '" . $iBanKey . "', bun = '" . $iBunKey . "',
									std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "',
									post_no = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDRNUMBER'] . "',
									birth = '" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "',
									address = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDR'] . "'
								WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $grade . "' AND ban = '" . $iBanKey . "'
									AND bun = '" . $iBunKey . "' AND std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "'
									AND name = '" . $iNameKey . "';
							";	// preg_replace("/[^0-9]*/s", "", trim($rowOfSchool['PHONE']))
						} else {	// 삽입
							$multiQyery .= "
								INSERT INTO member SET user_id = '" . ('s' . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "',
									member_level = '2', std_state = '재학', std_pyear = '" . $syrw['GRADEYEAR'] . "',
									name = '" . $iNameKey . "', password = PASSWORD('" .  preg_replace("/[^0-9]*/s", "",$studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "'),
									sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '남성'? '1' : '2') . "',
									gyeol = '" . $gyeol . "', hak = '" . $grade . "', ban = '" . $iBanKey . "', bun = '" . $iBunKey . "',
									std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "',
									post_no = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDRNUMBER'] . "',
									birth = '" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "',
									address = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDR'] . "',
									reg_date = '" . _getYearToSecond() . "';
							";
						}
					}
				}
			}
		}
		if ($multiQyery != '') {
			if ($result = @$db->multi_query($multiQyery)) {
				echo "
					<script type='text/javascript'>\n
						alert('[알림!] 학생 일괄등록을 정상적으로 처리하였습니다.');\n
						parent.multi_changes.list();
					</script>\n
				";
			}
		} else {
			echo "
				<script type='text/javascript'>\n
					alert('[알림!] 학생 일괄등록을 정상적으로 처리하였습니다.');\n
					parent.multi_changes.list();
				</script>\n
			";
		}
	}
?>