<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_db.php";

	/* 학생 학생부 성적 업로드 */
	include_once $_SERVER['DOCUMENT_ROOT'] . '/func/PHPExcel_1.8.0/PHPExcel.php';
	@header("Content-Type:text/html;charset=EUC-KR");
	
	$db = doConnect();	 // DB connection

	$itemCodeArray = array(
		'학생개인번호'	=> 'ONLYNUMBER', 
		'개인번호' 		=> 'ONLYNUMBER', 
		'성명(한자)' 	=> 'CNAME', 
		'한자성명' 		=> 'CNAME', 
		'성별' 			=> 'GENDER', 
		'생년월일' 		=> 'BIRTHDAY', 
		'우편번호' 		=> 'ADDRNUMBER', 
		'주소' 			=> 'ADDR', 
		'세부전공' 		=> 'DETAIL', 
		'복수전공' 		=> 'DOUBLE', 
		'부전공' 		=> 'SUBTITLE', 
		'비고' 			=> 'ETC'
	);

	$yearSetYn = $db->query("SELECT GRADEYEAR FROM ecs_school WHERE LASTYN = 'Y' LIMIT 1;");	// 해당년도 학적을 가져온다
	$syrw = $yearSetYn->fetch_array();
	
	
    if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
    {
        // Allow file types
        $vpb_allowed_extensions = array("xls","xlsx"); //"gif", "jpg", "jpeg", "png",
        
        $i=1;
        foreach($_FILES as $file)
        {
            
            $objPHPExcel = new PHPExcel();
            //$db = doConnect();
            
            /* Variables Declaration and Assignments */
            $vpb_image_filename = ($file['name']);
            $vpb_image_tmp_name = $file['tmp_name'];
            $vpb_file_extensions = pathinfo(strtolower($vpb_image_filename), PATHINFO_EXTENSION);
            
            //New file name
            $random_name_generated = time().rand(1234,9876).'.'.$vpb_file_extensions;
            
    //		echo $i."번째 =>";
    //		echo $vpb_image_filename ."..";
    //		echo $vpb_image_tmp_name ."..";
    //		echo $vpb_file_extensions ."..";
    //		echo $random_name_generated ."<br>";

            
            $i++;
            if($vpb_image_filename == "") 
            {
                //Browse for a photo that you wish to use
                echo "asdfasdfasfd";
            }
            else
            {
                if (in_array($vpb_file_extensions, $vpb_allowed_extensions)) 
                {

                    // 엑셀 데이터를 담을 배열을 선언한다.
                    $allData = array();
                    $hakAndHakgwaAndClasArrayList = array(); // 학과 닮을 배열
                    
                    // 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
                    $filename = $vpb_image_tmp_name; //($_FILES['file_upload']['tmp_name']);
                    
                    try {
                        // 업로드한 PHP 파일을 읽어온다.
                        $objPHPExcel = PHPExcel_IOFactory::load($filename);
                        $sheetsCount = $objPHPExcel -> getSheetCount();
                    
                        // 시트Sheet별로 읽기
                        for($i = 0; $i < $sheetsCount; $i++) {
                    
                            $objPHPExcel -> setActiveSheetIndex($i);
                            $sheet = $objPHPExcel -> getActiveSheet();
                            $highestRow = $sheet -> getHighestRow();   			           // 마지막 행
                            $highestColumn = $sheet -> getHighestColumn();	// 마지막 컬럼

                            // 학과때문에 3열 하나 가져와야함
                            for($row = 3; $row <= 3; $row++) {
                                // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
                                $hakGwaRowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
                                                   
                                // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
                                $hakAndHakgwaAndClasArrayList = explode(" ", iconv("utf-8", "euc-kr", $hakGwaRowData[0][0]));	// 학년, 학과, 학급 정보를 취득한다.
                            }
                    
                            // 한줄읽기
                            for($row = 4; $row <= $highestRow; $row++) {
                                // echo $highestRow . "<br/>";
                                // echo $row . "<br/>";
                                // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
                                $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
                    
                                // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
                                $allData[$row] = $rowData[0];
                            }
                        }
                    
                    } catch(exception $e) {
                        echo $e;
                    }	

                    for ($iCnt = 0; $iCnt < sizeof($hakAndHakgwaAndClasArrayList); $iCnt++) {
                        if (preg_match("/과/", $hakAndHakgwaAndClasArrayList[$iCnt]) || preg_match("/일반/", $hakAndHakgwaAndClasArrayList[$iCnt])) {	
                            $__hakAndHakgwaAndClasArrayList =  explode("(",$hakAndHakgwaAndClasArrayList[$iCnt]);
                            $nHakgwa = trim($__hakAndHakgwaAndClasArrayList[0]);	// 학과 추출
                        }
                
                        if (preg_match("/학년/", $hakAndHakgwaAndClasArrayList[$iCnt])) $nHak = preg_replace("/[^0-9]*/s", "", $hakAndHakgwaAndClasArrayList[$iCnt]); // 학년
                
                        if (preg_match("/반/", $hakAndHakgwaAndClasArrayList[$iCnt]) && !preg_match("/일반/", $hakAndHakgwaAndClasArrayList[$iCnt])) $nBan = preg_replace("/[^0-9]*/s", "", $hakAndHakgwaAndClasArrayList[$iCnt]); // 반
                    }
                    
                    if ($nHakgwa == '' || $nHak == '' || $nBan == '') {
						echo "
							<script type='text/javascript'>\n
								alert('[알림!] 엑셀폼을 확인하세요.');\n
							</script>\n
						";
						exit;				
                    } else {
                        
						// 처리년도가 저장 되어있을경우 인서트가 가능
						$selrst = $db->query("SELECT std_unique, gyeol, name FROM member WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $nHak . "' AND gyeol = '" . $nHakgwa . "' AND use_yn = 'Y';");

						$tmpMyinfo = array();
						while($row = @$selrst->fetch_array()) $tmpMyinfo[$row[std_unique]][$row[name]] = "true";

						$studentInfoArray = array();	$itemInfoArray = array();	$distinctArray = array();
						
                        if(sizeof($allData) > 0) {
                            foreach ($allData as $key => $value) {
                                $excelBan = iconv("UTF-8", "EUC-KR", $value[0]);	// 반
								$excelBun = iconv("UTF-8", "EUC-KR", $value[1]);	// 번호
								$excelName = iconv("UTF-8", "EUC-KR", $value[2]);	// 성명
								
								if ($excelBan != '' && $excelBun != '' && $excelName != '') {
									// echo "$excelBan/$excelBun/$excelName<br>";
									if ($excelBun == '번호' && $excelName == '성명') {
										$itemInfoArray = array();
										for ($j = 2; $j <= 12; $j++) {	// 4 ~ 7 열에 있는 정보만 조회
											if (iconv("UTF-8", "EUC-KR", $value[$j]) != '') $itemInfoArray[iconv("UTF-8", "EUC-KR", $value[$j])] = $j;
										}
									}
					
									/*print_r($itemInfoArray);
									echo "<br><br>";*/
									$itemInfoArray = sizeof($itemInfoArray) > 0? $itemInfoArray : array();	// 데이터가 있다면 그대로 없다면 초기화
									if ($excelBan != '반' && $excelBun != '번호' && $excelName != '성명' && sizeof($itemInfoArray) > 0) {	// 이런 항목이 있다면 넣지 말고
										foreach ($itemInfoArray AS $iNum => $val) {
											// echo $itemCodeArray[$key] . "<br>";
											$studentInfoArray[(int)$excelBan][(int)$excelBun][$excelName][$itemCodeArray[$iNum]] = iconv("UTF-8", "EUC-KR", $value[$val]);
											if ($itemCodeArray[$iNum] == 'ONLYNUMBER') $distinctArray['s' . iconv("UTF-8", "EUC-KR", $value[$val])] = 'TRUE';	// 중복아이디 필터링 기능
										}
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
													name = '" . $iNameKey . "', password = PASSWORD('" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "'),
													sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '남성'? '1' : '2') . "',
													gyeol = '" . $nHakgwa . "', hak = '" . $nHak . "', ban = '" . $iBanKey . "', bun = '" . $iBunKey . "',
													std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "',
													post_no = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDRNUMBER'] . "',
													birth = '" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "',
													address = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDR'] . "'
												WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $nHak . "' AND ban = '" . $iBanKey . "'
													AND bun = '" . $iBunKey . "' AND std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "'
													AND name = '" . $iNameKey . "';
											";	// preg_replace("/[^0-9]*/s", "", trim($rowOfSchool['PHONE']))
										} else {	// 삽입
											$multiQyery .= "
												INSERT INTO member SET user_id = '" . ('s' . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "',
													member_level = '2', std_state = '재학', std_pyear = '" . $syrw['GRADEYEAR'] . "',
													name = '" . $iNameKey . "', password = PASSWORD('" .  preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "'),
													sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '남성'? '1' : '2') . "',
													gyeol = '" . $nHakgwa . "', hak = '" . $nHak . "', ban = '" . $iBanKey . "', bun = '" . $iBunKey . "',
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
										parent.info400vObj.getList();
									</script>\n
								";
							}
						} else {
							echo "
								<script type='text/javascript'>\n
									alert('[알림!] 학생 일괄등록을 정상적으로 처리하였습니다.');\n
									parent.info400vObj.getList();
								</script>\n
							";
						}                     
                        
                    }
                }
            }
        }        
    }		

?>