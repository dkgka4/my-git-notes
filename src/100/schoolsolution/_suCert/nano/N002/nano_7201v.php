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
	$db = doConnect();
	
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_auth.php";	// �ݵ�� $db = doConnect(); �ؿ� �־�� �մϴ�.
?><script type="text/javascript">
	$.datepicker.setDefaults({
	    dateFormat: 'yy-mm-dd',
	    buttonImageOnly: true,
	    buttonText: "�޷�",
	    buttonImage: "/images/calendar.gif",
	    dayNamesMin: ['��','��', 'ȭ', '��', '��', '��', '��'], 
		monthNames : ['1��','2��','3��','4��','5��','6��','7��','8��','9��','10��','11��','12��']
	});

	var nano7201v = function() { }	// ó��

	nano7201v.getList = function() {	// @descript : ���
		if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

		var selected = '';
		$('#nano7201vCno option[value!="multiselect-all"]:selected').each(function() {
			selected += $(this).val() + ',';
		});

		$.ajax({
			async : false, type : "POST", url : "/_suCert/nano/N002/nano_7201j.php", data : "caseBy=getList&pHak=" + encodeURIComponent($("#nano7201vHak option:selected").val()) + "&pUniq=" + encodeURIComponent(selected), dataType : "html", 
			error : function(response, status, request) {	// ��� ���� �߻��� ó��
				alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
			},
			success: function (createHtml) {
				$('#nano7201vMainListTable tbody').empty().html(createHtml);

				$(".nano7201vViewFgYnClass").each(function() {
					if ($(this).attr("src").indexOf("nolines_plus") > -1) {	
						$(this).attr("src", "/images/treeimg/nolines_minus.gif");
						$("." + $(this).attr("valid.blank")).show();
					}
				});

				$(".nano7201vViewFgYnClass").on("click", function() {
					if ($(this).attr("src").indexOf("nolines_plus") > -1) {
						$(this).attr("src", "/images/treeimg/nolines_minus.gif");
						$("." + $(this).attr("valid.blank")).show();
					} else {
						$(this).attr("src", "/images/treeimg/nolines_plus.gif");
						$("." + $(this).attr("valid.blank")).hide();
					}
				});
			}
		});
	}

	$(document).ready(function() {
		nano7201v.getList();

		$('#nano7201vHak').multiselect({
			nonSelectedText : ':: &nbsp;�� ��&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' �� �г� ���� &nbsp;&nbsp;',
			includeSelectAllOption : true,
			onChange: function(option, checked) {
				var values = [];
				$('#nano7201vHak option').each(function() {
					if ($(this).val() !== option.val()) {
						values.push($(this).val());
					}
				});

				var param = '';
				$('#nano7201vHak option[value!="multiselect-all"]:selected').each(function() {
					param += $(this).val() + ',';
				});

				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getHakHakgwaClassList&builtPath=<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>&vHak=" + encodeURIComponent(param), dataType : "json",	// datatype �� Ÿ���� �߸� �����Ұ�� ���� ����~
					error : function(response, status, request) {	// ��� ���� �߻��� ó��
						alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
					},
					success: function (resObj) {
						$('#nano7201vGyl').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});

		$('#nano7201vGyl').multiselect({
			nonSelectedText : ':: &nbsp;�а� & �б�&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' �� �а� & �б� ���� &nbsp;&nbsp;',
			includeSelectAllOption : true,
			onChange: function(option, checked) {
				var values = [];

				$('#nano7201vGyl option').each(function() {
					if ($(this).val() !== option.val()) {
						values.push($(this).val());
					}
				});

				var param1 = $("#nano7201vHak option:selected").val() == undefined? "" : $("#nano7201vHak option:selected").val();
				var param2 = '';
				$('#nano7201vGyl option[value!="multiselect-all"]:selected').each(function() {
					param2 += $(this).val() + ',';
				});

				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getJoinClnoList2&vGyl=" + encodeURIComponent(param2), dataType : "json",	// datatype �� Ÿ���� �߸� �����Ұ�� ���� ����~
					error : function(response, status, request) {	// ��� ���� �߻��� ó��
						alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
					},
					success: function (resObj) {
						$('#nano7201vCno').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});

		$('#nano7201vCno').multiselect({
			nonSelectedText : ':: &nbsp;[�б�-��ȣ] �̸�&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' �� �л� ���� &nbsp;&nbsp;',
			numberDisplayed : 1
		});

		$("#nano7201vCertMainSearch").on("click", function() {	// @descript : ��ȸ ��ư Ŭ��
			nano7201v.getList();
		});

	    $("#nano7201vCertExcelDown").on('click', function() {
	    	if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

			var selected = '';
			$('#nano7201vCno option[value!="multiselect-all"]:selected').each(function() {
				selected += $(this).val() + ',';
			});
	
			location.replace("/_suCert/nano/N002/nano_7201j.php?caseBy=getExcel&pHak=" + encodeURIComponent($("#nano7201vHak option:selected").val()) + "&pUniq=" + encodeURIComponent(selected));
		});
	});
</script>
<div style="padding:0px 5px 0px 5px;">
	<div style="display:inline-block; border:1px solid #cccccc; background:#f8f8f6; padding:10px; border-radius:3px;">
		<select id="nano7201vHak" name="nano7201vHak" class="input_GrayLine">
			<option value="">:: �г� ::</option>
			<?= $searchSelectBoxListViewItem ?>
		</select>
		<select id="nano7201vGyl" name="nano7201vGyl" class="input_GrayLine" multiple="multiple"></select>
		<select id="nano7201vCno" name="nano7201vCno" class="input_GrayLine" multiple="multiple"></select>
		<a class="button white smallrounded" id="nano7201vCertMainSearch">��ȸ</a>
		
		<a class="button orange smallrounded" id="nano7201vCertExcelDown">�� �ڷ� ���� �����ޱ�</a>
	</div>

	<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7201vMainListTable" style="width:100%; margin-top:5px;">
		<thead>
		<tr>
			<th style="width:45px;">Ȯ��</th>
			<th style="width:45px;">�г�</th>
			<th style="width:125px;">�а�</th>
			<th style="width:45px;">�б�</th>
			<th style="width:45px;">��ȣ</th>
			<th style="width:90px;">�̸�</th>
			<th>���� �� �󼼳���</th>
			<th style="width:110px;">�򰡳�¥</th>
			<th style="width:90px;">�ۼ���</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>