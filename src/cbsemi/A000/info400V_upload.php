<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "/func/func_global.php");
	include_once $_SERVER['DOCUMENT_ROOT'] . "/func/func_db.php";

	include_once($_SERVER['DOCUMENT_ROOT'] . '/inc/lib.inc'); // ���� ���̺귯��

	/* �л� �л��� ���� ���ε� */
	$db = doConnect();	 // DB connection

	@header("Content-Type: text/html; charset=euc-kr");

	require_once $_SERVER['DOCUMENT_ROOT'] . '/func/func_reader.php';
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('euc-kr');
	$data->read($_FILES['Filename400V']['tmp_name']);
	error_reporting(E_ALL ^ E_NOTICE);
	
	
	$itemCodeArray = array('�л����ι�ȣ' => 'ONLYNUMBER','���ι�ȣ' => 'ONLYNUMBER', '����(����)' => 'CNAME', '����' => 'GENDER', '�������' => 'BIRTHDAY', '�����ȣ' => 'ADDRNUMBER', '�ּ�' => 'ADDR', '��������' => 'DETAIL', '��������' => 'DOUBLE', '������' => 'SUBTITLE', '���' => 'ETC');

	/* �ش�⵵ ������ �����´� */
	$yearSetYn = @$db->query("SELECT GRADEYEAR FROM `ecs_school` WHERE LASTYN='Y';");
	$syrw = @$yearSetYn->fetch_array();

	// ������ ����ִ� �г� ã��
	$excelHaksearch = explode(" ", $data->sheets[0]['cells'][3][1]);
	foreach($excelHaksearch AS $key => $value){
		$val = explode("�г�", $value);
		if (eregi("�г�", $value) && strlen($val[0]) == 1) {
			$grade = $val[0];	// �г� ����
		}
	}

	// ������ ����ִ� �а� ã��
	//2013�г⵵ ����ȸ��� 3�г� 01�� 
	$excelHaksearch =explode(" " , trim($data->sheets[0]['cells'][3][1]));
	foreach($excelHaksearch AS $key => $value){
		if(eregi("��",$value) || eregi("�Ϲ�",$value)){
			$gyeol = trim($value);	// �а� ����
		}
	}
	
	if($gyeol == ""){
		echo "
			<script type='text/javascript'>\n
				alert('[�˸�!] �������� Ȯ���ϼ���.');\n
			</script>\n
			";
		exit;
	}
	
	if ($syrw['GRADEYEAR'] != '' && $grade != '') {
		
		$selrst = @$db->query("SELECT std_unique, gyeol, name FROM member WHERE std_pyear='" . $syrw['GRADEYEAR'] . "' AND hak='" . $grade . "';");	// ó���⵵�� ���� �Ǿ�������� �μ�Ʈ�� ����

		$tmpMyinfo = array();
		while($row = @$selrst->fetch_array()) $tmpMyinfo[$row[std_unique]][$row[name]] = "true";

		$studentInfoArray = array();	$itemInfoArray = array();	$distinctArray = array();
		for ($i = 0; $i <= $data->sheets[0]['numRows']; $i++) {
			/*$tmpBan = explode(" " , trim($data->sheets[0]['cells'][3][1]));
			$tmpBanName = $tmpBan[sizeof($tmpBan) -1];*/
			
			// $excelBan = @intval(preg_replace("/[^0-9]*/s", "", $tmpBanName));	// ��
			$excelBan = $data->sheets[0]['cells'][$i][1];	// ��
			$excelBun = $data->sheets[0]['cells'][$i][2];	// ��ȣ
			$excelName = $data->sheets[0]['cells'][$i][3];	// ����
			
			
			if ($excelBan != '' && $excelBun != '' && $excelName != '') {
				// echo "$excelBan/$excelBun/$excelName<br>";
				if ($excelBun == '��ȣ' && $excelName == '����') {
					$itemInfoArray = array();
					for ($j = 3; $j <= 13; $j++) {	// 4 ~ 7 ���� �ִ� ������ ��ȸ
						if ($data->sheets[0]['cells'][$i][$j] != '') $itemInfoArray[$data->sheets[0]['cells'][$i][$j]] = $j;
					}
				}
				
				/*print_r($itemInfoArray);
				echo "<br><br>";*/
				$itemInfoArray = sizeof($itemInfoArray) > 0? $itemInfoArray : array();	// �����Ͱ� �ִٸ� �״�� ���ٸ� �ʱ�ȭ
				if ($excelBan != '��' && $excelBun != '��ȣ' && $excelName != '����' && sizeof($itemInfoArray) > 0) {	// �̷� �׸��� �ִٸ� ���� ����
					foreach ($itemInfoArray AS $key => $value) {
						// echo $itemCodeArray[$key] . "<br>";
						$studentInfoArray[(int)$excelBan][(int)$excelBun][$excelName][$itemCodeArray[$key]] = $data->sheets[0]['cells'][$i][$value];
						if ($itemCodeArray[$key] == 'ONLYNUMBER') $distinctArray['s' . $data->sheets[0]['cells'][$i][$value]] = 'TRUE';	// �ߺ����̵� ���͸� ���
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
									name = '" . $iNameKey . "', password = PASSWORD('" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "'),
									sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '����'? '1' : '2') . "',
									gyeol = '" . $gyeol . "', hak = '" . $grade . "', ban = '" . $iBanKey . "', bun = '" . $iBunKey . "',
									std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "',
									post_no = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDRNUMBER'] . "',
									birth = '" . preg_replace("/[^0-9]*/s", "", $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "',
									address = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ADDR'] . "'
								WHERE std_pyear = '" . $syrw['GRADEYEAR'] . "' AND hak = '" . $grade . "' AND ban = '" . $iBanKey . "'
									AND bun = '" . $iBunKey . "' AND std_unique = '" . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER'] . "'
									AND name = '" . $iNameKey . "';
							";	// preg_replace("/[^0-9]*/s", "", trim($rowOfSchool['PHONE']))
						} else {	// ����
							$multiQyery .= "
								INSERT INTO member SET user_id = '" . ('s' . $studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['ONLYNUMBER']) . "',
									member_level = '2', std_state = '����', std_pyear = '" . $syrw['GRADEYEAR'] . "',
									name = '" . $iNameKey . "', password = PASSWORD('" .  preg_replace("/[^0-9]*/s", "",$studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['BIRTHDAY']) . "'),
									sex = '" . ($studentInfoArray[$iBanKey][$iBunKey][$iNameKey]['GENDER'] == '����'? '1' : '2') . "',
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
?>