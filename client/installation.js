jQuery(document).ready(function(){
	var inst = new Installation();
	inst.setup();
});

var Installation = function() {
	var nextBtn = undefined;
}

Installation.prototype = {
	setup: function() {
		var self = this;

		jQuery(".title").html("Welcome!");
		jQuery(".install-content").html("<p>Please follow the steps through to set up your website.</p><p>Before you can begin setting up your website you need to create a database in your MySQL. If you do not know how, contact your hosting provider.</p>");
		this.nextBtn = jQuery("<input />", {
			type: "button",
			value: "Next",
			"class": "install-next-step"
		}).appendTo(".button-lane");

		this.nextBtn.click(function() {
			self.beginInstallation();
		});
	},

	beginInstallation: function() {
		var self = this;

		jQuery(".title").html("Step 1 - Set DB Credentials");
		jQuery(".install-content").html("<p>For this step you need to edit the file located in your CMS folder under: [path_to_your_cms]/libs/db_info.php.</p><p>In this file you need to write the credentials for your database connection. A password and a username is required.</p><p>Click next if you have finished editing the file.</p>");

		this.nextBtn.unbind("click");
		this.nextBtn.click(function() {
			self.checkDBCredentials();
		});
	},

	checkDBCredentials: function() {
		var self = this;

		jQuery.ajax({
			url: "server/ajax.php",
			type: "POST",
			data: "action=check_db_credentials",
			success: function(data, status) {
				var response = jQuery.parseJSON(data);
				if (response.success) {
					jQuery(".install-error-msg").empty();
					self.setupDB();
				} else {
					jQuery(".install-error-msg").html("<p>Please correct the values in the db_info file.</p>");
				}
			}
		});
	},

	setupDB: function() {
		var self = this;

		jQuery.ajax({
			url: "server/ajax.php",
			type: "POST",
			data: "action=setup_db",
			success: function(data, status) {
				self.createAdminUserInterface();
			}
		});
	},

	createAdminUserInterface: function() {
		var self = this;

		jQuery(".title").html("Step 2 - Set administrator name and password")
		jQuery(".install-content").html("<p>You need to create an admin user, with which you will be able to administer your site.</p><p>Please provide a username and a password.</p>");

		// Username
		jQuery("<div class=\"input-username\"></div>").appendTo(".install-content")
		jQuery("<label />", {
			"for": "username",
			text: "Username"
		}).appendTo(".input-username");
		var inputPW = jQuery("<input />", {
			type: "text",
			name: "username"
		}).appendTo(".input-username");

		// Password
		jQuery("<div class=\"input-password\"></div>").appendTo(".install-content")
		jQuery("<label />", {
			"for": "password",
			text: "Password"
		}).appendTo(".input-password");
		var inputUN = jQuery("<input />", {
			type: "password",
			name: "password"
		}).appendTo(".input-password");

		// Email
		jQuery("<div class=\"input-email\"></div>").appendTo(".install-content")
		jQuery("<label />", {
			"for": "email",
			text: "Email"
		}).appendTo(".input-email");
		var inputEM = jQuery("<input />", {
			type: "email",
			name: "email"
		}).appendTo(".input-email");

		this.nextBtn.unbind("click");
		this.nextBtn.click(function() {
			if (inputUN.val() === "") {
				jQuery(".install-error-msg").html("<p>Please specify a valid username and/or password</p>");
				return;
			}
			if (inputPW.val() === "") {
				jQuery(".install-error-msg").html("<p>Please specify a valid username and/or password</p>");
				return;
			}
			if (inputEM.val() === "") {
				jQuery(".install-error-msg").html("<p>Please specify a valid email</p>");
				return;
			}

			self.createAdminUser(inputUN.val(), inputPW.val(), inputEM.val());
		});
	},

	createAdminUser: function(username, password, email) {
		var self = this;

		jQuery.ajax({
			url: "server/ajax.php",
			type: "POST",
			data: "action=create_admin&username=" + username + "&password=" + password + "&email=" + email,
			success: function(data, status) {
				var response = jQuery.parseJSON(data);
				if (response.success) {
					jQuery(".install-error-msg").empty();
					self.selectThemeInterface();
				} else {
					jQuery(".install-error-msg").html("<p>The admin user could not be created. Please correct the fields and click next again.</p>");
				}
			}
		});
	},

	selectThemeInterface: function() {
		var self = this;

		this.nextBtn.unbind("click")

		jQuery(".title").html("Step 3 - Select the theme")
		jQuery(".install-content").html("<p>Select the theme you want for your site. You can change this afterwards, but it will pose a bit of work to arrange your content again.</p>");

		jQuery("<div class=\"select-theme\"></div>").appendTo(".install-content");
		jQuery("<label />", {
			"for": "theme-select-box",
			text: "Themes"
		}).appendTo(".select-theme");
		var selectThemeCB = jQuery("<select />", {
			name: "theme-select-box"
		}).appendTo(".select-theme");

		jQuery("<div class=\"theme-description\"></div>").appendTo(".install-content");

		jQuery.ajax({
			url: "server/ajax.php",
			type: "POST",
			data: "action=select&table=themes",
			success: function(data, status) {
				jQuery("<option value=\"0\">Select your theme</option>").appendTo(selectThemeCB);
				jQuery("<option value=\"0\">-----------------</option>").appendTo(selectThemeCB);

				var response = jQuery.parseJSON(data);
				console.debug(response);
				if (response.success) {
					for (var i = 0; i < response.data.length; i++) {
						opt = jQuery("<option />", {
							value: response.data[i].id,
							text: response.data[i].theme_name
						}).appendTo(selectThemeCB);
					}
				}

				selectThemeCB.change(function() {
					if (jQuery(this).val() !== "0") {
						for (var i = 0; i < response.data.length; i++) {
							if (response.data[i].id === jQuery(this).val()) {
								jQuery(".theme-description").html("<p>" + response.data[i].theme_description + "</p>");
							}
						}

						self.nextBtn.click(function() {
							self.selectTheme(jQuery(selectThemeCB).val());
						});
					} else {
						jQuery(".theme-description").html("<p>Please select a valid theme.</p>");
					}
				});
			}
		});
	},

	selectTheme: function(id) {
		var self = this;

		jQuery.ajax({
			url: "server/ajax.php",
			type: "POST",
			data: "action=insert&table=settings&data={\"layout\":" + id + "}",
			success: function(data, status) {
				console.debug(data);
			}
		})
	}
}
