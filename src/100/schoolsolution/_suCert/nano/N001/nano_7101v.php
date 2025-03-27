<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_db.php");

	/**
	 * @ author : 이근만
	 * @ since : 2024.12.04.
	 * @ descript : 자료관리 > 방과후 수업 관리
	 */

	if ($sess_Iden == "" || $sess_Code == "") {
		echo "
			<script language=\"javascript\">\n
				alert(\"[알림!] 로그인해 주십시오.\");\n
				location.href = \"/logout.php\";\n
			</script>\n
		";
	}
	
	$db = doConnect();

	include_once $_SERVER['DOCUMENT_ROOT'] . "func/func_auth.php";	// 반드시 $db = doConnect(); 밑에 있어야 합니다.
?><script type="text/javascript">
	$.datepicker.setDefaults({
	    dateFormat: 'yy-mm-dd',
	    buttonImageOnly: true,
	    buttonText: "달력",
	    buttonImage: "/images/calendar.gif",
	    dayNamesMin: ['일','월', '화', '수', '목', '금', '토'], 
		monthNames : ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월']
	});

	var nano7101v = function() { }	// 처리

	nano7101v.getList = function() {
		if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

		var selected = '';
		$('#nano7101vCno option[value!="multiselect-all"]:selected').each(function() {
			selected += $(this).val() + ',';
		});
		
		var pHak = $("#nano7101vHak option:selected").val();
		
		// 전체 체크, 분기별 체크 해제
		$(".nano0701AllChk").prop('checked', false);
		$(".nano0701BraChk").prop('checked', false);
		
		var url = "";
		if ('<?= $_SERVER['REMOTE_ADDR'] ?>' == '61.85.146.3') url = "/_suCert/nano/N001/nano_7101j(2).php";
		else url = "/_suCert/nano/N001/nano_7101j.php";

		$.ajax({
			async : false, type : "POST", url : url, data : "caseBy=getList&pUniq=" + encodeURIComponent(selected) + "&pHak=" + encodeURIComponent(pHak), dataType : "html", 
			error : function(response, status, request) {	// 통신 에러 발생시 처리
				alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
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
				
				// 테이블 앞 checkbox시 분기 전부 체크
				$('.nano7101RowClass').on('change', function() {
					var isChecked = $(this).is(':checked');
				
					// 같은 tr 안에서 현재 td 기준으로 다음 4개의 td 안 체크박스들 체크
				    var $td = $(this).closest('td');
				    var $tr = $td.closest('tr');
				    var $allTds = $tr.find('td');
				    var index = $allTds.index($td); // 현재 td 위치
				
				    // 다음 4개의 td에서 .child 체크박스 찾아서 체크
				    $allTds.slice(index + 1, index + 5).find('input.nano7101RowChildClass').prop('checked', isChecked);
				});
				
			}
		});
	}

	$(document).ready(function() {
		nano7101v.getList();

		$('#nano7101vHak').multiselect({
			nonSelectedText : ': : &nbsp;학 년&nbsp; : : &nbsp;&nbsp;&nbsp;',
			nSelectedText: ' 개 학년 선택 &nbsp;&nbsp;&nbsp;',
			includeSelectAllOption : true,
			onChange: function(option, checked) {
				var param = '';
				$('#nano7101vHak option[value!="multiselect-all"]:selected').each(function() {
					param += $(this).val() + ',';
				});

				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getHakHakgwaClassList&builtPath=<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>&vHak=" + encodeURIComponent(param), dataType : "json",	// datatype 로 타입을 잘못 지정할경우 에러 조심~
					error : function(response, status, request) {	// 통신 에러 발생시 처리
						alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
					},
					success: function (resObj) {
						$('#nano7101vGyl').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});

		$('#nano7101vGyl').multiselect({
			nonSelectedText : ': : &nbsp;학과 & 학급&nbsp; : : &nbsp;&nbsp;&nbsp;',
			nSelectedText: ' 개 학과 & 학급 선택 &nbsp;&nbsp;&nbsp;',
			includeSelectAllOption : true,
			onChange: function(option, checked) {
				var param1 = $("#nano7101vHak option:selected").val() == undefined? "" : $("#nano7101vHak option:selected").val();
				var param2 = '';
				$('#nano7101vGyl option[value!="multiselect-all"]:selected').each(function() {
					param2 += $(this).val() + ',';
				});

				$.ajax({
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getJoinClnoList2&vGyl=" + encodeURIComponent(param2), dataType : "json",	// datatype 로 타입을 잘못 지정할경우 에러 조심~
					error : function(response, status, request) {	// 통신 에러 발생시 처리
						alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
					},
					success: function (resObj) {
						$('#nano7101vCno').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});
		
		$('#nano7101vCno').multiselect({
			nonSelectedText : ': : &nbsp;[학급-번호] 이름&nbsp; : : &nbsp;&nbsp;&nbsp;',
			nSelectedText: ' 명 학생 선택 &nbsp;&nbsp;&nbsp;',
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
				error : function(response, status, request) {	// 통신 에러 발생시 처리
					alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
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
				error : function(response, status, request) {	// 통신 에러 발생시 처리
					alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
				},
				success: function (createHtml) {
					$("<div>" + createHtml + "</div>").dialog({ 
						title : ':: 방과후 수업 불러오기 ::', 
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
							"초기화" : function() {
								if(confirm("해당 설정년도의 데이터 기준으로 방과후의 모든 자료를 초기화 하시겠습니까?")) {
									$.post("/_suCert/nano/N001/nano_7101j.php","caseBy=doAfterSchoolReset",function(o){
										nano7101v.getList();
										$(".ui-dialog-titlebar-close").trigger('click');
									});
								}
							},						
							"불러오기" : {
								text : "불러오기",
								click : function() {
									let chkidxList = [];
									$(".aftercheckClass").each(function(){
										if($(this).is(":checked")) chkidxList.push($(this).val());
									});
									let idxjoin = chkidxList.join(",");
	
									if (idxjoin == "") {
										alert("[알림!] 불러올 프로젝트를 하나 이상 체크해 주세요");
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
							"닫기" : function() {
								$(".ui-dialog-titlebar-close").trigger('click');
							}
						}
					}).css({ 'width' : '100%' });
				}			
			});
		});
		
		// 방과후 분기별 저장
		$("#nano7101Save").on("click", function(){
			if (!menuAuth.get('<?= $sess_Code ?>', 'W', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;
			
			var selected = '';
			$('#nano7101vCno option[value!="multiselect-all"]:selected').each(function() {
				selected += $(this).val() + ',';
			});
			
			if (selected == "" || typeof selected == 'undefined') {
				alert("[알림!] 입력하실 학생을 선택해주세요.");
				return;
			}
			
			var param = "";
			
			$(".nano7101totalCalss").each(function(){
				
				var isChk = ($(this).is(":checked") ? "Y" : "N");
				
				param += "&" + $(this).attr('id') + "=" + encodeURIComponent(isChk);
			});
			
			$.ajax({
				async : false, type : "POST", url : "/_suCert/nano/N001/nano_7101j(2).php", data : "caseBy=doSave" + param, dataType : "json", 
				error : function(response, status, request) {	// 통신 에러 발생시 처리
					alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
				},
				success: function (resObj) {
					if (resObj.resData == "ok") {
						alert("[알림!] 정상적으로 처리되었습니다.");
						nano7101v.getList();
					} else {
						alert("[알림!] 처리 도중 오류가 발생하였습니다.");
						return;
					}
				}
			});					
		});
		
		// 분기별 체크시 해당 분기 모두 체크
		$('.nano0701BraChk').on('change', function() {
			var isChecked = $(this).is(':checked');
			var isVal = $(this).val();
			$(".nano7101" + isVal + "Class").prop('checked', isChecked);
			
		});
		
		// 전체 체크시 전부 체크
		$(".nano0701AllChk").on('change', function() {
			var isChecked = $(this).is(':checked');
			
			$(".nano7101RowClass").prop('checked', isChecked);
			
			$('.nano7101RowClass').each(function () {
				
				var isChildChecked = $(this).is(':checked');
			
				// 같은 tr 안에서 현재 td 기준으로 다음 4개의 td 안 체크박스들 체크
			    var $td = $(this).closest('td');
			    var $tr = $td.closest('tr');
			    var $allTds = $tr.find('td');
			    var index = $allTds.index($td); // 현재 td 위치
			
			    // 다음 4개의 td에서 .child 체크박스 찾아서 체크
			    $allTds.slice(index + 1, index + 5).find('input.nano7101RowChildClass').prop('checked', isChildChecked);
			});
		});
		

		
	});	//ready end
</script>
<div class="subBodyClass">
	<div style="display:inline-block; border:1px solid #cccccc; background:#f8f8f6; padding:10px; border-radius:3px;">
		<select id="nano7101vHak" name="nano7101vHak" class="input_GrayLine">
			<option value="">:: 학년 ::</option>
			<?= $searchSelectBoxListViewItem ?>
		</select>
		<select id="nano7101vGyl" name="nano7101vGyl" class="input_GrayLine" multiple="multiple"></select>
		<select id="nano7101vCno" name="nano7101vCno" class="input_GrayLine" multiple="multiple"></select>
		<a class="button white smallrounded" id="nano7101vCertMainSearch">조회</a>
	</div>

	<div style="width:100%;">
		<div>
			<div style="float:left;">
				<div style="padding-top:5px; padding-bottom:5px;">
					
				</div>
			</div>
			<div style="float:right;">
				<div style="padding-top:5px; padding-bottom:5px;text-align:right; clear:both;">
				    <!--<a class="button rosy smallrounded" id="afterSchPop">방과후 데이터 가져오기</a>-->
				    <a class="button rosy smallrounded" id="nano7101Save" style="width:90px;">저장</a>
				</div>
			</div>
			<div style="clear:both;"></div>
		</div>

		<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7101vInputItemTables" style="margin-top:0px">
			<thead>
			<tr>
				<th colspan="2">학급-번호-이름</th>
				<td colspan="3" style="text-align:left; padding:3px;" id="nano7101vViewStudent">조회 및 등록할 대상자(학생)를 선택하세요.</td>
			</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<?php if ($_SERVER['REMOTE_ADDR'] == '61.85.146.3') { ?>
	<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7101vMainListTable" style="width:100%; margin-top:5px;">
		<thead>
		<tr>
			<th style="width:45px;">확장</th>
			<th style="width:45px;">학년</th>
			<th style="width:125px;">학과</th>
			<th style="width:45px;">학급</th>
			<th style="width:45px;">번호</th>
			<th style="width:90px;">이름</th>
			<th><input type="checkbox" class="nano0701AllChk" style="width:18px; height:18px;" value=""/></th>
			<th>학년</th>
			<th>1분기 <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="1"/></th>			
			<th>2분기 <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="2"/></th>			
			<th>3분기 <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="3"/></th>			
			<th>4분기 <input type="checkbox" class="nano0701BraChk" style="width:18px; height:18px;" value="4"/></th>			
			<th>반영점수</th>			
			<th style="width:110px;">평가날짜</th>
			<th style="width:90px;">작성자</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
	
	<?php } else { ?>
	<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7101vMainListTable" style="width:100%; margin-top:5px;">
		<thead>
		<tr>
			<th style="width:45px;">확장</th>
			<th style="width:45px;">학년</th>
			<th style="width:125px;">학과</th>
			<th style="width:45px;">학급</th>
			<th style="width:45px;">번호</th>
			<th style="width:90px;">이름</th>
			<th>종합 및 상세내역</th>
			<th style="width:110px;">평가날짜</th>
			<th style="width:90px;">작성자</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
	<?php } ?>
</div>