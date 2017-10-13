var config;

var ua = navigator.userAgent.toLowerCase();
if (/iphone|ipad|ipod/.test(ua)) {
	config = {
		http_url : 'https://www.52kaiche.cn/',
		img_url : 'https://www.52kaiche.cn/'
	};
} else if (/android/.test(ua)) {
	config = {
		http_url : 'http://www.52kaiche.cn/',
		img_url : 'http://www.52kaiche.cn/'
	};
}
var App_Id = 'A6942550141584';

//POST获取方法
function fnPost(path, data, callback) {
	api.ajax({
		url: config.http_url+path,
		method: 'post',
		data: data
	}, function(ret, err) {
		callback(ret);
	});
};
//get获取方法
function fnGet(path, data, callback) {
	api.ajax({
		url: config.http_url+path,
		method: 'get',
		data: data
	}, function(ret, err) {
		callback(ret);
	});
};
