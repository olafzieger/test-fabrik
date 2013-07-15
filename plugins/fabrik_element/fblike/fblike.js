var FbLike = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fblike';
		this.parent(element, options);
		
		FB.Event.subscribe('edge.create', function (response) {
			this.like('+');
		}.bind(this));
		
		FB.Event.subscribe('edge.remove', function (response) {
			this.like('-');
		}.bind(this));
	},

	like: function (dir) {
		var data = {
				'option': 'com_fabrik',
				'format': 'raw',
				'task': 'plugin.pluginAjax',
				'plugin': 'fblike',
				'method': 'ajax_rate',
				'g': 'element',
				'element_id': this.options.elid,
				'row_id': this.options.row_id,
				'elementname': this.options.elid,
				'listid': this.options.listid,
				'direction': dir
			};

		new Request({url: '',
			'data': data,
			onComplete: function (r) {
				r = JSON.decode(r);
				if (r.error) {
					console.log(r.error);
				}
			}.bind(this)
		}).send();
	}
});