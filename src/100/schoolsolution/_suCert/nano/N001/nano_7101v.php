<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_db.php");

	/**
	 * @ author : �̱ٸ�
	 * @ since : 2024.12.04.
	 * @ descript : �ڷ���� > ����� ���� ����
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

	var nano7101v = function() { }	// ó��

	nano7101v.getList = function() {
		if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

		var selected = '';
		$('#nano7101vCno option[value!="multiselect-all"]:selected').each(function() {
			selected += $(this).val() + ',';
		});
		
		var pHak = $("#nano7101vHak option:selected").val();
		
		// ��ü üũ, �б⺰ üũ ����
		$(".nano0701AllChk").prop('checked', false);
		$(".nano0701BraChk").prop('checked', false);
		
		var url = "";
		if ('<?= $_SERVER['REMOTE_ADDR'] ?>' == '61.85.146.3') url = "/_suCert/nano/N001/nano_7101j(2).php";
		else url = "/_suCert/nano/N001/nano_7101j.php";

		$.ajax({
			async : false, type : "POST", url : url, data : "caseBy=getList&pUniq=" + encodeURIComponent(selected) + "&pHak=" + encodeURIComponent(pHak), dataType : "html", 
			error : function(response, status, request) {	// ��� ���� �߻��� ó��
				alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
			},
			success: function (createHtml) {
				$('#nano7101vMainListTable tbody').empty().html(createHtml);

				$(".nano7101vViewFgYnClass").each(function() {
					if ($(this).attr("src").indexOf("nolines_plus") > -1) {	
						$(this).attr("src", "/images/treeimg/nolines_minus.gif");
						$("." + $(this).attr("valid.blank")).show();
					}
				});
				
				$(".nano7101vViewFgYnClass").on("click", function() {
					if ($(this).attr("src").indexOf("nolines_plus") > -1) {
						$(this).attr("src", "/images/treeimg/nolines_minus.gif");
						$("." + $(this).attr("valid.blank")).show();
					} else {
						$(this).attr("src", "/images/treeimg/nolines_plus.gif");
						$("." + $(this).attr("valid.blank")).hide();
					}				
				});
				
				// ���̺� �� checkbox�� �б� ���� üũ
				$('.nano7101RowClass').on('change', function() {
					var isChecked = $(this).is(':checked');
				
					// ���� tr �ȿ��� ���� td �������� ���� 4���� td �� üũ�ڽ��� üũ
				    var $td = $(this).closest('td');
				    var $tr = $td.closest('tr');
				    var $allTds = $tr.find('td');
				    var index = $allTds.index($td); // ���� td ��ġ
				
				    // ���� 4���� td���� .child üũ�ڽ� ã�Ƽ� üũ
				    $allTds.slice(index + 1, index + 5).find('input.nano7101RowChildClass').prop('checked', isChecked);
				});
				
			}
		});
	}

	$(document).ready(function() {
		nano7101v.getList();

		$('#nano7101vHak').multiselect({
			nonSelectedText : ': : &nbsp;�� ��&nbsp; : : &nbsp;&nbsp;&nbsp;',
			nSelectedText: ' �� �г� ���� &nbsp;&nbsp;&nbsp;',
			includeSelectAllOption : true,
			onChange: function(option, checked) {
				var param = '';
				$('#nano7101vHak option[value!="multiselect-all"]:selected').each(function() {
					param += $(this).val() + ',';
				});

				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getHakHakgwaClassList&builtPath=<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>&vHak=" + encodeURIComponent(param), dataType : "json",	// datatype �� Ÿ���� �߸� �����Ұ�� ���� ����~
					error : function(response, status, request) {	// ��� ���� �߻��� ó��
						alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
					},
					success: function (resObj) {
						$('#nano7101vGyl').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});

		$('#nano7101vGyl').multiselect({
			nonSelectedText : ': : &nbsp;�а� & �б�&nbsp; : : &nbsp;&nbsp;&nbsp;',
			nSelectedText: ' �� �а� & �б� ���� &nbsp;&nbsp;&nbsp;',
			includeSelectAllOption : true,
			onChange: function(option, checked) {
				var param1 = $("#nano7101vHak option:selected").val() == undefined? "" : $("#nano7101vHak option:selected").val();
				var param2 = '';
				$('#nano7101vGyl option[value!="multiselect-all"]:selected').each(function() {
					param2 += $(this).val() + ',';
				});

				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getJoinClnoList2&vGyl=" + encodeURIComponent(param2), dataType : "json",	// datatype �� Ÿ���� �߸� �����Ұ�� ���� ����~
					error : function(response, status, request) {	// ��� ���� �߻��� ó��
						alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
					},
					success: function (resObj) {
						$('#nano7101vCno').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});
		
		$('#nano7101vCno').multiselect({
			nonSelectedText : ': : &nbsp;[�б�-��ȣ] �̸�&nbsp; : : &nbsp;&nbsp;&nbsp;',
			nSelectedText: ' �� �л� ���� &nbsp;&nbsp;&nbsp;',
			numberDisplayed : 1
		});

		$("#nano7101vCertMainSearch").on("click", function() {
			if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

			var selected = '';
			$('#nano7101vCno option[value!="multiselect-all"]:selected').each(function() {
				selected += $(this).val() + ',';
			});

			$.ajax({
				async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getStdList&uniqNo=" + encodeURIComponent(selected), dataType : "html", 
				error : function(response, status, request) {	// ��� ���� �߻��� ó��
					alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
				},
				success: function (createHtml) {
					$('#nano7101vViewStudent').empty().html(createHtml);

				    nano7101v.getList();
				}
			});
		});

		$(document).off('click', '#afterSchPop').on('click', '#afterSchPop', function() {
			$.ajax({
				async : false, type : "POST", url : "/_suCert/nano/N001/nano_7101j.php", data : "caseBy=getAfterSchoolDialog", dataType : "html", 
				error : function(response, status, request) {	// ��� ���� �߻��� ó��
					alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
				},
				success: function (createHtml) {
					$("<div>" + createHtml + "</div>").dialog({ 
						title : ':: ����� ���� �ҷ����� ::', 
						closeOnEscape : true, width : $(window).width() * 0.6, height : $(window).height() * 0.9,
						autoOpen : true, modal : true, draggable : true, resizable : true, bgiframe : false, 
						close : function() {
							nano7101v.getList();
							$(this).remove();
						},
						open : function() {
	            			$(".isNumeric").on('keyup', function() {
	            				if ($(this).val() != "") {
	            					if ($.trim($(this).val()) != '-') {
	            						if (!$.isNumeric($(this).val())) {
	            							$(this).val('');
	            							$(this).focus();
	            						}
	            					}
	            				}
	            			}).on('blur', function() {
	            				if ($.trim($(this).val()) == '-') $(this).val('');
	            			});
	
	            			$(document).off('click', '#aftercheckClassAll').on('click', '#aftercheckClassAll', function(e) {
	            			    $(".aftercheckClass").prop('checked', $(this).is(':checked'));
	            			});
	            			
	            			$(document).off('click', '.aftercheckClass').on('click', '.aftercheckClass', function(e) {
	            			    let isEmptyCount = 0;
	            			    $('.aftercheckClass').each(function(){
	            			        if (!$(this).is(':checked')) isEmptyCount++;
	            			    });
	            			    $("#aftercheckClassAll").prop('checked', isEmptyCount > 0? false : true);
	            			});
						},
						buttons : {
							"�ʱ�ȭ" : function() {
								if(confirm("�ش� �����⵵�� ������ �������� ������� ��� �ڷḦ �ʱ�ȭ �Ͻðڽ��ϱ�?")) {
									$.post("/_suCert/nano/N001/nano_7101j.php","caseBy=doAfterSchoolReset",function(o){
										nano7101v.getList();
										$(".ui-dialog-titlebar-close").trigger('click');
									});
								}
							},						
							"�ҷ�����" : {
								text : "�ҷ�����",
								click : function() {
									let chkidxList = [];
									$(".aftercheckClass").each(function(){
										if($(this).is(":checked")) chkidxList.push($(this).val());
									});
									let idxjoin = chkidxList.join(",");
	
									if (idxjoin == "") {
										alert("[�˸�!] �ҷ��� ������Ʈ�� �ϳ� �̻� üũ�� �ּ���");
										return;
									}
	
									let scoringIdxList = '';
									$(".aftercheckClass2").each(function() {
									    let kk = $(this).attr('valid.key');
										if ($(this).val() != '') scoringIdxList += (kk + "^" + $(this).val() + ";");
									});
	
									let timeIdxList = '';
									$(".aftercheckClass3").each(function() {
									    let timeNameKey = $(this).attr('name');
									    let kk = $(this).attr('valid.key');
										if ($("input:radio[name='" + timeNameKey + "']:checked").val() == 'P') timeIdxList += (kk + ",");
									});

									/*console.log("/_suCert/nano/N001/nano_7101j.php?caseBy=doAfterSchoolSave&idxjoin=" + encodeURIComponent(chkidxList) + "&scoringKey=" + encodeURIComponent(scoringIdxList) + "&timeKey=" + encodeURIComponent(timeIdxList));*/
									$.post("/_suCert/nano/N001/nano_7101j.php", "caseBy=doAfterSchoolSave&idxjoin=" + encodeURIComponent(chkidxList) + "&scoringKey=" + encodeURIComponent(scoringIdxList) + "&timeKey=" + encodeURIComponent(timeIdxList), function(o) {
										alert(o);
										nano7101v.getList();
										$(".ui-dialog-titlebar-close").trigger('click');
									});
									
									
								}
							},
							"�ݱ�" : function() {
								$(".ui-dialog-titlebar-close").trigger('click');
							}
						}
					}).css({ 'width' : '100%' });
				}			
			});
		});
		
		// ����� �б⺰ ����
		$("#nano7101Save").on("click", function(){
			if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;
			
			var selected = '';
			$('#nano7101vCno option[value!="multiselect-all"]:selected').each(function() {
				selected += $(this).val() + ',';
			});
			
			if (selected == "" || typeof selected == 'undefined') {
				alert("[�˸�!] �Է��Ͻ� �л��� �������ּ���.");
				return;
			}
			
			var param = "";
			
			$(".nano7101totalCalss").each(function(){
				
				var isChk = ($(this).is(":checked") ? "Y" : "N");
				
				param += "&" + $(this).attr('id') + "=" + encodeURIComponent(isChk);
			});
			
			$.ajax({
				async : false, type : "POST", url : "/_suCert/nano/N001/nano_7101j(2).php", data : "caseBy=doSave" + param, dataType : "json", 
				error : function(response, status, request) {	// ��� ���� �߻��� ó��
					alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
				},
				success: function (resObj) {
					if (resObj.resData == "ok") {
						alert("[�˸�!] ���������� ó���Ǿ����ϴ�.");
						nano7101v.getList();
					} else {
						alert("[�˸�!] ó�� ���� ������ �߻��Ͽ����ϴ�.");
						return;
					}
				}
			});					
		});
		
		// �б⺰ üũ�� �ش� �б� ��� üũ
		$('.nano0701BraChk').on('change', function() {
			var isChecked = $(this).is(':checked');
			var isVal = $(this).val();
			$(".nano7101" + isVal + "Class").prop('checked', isChecked);
			
		});
		
		// ��ü üũ�� ���� üũ
		$(".nano0701AllChk").on('change', function() {
			var isChecked = $(this).is(':checked');
			
			$(".nano7101RowClass").prop('checked', isChecked);
			
			$('.nano7101RowClass').each(function () {
				
				var isChildChecked = $(this).is(':checked');
			
				// ���� tr �ȿ��� ���� td �������� ���� 4���� td �� üũ�ڽ��� üũ
			    var $td = $(this).closest('td');
			    var $tr = $td.closest('tr');
			    var $allTds = $tr.find('td');
			    var index = $allTds.index($td); // ���� td ��ġ
			
			    // ���� 4���� td���� .child üũ�ڽ� ã�Ƽ� üũ
			    $allTds.slice(index + 1, index + 5).find('input.nano7101RowChildClass').prop('checked', isChildChecked);
			});
		});
		

		
	});	//ready end
</script>
<div class="subBodyClass">
	<div style="display:inline-block; border:1px solid #cccccc; background:#f8f8f6; padding:10px; border-radius:3px;">
		<select id="nano7101vHak" name="nano7101vHak" class="input_GrayLine">
			<option value="">:: �г� ::</option>
			<?= $searchSelectBoxListViewItem ?>
		</select>
		<select id="nano7101vGyl" name="nano7101vGyl" class="input_GrayLine" multiple="multiple"></select>
		<select id="nano7101vCno" name="nano7101vCno" class="input_GrayLine" multiple="multiple"></select>
		<a class="button white smallrounded" id="nano7101vCertMainSearch">��ȸ</a>
	</div>

	<div style="width:100%;">
		<div>
			<div style="float:left;">
				<div style="padding-top:5px; padding-bottom:5px;">
					
				</div>
			</div>
			<div style="float:right;">
				<div style="padding-top:5px; padding-bottom:5px;text-align:right; clear:both;">
				    <!--<a class="button rosy smallrounded" id="afterSchPop">����� ������ ��������</a>-->
				    <a class="button rosy smallrounded" id="nano7101Save" style="width:90px;">����</a>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div>

		<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7101vInputItemTables" style="margin-top:0px">
			<thead>
			<tr>
				<th colspan="2">�б�-��ȣ-�̸�</th>
				<td colspan="3" style="text-align:left; padding:3px;" id="nano7101vViewStudent">��ȸ �� ����� �����(�л�)�� �����ϼ���.</td>
			</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<?php if ($_SERVER['REMOTE_ADDR'] == '61.85.146.3') { ?>
	<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7101vMainListTable" style="width:100%; margin-top:5px;">
		<thead>
		<tr>
			<th style="width:45px;">Ȯ��</th>
			<th style="width:45px;">�г�</th>
			<th style="width:125px;">�а�</th>
			<th style="width:45px;">�б�</th>
			<th style="width:45px;">��ȣ</th>
			<th style="width:90px;">�̸�</th>
			<th><input type="checkbox" class="nano0701AllChk" style="width:18px; height:18px;" value=""/></th>
			<th>�г�</th>
			<th>1�б� <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="1"/></th>			
			<th>2�б� <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="2"/></th>			
			<th>3�б� <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="3"/></th>			
			<th>4�б� <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="4"/></th>			
			<th>�ݿ�����</th>			
			<th style="width:110px;">�򰡳�¥</th>
			<th style="width:90px;">�ۼ���</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
	
	<?php } else { ?>
	<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7101vMainListTable" style="width:100%; margin-top:5px;">
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
	<?php } ?>
</div>