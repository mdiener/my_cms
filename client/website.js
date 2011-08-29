jQuery(document).ready(function() {
	var site = new Site();
	site.setup();
});

var Site = function() {

}

Site.prototype = {
	setup: function() {
		var me = this;

		me.getPage();

		/*me.displayHeader();
		me.displayNavigation();
		me.displayContent();*/
	},

	getPage: function() {
		jQuery.ajax({
			url: "server/ajax.php",
			type: "POST",
			data: "action=getPage",
			success: function(data, status) {

			}
		});
	}
}
