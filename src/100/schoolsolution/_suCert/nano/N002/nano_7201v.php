<?php
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_global.php");
	include_once($_SERVER['DOCUMENT_ROOT'] . "func/func_db.php");
	
	/**
	 * @ author : 이근만
	 * @ since : 2024.12.04.
	 * @ descript : 자료조회 > 방과후 수업 조회
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

	var nano7201v = function() { }	// 처리

	nano7201v.getList = function() {	// @descript : 목록
		if (!menuAuth.get('<?= $sess_Code ?>', 'R', '<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>')) return;

		var selected = '';
		$('#nano7201vCno option[value!="multiselect-all"]:selected').each(function() {
			selected += $(this).val() + ',';
		});

		$.ajax({
			async : false, type : "POST", url : "/_suCert/nano/N002/nano_7201j.php", data : "caseBy=getList&pHak=" + encodeURIComponent($("#nano7201vHak option:selected").val()) + "&pUniq=" + encodeURIComponent(selected), dataType : "html", 
			error : function(response, status, request) {	// 통신 에러 발생시 처리
				alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
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
			nonSelectedText : ':: &nbsp;학 년&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' 개 학년 선택 &nbsp;&nbsp;',
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
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getHakHakgwaClassList&builtPath=<?= str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']) ?>&vHak=" + encodeURIComponent(param), dataType : "json",	// datatype 로 타입을 잘못 지정할경우 에러 조심~
					error : function(response, status, request) {	// 통신 에러 발생시 처리
						alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
					},
					success: function (resObj) {
						$('#nano7201vGyl').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});

		$('#nano7201vGyl').multiselect({
			nonSelectedText : ':: &nbsp;학과 & 학급&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' 개 학과 & 학급 선택 &nbsp;&nbsp;',
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
					async : false, type : "POST", url : "/potal_ajax.php", data : "caseBy=getJoinClnoList2&vGyl=" + encodeURIComponent(param2), dataType : "json",	// datatype 로 타입을 잘못 지정할경우 에러 조심~
					error : function(response, status, request) {	// 통신 에러 발생시 처리
						alert("[알림!] 데이터 통신 도중 오류가 발생하였습니다.\n발생에 따른 사유는 아래와 같습니다.\n\n1. 장시간 로그인 방치로 인한 세션 종료!\n2. 데이터 누락 및 부재로 인한 데이터 오류!\n3. 서버와의 통신 오류!\n4. 기타 예외 사항!\n\n- 로그아웃 후 재 로그인 해보십시오.");
					},
					success: function (resObj) {
						$('#nano7201vCno').multiselect('dataprovider', resObj.resData);
					}
				});
			}
		});

		$('#nano7201vCno').multiselect({
			nonSelectedText : ':: &nbsp;[학급-번호] 이름&nbsp; :: &nbsp;&nbsp;',
			nSelectedText: ' 명 학생 선택 &nbsp;&nbsp;',
			numberDisplayed : 1
		});

		$("#nano7201vCertMainSearch").on("click", function() {	// @descript : 조회 버튼 클릭
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
			<option value="">:: 학년 ::</option>
			<?= $searchSelectBoxListViewItem ?>
		</select>
		<select id="nano7201vGyl" name="nano7201vGyl" class="input_GrayLine" multiple="multiple"></select>
		<select id="nano7201vCno" name="nano7201vCno" class="input_GrayLine" multiple="multiple"></select>
		<a class="button white smallrounded" id="nano7201vCertMainSearch">조회</a>
		
		<a class="button orange smallrounded" id="nano7201vCertExcelDown">현 자료 엑셀 내려받기</a>
	</div>

	<table border="0" cellSpacing="0" cellPadding="0" class="tableGray_type" id="nano7201vMainListTable" style="width:100%; margin-top:5px;">
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
</div>