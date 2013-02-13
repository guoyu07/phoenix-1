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
	}

}

var laoshi = new laoshi();