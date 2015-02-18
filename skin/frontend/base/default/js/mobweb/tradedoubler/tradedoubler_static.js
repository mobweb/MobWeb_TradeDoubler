$async = true; // true : Asynchronous script / false : Synchronous Script

function getVar(name) {
	get_string = document.location.search;
	return_value = '';
	do {
		name_index = get_string.indexOf(name + '=');
		if (name_index != -1) {
			get_string = get_string.substr(name_index + name.length + 1,
				get_string.length - name_index);
			end_of_value = get_string.indexOf('&');
			if (end_of_value != -1) {
				value = get_string.substr(0, end_of_value);
			} else {
				value = get_string;
			}
			if (return_value == '' || value == '') {
				return_value += value;
			} else {
				return_value += ', ' + value;
			}
		}
	}
	while (name_index != -1) {
		space = return_value.indexOf('+');
	}
	while (space != -1) {
		return_value = return_value.substr(0, space) + ' ' +
			return_value.substr(space + 1, return_value.length);
		space = return_value.indexOf('+');
	}
	return (return_value);
}

function setCookie(name, value, expires, path, domain, secure) {
	var today = new Date();
	today.setTime(today.getTime());
	if (expires) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date(today.getTime() + (expires));
	document.cookie = name + "=" + escape(value) +
		((expires) ? "; expires=" + expires_date.toGMTString() : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		((secure) ? "; secure" : "");
}

function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
		begin = dc.indexOf(prefix);
		if (begin != 0) return null;
	} else {
		begin += 2;
	}
	var end = document.cookie.indexOf(";", begin);
	if (end == -1) {
		end = dc.length;
	}
	return unescape(dc.substring(begin + prefix.length, end));
}

var mytduid = getVar("tduid");
if (mytduid != "") {
	setCookie("TRADEDOUBLER", mytduid, 365);
}

if (typeof(TDConf) != "undefined") {
	TDConf.sudomain = ("https:" == document.location.protocol) ? "swrap" : "wrap";
	TDConf.host = ".tradedoubler.com/wrap";
	TDConf.containerTagURL = (("https:" == document.location.protocol) ? "https://" : "http://") + TDConf.sudomain + TDConf.host;

	if (typeof(TDConf.Config) != "undefined") {
		if ($async) {

			var TDAsync = document.createElement("script");
			TDAsync.src = TDConf.containerTagURL + "?id=" + TDConf.Config.containerTagId;
			TDAsync.async = "yes";
			TDAsync.width = 0;
			TDAsync.height = 0;
			TDAsync.frameBorder = 0;
			document.body.appendChild(TDAsync);
		} else {
			document.write(unescape("%3Cscript src='" + TDConf.containerTagURL  + "?id="+ TDConf.Config.containerTagId +" ' type='text/javascript'%3E%3C/script%3E"));
		}
	}
}