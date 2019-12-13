(function() {
	var FilePicker = window.FilePicker = function(options) {
		this.apiKey = options.apiKey;
		this.clientId = options.clientId;
		this.buttonEl = options.buttonEl;
		this.onSelect = options.onSelect;
		this.buttonEl.addEventListener('click', this.open.bind(this));
		this.buttonEl.disabled = true;
		gapi.client.setApiKey(this.apiKey);
		gapi.client.load('drive', 'v2', this._driveApiLoaded.bind(this));
		google.load('picker', '1', {
			callback: this._pickerApiLoaded.bind(this)
		});
	}
	FilePicker.prototype = {
		open: function() {
			var token = gapi.auth.getToken();
			if (token) {
				this._showPicker();
				} else {
				this._doAuth(false, function() {
					this._showPicker();
				}.bind(this));
			}
		},
		_showPicker: function() {
			var accessToken = gapi.auth.getToken().access_token;
			var uploadView = new google.picker.DocsUploadView();
			uploadView.setIncludeFolders(true);
			this.picker = new google.picker.PickerBuilder().addView(google.picker.ViewId.DOCS).addView(uploadView).enableFeature(google.picker.Feature.MULTISELECT_ENABLED).setAppId(this.clientId).setOAuthToken(accessToken).setCallback(this._pickerCallback.bind(this)).build().setVisible(true);
		},
		_pickerCallback: function(data) {
			if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
				var files = data.docs;
				if (this.onSelect) {
					this.onSelect(files);
				}
			}
		},
		_pickerApiLoaded: function() {
			this.buttonEl.disabled = false;
		},
		_driveApiLoaded: function() {
			this._doAuth(true);
		},
		_doAuth: function(immediate, callback) {
			gapi.auth.authorize({
				//client_id: this.clientId + '.apps.googleusercontent.com',
				client_id: this.clientId,
				scope: 'https://www.googleapis.com/auth/drive',
				immediate: immediate
			}, callback);
		}
	};
}());