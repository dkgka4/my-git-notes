<?php
	/*
	*	@author : ����ȣ
	*	@since : 2023-03-07
	* 	@descript : 
	*	1. ������ -> ����, ��⵵ -> ���� �ؼ� �־����
	*	2. �� �ٸ��� ����
	*	3. univ, ��ǰ����(������, ��ǰ��) PDF�ٿ� ����
	*	4. Ȥ�ø��� ���
	*
	*/
    function db_conn()
    {
        $result = new mysqli("211.43.210.64","blsoft89","whgdmstjdwjr89","blsoft");

        if(!$result)
        {
            echo "Error[".mysqli_connect_errno() . "] : " . mysqli_connect_error()."<br />";
        }
        else
        {
            return $result;
        }
    }
    
    $caseBy = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['caseBy'])));	// ==
    

	
	$db = db_conn();
	
	
	switch ($caseBy) {
		case "pdf_start" :
			$pIdx = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['selIdx'])));	// ������ȣ
			$pJiyok = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['selJiyok'])));	// ����
			$pSchool = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['selSchool'])));	// �б�
			
			$result = $db -> query ("SELECT * FROM bl_univ_estimate WHERE jiyok = '" . $pJiyok . "' AND schoolname LIKE '%" . $pSchool . "%' AND idx = '" . $pIdx . "' LIMIT 1;");
			$row = $result -> fetch_array();
			
			$htm = time() . mt_rand(0, 9999);  // �ӽ����ϸ�
			//$pUrl = "http://blsoft.co.kr/17blsoft/popup/estimatePopE.php?caseBy=getView&pdf=Y&selIdx=" . $pIdx . "&selJiyok=" . urlencode($pJiyok) . "&selSchool=" . urlencode($pSchool);
			
			// ���� �������� Ȯ�� @���� 250110
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';			
			$pUrl = $protocol."://blsoft.co.kr/popup/estimatePopE.php?caseBy=getView&pdf=Y&selIdx=" . $pIdx . "&selJiyok=" . urlencode($pJiyok) . "&selSchool=" . urlencode($pSchool);
			
			$file_rename = md5($htm) . ".pdf";
			$command = "wkhtmltopdf --page-size A4 --margin-bottom 0mm --margin-left 7mm --margin-right 7mm --margin-top 0mm '" . $pUrl . "' PDF/" . $file_rename;
			@exec($command);
			
			
			$file = $_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $file_rename;
			if (file_exists($file)) {
				// echo "~~~~" . $file_rename;
				$pdf = file_get_contents($file);
				header('Content-Type: file/unknown');
				header('Content-Disposition: attachment; filename=' . $row['schoolname'] . ' ������(' . date('Y�� m�� d�� H�� i�� s��') . ').pdf');
				header('Content-Transfer-Encoding:binary');
				header('Content-Length: ' . filesize($file));
				header('Progma: no-cache');
				header('Expires: 0');
				
				$fp = fopen($file, "rb");
				if (!fpassthru($fp)) 
				fclose($fp);
			}	
			// �ӽ����� ����
			@unlink($_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $htm . ".html");
			@unlink($_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $file_rename);			
		break;

		// ������
		case "pdf_mystart" :
			
			$_deliverArray = array();	// �Ѿ�� Ű����...
			$_requestTextVal = "";
			foreach ($_REQUEST AS $keys => $values) {
				$tmpArray = explode("_", $keys);
				if (@sizeof($tmpArray) == 2 && $tmpArray[0] == "deliverID" && $values != "") $_deliverArray[] = addslashes(iconv("utf-8", "euc-kr", urldecode($values)));
				if ($keys != 'caseBy') {
					$_requestTextVal .= "&" . $keys . "=" . urlencode($values);
				}
			}
			$queryOfString = "";	// ������ �߰� �� ���Ǿ��...
			if (@sizeof($_deliverArray) > 0) {
				for ($i = 0; $i < @sizeof($_deliverArray); $i++) {
					$queryOfString .= $_deliverArray[$i] . ($i == (@sizeof($_deliverArray) - 1)? "" : ",");
				}
			}
			
			
//			$db -> query ("
//			SELECT item_no, item_class_top, item_class_middle, item_class_bottom, item_sort, item_standard, item_size, item_use, item_type, 
//			item_name, item_made, item_unit, item_pay, item_etc1, item_file_name, item_file_size, item_file_type, item_file_rename, item_mount, 
//			item_agency, item_gbn, item_business, item_businessCd, jiyeok, item_line, item_io, item_user, item_usertel, item_userhp, item_userfax, 
//			item_mail, item_zipcode, item_addr, item_manager, item_svnumber, item_code, 
//			DATE_FORMAT(item_date, '%Y-%m-%d') AS item_date, DATE_FORMAT(item_today, '%Y-%m-%d') AS item_today FROM order_output 
//			WHERE item_no IN (" . ($queryOfString == ""? "NULL" : $queryOfString) . ") 
//			ORDER BY item_date DESC, item_pay desc;");
			
			$htm = time() . mt_rand(0, 9999);  // �ӽ����ϸ�
			// ���� �������� Ȯ�� @���� 250110
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';						
			$pUrl = $protocol."://blsoft.co.kr//manager/new_order/omngr/omngr_104E_PDF.php?caseBy=getViewPDF&pdf=Y" . $_requestTextVal;
			
			$file_rename = md5($htm) . ".pdf";
			$command = "wkhtmltopdf --page-size A4 --margin-bottom 0mm --margin-left 7mm --margin-right 7mm --margin-top 0mm '" . $pUrl . "' PDF/" . $file_rename;
			@exec($command);
			
			
			$file = $_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $file_rename;
			if (file_exists($file)) {
				// echo "~~~~" . $file_rename;
				$pdf = file_get_contents($file);
				header('Content-Type: file/unknown');
				header('Content-Disposition: attachment; filename=������(' . date('Y�� m�� d�� H�� i�� s��') . ').pdf');
				header('Content-Transfer-Encoding:binary');
				header('Content-Length: ' . filesize($file));
				header('Progma: no-cache');
				header('Expires: 0');
				
				$fp = fopen($file, "rb");
				if (!fpassthru($fp)) 
				fclose($fp);
			}	
			// �ӽ����� ����
			@unlink($_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $htm . ".html");
			@unlink($_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $file_rename);			
		break;	
		
		
		case "pdf_delivery" :
			
			$_deliverArray = array();	// �Ѿ�� Ű����...
			$_requestTextVal = "";
			foreach ($_REQUEST AS $keys => $values) {
				$tmpArray = explode("_", $keys);
				if (@sizeof($tmpArray) == 2 && $tmpArray[0] == "deliverID" && $values != "") $_deliverArray[] = addslashes(iconv("utf-8", "euc-kr", urldecode($values)));
				if ($keys != 'caseBy') {
					$_requestTextVal .= "&" . $keys . "=" . urlencode($values);
				}
			}
			$queryOfString = "";	// ������ �߰� �� ���Ǿ��...
			if (@sizeof($_deliverArray) > 0) {
				for ($i = 0; $i < @sizeof($_deliverArray); $i++) {
					$queryOfString .= $_deliverArray[$i] . ($i == (@sizeof($_deliverArray) - 1)? "" : ",");
				}
			}
			
//			$db -> query ("
//			SELECT item_no, item_class_top, item_class_middle, item_class_bottom, item_sort, item_standard, item_size, item_use, item_type, 
//			item_name, item_made, item_unit, item_pay, item_etc1, item_file_name, item_file_size, item_file_type, item_file_rename, item_mount, 
//			item_agency, item_gbn, item_business, item_businessCd, jiyeok, item_line, item_io, item_user, item_usertel, item_userhp, item_userfax, 
//			item_mail, item_zipcode, item_addr, item_manager, item_svnumber, item_code, 
//			DATE_FORMAT(item_date, '%Y-%m-%d') AS item_date, DATE_FORMAT(item_today, '%Y-%m-%d') AS item_today FROM order_output 
//			WHERE item_no IN (" . ($queryOfString == ""? "NULL" : $queryOfString) . ") 
//			ORDER BY item_date DESC, item_pay desc;");
			


			$htm = time() . mt_rand(0, 9999);  // �ӽ����ϸ�
			// ���� �������� Ȯ�� @���� 250110
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';						
			$pUrl = $protocol."://blsoft.co.kr//manager/new_order/omngr/omngr_105T_PDF.php?caseBy=getViewPDF&pdf=Y" . $_requestTextVal;
			
			if ($_SERVER['REMOTE_ADDR'] == '61.85.146.3') {
//				echo $pUrl;
//				exit;
			}
			
			$file_rename = md5($htm) . ".pdf";
			$command = "wkhtmltopdf --page-size A4 --margin-bottom 0mm --margin-left 7mm --margin-right 7mm --margin-top 0mm '" . $pUrl . "' PDF/" . $file_rename;
			@exec($command);
			
			
			$file = $_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $file_rename;
			if (file_exists($file)) {
				// echo "~~~~" . $file_rename;
				$pdf = file_get_contents($file);
				header('Content-Type: file/unknown');
				header('Content-Disposition: attachment; filename=��ǰ��(' . date('Y�� m�� d�� H�� i�� s��') . ').pdf');
				header('Content-Transfer-Encoding:binary');
				header('Content-Length: ' . filesize($file));
				header('Progma: no-cache');
				header('Expires: 0');
				
				$fp = fopen($file, "rb");
				if (!fpassthru($fp)) 
				fclose($fp);
			}	
			// �ӽ����� ����
			@unlink($_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $htm . ".html");
			@unlink($_SERVER['DOCUMENT_ROOT'] . "/result/PDF/" . $file_rename);			
		break;				
			
		default:break;
	}
	
?>