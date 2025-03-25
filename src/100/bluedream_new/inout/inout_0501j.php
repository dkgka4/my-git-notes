<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . 'func/func_global.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . 'func/func_db.php';

	$caseBy = addslashes(iconv("utf-8", "euc-kr", urldecode($_REQUEST['caseBy'])));
	if ($caseBy == "" || strtolower($_SERVER['REQUEST_METHOD']) != "post") {
		echo "
			<script language=\"javascript\">\n
				alert(\"[�˸�!] �߸��� ���� �Դϴ�. ������ �ҷ��ü� �����ϴ�.\");\n
			</script>\n
		";		
  		exit;	
	}

	/*******************************
	*	�ڷ���� > ��Ź�� ���Ƹ�Ȱ��
	*	@ author : �̱ٸ�
	*	@ since : 2020.12.21.
	*******************************/

	$db = doConnect();	// db connect
	
	$resOfSchInfo = $db->query("SELECT * FROM school_info WHERE LASTYN = 'Y' LIMIT 1;");
	$rowOfSchInfo = $resOfSchInfo->fetch_array();

	$ioGradeYear = $rowOfSchInfo['GRADEYEAR'];

	switch ($caseBy) {
		case "getClub" : 	// ���⵵ ���л� ���Ƹ� ���
			$result = $db->query("SELECT * FROM member WHERE std_pyear = '" . $ioGradeYear . "' AND std_state = '����' AND use_yn = 'Y' AND department2 <> '' AND department2 IS NOT NULL GROUP BY department2;");
			while ($row = $result->fetch_array()) {
				echo "<option value=\"" . $row['department2'] . "\">" . $row['department2'] . "</option>";
			}
		break;

		case "getList" : 	// ������ ��ȸ
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
					$ptmt = $db->query("SELECT *, DATE_FORMAT(ioItem01, '%Y.%m.%d.') AS inputDate FROM school_data WHERE uniqKey = '" . $uniqKey . "' AND ioYear = '" . $ioGradeYear . "' AND ioHakgi = '" . $pHakgi . "' AND (ioDiv = '���Ƹ�Ȱ��' OR ioDiv = '���Ƹ�Ȱ��-�л���') ORDER BY ioDiv, ioItem01;");
					while ($rs = $ptmt->fetch_array()) {
						if ($rowCount == 0) {
?>
	<tr>
		<td><img style="cursor:pointer;" src="/images/<?= $ptmt->num_rows > 1? "nolines_plus" : "nolines_minus" ?>.gif" class="<?= $ptmt->num_rows > 1? "inout0501vExtendClass" : "" ?>" valid.blank="inout0501vExtendHiddenClass<?= $uniqKey ?>" align="absmiddle"></td>
		<td><?= $rs['sBan'] ?></td>
		<td><?= $rs['sBun'] ?></td>
		<td><?= $rs['sName'] ?></td>

		<td><input type="checkbox" class="inout0501vChoiceItemClass" valid.uKeys="<?= $uniqKey ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" value="<?= $rs['idx'] ?>"></td>
		<td><?= $rs['ioHakgi'] ?>�б�</td>
<?php
							if ($rs['ioDiv'] == '���Ƹ�Ȱ��') {
?>
		<td><?= $rs['inputDate'] ?></td>
		<td><?= stripslashes($rs['ioItem02']) ?></td>
		<td style="text-align:left;"><?= $rs['ioItem06'] == 'Y'? '<span style="font-weight:bold; color:blue;">[���翬��]</span> ' : '' ?><?= nl2br(stripslashes($rs['ioItem03'])) ?></td>
		<td><?= stripslashes($rs['ioItem04']) ?></td>
<?php
							} else {
?>
		<td colspan='3' style="text-align:left;"><span style="font-weight:bold;">�� �б���Ȱ��Ϻ� �ݿ���� �� : </span><?= $rs['ioItem02'] != ''? "<br>" . nl2br(stripslashes($rs['ioItem02'])) : nl2br(stripslashes($rs['ioItem02'])) ?></td>
		<td><?= stripslashes($rs['ioItem01']) ?></td>
<?php
							}
?>
		<td><a class="button smallrounded blue input0501vModifyClasses" valid.type="<?= $rs['ioDiv'] == '���Ƹ�Ȱ��'? '1' : '2' ?>" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">����</a> <a class="button smallrounded rosy input0501vRemoveClasses" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">����</a></td>
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
		<td><?= $rs['ioHakgi'] ?>�б�</td>
<?php
							if ($rs['ioDiv'] == '���Ƹ�Ȱ��') {
?>
		<td><?= $rs['inputDate'] ?></td>
		<td><?= stripslashes($rs['ioItem02']) ?></td>
		<td style="text-align:left;"><?= nl2br(stripslashes($rs['ioItem03'])) ?></td>
		<td><?= stripslashes($rs['ioItem04']) ?></td>
<?php
							} else {
?>
		<td colspan='3' style="text-align:left;"><span style="font-weight:bold;">�� �б���Ȱ��Ϻ� �ݿ���� �� : </span><?= $rs['ioItem02'] != ''? "<br>" . nl2br(stripslashes($rs['ioItem02'])) : nl2br(stripslashes($rs['ioItem02'])) ?></td>
		<td><?= stripslashes($rs['ioItem01']) ?></td>
<?php
							}
?>
		<td><a class="button smallrounded blue input0501vModifyClasses" valid.type="<?= $rs['ioDiv'] == '���Ƹ�Ȱ��'? '1' : '2' ?>" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">����</a> <a class="button smallrounded rosy input0501vRemoveClasses" valid.idx="<?= $rs['idx'] ?>" valid.hakgi="<?= $rs['ioHakgi'] ?>" valid.uKeys="<?= $uniqKey ?>">����</a></td>
	</tr>
<?php
						}
						
						$rowCount++;	// �� �л��� ������ ���� ī��Ʈ
						$realCnt++;	// ��ȸ ��� ��� �л��� ������ ī��Ʈ
					}
				}
			}

			if ($realCnt <= 0) {
?>
	<tr>
		<td colspan="11" style="text-align:center; height:50px;">:: ��ȸ�� �����Ͱ� �����ϴ�. ::</td>
	</tr>
<?php
			}
	 	break;
	 	
		case "doSave" : 
			$pUniqNo = addslashes(htmlspecialchars($_REQUEST['inout0501vTarget'], ENT_QUOTES, 'ISO-8859-1'));	// ��� �л�, ������ ��ǥ.
			
			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501vItem00'], ENT_QUOTES, 'ISO-8859-1'));	// �б�
			
			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem01'], ENT_QUOTES, 'ISO-8859-1'));	// �Ͻ�
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem02'], ENT_QUOTES, 'ISO-8859-1'));	// �̼��ð�
			$pItem03 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem03'], ENT_QUOTES, 'ISO-8859-1'));	// Ȱ������
			$pItem04 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem04'], ENT_QUOTES, 'ISO-8859-1'));	// �μ���

			$pItem05 = $_REQUEST['inout0501vItem05'];	// ���� ����

			$pItem06 = addslashes(htmlspecialchars($_REQUEST['inout0501vItem06'], ENT_QUOTES, 'ISO-8859-1'));	// ���翬�� Y

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

					$ptmt2 = $db->query("SELECT IFNULL(MAX(ioSort) + 1, 1) AS maxValue FROM school_data WHERE uniqKey = '" . $uniqKey . "' AND ioDiv = '���Ƹ�Ȱ��';");
					$rs2 = $ptmt2->fetch_array();

					$db->query("
						INSERT INTO school_data SET 
							uniqKey = '" . $uniqKey . "', sHak = '" . $rs['hak'] . "', sBan = '" . $rs['ban'] . "', sBun = '" . $rs['bun'] . "', sName = '" . $rs['name'] . "', 
							ioYear = '" . $ioGradeYear . "', ioDiv = '���Ƹ�Ȱ��', ioSort = '" . $rs2['maxValue'] . "', 
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
			$pUniqNo = addslashes(htmlspecialchars($_REQUEST['inout0501vTarget2'], ENT_QUOTES, 'ISO-8859-1'));	// ��� �л�, ������ ��ǥ.

			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501v2Item00'], ENT_QUOTES, 'ISO-8859-1'));	// �б�

			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501v2Item01'], ENT_QUOTES, 'ISO-8859-1'));	// �μ���
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501v2Item02'], ENT_QUOTES, 'ISO-8859-1'));	// Ư�����

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

					$ptmt2 = $db->query("SELECT IFNULL(MAX(ioSort) + 1, 1) AS maxValue FROM school_data WHERE uniqKey = '" . $uniqKey . "' AND ioDiv = '���Ƹ�Ȱ��-�л���';");
					$rs2 = $ptmt2->fetch_array();

					$db->query("
						INSERT INTO school_data SET 
							uniqKey = '" . $uniqKey . "', sHak = '" . $rs['hak'] . "', sBan = '" . $rs['ban'] . "', sBun = '" . $rs['bun'] . "', sName = '" . $rs['name'] . "', 
							ioYear = '" . $ioGradeYear . "', ioDiv = '���Ƹ�Ȱ��-�л���', ioSort = '" . $rs2['maxValue'] . "', 
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
			$pUniqKey = addslashes(htmlspecialchars($_REQUEST['pUniqIdx'], ENT_QUOTES, 'ISO-8859-1'));	// ��� �л�, ������ ��ǥ.

			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem00'], ENT_QUOTES, 'ISO-8859-1'));	// �б�
			
			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem01'], ENT_QUOTES, 'ISO-8859-1'));	// �г�
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem02'], ENT_QUOTES, 'ISO-8859-1'));	// Ȱ������ �Ǵ� ����
			$pItem03 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem03'], ENT_QUOTES, 'ISO-8859-1'));	// Ȱ������
			$pItem04 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem04'], ENT_QUOTES, 'ISO-8859-1'));	// Ȱ������

			$pItem05 = $_REQUEST['inout0501vDialogItem05'];	// ���� ����

			$pItem06 = addslashes(htmlspecialchars($_REQUEST['inout0501vDialogItem06'], ENT_QUOTES, 'ISO-8859-1'));	// ���翬�� Y

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
			$pUniqKey = addslashes(htmlspecialchars($_REQUEST['pUniqIdx'], ENT_QUOTES, 'ISO-8859-1'));	// ��� �л�, ������ ��ǥ.
			
			$pItemHakgi = addslashes(htmlspecialchars($_REQUEST['inout0501v2DialogItem00'], ENT_QUOTES, 'ISO-8859-1'));	// �б�
			
			$pItem01 = addslashes(htmlspecialchars($_REQUEST['inout0501v2DialogItem01'], ENT_QUOTES, 'ISO-8859-1'));	// �μ���
			$pItem02 = addslashes(htmlspecialchars($_REQUEST['inout0501v2DialogItem02'], ENT_QUOTES, 'ISO-8859-1'));	// Ư�����

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
<div style="width:100%; font-weight:bold;"><?= $row['sHak'] ?>�г� <?= $row['sBan'] ?>�� <?= $row['sBun'] ?>�� <?= $row['sName'] ?></div>
<form name="inout0501vDialogForm" id="inout0501vDialogForm" method="POST">

	<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types" style="width:100%; margin-top:8px;">
<?php
			if ($row['ioDiv'] == '���Ƹ�Ȱ��') {
?>
		<tr>
			<th style="width:120px;">�б�</th>
			<td style="text-align:left;">
				<select id="inout0501vDialogItem00" name="inout0501vDialogItem00" class="input_GrayLine" style="width:70px;">
					<option value="<?= $row['ioHakgi'] ?>" selected><?= $row['ioHakgi'] ?>�б�</option>
					<!--<option value="1" <?= $row['ioHakgi'] == '1'? 'selected' : '' ?>>1�б�</option>
					<option value="2" <?= $row['ioHakgi'] == '2'? 'selected' : '' ?>>2�б�</option>-->
				</select>
			</td>
		</tr>
		<tr>
			<th>����</th>
			<td style="text-align:left;"><input type="text" id="inout0501vDialogItem01" name="inout0501vDialogItem01" valid.label="����" placeholder="����" class="input_GrayLineCenter" readonly style='background-color:#e3e3e3; width:85px; margin-right:3px;' value="<?= stripslashes($row['ioItem01']) ?>"></td>
		</tr>
		<tr>
			<th>�̼��ð�<br>(���� Ŭ��!)</th>
			<td style="text-align:left;">
				<select id="inout0501vDialogItem05u" name="inout0501vDialogItem05u" class="input_GrayLine" multiple style="height:65px !important; width:60px;">
<?php
				$timeTable = explode(",",$row['ioApplyTime']);
				for ($i = 1; $i <= 7; $i++) {
					echo "<option value=\"" . $i . "\">" . $i . "����</option>";
				}
			
				$tempArrayList = explode(",", $row['ioItem05']);
				$___tempArrayList = array();
				for ($x = 0; $x < @sizeof($tempArrayList); $x++) {
					if ($tempArrayList[$x] != "") $___tempArrayList[$tempArrayList[$x]] = 'TRUE';
				}
				
				@ksort($___tempArrayList);
?>
				</select>
				��
				<select id="inout0501vDialogItem05" name="inout0501vDialogItem05[]" class="input_GrayLine" multiple style="height:65px !important; width:60px;">
<?php
				if (@sizeof($___tempArrayList) > 0) {
					foreach ($___tempArrayList AS $k => $v) {
						echo "<option value=\"" . $k . "\">" . $k . "����</option>";
					}
				}
				if (@sizeof($timeTable) > 0){
					foreach ($timeTable AS $k => $v){
?>
					<option value="<?=$v != '' ? $v : '' ?>"><?=$v != '' ? $v ."����" : '' ?></option>	
<?php
						
					}
				}
					
?>
				</select>
				<div style="margin-top:1px;">
					<input type="text" id="inout0501vDialogItem02" name="inout0501vDialogItem02" valid.label="�̼��ð�" class="input_GrayLineCenter isNumeric" readonly style="background-color:#e3e3e3; width:140px;" value="<?= stripslashes($row['ioItem02']) ?>">
				</div>
			</td>
		</tr>
		<tr>
			<th>Ȱ������</th>
			<td style="text-align:left;"><textarea id="inout0501vDialogItem03" name="inout0501vDialogItem03" valid.label="Ȱ������" placeholder="Ȱ������" class="input_GrayLine" style="height:68px; width:100%;"><?= stripslashes($row['ioItem03']) ?></textarea></td>
		</tr>
		<tr>
			<th>�μ���</th>
			<td style="text-align:left;"><input type="text" id="inout0501vDialogItem04" name="inout0501vDialogItem04" valid.label="�μ���" placeholder="�μ���" class="input_GrayLineCenter" style="width:100%;" value="<?= stripslashes($row['ioItem04']) ?>"></td>
		</tr>
		<tr>
			<th>���翬��</th>
			<td style="text-align:center;"><input type="checkbox" id="inout0501vDialogItem06" name="inout0501vDialogItem06" valid.label="���翬��" style="width:20px; height:20px;" <?= $row['ioItem06'] == 'Y'? 'checked' : '' ?> value="Y"></td>
		</tr>
<?php
			} else if ($row['ioDiv'] == '���Ƹ�Ȱ��-�л���') {
?>
		<tr>
			<th style="width:120px;">�б�</th>
			<td style="text-align:left;">
				<select id="inout0501v2DialogItem00" name="inout0501v2DialogItem00" class="input_GrayLine" style="width:70px;">
					<option value="1" <?= $row['ioHakgi'] == '1'? 'selected' : '' ?>>1�б�</option>
					<option value="2" <?= $row['ioHakgi'] == '2'? 'selected' : '' ?>>2�б�</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>�μ���</th>
			<td style="text-align:left;"><input type="text" id="inout0501v2DialogItem01" name="inout0501v2DialogItem01" valid.label="�μ���" placeholder="�μ���" class="input_GrayLineCenter" style="width:100%;" value="<?= stripslashes($row['ioItem01']) ?>"></td>
		</tr>
		<tr>
			<th>Ư�����<br>(<span id="counterOfDialogIO0501v">###</span>�� �̳�)</th>
			<td style="text-align:left;"><textarea id="inout0501v2DialogItem02" name="inout0501v2DialogItem02" valid.label="Ư�����" placeholder="Ư�����" class="input_GrayLine" stringMaxLength="250" style="height:68px; width:100%;"><?= stripslashes($row['ioItem02']) ?></textarea></td>
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