<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_db.php");

	/**
	 * @ author : �̱ٸ�
	 * @ since : 2024.12.04.
	 * @ descript : �ڷ���ȸ > ����� ���� ��ȸ
	 */
	
	if ($sess_Iden == "" || $sess_Code == "") {
		echo "
			<script language=\"javascript\">\n
				alert(\"[�˸�!] �α����� �ֽʽÿ�.\");\n
				location.href = \"/logout.php\";\n
			</script>\n
		";
	}
	
	$caseBy = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['caseBy'])));
	if ($caseBy != "getExcel") {
		if ($caseBy == "" || strtolower($_SERVER['REQUEST_METHOD']) != "post") {
			echo "
				<script language=\"javascript\">\n
					alert(\"[�˸�!] �߸��� ���� �Դϴ�. ������ �ҷ��ü� �����ϴ�.\");\n
				</script>\n
			";		
	  		exit;	
		}
	}
	
	$db = doConnect();	 // DB connection

	$yearSetYn = $db->query("SELECT GRADEYEAR FROM ecs_school WHERE LASTYN = 'Y' LIMIT 1;");	// ����⵵ Ȯ��
	$syrw = $yearSetYn->fetch_array();

	switch ($caseBy) {
		case "getList" : 	// @descript : ���
			$pUniq = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pUniq'])));
			$pHak = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pHak'])));

			$teacherList = array();	// �����Է��� ���������
			$results = $db->query("SELECT no, user_id, name FROM member WHERE member_level = '1' AND use_yn = 'Y' OR member_level = '99';");
			while ($rows = $results->fetch_array()) {
				$teacherList[$rows['no']]['name'] = $rows['name'];
				$teacherList[$rows['no']]['user_id'] = $rows['user_id'];
			}

			$tempArrayList = explode(",", $pUniq);

			$stringArrayList = array();
			for ($i = 0; $i < @sizeof($tempArrayList); $i++) {
				if ($tempArrayList[$i] != "") $stringArrayList[$tempArrayList[$i]] = "TRUE";
			}

			if ($pHak == '3') $pastGradeYear = $nSelectYear - 2;
			else if ($pHak == '2') $pastGradeYear = $nSelectYear - 1;
			else if ($pHak == '1') $pastGradeYear = $nSelectYear;

			$res1 = $db->query("SELECT * FROM new_cert_acknowledgment_mgr WHERE gYear = '" . $pastGradeYear . "' AND gTypeof = 'NEW����ļ���-������ؼ���' LIMIT 1;");
			$r1 = $res1->fetch_array();
			
			$nElemType11SetData = array();	// ����ļ���
			$nElemType11SetData[$r1['gTypeof']]['����ļ���_�����⼮��'] 				= $r1['gCert1'] == ''? 0 : $r1['gCert1'];	// ����ļ��� �����⼮��
			$nElemType11SetData[$r1['gTypeof']]['����ļ���_�����⼮��_�ο�����'] 		= $r1['gCert2'] == ''? 0 : $r1['gCert2'];	// ����ļ��� �����⼮�� �ο�����
			
			$tableViewCnt = 0;
			if (@sizeof($stringArrayList) > 0) {
				foreach ($stringArrayList AS $uniqKey => $uniqValue) {
					$result = $db->query("
						SELECT gyeol, hak, ban, bun, name, std_unique FROM member
						WHERE member_level = '2' AND std_pyear = '" . $syrw['GRADEYEAR'] . "' AND use_yn = 'Y' AND std_unique = '" . $uniqKey . "' LIMIT 1;
					");
					$row = $result->fetch_array();

					$afterSchoolData = array();	// ����� ���� �� ���� ����

					$vElemType11FinalData = array();	// ����� ���� ���� data

					$pstmt = $db->query("
						SELECT i_no, i_type, i_uniq, i_hakgwa, i_hak, i_ban, i_bun, i_name, 
							i_cert, let_totaltime, lec_time, lec_ratio, lec_Score, DATE_FORMAT( i_date, '%Y-%m-%d' ) AS i_date, i_write, i_year, modDate, DATE_FORMAT( regDate, '%Y-%m-%d' ) AS regDate 
						FROM new_cert_data WHERE i_type IN ('NEW����ļ���_�⼮��') AND i_uniq = '" . $uniqKey . "' ORDER BY regDate DESC;
					");
					while ($rw = $pstmt->fetch_array()) $afterSchoolData[] = $rw;
					
					for ($mCnt = 0; $mCnt < sizeof($afterSchoolData); $mCnt++) {
						$vElemType11FinalData['����ļ���_��������'] += ($afterSchoolData[$mCnt]['lec_ratio'] >= $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��']? $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��_�ο�����'] : 0);
					}

					if (sizeof($afterSchoolData) > 0) {
?>
	<tr>
		<td style="padding-right:0px; padding-left:0px; text-align:center; vertical-align:top;"><img style="cursor:pointer;" src="/images/treeimg/nolines_plus.gif" class="nano7201vViewFgYnClass" valid.blank="nano7201vHiddenClass<?= $uniqKey ?>" align="absmiddle"></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['hak'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['gyeol'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['ban'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['bun'] ?></td>
		<td style="padding-right:0px; padding-left:0px; vertical-align:top;"><?= $row['name'] ?></td>
		<td style="text-align:left; padding-left:2px; padding-right:2px; background-color:#e3e3e3;" colspan="3">
<?php
						echo '<div>[ <span style="font-weight:bold; color:red;">����� ���� ����</span> ] <span style="font-weight:bold; color:blue;">' . ($vElemType11FinalData['����ļ���_��������'] == ''? '' : $vElemType11FinalData['����ļ���_��������']) . '</span></div>';
?>
		</td>
	</tr>
<?php
						for ($mCnt = 0; $mCnt < sizeof($afterSchoolData); $mCnt++) {
							$viewString = '';
							if ($afterSchoolData[$mCnt]['i_type'] == 'NEW����ļ���_�⼮��') {
								$viewString .= '[ <span style="font-weight:bold; color:blue;">����� ����</span> ]';
								$viewString .= " " . $afterSchoolData[$mCnt]['i_cert'] . ($afterSchoolData[$mCnt]['lec_ratio'] != ''? " ���⼮��" . $afterSchoolData[$mCnt]['lec_ratio'] . "%, " . ($afterSchoolData[$mCnt]['lec_time'] . "/" . $afterSchoolData[$mCnt]['let_totaltime']) . "��" . ($afterSchoolData[$mCnt]['lec_ratio'] >= $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��']? " <span style='font-weight:bold;'>(" . $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��_�ο�����'] . "��)</span>" : "") : "");
							}
?>
	<tr class="nano7201vHiddenClass<?= $uniqKey ?>" style="display:none;">
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
		<td colspan="9" style="text-align:center; height:52px;">:: ��ϵ� �����Ͱ� �����ϴ�. ::</td>
	</tr>
<?php
			}
		break;

		case "getExcel" :
			@header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			@header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			@header ("Cache-Control: no-cache, must-revalidate");
			@header ("Pragma: no-cache");
			@header ("Content-type: application/x-msexcel");
			@header ("Content-Disposition: attachment; filename=����� ���� ��ȸ(" . date("Y�� m�� d�� H�� i�� s��") . ").xls" );
			@header ("Content-Description: PHP/INTERBASE Generated Data" );
			@header ("Content-charset=euc-kr");	
?>
	<table border="1" cellSpacing="0" cellPadding="0" style="width:100%;">
		<tr>
			<th style="background-color:#c3c3c3; font-size:10px;">�г�</th>
			<th style="background-color:#c3c3c3; font-size:10px;">�а�</th>
			<th style="background-color:#c3c3c3; font-size:10px;">�б�</th>
			<th style="background-color:#c3c3c3; font-size:10px;">��ȣ</th>
			<th style="background-color:#c3c3c3; font-size:10px;">�̸�</th>
			<th style="background-color:#c3c3c3; font-size:10px;">���� �� �󼼳���</th>
			<th style="background-color:#c3c3c3; font-size:10px;">�� ��¥</th>
			<th style="background-color:#c3c3c3; font-size:10px;">���� �Է���</th>
		</tr>
<?php
			$pUniq = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pUniq'])));
			$pHak = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['pHak'])));

			$teacherList = array();	// �����Է��� ���������
			$results = $db->query("SELECT no, user_id, name FROM member WHERE member_level = '1' AND use_yn = 'Y' OR member_level = '99';");
			while ($rows = $results->fetch_array()) {
				$teacherList[$rows['no']]['name'] = $rows['name'];
				$teacherList[$rows['no']]['user_id'] = $rows['user_id'];
			}

			$tempArrayList = explode(",", $pUniq);

			$stringArrayList = array();
			for ($i = 0; $i < @sizeof($tempArrayList); $i++) {
				if ($tempArrayList[$i] != "") $stringArrayList[$tempArrayList[$i]] = "TRUE";
			}

			if ($pHak == '3') $pastGradeYear = $nSelectYear - 2;
			else if ($pHak == '2') $pastGradeYear = $nSelectYear - 1;
			else if ($pHak == '1') $pastGradeYear = $nSelectYear;

			$res1 = $db->query("SELECT * FROM new_cert_acknowledgment_mgr WHERE gYear = '" . $pastGradeYear . "' AND gTypeof = 'NEW����ļ���-������ؼ���' LIMIT 1;");
			$r1 = $res1->fetch_array();
			
			$nElemType11SetData = array();	// ����ļ���
			$nElemType11SetData[$r1['gTypeof']]['����ļ���_�����⼮��'] 				= $r1['gCert1'] == ''? 0 : $r1['gCert1'];	// ����ļ��� �����⼮��
			$nElemType11SetData[$r1['gTypeof']]['����ļ���_�����⼮��_�ο�����'] 		= $r1['gCert2'] == ''? 0 : $r1['gCert2'];	// ����ļ��� �����⼮�� �ο�����
			
			$tableViewCnt = 0;
			if (@sizeof($stringArrayList) > 0) {
				foreach ($stringArrayList AS $uniqKey => $uniqValue) {
					$result = $db->query("
						SELECT gyeol, hak, ban, bun, name, std_unique FROM member
						WHERE member_level = '2' AND std_pyear = '" . $syrw['GRADEYEAR'] . "' AND use_yn = 'Y' AND std_unique = '" . $uniqKey . "' LIMIT 1;
					");
					$row = $result->fetch_array();

					$afterSchoolData = array();	// ����� ���� �� ���� ����

					$vElemType11FinalData = array();	// ����� ���� ���� data

					$pstmt = $db->query("
						SELECT i_no, i_type, i_uniq, i_hakgwa, i_hak, i_ban, i_bun, i_name, 
							i_cert, let_totaltime, lec_time, lec_ratio, lec_Score, DATE_FORMAT( i_date, '%Y-%m-%d' ) AS i_date, i_write, i_year, modDate, DATE_FORMAT( regDate, '%Y-%m-%d' ) AS regDate 
						FROM new_cert_data WHERE i_type IN ('NEW����ļ���_�⼮��') AND i_uniq = '" . $uniqKey . "' ORDER BY regDate DESC;
					");
					while ($rw = $pstmt->fetch_array()) $afterSchoolData[] = $rw;
					
					for ($mCnt = 0; $mCnt < sizeof($afterSchoolData); $mCnt++) {
						$vElemType11FinalData['����ļ���_��������'] += ($afterSchoolData[$mCnt]['lec_ratio'] >= $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��']? $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��_�ο�����'] : 0);
					}

					if (sizeof($afterSchoolData) > 0) {
?>
	<tr>
		<td valign="top" style="font-size:10px;"><?= $row['hak'] ?></td>
		<td valign="top" style="font-size:10px;"><?= $row['gyeol'] ?></td>
		<td valign="top" style="font-size:10px;"><?= $row['ban'] ?></td>
		<td valign="top" style="font-size:10px;"><?= $row['bun'] ?></td>
		<td valign="top" style="font-size:10px;"><?= $row['name'] ?></td>
		<td style="background-color:#e3e3e3; font-size:10px;" colspan="3">
<?php
						echo '<div>[ <span style="font-weight:bold; color:red;">����� ���� ����</span> ] <span style="font-weight:bold; color:blue;">' . ($vElemType11FinalData['����ļ���_��������'] == ''? '' : $vElemType11FinalData['����ļ���_��������']) . '</span></div>';
?>
		</td>
	</tr>
<?php
						for ($mCnt = 0; $mCnt < sizeof($afterSchoolData); $mCnt++) {
							$viewString = '';
							if ($afterSchoolData[$mCnt]['i_type'] == 'NEW����ļ���_�⼮��') {
								$viewString .= '[ <span style="font-weight:bold; color:blue;">����� ����</span> ]';
								$viewString .= " " . $afterSchoolData[$mCnt]['i_cert'] . ($afterSchoolData[$mCnt]['lec_ratio'] != ''? " ���⼮��" . $afterSchoolData[$mCnt]['lec_ratio'] . "%, " . ($afterSchoolData[$mCnt]['lec_time'] . "/" . $afterSchoolData[$mCnt]['let_totaltime']) . "��" . ($afterSchoolData[$mCnt]['lec_ratio'] >= $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��']? " <span style='font-weight:bold;'>(" . $nElemType11SetData['NEW����ļ���-������ؼ���']['����ļ���_�����⼮��_�ο�����'] . "��)</span>" : "") : "");
							}
?>
	<tr>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="border-top:1px solid #ffffff;">&nbsp;</td>
		<td style="font-size:10px;"><?= $viewString ?></td>
		<td style="font-size:10px;"><?= $afterSchoolData[$mCnt]['i_date'] ?></td>
		<td style="font-size:10px;"><?= $teacherList[$afterSchoolData[$mCnt]['i_write']]['name'] ?></td>
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
		<td colspan="8" style="text-align:center; font-size:10px;">:: ��ϵ� �����Ͱ� �����ϴ�. ::</td>
	</tr>
<?php
			}
?>
	</table>
<?php
		break;
		
		default : break;
	}
?>