function laoshi() {

	this.rpc = function (data) {
		jQuery.getJSON('/staff/laoshi_rpc.php', data);
		return true;
	};

	this.toast = function (msg, fadetime) {
		$('#toast_msg').html(msg).fadeIn();
		setTimeout("$('#toast_msg').fadeOut()", 3000);
	}

}

var laoshi = new laoshi();