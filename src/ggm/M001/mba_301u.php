<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_db.php";

	/* �л� �л��� ���� ���ε� */
	include_once $_SERVER['DOCUMENT_ROOT'] . '/func/PHPExcel_1.8.0/PHPExcel.php';
	@header("Content-Type:text/html;charset=EUC-KR;");
//	@header("Content-Type:text/html;charset=UTF-8;");
	
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
                            for($row = 1; $row <= 1; $row++) {
                                // $rowData�� ������ �����͸� ������ �迭ó�� �ȴ�.
                                $hakGwaRowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
                                                   
                                // $rowData�� ���� ���� ��� �ʱ�ȭ �Ǳ⶧���� ���� ���� ���ο� �迭�� �����ϰ� ��´�.
                                $hakAndHakgwaAndClasArrayList = explode(" ", iconv("utf-8", "euc-kr", $hakGwaRowData[0][0]));	// �г�, �а�, �б� ������ ����Ѵ�.
                                $hakAndHakgwaAndClasArrayList = $hakGwaRowData[0];
                            }
                    
                            // �����б�
                            for($row = 2; $row <= $highestRow; $row++) {
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
                    
                    // ���� �׸�� ��������
                    $itemTitleArray = array();
                    if (is_array($hakAndHakgwaAndClasArrayList) && sizeof($hakAndHakgwaAndClasArrayList) > 0) {
                    	foreach ($hakAndHakgwaAndClasArrayList AS $exNum => $exText) {
                    		$iconvExNum = trim(iconv("UTF-8", "EUC-KR", $exNum));
                    		$iconvExText = trim(iconv("UTF-8", "EUC-KR", $exText));
                    		$itemTitleArray[$iconvExText] = $iconvExNum;
                    	}
                    }
                    
                    if (is_array($itemTitleArray) && sizeof($itemTitleArray) > 0) {

						$studentInfoArray = array(); // �л��� ���� ���� �迭
						
                        if(sizeof($allData) > 0) {
                            foreach ($allData as $key => $value) {
                                $excelHak = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['�г�']]);			// �г�
                                $excelBan = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['��']]);			// ��
                                $excelHakgwa = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['�а���']]);		// �а�
								$excelBun = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['��ȣ']]);			// ��ȣ
								$excelName = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['����']]);			// ����
								$excelUniq = iconv("UTF-8", "EUC-KR", $value[$itemTitleArray['�л����ι�ȣ']]);	// �л����ι�ȣ
								
								if ($excelHakgwa != '' && $excelHak != '' && $excelBan != '' && $excelBun != '' && $excelName != '' && $excelUniq != '') {
									
									foreach ($itemTitleArray AS $iTxt => $val) {
										// echo $itemCodeArray[$key] . "<br>";
										$studentInfoArray[$excelUniq][$iTxt] = iconv("UTF-8", "EUC-KR", $value[$val]);
									}
								}								
                            }
                        }
                        
	                   	$multiQyery = "";	// GENDER
	                   	if (sizeof($studentInfoArray) > 0) {	// �л� �ڷᰡ �ִٸ�
	                   		foreach ($studentInfoArray AS $iUniqKey => $iValueArr) {
	                   		
		                   		// ó���⵵�� ���� �Ǿ�������� �μ�Ʈ�� ����
								$selrst = $db->query("SELECT * FROM member WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $iValueArr['�г�'] . "' AND gyeol = '" . $iValueArr['�а�'] . "' AND ban = '" . $iValueArr['��'] . "' AND bun = '" . $iValueArr['��ȣ'] . "' LIMIT 1;");
								$row = @$selrst->fetch_array();
								
								if ($row['no'] != "") {
									$multiQyery .= "
										UPDATE member SET member_level = '2', std_state = '����', std_pyear = '" . $syrw['GRADEYEAR'] . "',
											name = '" . $iValueArr['����'] . "', password = PASSWORD('" . preg_replace("/[^0-9]*/s", "", $iValueArr['�л����ι�ȣ']) . "'),
											sex = '" . ($iValueArr['����'] == '����'? '1' : '2') . "',
											gyeol = '" . $iValueArr['�а���'] . "', hak = '" . $iValueArr['�г�'] . "', ban = '" . $iValueArr['��'] . "', 
											bun = '" . $iValueArr['��ȣ'] . "',
											std_unique = '" . $iValueArr['�л����ι�ȣ'] . "',
											post_no = '" . $iValueArr['�����ȣ'] . "',
											birth = '" . preg_replace("/[^0-9]*/s", "", $iValueArr['�������']) . "',
											address = '" . $iValueArr['�ּ�'] . "'
										WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $row['hak'] . "' AND ban = '" . $row['ban'] . "'
											AND bun = '" . $row['bun'] . "' AND std_unique = '" . $row['std_unique'] . "'
											AND name = '" . $row['name'] . "';
									";	// preg_replace("/[^0-9]*/s", "", trim($rowOfSchool['PHONE']))								
								} else {
									$multiQyery .= "
										INSERT INTO member SET user_id = '" . ('s' . $iValueArr['�л����ι�ȣ']) . "',
											member_level = '2', std_state = '����', std_pyear = '" . $syrw['GRADEYEAR'] . "',
											name = '" . $iValueArr['����'] . "', password = PASSWORD('" .  preg_replace("/[^0-9]*/s", "", $iValueArr['�л����ι�ȣ']) . "'),
											sex = '" . ($iValueArr['����'] == '����'? '1' : '2') . "',
											gyeol = '" . $iValueArr['�а���'] . "', hak = '" . $iValueArr['�г�'] . "', ban = '" . $iValueArr['��'] . "', 
											bun = '" . $iValueArr['��ȣ'] . "',
											std_unique = '" . $iValueArr['�л����ι�ȣ'] . "',
											post_no = '" . $iValueArr['�����ȣ'] . "',
											birth = '" . preg_replace("/[^0-9]*/s", "", $iValueArr['�������']) . "',
											address = '" . $iValueArr['�ּ�'] . "',
											reg_date = '" . _getYearToSecond() . "';
									";								
								}
                   			}
                  	 	}
						
						if ($multiQyery != '') {
							if ($result = @$db->multi_query($multiQyery)) {
								echo "
									<script type='text/javascript'>\n
										alert('[�˸�!] �л� �ϰ������ ���������� ó���Ͽ����ϴ�.');\n
										parent.multi_changes.list();
									</script>\n
								";
							}
						} else {
							echo "
								<script type='text/javascript'>\n
									alert('[�˸�!] �л� �ϰ������ ���������� ó���Ͽ����ϴ�.');\n
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