window.addEvent('domready', function() {
	/* Select a file */
	var el = $('remote_file');

	var btn = $('j2xml-open');

	var div, manager2;
	var complete = function(path, file) {
		el.set('value', path);
		if(div) div.destroy();
		var icon = new Asset.image(this.assetBasePath+'Images/cancel.png', {
			'class': 'file-cancel', title: 'deselect'}).addEvent('click', function(e){
			e.stop();
			el.set('value', '');
			var self = this;
			div.fade(0).get('tween').chain(function(){
				div.destroy();
				manager2.tips.hide();
				manager2.tips.detach(self);
			});
		});
		manager2.tips.attach(icon);

		div = new Element('div', {
			'class': 'selected-file', text: 'Remote file: '}).adopt(
			new Asset.image(file.icon, {
				'class': 'mime-icon'}),
			new Element('span', {
			text: file.name}),
			icon
			).inject(el, 'after');
	};

	var els = document.getElementsByTagName('input');
	var token = 'token';
	for (var i = 0; i < els.length; i++) {
		if ((els[i].type == 'hidden') && (els[i].name.length == 32) && els[i].value == '1') {
			token = els[i].name;
			break;
		}
	}
	
	manager2 = new FileManager({
		url: 'index.php?option=com_j2xml&task=file.select&format=json&'+token+'=1',
		language: 'en',
		filter: ['application/x-gzip','text/xml','application/xml'],
		hideOnClick: true,
		assetBasePath: '../media/lib_filemanager/',
		uploadAuthData: {
			session: 'MySessionId'},
			selectable: true,
			upload: false,
			destroy: false,
			preview: false,
			rename: false,
			createFolders: false,
			onComplete: complete
	});

	el.setStyle('display', 'none');
	var val = el.get('value');
	if(val) complete.apply(manager2, [val, {
		name: val.split('/').getLast(),
		icon: '../media/lib_filemanager/Images/Icons/'+val.split('.').getLast()+'.png'
	}]);

	btn.addEvent('click', manager2.show.bind(manager2));
});