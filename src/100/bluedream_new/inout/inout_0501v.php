<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_global.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_db.php";
	
	if ($sess_Iden == '') {	// �α��� �Ǿ� ���� �ʴٸ�
		echo "
			<script type=\"text/javascript\">
				alert('[�˸�!] �α��� �� �̿밡�� �մϴ�.');
				location.href = '/logout.php';
			</script>
		";
		exit;
	}
	
	/*******************************
	*	�ڷ���� > ��Ź�� ���Ƹ�Ȱ��
	*	@ author : �̱ٸ�
	*	@ since : 2020.12.21.
	*******************************/

	$db = doConnect();
	
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_auth.php";	// �ݵ�� $db = doConnect(); �ؿ� �־�� �մϴ�.
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
<script type="text/javascript">
	var io0501vObj = function() { }

	io0501vObj.getDateToHakgi = function(YmdTarget, targetIden) {
		$.ajax({
			async : false, type : "POST", url : "/basic/basic_0301j.php", data : "caseBy=getDateToHakgi&pYmd=" + encodeURIComponent($("#" + YmdTarget).val()), dataType : "json", 
			error : function(response, status, request) {	// ��� ���� �߻��� ó��
				alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
			},
			success: function (resObj) {
				if (resObj.resHakgi == '') {
					$("#" + YmdTarget).val("");
					$("#" + targetIden).empty().html("<option value=\"\" selected>:: �б� ::</option>");
					alert("[�˸�!]\n" + resObj.resHakgi1 + ",\n" + resObj.resHakgi2);
					return;
				} else {
					$("#" + targetIden).empty().html("<option value=\"" + resObj.resHakgi + "\" selected>" + resObj.resHakgi + "�б�</option>");
				}
			}
		});
	}

	io0501vObj.getList = function() {
		if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

		var selected = '';
		$('#inout0501vCno option[value!="multiselect-all"]:selected').each(function() {
			selected += $(this).val() + ',';
		});

		$.ajax({
			async : false, type : "POST", url : "/inout/inout_0501j.php", data : "caseBy=getList&uniqNo=" + encodeURIComponent(selected) + "&pHakgi=" + encodeURIComponent($("input:radio[name='inout0501vHakgi']:checked").val()), dataType : "html", 
			error : function(response, status, request) {	// ��� ���� �߻��� ó��
				alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
			},
			success: function (createHtml) {
				$('#inout0501vMainTable tbody').empty().html(createHtml);

				$("#inout0501vAllChk").prop('checked', false);

				$(".inout0501vExtendClass").unbind('click').on("click", function() {
					if ($(this).attr("src").indexOf("nolines_plus") > -1) {
						$(this).attr("src", "/images/nolines_minus.gif");
						$("." + $(this).attr("valid.blank")).show();
					} else {
						$(this).attr("src", "/images/nolines_plus.gif");
						$("." + $(this).attr("valid.blank")).hide();
					}				
				});

				$(".input0501vRemoveClasses").unbind('click').on("click", function() {
					if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;
					
					var iop = $(this).attr('valid.uKeys') + ':' + $(this).attr('valid.hakgi');
					if (!endingClose.get(iop, '01')) {
						return;
					}

					if (confirm("[�˸�!] ���� ����� �����ʹ� ������ ���� �˴ϴ�.\n\n������ �����Ͻðڽ��ϱ�?")) {
						var pIdx = $(this).attr("valid.idx");
						$.post('/inout/inout_0501j.php', 'caseBy=removeItem&pRemoveIdxs=' + pIdx, function(p) {
							io0501vObj.getList();
						});
					}
				});

				$("#inout0501vAllChk").unbind('click').on('click', function() {
					$(".inout0501vChoiceItemClass").prop('checked', $(this).is(":checked")? true : false);
				});

				$(".inout0501vChoiceItemClass").unbind('click').on('click', function() {
					var isEmptyCnt = 0;
					$(".inout0501vChoiceItemClass").each(function() {
						if (!$(this).is(":checked")) isEmptyCnt++;
					});

					$("#inout0501vAllChk").prop('checked', isEmptyCnt > 0? false : true);
				});

				$("#inout0501vBatchRemove").unbind('click').on('click', function() {
					if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) {
						return;
					}

					var isCnt = 0;
					var transValue = "";
					var ___transValue = "";
					$(".inout0501vChoiceItemClass").each(function() {
						if ($(this).is(":checked")) {
							isCnt++;
							transValue += $(this).val() + ",";
							
							___transValue += $(this).attr('valid.uKeys') + ":" + $(this).attr('valid.hakgi') + ",";
						}
					});
					
					if (!endingClose.get(___transValue, '01')) {
						return;
					}

					if (isCnt > 0) {
						if (confirm("[�˸�!] ���� ����� �����ʹ� ������ ���� �˴ϴ�.\n\n������ �����Ͻðڽ��ϱ�?")) {
							$.post('/inout/inout_0501j.php', 'caseBy=removeItem&pRemoveIdxs=' + transValue, function(p) {
								io0501vObj.getList();
							});
						}
					} else {
						alert("[�˸�!] �ϰ����� �� �׸��� �����ϼ���.");
						return;
					}
				});

				$(".input0501vModifyClasses").unbind('click').on('click', function() {
					if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

					var iop = $(this).attr('valid.uKeys') + ':' + $(this).attr('valid.hakgi');
					if (!endingClose.get(iop, '01')) {
						return;
					}

					var pIdx = $(this).attr('valid.idx');
					var pType = $(this).attr('valid.type');

					$.post('/inout/inout_0501j.php', 'caseBy=getDialog&pDialogIdx=' + pIdx, function(dialogHtml) {
						$("<div id=\"inout0501vDialogView\">" + dialogHtml + "</div>").dialog({
							open : function() {
								$(".isNumeric").on('keyup', function() {	// ���ڸ�
									if ($(this).val() != "") {
										if (!$.isNumeric($(this).val())) {
											$(this).val('');
											$(this).focus();
										}
									}
								});

		$('#inout0501v2DialogItem02').keyup(function (e) {
			var maxLength = parseInt($(this).attr('stringMaxLength'));
			$('#counterOfDialogIO0501v').html(addComma($(this).val().length) + ' / ' + addComma(maxLength));

			if ($(this).val().length > maxLength) {
				alert("[�˸�!] �Է°��� ���ڼ��� �ʰ��Ͽ� �ʰ��� ���ڸ� �����մϴ�.\n( " + (addComma($(this).val().length) + ' / ' + addComma(maxLength)) + " )");
				$(this).val($(this).val().substring(0, maxLength));
				
				$('#counterOfDialogIO0501v').html(addComma($(this).val().length) + ' / ' + addComma(maxLength));
				return;
			}
		});
		$('#inout0501v2DialogItem02').keyup();

								var today = new Date();
							    $("#inout0501vDialogItem01").datepicker({	//  readonly style='width:80px; margin-right:3px;'
							        defaultDate: new Date(today),
							        showOn: "both", // focus, button, both
							        showAnim: "drop", // blind, clip, drop, explode, fold, puff, slide, scale, size, pulsate, bounce, highlight, shake, transfer
							        showOptions: {direction: 'horizontal'},
							        duration: 200
							    });
							    
								$("#inout0501vDialogItem05u").unbind('click').on('click', function() {
									var choice = $(this).val();
						
									if (choice != null) {
    									var uniqueNames = [];
    									$("#inout0501vDialogItem05").find("option").each(function() {
    										if ($(this).val() != '') uniqueNames[uniqueNames.length] = $(this).val();
    									});
    						
    									var result = true;
    									for (var c = 0; c < uniqueNames.length; c++) {
    										if (choice == uniqueNames[c]) {
    											result = false;
    											break;
    										}
    									}
    						
    									if (result) uniqueNames.push(choice);
    									uniqueNames.sort();
    						
    									$("#inout0501vDialogItem05").empty();
    									for (var s = 0; s < uniqueNames.length; s++) {
    										$("#inout0501vDialogItem05").append("<option value=\"" + uniqueNames[s] + "\">" + uniqueNames[s] + "����</option>");
    									}
    						
    									$("#inout0501vDialogItem02").val($("#inout0501vDialogItem05 option").length == 0? "" : $("#inout0501vDialogItem05 option").length);
									}
								});
						
								$("#inout0501vDialogItem05").unbind('click').on('click', function() {
									$("#inout0501vDialogItem05 option:eq(" + $("#inout0501vDialogItem05 option").index($("#inout0501vDialogItem05 option:selected")) + ")").remove();
						
									$("#inout0501vDialogItem02").val($("#inout0501vDialogItem05 option").length == 0? "" : $("#inout0501vDialogItem05 option").length);
								});
							},
							closeOnEscape : true, autoOpen : true, width : 600, height : 'auto', modal : true, resizable : false, draggable : true, bgiframe : true, title : ":: ��Ź�� ���Ƹ�Ȱ�� ����",
							buttons : {
								"����" : function() {
									if (pType == '1') {
										$.ajax({
											async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=getHoliday&pDate=" + encodeURIComponent($("#inout0501vDialogItem01").val()), dataType : "json", 
											error : function(response, status, request) {	// ��� ���� �߻��� ó��
												alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
											},
											success: function (rJson) {
												if (rJson.resData == 'Y') {
										
													if ($("#inout0501vDialogItem01").val() == '') {
														alert("[�˸�!] " + $("#inout0501vDialogItem01").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
														$("#inout0501vDialogItem01").focus();
														return;
													}
										
													if ($("#inout0501vDialogItem02").val() == '') {
														alert("[�˸�!] " + $("#inout0501vDialogItem02").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
														$("#inout0501vDialogItem02").focus();
														return;
													}
										
													if ($("#inout0501vDialogItem03").val() == '') {
														alert("[�˸�!] " + $("#inout0501vDialogItem03").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
														$("#inout0501vDialogItem03").focus();
														return;
													}
										
													if ($("#inout0501vDialogItem04").val() == '') {
														alert("[�˸�!] " + $("#inout0501vDialogItem04").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
														$("#inout0501vDialogItem04").focus();
														return;
													}
	
													var applyTime = '';
													$("#inout0501vDialogItem05").find("option").each(function() {
														$(this).prop('selected', true);
														
														if ($(this).val() != '') applyTime += $(this).val() + ',';
													});
										
													var kDate = $("#inout0501vDialogItem01").val();
										
													var tmp = [];
													$("#inout0501vDialogItem05").find('option').each(function() {
														tmp[tmp.length] = parseInt($(this).val());
													});
										
													var vMax = Math.max.apply(null, tmp);	// �̼����� �ִ밪
													var vMin = Math.min.apply(null, tmp);	// �̼����� �ּҰ�

													$.ajax({
														async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=get&proCs=mod&pCode=" + pIdx + "&pUniq=" + encodeURIComponent(iop) + "&pDate=" + encodeURIComponent($("#inout0501vDialogItem01").val()) + "&applyTime=" + encodeURIComponent(applyTime), dataType : "json", 
														error : function(response, status, request) {	// ��� ���� �߻��� ó��
															alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
														},
														success: function (resObj) {
															var errorText = '';
															var errorCnt = 1;
								for (var u in resObj) {
									if (resObj[u]['resCode'] == '0') {
										
									} else if (resObj[u]['resCode'] == '1') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + kDate + ', �Ϻ� ���� ó�� �ȵ�</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>���� ����!!</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '-1') {
										for (var w = 0; w < resObj[u]['resTime'].length; w++) {
											for (var z in resObj[u]['resTime'][w]) {
												if (resObj[u]['resTime'][w][z] == '���(����)' || resObj[u]['resTime'][w][z] == '���(����)' || resObj[u]['resTime'][w][z] == '���(������)' || resObj[u]['resTime'][w][z] == '���(��Ÿ)' || resObj[u]['resTime'][w][z] == '���(����)') {
													// alert('>>' + parseInt(z) + ', vMin : ' + vMin + ', vMax : ' + vMax);
													// if (parseInt(z) ?= vMin) {
													if (parseInt(z) == vMax || parseInt(z) == vMin) {	// @author : �̱ٸ�, @modify : 2018.07.06., @descript : 6���� ���(����)�� ���, 5���� �Է��Ϸ� �ص� 6���ö����� �Է��� �ȵȴٰ� ��. �׷��� ����
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "����, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else if (resObj[u]['resTime'][w][z] == '����(����)' || resObj[u]['resTime'][w][z] == '����(����)' || resObj[u]['resTime'][w][z] == '����(������)' || resObj[u]['resTime'][w][z] == '����(��Ÿ)' || resObj[u]['resTime'][w][z] == '����(����)') {
													if (parseInt(z) >= vMin) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "����, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else {
													if (parseInt(z) <= vMax) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "����, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												}
											}
										}
									} else if (resObj[u]['resCode'] == '-2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>�̹� ' + resObj[u]['resTime'] + ' ���� ó����!</b> )<br>';
										errorCnt++;
									}
								}
										
															if (errorText == '') {
																var frm = $("#inout0501vDialogForm");
																frm.attr("method", "post");
																frm.attr("action", "/inout/inout_0501j.php?caseBy=doUpdate&pUniqIdx=" + pIdx);
																frm.attr("target", "io0501vIframeTarget");
																frm.submit();
																
																var mmsmsm = $("#io0501vIframeTarget").unbind().load(function () {
																	iframeContents = $(this).contents().find('body').html();
																	if ($.trim(iframeContents) == "true") {
																    	io0501vObj.getList();	// ����Ʈ �ҷ����� �Լ�.
																    	$("#inout0501vDialogView").remove();
																	} else {
																		alert("[�˸�!] ����� ������ �߻��Ͽ����ϴ�.\n����� �ٽ� ���ٶ��ϴ�.");
																	}
										
																	$(this).unbind('load');
																	return false;
																});
															} else {
																$("<div id=\"inout0501vDialogViews\">" + errorText + "</div>").dialog({
																	closeOnEscape : true, autoOpen : true, width : 'auto', height : 'auto', modal : true, resizable : false, draggable : true, bgiframe : true, title : ":: ó�� ���",
																	buttons : {
																		"�ݱ�" : function() {
																			$("#inout0501vDialogViews").remove();
																		}
																	}
																});
															}
														}
													});
												} else {
													alert("[�ԷºҰ�] " + $("#inout0501vDialogItem01").val() + " [ " + ($.trim(rJson.resText) == ''? '-' : $.trim(rJson.resText)) + " ] �������� �ƴմϴ�.\n\n\"�ڷ���� > ������, ����(������) ����\"���� ����!!");
													return;
												}
											}
										});

									} else if (pType == '2') {
										if ($("#inout0501v2DialogItem01").val() == '') {
											alert("[�˸�!] " + $("#inout0501v2DialogItem01").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
											$("#inout0501v2DialogItem01").focus();
											return;
										}
							
										/*if ($("#inout0501v2DialogItem02").val() == '') {	// @author : �̱ٸ�, @modify : 2019.07.12., @descript : ����� ���û������� üũ ���, �迵�� ������
											alert("[�˸�!] " + $("#inout0501v2DialogItem02").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
											$("#inout0501v2DialogItem02").focus();
											return;
										}*/
										
										var frm = $("#inout0501vDialogForm");
										frm.attr("method", "post");
										frm.attr("action", "/inout/inout_0501j.php?caseBy=doUpdate2&pUniqIdx=" + pIdx);
										frm.attr("target", "io0501vIframeTarget");
										frm.submit();
										
										var mmsmsm = $("#io0501vIframeTarget").unbind().load(function () {
											iframeContents = $(this).contents().find('body').html();
											if ($.trim(iframeContents) == "true") {
										    	io0501vObj.getList();	// ����Ʈ �ҷ����� �Լ�.
										    	$("#inout0501vDialogView").remove();
											} else {
												alert("[�˸�!] ����� ������ �߻��Ͽ����ϴ�.\n����� �ٽ� ���ٶ��ϴ�.");
											}
							
											$(this).unbind('load');
											return false;
										});
									}
								}, 
								"�ݱ�" : function() {
									$(this).remove();
								}
							}
						});

						// autosize(document.querySelectorAll('textarea'));
					});
				});
			}
		});
		
		$(".inout0501vExtendClass").trigger('click');
	}

	$(document).ready(function() {
		$(".isNumeric").on('keyup', function() {	// ���ڸ�
			if ($(this).val() != "") {
				if (!$.isNumeric($(this).val())) {
					$(this).val('');
					$(this).focus();
				}
			}
		});

		// autosize(document.querySelectorAll('textarea'));

		$.datepicker.setDefaults({
		    dateFormat: 'yy-mm-dd',
		    buttonImageOnly: true,
		    buttonText: "�޷�",
		    buttonImage: "/images/calendar.gif",
		    dayNamesMin: ['��','��', 'ȭ', '��', '��', '��', '��'], 
			monthNames : ['1��','2��','3��','4��','5��','6��','7��','8��','9��','10��','11��','12��']
		});

		var today = new Date();
	    $("#inout0501vItem01").datepicker({	//  readonly style='width:80px; margin-right:3px;'
	        defaultDate: new Date(today),
	        showOn: "both", // focus, button, both
	        showAnim: "drop", // blind, clip, drop, explode, fold, puff, slide, scale, size, pulsate, bounce, highlight, shake, transfer
	        showOptions: {direction: 'horizontal'},
	        duration: 200,
	        onSelect : function() {
	        	io0501vObj.getDateToHakgi("inout0501vItem01", "inout0501vItem00");
	        }
	    });

		$.ajax({
			async : false, type : "POST", url : "/inout/inout_0501j.php", data : "caseBy=getClub", dataType : "html", 
			error : function(response, status, request) {	// ��� ���� �߻��� ó��
				alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
			},
			success: function (resObj) {
				
				// resObj.resData.unshift({ "label" : ":: &nbsp;�а� & �б�&nbsp; :: &nbsp;&nbsp;", "value" : "" });
				
				resObj = "<option value=\"\">���Ƹ� �μ�</option>" + resObj;
				
				$("#inout0501vCls2").empty().html(resObj);
			}
		});

		$('#inout0501vCls2').multiselect({
			nonSelectedText : ':: &nbsp;���Ƹ� �μ�&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' �� ���Ƹ� �μ� ���� &nbsp;&nbsp;',
			onChange : function(option, checked) {
				var param2 = '';
				$('#inout0501vCls2 option[value!="multiselect-all"]:selected').each(function() {
					param2 += $(this).val() + '`';
				});

				$("#inout0501v2Item01, #inout0501vItem04").val($('#inout0501vCls2 option:selected').val());
				
				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getJoinClnoList3&vClub=" + encodeURIComponent(param2), dataType : "json", 
					error : function(response, status, request) {	// ��� ���� �߻��� ó��
						alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
					},
					success: function (resObj) {
						$('#inout0501vCno').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});
		
		$('#inout0501vCno').multiselect({
			nonSelectedText : ':: &nbsp;�� �� (��ȣ)&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' �� �л� ���� &nbsp;&nbsp;'
		});

		$("#inout0501vSave").unbind('click').on('click', function() {
			if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

			var pPersonKey = '';
			$('#inout0501vCno option[value!="multiselect-all"]:selected').each(function() {
				pPersonKey += $(this).val() + ':' + $("#inout0501vItem00 option:selected").val() + ',';
			});

			if (!endingClose.get(pPersonKey, '01')) {
				return;
			}
			
			$.ajax({
				async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=getHoliday&pDate=" + encodeURIComponent($("#inout0501vItem01").val()), dataType : "json", 
				error : function(response, status, request) {	// ��� ���� �߻��� ó��
					alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
				},
				success: function (rJson) {
					if (rJson.resData == 'Y') {

						var selected = '';
						$('#inout0501vCno option[value!="multiselect-all"]:selected').each(function() {
							selected += $(this).val() + ',';
						});
			
						if (selected == "") {
							alert("[�˸�!] ��ȸ �� ����� �����(�л�)�� �����ϼ���.");
							return;
						} else $("#inout0501vTarget").val(selected);
			
						if ($("#inout0501vItem01").val() == '') {
							alert("[�˸�!] " + $("#inout0501vItem01").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
							$("#inout0501vItem01").focus();
							return;
						}
			
						if ($("#inout0501vItem02").val() == '') {
							alert("[�˸�!] " + $("#inout0501vItem02").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
							$("#inout0501vItem02").focus();
							return;
						}
			
						if ($("#inout0501vItem03").val() == '') {
							alert("[�˸�!] " + $("#inout0501vItem03").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
							$("#inout0501vItem03").focus();
							return;
						}
			
						if ($("#inout0501vItem04").val() == '') {
							alert("[�˸�!] " + $("#inout0501vItem04").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
							$("#inout0501vItem04").focus();
							return;
						}

						var applyTime = '';
						$("#inout0501vItem05").find("option").each(function() {
							$(this).prop('selected', true);
							
							if ($(this).val() != '') applyTime += $(this).val() + ',';
						});
			
						var kDate = $("#inout0501vItem01").val();
			
						var tmp = [];
						$("#inout0501vItem05").find('option').each(function() {
							tmp[tmp.length] = parseInt($(this).val());
						});
			
						var vMax = Math.max.apply(null, tmp);	// �̼����� �ִ밪
						var vMin = Math.min.apply(null, tmp);	// �̼����� �ּҰ�

						$.ajax({
							async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=get&pUniq=" + encodeURIComponent(selected) + "&pDate=" + encodeURIComponent($("#inout0501vItem01").val()) + "&applyTime=" + encodeURIComponent(applyTime), dataType : "json", 
							error : function(response, status, request) {	// ��� ���� �߻��� ó��
								alert("[�˸�!] ������ ��� ���� ������ �߻��Ͽ����ϴ�.\n�߻��� ���� ������ �Ʒ��� �����ϴ�.\n\n1. ��ð� �α��� ��ġ�� ���� ���� ����!\n2. ������ ���� �� ����� ���� ������ ����!\n3. �������� ��� ����!\n4. ��Ÿ ���� ����!\n\n- �α׾ƿ� �� �� �α��� �غ��ʽÿ�.");
							},
							success: function (resObj) {
								var errorText = '';
								var errorCnt = 1;
								for (var u in resObj) {
									if (resObj[u]['resCode'] == '0') {
										
									} else if (resObj[u]['resCode'] == '1') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + kDate + ', �Ϻ� ���� ó�� �ȵ�</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>���� ����!!</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '-1') {
										for (var w = 0; w < resObj[u]['resTime'].length; w++) {
											for (var z in resObj[u]['resTime'][w]) {
												if (resObj[u]['resTime'][w][z] == '���(����)' || resObj[u]['resTime'][w][z] == '���(����)' || resObj[u]['resTime'][w][z] == '���(������)' || resObj[u]['resTime'][w][z] == '���(��Ÿ)' || resObj[u]['resTime'][w][z] == '���(����)') {
													// alert('>>' + parseInt(z) + ', vMin : ' + vMin + ', vMax : ' + vMax);
													// if (parseInt(z) ?= vMin) {
													if (parseInt(z) == vMax || parseInt(z) == vMin) {	// @author : �̱ٸ�, @modify : 2018.07.06., @descript : 6���� ���(����)�� ���, 5���� �Է��Ϸ� �ص� 6���ö����� �Է��� �ȵȴٰ� ��. �׷��� ����
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "����, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else if (resObj[u]['resTime'][w][z] == '����(����)' || resObj[u]['resTime'][w][z] == '����(����)' || resObj[u]['resTime'][w][z] == '����(������)' || resObj[u]['resTime'][w][z] == '����(��Ÿ)' || resObj[u]['resTime'][w][z] == '����(����)') {
													if (parseInt(z) >= vMin) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "����, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else {
													if (parseInt(z) <= vMax) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "����, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												}
											}
										}
									} else if (resObj[u]['resCode'] == '-2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>�̹� ' + resObj[u]['resTime'] + ' ���� ó����!</b> )<br>';
										errorCnt++;
									}
								}
			
								if (errorText == '') {
									var frm = $("#inout0501vMainForm");
									frm.attr("method", "post");
									frm.attr("action", "/inout/inout_0501j.php?caseBy=doSave");
									frm.attr("target", "io0501vIframeTarget");
									frm.submit();
						
									var mmsmsm = $("#io0501vIframeTarget").unbind().load(function () {
										iframeContents = $(this).contents().find('body').html();
										if ($.trim(iframeContents) == "true") {
									    	io0501vObj.getList();	// ����Ʈ �ҷ����� �Լ�.

									    	$(".inputInitClasses").val('');

									    	$("#inout0501vItem05").empty();
									    	$("#inout0501vItem06").prop('checked', false);
										} else {
											alert("[�˸�!] ����� ������ �߻��Ͽ����ϴ�.\n����� �ٽ� ���ٶ��ϴ�.");
										}
						
										$(this).unbind('load');
										return false;
									});
								} else {
									$("<div id=\"inout0501vDialogView\">" + errorText + "</div>").dialog({
										closeOnEscape : true, autoOpen : true, width : 'auto', height : 'auto', modal : true, resizable : false, draggable : true, bgiframe : true, title : ":: ó�� ���",
										buttons : {
											"�ݱ�" : function() {
												$("#inout0501vDialogView").remove();
											}
										}
									});
								}
							}
						});
					} else {
						alert("[�ԷºҰ�] " + $("#inout0501vItem01").val() + " [ " + ($.trim(rJson.resText) == ''? '-' : $.trim(rJson.resText)) + " ] �������� �ƴմϴ�.\n\n\"�ڷ���� > ������, ����(������) ����\"���� ����!!");
						return;
					}
				}
			});
		});
		
		$("#inout0501vSave2").unbind('click').on('click', function() {
			if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

			var selected = '';
			var ___selected = '';
			$('#inout0501vCno option[value!="multiselect-all"]:selected').each(function() {
				selected += $(this).val() + ',';
				___selected += $(this).val() + ':' + $("#inout0501v2Item00 option:selected").val()  + ',';
			});

			if (!endingClose.get(___selected, '01')) {
				return;
			}

			if (selected == "") {
				alert("[�˸�!] ��ȸ �� ����� �����(�л�)�� �����ϼ���.");
				return;
			} else $("#inout0501vTarget2").val(selected);

			if ($("#inout0501v2Item01").val() == '') {
				alert("[�˸�!] " + $("#inout0501v2Item01").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
				$("#inout0501v2Item01").focus();
				return;
			}

			/*if ($("#inout0501v2Item02").val() == '') {	// @author : �̱ٸ�, @modify : 2019.07.12., @descript : ����� ���û������� üũ ���, �迵�� ������
				alert("[�˸�!] " + $("#inout0501v2Item02").attr('valid.label') + " �׸��� �ʼ��Է� �׸��Դϴ�.");
				$("#inout0501v2Item02").focus();
				return;
			}*/

			var frm = $("#inout0501vMainForm2");
			frm.attr("method", "post");
			frm.attr("action", "/inout/inout_0501j.php?caseBy=doSave2");
			frm.attr("target", "io0501vIframeTarget");
			frm.submit();

			var mmsmsm = $("#io0501vIframeTarget").unbind().load(function () {
				iframeContents = $(this).contents().find('body').html();
				if ($.trim(iframeContents) == "true") {
			    	io0501vObj.getList();	// ����Ʈ �ҷ����� �Լ�.
			    	
			    	$(".inputInitClasses2").val('');
				} else {
					alert("[�˸�!] ����� ������ �߻��Ͽ����ϴ�.\n����� �ٽ� ���ٶ��ϴ�.");
				}

				$(this).unbind('load');
				return false;
			});
		});

		// io0501vObj.getList();	// ����Ʈ �ҷ����� �Լ�.
		$("#inout0501vMainSearch").unbind('click').on('click', function() {	// ��ȸ ��ư Ŭ����
			io0501vObj.getList();
		});
		
		$("#inout0501vBatchOpenClose").unbind('click').on('click', function() {
			if ($(this).text() == '��δ���') $(this).text('��ο���');
			else $(this).text('��δ���');
			$(".inout0501vExtendClass").trigger('click');
		});

		$("#inout0501vItem05u").unbind('click').on('click', function() {
			var choice = $(this).val();

			if (choice != null) {
    			var uniqueNames = [];
    			$("#inout0501vItem05").find("option").each(function() {
    				if ($(this).val() != '') uniqueNames[uniqueNames.length] = $(this).val();
    			});
    
    			var result = true;
    			for (var c = 0; c < uniqueNames.length; c++) {
    				if (choice == uniqueNames[c]) {
    					result = false;
    					break;
    				}
    			}
    
    			if (result) uniqueNames.push(choice);
    			uniqueNames.sort();
    
    			$("#inout0501vItem05").empty();
    			for (var s = 0; s < uniqueNames.length; s++) {
    				$("#inout0501vItem05").append("<option value=\"" + uniqueNames[s] + "\">" + uniqueNames[s] + "����</option>");
    			}
    
    			$("#inout0501vItem02").val($("#inout0501vItem05 option").length == 0? "" : $("#inout0501vItem05 option").length);
			}
		});

		$("#inout0501vItem05").unbind('click').on('click', function() {
			$("#inout0501vItem05 option:eq(" + $("#inout0501vItem05 option").index($("#inout0501vItem05 option:selected")) + ")").remove();

			$("#inout0501vItem02").val($("#inout0501vItem05 option").length == 0? "" : $("#inout0501vItem05 option").length);
		});
		
		$('#inout0501v2Item02').keyup(function (e) {
			var maxLength = parseInt($(this).attr('stringMaxLength'));
			$('#counterOfIO0501v').html(addComma($(this).val().length) + ' / ' + addComma(maxLength));

			if ($(this).val().length > maxLength) {
				alert("[�˸�!] �Է°��� ���ڼ��� �ʰ��Ͽ� �ʰ��� ���ڸ� �����մϴ�.\n( " + (addComma($(this).val().length) + ' / ' + addComma(maxLength)) + " )");
				$(this).val($(this).val().substring(0, maxLength));
				
				$('#counterOfIO0501v').html(addComma($(this).val().length) + ' / ' + addComma(maxLength));
				return;
			}
		});

		$('#inout0501v2Item02').keyup();
	});
</script>
</head>
<body>
	<div style="font-size:13px; font-weight:bold; margin-bottom:5px; padding-bottom:4px; border-bottom:1px dotted #a3a3a3;"><i class="icon-th-large"></i> ��Ź�� ���Ƹ�Ȱ��</div>

	<div style="display:inline-block; border:1px solid #c3c3c3; padding:4px 8px; border-radius:5px; background-color:#f3f3f3;">
		<select id="inout0501vCls2" name="inout0501vCls2" class="input_GrayLine"></select>
		<select id="inout0501vCno" name="inout0501vCno" class="input_GrayLine" multiple="multiple"></select>

		&nbsp;
		<input type="radio" name="inout0501vHakgi" id="inout0501vHakgi1" checked value="1"> <label for="inout0501vHakgi1">1�б�</label>
		&nbsp;
		<input type="radio" name="inout0501vHakgi" id="inout0501vHakgi2" value="2"> <label for="inout0501vHakgi2">2�б�</label>

		&nbsp;
		<a class="button gray smallrounded" id="inout0501vMainSearch">��ȸ</a>
	</div>

	<form name="inout0501vMainForm" id="inout0501vMainForm" method="POST">
		<input type="hidden" name="inout0501vTarget" id="inout0501vTarget">

		<div style="margin-top:8px; margin-bottom:4px;"><i class="icon-th"></i> �������</div>
		<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types">
			<tr>
				<th style="width:120px;">�б�</th>
				<th style="width:170px;">�̼��ð� (���� Ŭ��!)</th>
				<th>Ȱ������</th>
				<th style="width:120px;">�μ���</th>
				<th style="width:60px;">���</th>
			</tr>
			<tr>
				<td valign="top">
					<select id="inout0501vItem00" name="inout0501vItem00" class="input_GrayLine" style="width:100%;">
						<option value="">:: �б� ::</option>
					</select>
				</td>
				<td rowspan="3" valign="top">
					<select id="inout0501vItem05u" name="inout0501vItem05u" class="input_GrayLine inputInitClasses" multiple style="height:65px !important; width:60px;">
<?php
	for ($i = 1; $i <= 7; $i++) {
		echo "<option value=\"" . $i . "\" style=\"font-size:1em;\">" . $i . "����</option>";
	}
?>
					</select>
					��
					<select id="inout0501vItem05" name="inout0501vItem05[]" class="input_GrayLine inputInitClasses" multiple style="height:65px !important; width:60px;"></select>
					<div style="margin-top:1px;">
						<input type="text" id="inout0501vItem02" name="inout0501vItem02" valid.label="�̼��ð�" readonly class="input_GrayLineCenter isNumeric inputInitClasses" style="background-color:#e3e3e3; width:95%;">
					</div>
				</td>
				<td rowspan="3" valign="top"><textarea id="inout0501vItem03" name="inout0501vItem03" valid.label="Ȱ������" placeholder="Ȱ������" class="input_GrayLine inputInitClasses" style="height:96px; width:100%;"></textarea></td>
				<!-- @author : ����ȣ @since : 2022-06-22 @descript : inputInitClasses Ŭ���� ���� ����� ���Ƹ��� �ʱ�ȭ �ȵǰ� �س��� -->
				<td valign="top"><input type="text" id="inout0501vItem04" name="inout0501vItem04" valid.label="�μ���" placeholder="�μ���" readonly class="input_GrayLineCenter" style="width:100%; background-color:#f3f3f3;"></td>
				<td rowspan="3" valign="top">
					<a class="button smallrounded orange" id="inout0501vSave"><div style="height:26px;">&nbsp;</div>������<br/>����<div style="height:26px;">&nbsp;</div></a>
				</td>
			</tr>
			<tr>
				<th>����</th>
				<th>���翬��</th>
			</tr>
			<tr>
				<td valign="top"><input type="text" id="inout0501vItem01" name="inout0501vItem01" valid.label="����" placeholder="����" class="input_GrayLineCenter inputInitClasses" readonly style='background-color:#e3e3e3; width:85px; margin-right:3px;'></td>
				<td valign="top"><input type="checkbox" id="inout0501vItem06" name="inout0501vItem06" style="width:20px; height:20px;" value="Y"></td>
			</tr>
		</table>
	</form>

	<form name="inout0501vMainForm2" id="inout0501vMainForm2" method="POST">
		<input type="hidden" name="inout0501vTarget2" id="inout0501vTarget2">

		<div style="margin-top:8px; margin-bottom:4px;"><i class="icon-th"></i> �б���Ȱ��Ϻ� �ݿ����</div>

		<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types">
			<tr>
				<th style="width:75px">�б�</th>
				<th style="width:120px;">�μ���</th>
				<th>Ư����� (<span id="counterOfIO0501v">###</span>�� �̳�)</th>
				<th style="width:60px;">���</th>
			</tr>
			<tr>
				<td valign="top">
					<select id="inout0501v2Item00" name="inout0501v2Item00" class="input_GrayLine" style="width:100%;">
						<option value="1">1�б�</option>
						<option value="2">2�б�</option>
					</select>
				</td>
				<td valign="top"><input type="text" id="inout0501v2Item01" name="inout0501v2Item01" valid.label="�μ���" placeholder="�μ���" readonly class="input_GrayLineCenter" style="width:100%; background-color:#f3f3f3;"></td>
				<td valign="top"><textarea id="inout0501v2Item02" name="inout0501v2Item02" valid.label="Ư�����" placeholder="Ư�����" class="input_GrayLine inputInitClasses2" style="height:68px; width:100%;" stringMaxLength='250'></textarea></td>
				<td><a class="button smallrounded orange" id="inout0501vSave2"><div style="height:12px;">&nbsp;</div>������<br/>����<div style="height:12px;">&nbsp;</div></a></td>
			</tr>
		</table>
	</form>

	<div style="margin-top:8px; margin-bottom:4px; text-align:right;">
		<a class="button smallrounded green" id="inout0501vBatchOpenClose">��δ���</a> <a class="button smallrounded rosy" id="inout0501vBatchRemove">�ϰ�����</a>
	</div>
	
	<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types" id="inout0501vMainTable">
		<thead>
		<tr>
			<th style="width:40px;">Ȯ��</th>
			<th style="width:45px;">�б�</th>
			<th style="width:45px;">��ȣ</th>
			<th style="width:100px;">�̸�</th>
			<th style="width:45px;"><input type="checkbox" id="inout0501vAllChk"></th>
			<th style="width:55px;">�б�</th>
			<th style="width:80px;">����</th>
			<th style="width:70px;">�̼��ð�</th>
			<th>Ȱ������ (Ư�����)</th>
			<th style="width:120px;">�μ���</th>
			<th style="width:90px;">���</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td colspan="11" style="text-align:center; height:50px;">:: ��ȸ�� �����Ͱ� �����ϴ�. ::</td>
		</tr>
		</tbody>
	</table>
	<iframe id="io0501vIframeTarget" name="io0501vIframeTarget" style="display:none;"></iframe>	<!--width:100%; height:100%;-->
</body>
</html>