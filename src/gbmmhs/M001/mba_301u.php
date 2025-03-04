<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_db.php";

	/* �л� �л��� ���� ���ε� */
	include_once $_SERVER['DOCUMENT_ROOT'] . '/func/PHPExcel_1.8.0/PHPExcel.php';
	@header("Content-Type:text/html;charset=EUC-KR");
	
	$db = doConnect();	 // DB connection

	$itemCodeArray = array(
		'�л����ι�ȣ'	=> 'ONLYNUMBER', 
		'���ι�ȣ' 		=> 'ONLYNUMBER', 
		'����(����)' 	=> 'CNAME', 
		'���ڼ���' 		=> 'CNAME', 
		'����' 			=> 'GENDER', 
		'�������' 		=> 'BIRTHDAY', 
		'�����ȣ' 		=> 'ADDRNUMBER', 
		'�ּ�' 			=> 'ADDR', 
		'��������' 		=> 'DETAIL', 
		'��������' 		=> 'DOUBLE', 
		'������' 		=> 'SUBTITLE', 
		'���' 			=> 'ETC'
	);

	$yearSetYn = $db->query("SELECT GRADEYEAR FROM ecs_school WHERE LASTYN = 'Y' LIMIT 1;");	// �ش�⵵ ������ �����´�
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
            
    //		echo $i."��° =>";
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

                    // ���� �����͸� ���� �迭�� �����Ѵ�.
                    $allData = array();
                    $hakAndHakgwaAndClasArrayList = array(); // �а� ���� �迭
                    
                    // ������ ���������� utf-8�� ��� �ѱ����� �̸��� �����Ƿ� euc-kr�� ��ȯ���ش�.
                    $filename = $vpb_image_tmp_name; //($_FILES['file_upload']['tmp_name']);
                    
                    try {
                        // ���ε��� PHP ������ �о�´�.
                        $objPHPExcel = PHPExcel_IOFactory::load($filename);
                        $sheetsCount = $objPHPExcel -> getSheetCount();
                    
                        // ��ƮSheet���� �б�
                        for($i = 0; $i < $sheetsCount; $i++) {
                    
                            $objPHPExcel -> setActiveSheetIndex($i);
                            $sheet = $objPHPExcel -> getActiveSheet();
                            $highestRow = $sheet -> getHighestRow();   			           // ������ ��
                            $highestColumn = $sheet -> getHighestColumn();	// ������ �÷�

                            // �а������� 3�� �ϳ� �����;���
                            for($row = 3; $row <= 3; $row++) {
                                // $rowData�� ������ �����͸� ������ �迭ó�� �ȴ�.
                                $hakGwaRowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
                                                   
                                // $rowData�� ���� ���� ��� �ʱ�ȭ �Ǳ⶧���� ���� ���� ���ο� �迭�� �����ϰ� ��´�.
                                $hakAndHakgwaAndClasArrayList = explode(" ", iconv("utf-8", "euc-kr", $hakGwaRowData[0][0]));	// �г�, �а�, �б� ������ ����Ѵ�.
                            }
                    
                            // �����б�
                            for($row = 4; $row <= $highestRow; $row++) {
                                // echo $highestRow . "<br/>";
                                // echo $row . "<br/>";
                                // $rowData�� ������ �����͸� ������ �迭ó�� �ȴ�.
                                $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
                    
                                // $rowData�� ���� ���� ��� �ʱ�ȭ �Ǳ⶧���� ���� ���� ���ο� �迭�� �����ϰ� ��´�.
                                $allData[$row] = $rowData[0];
                            }
                        }
                    
                    } catch(exception $e) {
                        echo $e;
                    }	

                    for ($iCnt = 0; $iCnt < sizeof($hakAndHakgwaAndClasArrayList); $iCnt++) {
                        if (preg_match("/��/", $hakAndHakgwaAndClasArrayList[$iCnt]) || preg_match("/�Ϲ�/", $hakAndHakgwaAndClasArrayList[$iCnt])) {	
                            $__hakAndHakgwaAndClasArrayList =  explode("(",$hakAndHakgwaAndClasArrayList[$iCnt]);
                            $nHakgwa = trim($__hakAndHakgwaAndClasArrayList[0]);	// �а� ����
                        }
                
                        if (preg_match("/�г�/", $hakAndHakgwaAndClasArrayList[$iCnt])) $nHak = preg_replace("/[^0-9]*/s", "", $hakAndHakgwaAndClasArrayList[$iCnt]); // �г�
                
                        if (preg_match("/��/", $hakAndHakgwaAndClasArrayList[$iCnt]) && !preg_match("/�Ϲ�/", $hakAndHakgwaAndClasArrayList[$iCnt])) $nBan = preg_replace("/[^0-9]*/s", "", $hakAndHakgwaAndClasArrayList[$iCnt]); // ��
                    }
                    
                    if ($nHakgwa == '' || $nHak == '' || $nBan == '') {
						echo "
							<script type='text/javascript'>\n
								alert('[�˸�!] �������� Ȯ���ϼ���.');\n
							</script>\n
						";
						exit;				
                    } else {
                        
						// ó���⵵�� ���� �Ǿ�������� �μ�Ʈ�� ����
						$selrst = $db->query("SELECT std_unique, gyeol, name FROM member WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $nHak . "' AND gyeol = '" . $nHakgwa . "' AND use_yn = 'Y';");

						$tmpMyinfo = array();
						while($row = @$selrst->fetch_array()) $tmpMyinfo[$row[std_unique]][$row[name]] = "true";

						$studentInfoArray = array();	$itemInfoArray = array();	$distinctArray = array();
						
                        if(sizeof($allData) > 0) {
                            foreach ($allData as $key => $value) {
                                $excelBan = iconv("UTF-8", "EUC-KR", $value[0]);	// ��
								$excelBun = iconv("UTF-8", "EUC-KR", $value[1]);	// ��ȣ
								$excelName = iconv("UTF-8", "EUC-KR", $value[2]);	// ����
								
								if ($excelBan != '' && $excelBun != '' && $excelName != '') {
									// echo "$excelBan/$excelBun/$excelName<br>";
									if ($excelBun == '��ȣ' && $excelName == '����') {
										$itemInfoArray = array();
										for ($j = 2; $j <= 12; $j++) {	// 4 ~ 7 ���� �ִ� ������ ��ȸ
											if (iconv("UTF-8", "EUC-KR", $value[$j]) != '') $itemInfoArray[iconv("UTF-8", "EUC-KR", $value[$j])] = $j;
										}
									}
					
									/*print_r($itemInfoArray);
									echo "<br><br>";*/
									$itemInfoArray = sizeof($itemInfoArray) > 0? $itemInfoArray : array();	// �����Ͱ� �ִٸ� �״�� ���ٸ� �ʱ�ȭ
									if ($excelBan != '��' && $excelBun != '��ȣ' && $excelName != '����' && sizeof($itemInfoArray) > 0) {	// �̷� �׸��� �ִٸ� ���� ����
										foreach ($itemInfoArray AS $iNum => $val) {
											// echo $itemCodeArray[$key] . "<br>";
											$studentInfoArray[(int)$excelBan][(int)$excelBun][$excelName][$itemCodeArray[$iNum]] = iconv("UTF-8", "EUC-KR", $value[$val]);
											if ($itemCodeArray[$iNum] == 'ONLYNUMBER') $distinctArray['s' . iconv("UTF-8", "EUC-KR", $value[$val])] = 'TRUE';	// �ߺ����̵� ���͸� ���
										}
									}
								}								
                            	
                            }
                        }
                        
	                   	$multiQyery = "";	// GENDER
						if (sizeof($studentInfoArray) > 0) {	// �л� �ڷᰡ �ִٸ�
							foreach ($studentInfoArray AS $iBanKey => $iBanValue) {
								foreach ($studentInfoArray[$iBanKey] AS $iBunKey => $iBunValue) {
									foreach ($studentInfoArray[$iBanKey][$iBunKey] AS $iNameKey => $iNameValue) {
										if ($tmpMyinfo[$studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']][$iNameKey] == 'true') {	// ������Ʈ
											$multiQyery .= "
												UPDATE member SET member_level = '2', std_state = '����', std_pyear = '" . $syrw['GRADEYEAR'] . "',
													name = '" . $iNameKey . "', password = PASSWORD('" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "'),
													sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '����'? '1' : '2') . "',
													gyeol = '" . $nHakgwa . "', hak = '" . $nHak . "', ban = '" . $iBanKey . "', bun = '" . $iBunKey . "',
													std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "',
													post_no = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDRNUMBER'] . "',
													birth = '" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "',
													address = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDR'] . "'
												WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $nHak . "' AND ban = '" . $iBanKey . "'
													AND bun = '" . $iBunKey . "' AND std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "'
													AND name = '" . $iNameKey . "';
											";	// preg_replace("/[^0-9]*/s", "", trim($rowOfSchool['PHONE']))
										} else {	// ����
											$multiQyery .= "
												INSERT INTO member SET user_id = '" . ('s' . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "',
													member_level = '2', std_state = '����', std_pyear = '" . $syrw['GRADEYEAR'] . "',
													name = '" . $iNameKey . "', password = PASSWORD('" .  preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "'),
													sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '����'? '1' : '2') . "',
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
										alert('[�˸�!] �л� �ϰ������ ���������� ó���Ͽ����ϴ�.');\n
										parent.info400vObj.getList();
									</script>\n
								";
							}
						} else {
							echo "
								<script type='text/javascript'>\n
									alert('[�˸�!] �л� �ϰ������ ���������� ó���Ͽ����ϴ�.');\n
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