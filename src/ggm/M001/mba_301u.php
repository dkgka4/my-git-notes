<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_db.php";

	/* 학생 학생부 성적 업로드 */
	include_once $_SERVER['DOCUMENT_ROOT'] . '/func/PHPExcel_1.8.0/PHPExcel.php';
	@header("Content-Type:text/html;charset=EUC-KR;");
//	@header("Content-Type:text/html;charset=UTF-8;");
	
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
                            for($row = 1; $row <= 1; $row++) {
                                // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
                                $hakGwaRowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
                                                   
                                // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
                                $hakAndHakgwaAndClasArrayList = explode(" ", iconv("utf-8", "euc-kr", $hakGwaRowData[0][0]));	// 학년, 학과, 학급 정보를 취득한다.
                                $hakAndHakgwaAndClasArrayList = $hakGwaRowData[0];
                            }
                    
                            // 한줄읽기
                            for($row = 2; $row <= $highestRow; $row++) {
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
                    
                    // 저장 항목들 가져오기
                    $itemTitleArray = array();
                    if (is_array($hakAndHakgwaAndClasArrayList) && sizeof($hakAndHakgwaAndClasArrayList) > 0) {
                    	foreach ($hakAndHakgwaAndClasArrayList AS $exNum => $exText) {
                    		$iconvExNum = trim(iconv("UTF-8", "EUC-KR", $exNum));
                    		$iconvExText = trim(iconv("UTF-8", "EUC-KR", $exText));
                    		$itemTitleArray[$iconvExText] = $iconvExNum;
                    	}
                    }
                    
                    if (is_array($itemTitleArray) && sizeof($itemTitleArray) > 0) {

						$studentInfoArray = array(); // 학생들 정보 담을 배열
						
                        if(sizeof($allData) > 0) {
                            foreach ($allData as $key => $value) {
                                $excelHak = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['학년']]);			// 학년
                                $excelBan = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['반']]);			// 반
                                $excelHakgwa = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['학과명']]);		// 학과
								$excelBun = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['번호']]);			// 번호
								$excelName = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['성명']]);			// 성명
								$excelUniq = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['학생개인번호']]);	// 학생개인번호
								
								if ($excelHakgwa != '' && $excelHak != '' && $excelBan != '' && $excelBun != '' && $excelName != '' && $excelUniq != '') {
									
									foreach ($itemTitleArray AS $iTxt => $val) {
										// echo $itemCodeArray[$key] . "<br>";
										$studentInfoArray[$excelUniq][$iTxt] = iconv("UTF-8", "EUC-KR", $value[$val]);
									}
								}								
                            }
                        }
                        
	                   	$multiQyery = "";	// GENDER
	                   	if (sizeof($studentInfoArray) > 0) {	// 학생 자료가 있다면
	                   		foreach ($studentInfoArray AS $iUniqKey => $iValueArr) {
	                   		
		                   		// 처리년도가 저장 되어있을경우 인서트가 가능
								$selrst = $db->query("SELECT * FROM member WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $iValueArr['학년'] . "' AND gyeol = '" . $iValueArr['학과'] . "' AND ban = '" . $iValueArr['반'] . "' AND bun = '" . $iValueArr['번호'] . "' LIMIT 1;");
								$row = @$selrst->fetch_array();
								
								if ($row['no'] != "") {
									$multiQyery .= "
										UPDATE member SET member_level = '2', std_state = '재학', std_pyear = '" . $syrw['GRADEYEAR'] . "',
											name = '" . $iValueArr['성명'] . "', password = PASSWORD('" . preg_replace("/[^0-9]*/s", "", $iValueArr['학생개인번호']) . "'),
											sex = '" . ($iValueArr['성별'] == '남성'? '1' : '2') . "',
											gyeol = '" . $iValueArr['학과명'] . "', hak = '" . $iValueArr['학년'] . "', ban = '" . $iValueArr['반'] . "', 
											bun = '" . $iValueArr['번호'] . "',
											std_unique = '" . $iValueArr['학생개인번호'] . "',
											post_no = '" . $iValueArr['우편번호'] . "',
											birth = '" . preg_replace("/[^0-9]*/s", "", $iValueArr['생년월일']) . "',
											address = '" . $iValueArr['주소'] . "'
										WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $row['hak'] . "' AND ban = '" . $row['ban'] . "'
											AND bun = '" . $row['bun'] . "' AND std_unique = '" . $row['std_unique'] . "'
											AND name = '" . $row['name'] . "';
									";	// preg_replace("/[^0-9]*/s", "", trim($rowOfSchool['PHONE']))								
								} else {
									$multiQyery .= "
										INSERT INTO member SET user_id = '" . ('s' . $iValueArr['학생개인번호']) . "',
											member_level = '2', std_state = '재학', std_pyear = '" . $syrw['GRADEYEAR'] . "',
											name = '" . $iValueArr['성명'] . "', password = PASSWORD('" .  preg_replace("/[^0-9]*/s", "", $iValueArr['학생개인번호']) . "'),
											sex = '" . ($iValueArr['성별'] == '남성'? '1' : '2') . "',
											gyeol = '" . $iValueArr['학과명'] . "', hak = '" . $iValueArr['학년'] . "', ban = '" . $iValueArr['반'] . "', 
											bun = '" . $iValueArr['번호'] . "',
											std_unique = '" . $iValueArr['학생개인번호'] . "',
											post_no = '" . $iValueArr['우편번호'] . "',
											birth = '" . preg_replace("/[^0-9]*/s", "", $iValueArr['생년월일']) . "',
											address = '" . $iValueArr['주소'] . "',
											reg_date = '" . _getYearToSecond() . "';
									";								
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
                }
            }
        }        
    }		

?>