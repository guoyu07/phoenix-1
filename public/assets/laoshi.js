function laoshi() {

	this.rpc = function (data) {
        var whatToRtn = {};
		jQuery.getJSON('/staff/laoshi_rpc.php', data, function(retInfo) {
            whatToRtn = {"status": retInfo.status,
                        "msg": retInfo.msg,
                        "code": retInfo.code};
            return whatToRtn;
        });
	};

	this.toast = function (msg, fadetime) {
		$('#toast_msg').html(msg).fadeIn();
		setTimeout("$('#toast_msg').fadeOut()", 3000);
	};

    this.pushUpdate = function (field, value, cid) {
        //do nothing
        laoshi.rpc({'method': 'update_course', 'field': field, 'value': encodeURIComponent(value), 'courseid': cid});
        console.log(field + ' updated with value ' + value);
        laoshi.toast('Your changes have been saved.');
    };

    this.pushClassUpdate = function (field, value, cid) {
        //do nothing
        laoshi.rpc({'method': 'update_class', 'field': field, 'value': encodeURIComponent(value), 'classid': cid});
        console.log(field + ' updated with value ' + value);
        laoshi.toast('Your changes have been saved.');
    };

}

var laoshi = new laoshi();