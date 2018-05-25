$(function() {
	
	/*跳转到选择模板*/
/*	$("#sel_model").click(function(){
		window.open("select.html")
	})*/
	
	/*跳转到报告界面*/
	/*$("#a_report").click(function(){
		window.open("a.html")
	})*/
	
	/*以下为填写内容的内容*/
	var report = {
		number: '201601CC104',
		name: '水泥',
		type: '抽样检验',
		client: '北邮',
		creat_date: '2016年01月02日',
		creat_unit: '阿里公司',
		model: '————',
		sample_site: '工厂仓库',
		brand: '巫师',
		sample_person: '小明',
		rate: '优',
		sample_date: '2016年01月04日',
		smaple_number: '200g',
		state: '块状',
		sample_vol: '1t',
		sample_unit: '建筑材料工业技术监督研究中心',
		test_rule: 'JC/T 1074-2008《室内空气净化功能涂覆材料净化性能》',
		test_project: '1.甲醛净化效率',
		test_result: '*经检验，抽检样品的甲醛净化效率的检验结果符合标准JC/T 1074-2008中Ⅱ类材料的技术指标要求。*',
		sign_year: '2016',
		sign_month: '02',
		sign_day: '26',
	};

	function getVal() {
		/*以下为封面的内容*/
		$('#a_cover_key').val(report['number']);
		$('#a_cover_name_content').val(report['name']);
		$('#a_cover_client_content').val(report['client']);
		$('#a_cover_type_content').val(report['type']);
		
		/*以下是第二页的内容*/
		$('#a_key_number').val(report['number']);
		$('#a_name').val(report['name']);
		$('#a_category').val(report['type']);
		$('#a_test_unit').val(report['client']);
		$('#a_creat_date').val(report['creat_date']);
		$('#a_creat_unit').val(report['creat_unit']);
		$('#a_model').val(report['model']);
		$('#a_local').val(report['sample_site']);
		$('#a_brand').val(report['brand']);
		$('#a_sample_people').val(report['sample_person']);
		$('#a_rate').val(report['rate']);
		$('#a_sample_date').val(report['sample_date']);
		$('#a_number').val(report['smaple_number']);
		$('#a_state').val(report['state']);
		$('#a_vol').val(report['sample_vol']);
		$('#a_sample_unit').val(report['sample_unit']);
		$('#a_sample_rule').val(report['test_rule']);
		$('#a_pro').val(report['test_project']);
		$('#a_result').val(report['test_result']);
		$('#a_sign_year').val(report['sign_year']);
		$('#a_sign_month').val(report['sign_month']);
		$('#a_sign_day').val(report['sign_day']);
	}
	$('#change').click(function() {
		getVal();
	})
})