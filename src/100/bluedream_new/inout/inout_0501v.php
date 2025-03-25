<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_global.php";
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_db.php";
	
	if ($sess_Iden == '') {	// 로그인 되어 있지 않다면
		echo "
			<script type=\"text/javascript\">
				alert('[알림!] 로그인 후 이용가능 합니다.');
				location.href = '/logout.php';
			</script>
		";
		exit;
	}
	
	/*******************************
	*	자료관리 > 위탁생 동아리활동
	*	@ author : 이근만
	*	@ since : 2020.12.21.
	*******************************/

	$db = doConnect();
	
	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_auth.php";	// 반드시 $db = doConnect(); 밑에 있어야 합니다.
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
<script type="text/javascript">
	var io0501vObj = function() { }

	io0501vObj.getDateToHakgi = function(YmdTarget, targetIden) {
		$.ajax({
			async : false, type : "POST", url : "/basic/basic_0301j.php", data : "caseBy=getDateToHakgi&pYmd=" + encodeURIComponent($("#" + YmdTarget).val()), dataType : "json", 
			error : function(response, status, request) {	// 통신 에러 발생시 처리
				alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
			},
			success: function (resObj) {
				if (resObj.resHakgi == '') {
					$("#" + YmdTarget).val("");
					$("#" + targetIden).empty().html("<option value=\"\" selected>:: 학기 ::</option>");
					alert("[알림!]\n" + resObj.resHakgi1 + ",\n" + resObj.resHakgi2);
					return;
				} else {
					$("#" + targetIden).empty().html("<option value=\"" + resObj.resHakgi + "\" selected>" + resObj.resHakgi + "학기</option>");
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
			error : function(response, status, request) {	// 통신 에러 발생시 처리
				alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
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

					if (confirm("[알림!] 삭제 진행시 데이터는 영구히 삭제 됩니다.\n\n삭제를 진행하시겠습니까?")) {
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
						if (confirm("[알림!] 삭제 진행시 데이터는 영구히 삭제 됩니다.\n\n삭제를 진행하시겠습니까?")) {
							$.post('/inout/inout_0501j.php', 'caseBy=removeItem&pRemoveIdxs=' + transValue, function(p) {
								io0501vObj.getList();
							});
						}
					} else {
						alert("[알림!] 일괄삭제 할 항목을 선택하세요.");
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
								$(".isNumeric").on('keyup', function() {	// 숫자만
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
				alert("[알림!] 입력가능 문자수를 초과하여 초과된 문자를 제거합니다.\n( " + (addComma($(this).val().length) + ' / ' + addComma(maxLength)) + " )");
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
    										$("#inout0501vDialogItem05").append("<option value=\"" + uniqueNames[s] + "\">" + uniqueNames[s] + "교시</option>");
    									}
    						
    									$("#inout0501vDialogItem02").val($("#inout0501vDialogItem05 option").length == 0? "" : $("#inout0501vDialogItem05 option").length);
									}
								});
						
								$("#inout0501vDialogItem05").unbind('click').on('click', function() {
									$("#inout0501vDialogItem05 option:eq(" + $("#inout0501vDialogItem05 option").index($("#inout0501vDialogItem05 option:selected")) + ")").remove();
						
									$("#inout0501vDialogItem02").val($("#inout0501vDialogItem05 option").length == 0? "" : $("#inout0501vDialogItem05 option").length);
								});
							},
							closeOnEscape : true, autoOpen : true, width : 600, height : 'auto', modal : true, resizable : false, draggable : true, bgiframe : true, title : ":: 위탁생 동아리활동 수정",
							buttons : {
								"저장" : function() {
									if (pType == '1') {
										$.ajax({
											async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=getHoliday&pDate=" + encodeURIComponent($("#inout0501vDialogItem01").val()), dataType : "json", 
											error : function(response, status, request) {	// 통신 에러 발생시 처리
												alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
											},
											success: function (rJson) {
												if (rJson.resData == 'Y') {
										
													if ($("#inout0501vDialogItem01").val() == '') {
														alert("[알림!] " + $("#inout0501vDialogItem01").attr('valid.label') + " 항목은 필수입력 항목입니다.");
														$("#inout0501vDialogItem01").focus();
														return;
													}
										
													if ($("#inout0501vDialogItem02").val() == '') {
														alert("[알림!] " + $("#inout0501vDialogItem02").attr('valid.label') + " 항목은 필수입력 항목입니다.");
														$("#inout0501vDialogItem02").focus();
														return;
													}
										
													if ($("#inout0501vDialogItem03").val() == '') {
														alert("[알림!] " + $("#inout0501vDialogItem03").attr('valid.label') + " 항목은 필수입력 항목입니다.");
														$("#inout0501vDialogItem03").focus();
														return;
													}
										
													if ($("#inout0501vDialogItem04").val() == '') {
														alert("[알림!] " + $("#inout0501vDialogItem04").attr('valid.label') + " 항목은 필수입력 항목입니다.");
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
										
													var vMax = Math.max.apply(null, tmp);	// 이수교시 최대값
													var vMin = Math.min.apply(null, tmp);	// 이수교시 최소값

													$.ajax({
														async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=get&proCs=mod&pCode=" + pIdx + "&pUniq=" + encodeURIComponent(iop) + "&pDate=" + encodeURIComponent($("#inout0501vDialogItem01").val()) + "&applyTime=" + encodeURIComponent(applyTime), dataType : "json", 
														error : function(response, status, request) {	// 통신 에러 발생시 처리
															alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
														},
														success: function (resObj) {
															var errorText = '';
															var errorCnt = 1;
								for (var u in resObj) {
									if (resObj[u]['resCode'] == '0') {
										
									} else if (resObj[u]['resCode'] == '1') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + kDate + ', 일별 마감 처리 안됨</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>교시 선택!!</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '-1') {
										for (var w = 0; w < resObj[u]['resTime'].length; w++) {
											for (var z in resObj[u]['resTime'][w]) {
												if (resObj[u]['resTime'][w][z] == '결과(질병)' || resObj[u]['resTime'][w][z] == '결과(무단)' || resObj[u]['resTime'][w][z] == '결과(미인정)' || resObj[u]['resTime'][w][z] == '결과(기타)' || resObj[u]['resTime'][w][z] == '결과(인정)') {
													// alert('>>' + parseInt(z) + ', vMin : ' + vMin + ', vMax : ' + vMax);
													// if (parseInt(z) ?= vMin) {
													if (parseInt(z) == vMax || parseInt(z) == vMin) {	// @author : 이근만, @modify : 2018.07.06., @descript : 6교시 결과(무단)일 경우, 5교시 입력하려 해도 6교시때문에 입력이 안된다고 함. 그래서 수정
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "교시, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else if (resObj[u]['resTime'][w][z] == '지각(질병)' || resObj[u]['resTime'][w][z] == '지각(무단)' || resObj[u]['resTime'][w][z] == '지각(미인정)' || resObj[u]['resTime'][w][z] == '지각(기타)' || resObj[u]['resTime'][w][z] == '지각(인정)') {
													if (parseInt(z) >= vMin) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "교시, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else {
													if (parseInt(z) <= vMax) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "교시, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												}
											}
										}
									} else if (resObj[u]['resCode'] == '-2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>이미 ' + resObj[u]['resTime'] + ' 에서 처리됨!</b> )<br>';
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
																    	io0501vObj.getList();	// 리스트 불러오는 함수.
																    	$("#inout0501vDialogView").remove();
																	} else {
																		alert("[알림!] 등록중 오류가 발생하였습니다.\n잠시후 다시 사용바랍니다.");
																	}
										
																	$(this).unbind('load');
																	return false;
																});
															} else {
																$("<div id=\"inout0501vDialogViews\">" + errorText + "</div>").dialog({
																	closeOnEscape : true, autoOpen : true, width : 'auto', height : 'auto', modal : true, resizable : false, draggable : true, bgiframe : true, title : ":: 처리 결과",
																	buttons : {
																		"닫기" : function() {
																			$("#inout0501vDialogViews").remove();
																		}
																	}
																});
															}
														}
													});
												} else {
													alert("[입력불가] " + $("#inout0501vDialogItem01").val() + " [ " + ($.trim(rJson.resText) == ''? '-' : $.trim(rJson.resText)) + " ] 수업일이 아닙니다.\n\n\"자료관리 > 출결관리, 휴일(수업일) 관리\"에서 설정!!");
													return;
												}
											}
										});

									} else if (pType == '2') {
										if ($("#inout0501v2DialogItem01").val() == '') {
											alert("[알림!] " + $("#inout0501v2DialogItem01").attr('valid.label') + " 항목은 필수입력 항목입니다.");
											$("#inout0501v2DialogItem01").focus();
											return;
										}
							
										/*if ($("#inout0501v2DialogItem02").val() == '') {	// @author : 이근만, @modify : 2019.07.12., @descript : 요놈은 선택사항으로 체크 요망, 김영수 선생님
											alert("[알림!] " + $("#inout0501v2DialogItem02").attr('valid.label') + " 항목은 필수입력 항목입니다.");
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
										    	io0501vObj.getList();	// 리스트 불러오는 함수.
										    	$("#inout0501vDialogView").remove();
											} else {
												alert("[알림!] 등록중 오류가 발생하였습니다.\n잠시후 다시 사용바랍니다.");
											}
							
											$(this).unbind('load');
											return false;
										});
									}
								}, 
								"닫기" : function() {
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
		$(".isNumeric").on('keyup', function() {	// 숫자만
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
		    buttonText: "달력",
		    buttonImage: "/images/calendar.gif",
		    dayNamesMin: ['일','월', '화', '수', '목', '금', '토'], 
			monthNames : ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월']
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
			error : function(response, status, request) {	// 통신 에러 발생시 처리
				alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
			},
			success: function (resObj) {
				
				// resObj.resData.unshift({ "label" : ":: &nbsp;학과 & 학급&nbsp; :: &nbsp;&nbsp;", "value" : "" });
				
				resObj = "<option value=\"\">동아리 부서</option>" + resObj;
				
				$("#inout0501vCls2").empty().html(resObj);
			}
		});

		$('#inout0501vCls2').multiselect({
			nonSelectedText : ':: &nbsp;동아리 부서&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' 개 동아리 부서 선택 &nbsp;&nbsp;',
			onChange : function(option, checked) {
				var param2 = '';
				$('#inout0501vCls2 option[value!="multiselect-all"]:selected').each(function() {
					param2 += $(this).val() + '`';
				});

				$("#inout0501v2Item01, #inout0501vItem04").val($('#inout0501vCls2 option:selected').val());
				
				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getJoinClnoList3&vClub=" + encodeURIComponent(param2), dataType : "json", 
					error : function(response, status, request) {	// 통신 에러 발생시 처리
						alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
					},
					success: function (resObj) {
						$('#inout0501vCno').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});
		
		$('#inout0501vCno').multiselect({
			nonSelectedText : ':: &nbsp;이 름 (번호)&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' 명 학생 선택 &nbsp;&nbsp;'
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
				error : function(response, status, request) {	// 통신 에러 발생시 처리
					alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
				},
				success: function (rJson) {
					if (rJson.resData == 'Y') {

						var selected = '';
						$('#inout0501vCno option[value!="multiselect-all"]:selected').each(function() {
							selected += $(this).val() + ',';
						});
			
						if (selected == "") {
							alert("[알림!] 조회 및 등록할 대상자(학생)를 선택하세요.");
							return;
						} else $("#inout0501vTarget").val(selected);
			
						if ($("#inout0501vItem01").val() == '') {
							alert("[알림!] " + $("#inout0501vItem01").attr('valid.label') + " 항목은 필수입력 항목입니다.");
							$("#inout0501vItem01").focus();
							return;
						}
			
						if ($("#inout0501vItem02").val() == '') {
							alert("[알림!] " + $("#inout0501vItem02").attr('valid.label') + " 항목은 필수입력 항목입니다.");
							$("#inout0501vItem02").focus();
							return;
						}
			
						if ($("#inout0501vItem03").val() == '') {
							alert("[알림!] " + $("#inout0501vItem03").attr('valid.label') + " 항목은 필수입력 항목입니다.");
							$("#inout0501vItem03").focus();
							return;
						}
			
						if ($("#inout0501vItem04").val() == '') {
							alert("[알림!] " + $("#inout0501vItem04").attr('valid.label') + " 항목은 필수입력 항목입니다.");
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
			
						var vMax = Math.max.apply(null, tmp);	// 이수교시 최대값
						var vMin = Math.min.apply(null, tmp);	// 이수교시 최소값

						$.ajax({
							async : false, type : "POST", url : "/inout/inout_1501j.php", data : "caseBy=get&pUniq=" + encodeURIComponent(selected) + "&pDate=" + encodeURIComponent($("#inout0501vItem01").val()) + "&applyTime=" + encodeURIComponent(applyTime), dataType : "json", 
							error : function(response, status, request) {	// 통신 에러 발생시 처리
								alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
							},
							success: function (resObj) {
								var errorText = '';
								var errorCnt = 1;
								for (var u in resObj) {
									if (resObj[u]['resCode'] == '0') {
										
									} else if (resObj[u]['resCode'] == '1') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + kDate + ', 일별 마감 처리 안됨</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>교시 선택!!</b> )<br>';
										errorCnt++;
									} else if (resObj[u]['resCode'] == '-1') {
										for (var w = 0; w < resObj[u]['resTime'].length; w++) {
											for (var z in resObj[u]['resTime'][w]) {
												if (resObj[u]['resTime'][w][z] == '결과(질병)' || resObj[u]['resTime'][w][z] == '결과(무단)' || resObj[u]['resTime'][w][z] == '결과(미인정)' || resObj[u]['resTime'][w][z] == '결과(기타)' || resObj[u]['resTime'][w][z] == '결과(인정)') {
													// alert('>>' + parseInt(z) + ', vMin : ' + vMin + ', vMax : ' + vMax);
													// if (parseInt(z) ?= vMin) {
													if (parseInt(z) == vMax || parseInt(z) == vMin) {	// @author : 이근만, @modify : 2018.07.06., @descript : 6교시 결과(무단)일 경우, 5교시 입력하려 해도 6교시때문에 입력이 안된다고 함. 그래서 수정
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "교시, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else if (resObj[u]['resTime'][w][z] == '지각(질병)' || resObj[u]['resTime'][w][z] == '지각(무단)' || resObj[u]['resTime'][w][z] == '지각(미인정)' || resObj[u]['resTime'][w][z] == '지각(기타)' || resObj[u]['resTime'][w][z] == '지각(인정)') {
													if (parseInt(z) >= vMin) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "교시, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												} else {
													if (parseInt(z) <= vMax) {
														errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>' + (z + "교시, " + resObj[u]['resTime'][w][z]) + '</b> )<br>';
														errorCnt++;
													}
												}
											}
										}
									} else if (resObj[u]['resCode'] == '-2') {
										errorText += errorCnt + "). " + resObj[u]['resUniq'] + ' ( <b>이미 ' + resObj[u]['resTime'] + ' 에서 처리됨!</b> )<br>';
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
									    	io0501vObj.getList();	// 리스트 불러오는 함수.

									    	$(".inputInitClasses").val('');

									    	$("#inout0501vItem05").empty();
									    	$("#inout0501vItem06").prop('checked', false);
										} else {
											alert("[알림!] 등록중 오류가 발생하였습니다.\n잠시후 다시 사용바랍니다.");
										}
						
										$(this).unbind('load');
										return false;
									});
								} else {
									$("<div id=\"inout0501vDialogView\">" + errorText + "</div>").dialog({
										closeOnEscape : true, autoOpen : true, width : 'auto', height : 'auto', modal : true, resizable : false, draggable : true, bgiframe : true, title : ":: 처리 결과",
										buttons : {
											"닫기" : function() {
												$("#inout0501vDialogView").remove();
											}
										}
									});
								}
							}
						});
					} else {
						alert("[입력불가] " + $("#inout0501vItem01").val() + " [ " + ($.trim(rJson.resText) == ''? '-' : $.trim(rJson.resText)) + " ] 수업일이 아닙니다.\n\n\"자료관리 > 출결관리, 휴일(수업일) 관리\"에서 설정!!");
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
				alert("[알림!] 조회 및 등록할 대상자(학생)를 선택하세요.");
				return;
			} else $("#inout0501vTarget2").val(selected);

			if ($("#inout0501v2Item01").val() == '') {
				alert("[알림!] " + $("#inout0501v2Item01").attr('valid.label') + " 항목은 필수입력 항목입니다.");
				$("#inout0501v2Item01").focus();
				return;
			}

			/*if ($("#inout0501v2Item02").val() == '') {	// @author : 이근만, @modify : 2019.07.12., @descript : 요놈은 선택사항으로 체크 요망, 김영수 선생님
				alert("[알림!] " + $("#inout0501v2Item02").attr('valid.label') + " 항목은 필수입력 항목입니다.");
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
			    	io0501vObj.getList();	// 리스트 불러오는 함수.
			    	
			    	$(".inputInitClasses2").val('');
				} else {
					alert("[알림!] 등록중 오류가 발생하였습니다.\n잠시후 다시 사용바랍니다.");
				}

				$(this).unbind('load');
				return false;
			});
		});

		// io0501vObj.getList();	// 리스트 불러오는 함수.
		$("#inout0501vMainSearch").unbind('click').on('click', function() {	// 조회 버튼 클릭시
			io0501vObj.getList();
		});
		
		$("#inout0501vBatchOpenClose").unbind('click').on('click', function() {
			if ($(this).text() == '모두닫힘') $(this).text('모두열림');
			else $(this).text('모두닫힘');
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
    				$("#inout0501vItem05").append("<option value=\"" + uniqueNames[s] + "\">" + uniqueNames[s] + "교시</option>");
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
				alert("[알림!] 입력가능 문자수를 초과하여 초과된 문자를 제거합니다.\n( " + (addComma($(this).val().length) + ' / ' + addComma(maxLength)) + " )");
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
	<div style="font-size:13px; font-weight:bold; margin-bottom:5px; padding-bottom:4px; border-bottom:1px dotted #a3a3a3;"><i class="icon-th-large"></i> 위탁생 동아리활동</div>

	<div style="display:inline-block; border:1px solid #c3c3c3; padding:4px 8px; border-radius:5px; background-color:#f3f3f3;">
		<select id="inout0501vCls2" name="inout0501vCls2" class="input_GrayLine"></select>
		<select id="inout0501vCno" name="inout0501vCno" class="input_GrayLine" multiple="multiple"></select>

		&nbsp;
		<input type="radio" name="inout0501vHakgi" id="inout0501vHakgi1" checked value="1"> <label for="inout0501vHakgi1">1학기</label>
		&nbsp;
		<input type="radio" name="inout0501vHakgi" id="inout0501vHakgi2" value="2"> <label for="inout0501vHakgi2">2학기</label>

		&nbsp;
		<a class="button gray smallrounded" id="inout0501vMainSearch">조회</a>
	</div>

	<form name="inout0501vMainForm" id="inout0501vMainForm" method="POST">
		<input type="hidden" name="inout0501vTarget" id="inout0501vTarget">

		<div style="margin-top:8px; margin-bottom:4px;"><i class="icon-th"></i> 누가기록</div>
		<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types">
			<tr>
				<th style="width:120px;">학기</th>
				<th style="width:170px;">이수시간 (교시 클릭!)</th>
				<th>활동내용</th>
				<th style="width:120px;">부서명</th>
				<th style="width:60px;">기능</th>
			</tr>
			<tr>
				<td valign="top">
					<select id="inout0501vItem00" name="inout0501vItem00" class="input_GrayLine" style="width:100%;">
						<option value="">:: 학기 ::</option>
					</select>
				</td>
				<td rowspan="3" valign="top">
					<select id="inout0501vItem05u" name="inout0501vItem05u" class="input_GrayLine inputInitClasses" multiple style="height:65px !important; width:60px;">
<?php
	for ($i = 1; $i <= 7; $i++) {
		echo "<option value=\"" . $i . "\" style=\"font-size:1em;\">" . $i . "교시</option>";
	}
?>
					</select>
					→
					<select id="inout0501vItem05" name="inout0501vItem05[]" class="input_GrayLine inputInitClasses" multiple style="height:65px !important; width:60px;"></select>
					<div style="margin-top:1px;">
						<input type="text" id="inout0501vItem02" name="inout0501vItem02" valid.label="이수시간" readonly class="input_GrayLineCenter isNumeric inputInitClasses" style="background-color:#e3e3e3; width:95%;">
					</div>
				</td>
				<td rowspan="3" valign="top"><textarea id="inout0501vItem03" name="inout0501vItem03" valid.label="활동내용" placeholder="활동내용" class="input_GrayLine inputInitClasses" style="height:96px; width:100%;"></textarea></td>
				<!-- @author : 유상호 @since : 2022-06-22 @descript : inputInitClasses 클래스 빼서 저장시 동아리명 초기화 안되게 해놓음 -->
				<td valign="top"><input type="text" id="inout0501vItem04" name="inout0501vItem04" valid.label="부서명" placeholder="부서명" readonly class="input_GrayLineCenter" style="width:100%; background-color:#f3f3f3;"></td>
				<td rowspan="3" valign="top">
					<a class="button smallrounded orange" id="inout0501vSave"><div style="height:26px;">&nbsp;</div>데이터<br/>저장<div style="height:26px;">&nbsp;</div></a>
				</td>
			</tr>
			<tr>
				<th>일자</th>
				<th>봉사연계</th>
			</tr>
			<tr>
				<td valign="top"><input type="text" id="inout0501vItem01" name="inout0501vItem01" valid.label="일자" placeholder="일자" class="input_GrayLineCenter inputInitClasses" readonly style='background-color:#e3e3e3; width:85px; margin-right:3px;'></td>
				<td valign="top"><input type="checkbox" id="inout0501vItem06" name="inout0501vItem06" style="width:20px; height:20px;" value="Y"></td>
			</tr>
		</table>
	</form>

	<form name="inout0501vMainForm2" id="inout0501vMainForm2" method="POST">
		<input type="hidden" name="inout0501vTarget2" id="inout0501vTarget2">

		<div style="margin-top:8px; margin-bottom:4px;"><i class="icon-th"></i> 학교생활기록부 반영기록</div>

		<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types">
			<tr>
				<th style="width:75px">학기</th>
				<th style="width:120px;">부서명</th>
				<th>특기사항 (<span id="counterOfIO0501v">###</span>자 이내)</th>
				<th style="width:60px;">기능</th>
			</tr>
			<tr>
				<td valign="top">
					<select id="inout0501v2Item00" name="inout0501v2Item00" class="input_GrayLine" style="width:100%;">
						<option value="1">1학기</option>
						<option value="2">2학기</option>
					</select>
				</td>
				<td valign="top"><input type="text" id="inout0501v2Item01" name="inout0501v2Item01" valid.label="부서명" placeholder="부서명" readonly class="input_GrayLineCenter" style="width:100%; background-color:#f3f3f3;"></td>
				<td valign="top"><textarea id="inout0501v2Item02" name="inout0501v2Item02" valid.label="특기사항" placeholder="특기사항" class="input_GrayLine inputInitClasses2" style="height:68px; width:100%;" stringMaxLength='250'></textarea></td>
				<td><a class="button smallrounded orange" id="inout0501vSave2"><div style="height:12px;">&nbsp;</div>데이터<br/>저장<div style="height:12px;">&nbsp;</div></a></td>
			</tr>
		</table>
	</form>

	<div style="margin-top:8px; margin-bottom:4px; text-align:right;">
		<a class="button smallrounded green" id="inout0501vBatchOpenClose">모두닫힘</a> <a class="button smallrounded rosy" id="inout0501vBatchRemove">일괄삭제</a>
	</div>
	
	<table cellpadding="0" cellspacing="0" border="0" class="tableGray_types" id="inout0501vMainTable">
		<thead>
		<tr>
			<th style="width:40px;">확장</th>
			<th style="width:45px;">학급</th>
			<th style="width:45px;">번호</th>
			<th style="width:100px;">이름</th>
			<th style="width:45px;"><input type="checkbox" id="inout0501vAllChk"></th>
			<th style="width:55px;">학기</th>
			<th style="width:80px;">일자</th>
			<th style="width:70px;">이수시간</th>
			<th>활동내용 (특기사항)</th>
			<th style="width:120px;">부서명</th>
			<th style="width:90px;">기능</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td colspan="11" style="text-align:center; height:50px;">:: 조회된 데이터가 없습니다. ::</td>
		</tr>
		</tbody>
	</table>
	<iframe id="io0501vIframeTarget" name="io0501vIframeTarget" style="display:none;"></iframe>	<!--width:100%; height:100%;-->
</body>
</html>