if (typeof d3.chart != "object") d3.chart = {};

d3.chart.factory = function () {
	var factory = {},
	p = 0,
	data = [],
	queue = [],
	charts = [],
	start = true,
	datef = d3.time.format("%Y-%m-%d %X"),
	s,e,
	rangeStart,
	rangeEnd,
	request,
	selector,
	w,h,d,m;

	factory.source = function(s) {
		request_fullurl = request_url+"&type="+s;

		return factory;
	};

	factory.range = function(start,end){
		s = datef.parse(start);
		e = datef.parse(end);

		if ( !rangeStart ) {
			rangeStart = s;
			rangeEnd = e;
		}

		return factory;
	};

	factory.canvas = function(width, height, margin) {
		w = width;
		h = height;
		m = margin;

		return factory;
	};

	factory.target = function(sel) {
		selector = sel;

		p++;

		return factory;
	};

	factory.create = function(t, param) {
		queue.push({start:s,end:e,width:w,height:h,margin:m,target:selector,pos:p,type:t,parameters:param});

		if ( start ) {
			start = false;

			factory.triggerqueue();
		}

		return factory;
	};

	factory.update = function(param) {
		if ( typeof param == 'undefined' ) {
			param = {};
		}

		if ( typeof param.start != 'undefined' ) {
			param.start = datef.parse(param.start);
			param.end = datef.parse(param.end);
		} else {
			var range = jQuery('.jqui-daterangepicker').val();

			if ( range.indexOf(" - ") === false ) {
				var rangestart = range;
				var rangeend = range;
			} else {
				var rangestart = range.slice(0, 10);
				var rangeend = range.slice(13);
			}

			param.start = datef.parse(rangestart+" 00:00:00");
			param.end = datef.parse(rangeend+" 23:59:59");
		}

		newparams = charts[selector].params();

		for (var attrname in param) { newparams[attrname] = param[attrname]; }

		newparams.update = selector;

		queue.push({start:s,end:e,width:w,height:h,margin:m,target:selector,pos:p,parameters:newparams});

		jQuery(".jqui-loading").html("Loading Data...");
		if ( start ) {
			start = false;

			factory.triggerqueue();
		}

		return factory;
	};

	factory.triggerqueue = function() {
		if ( queue.length ) {
			factory.getData(queue.shift());
		} else {
			start = true;
			jQuery(".jqui-loading").html("");
		}
	};

	factory.getData = function(request) {
		if ( data.length < 1 ) {
			factory.requestData(function(json) { factory.acquireData(json, request); }, request.start, request.end);
		} else if ( ( request.start >= rangeStart ) && ( request.end <= rangeEnd ) ) {
			factory.doCallback(request);
		} else if ( request.start < rangeStart ) {
			if ( ( request.end > rangeEnd ) ) {

				factory.requestData(
						function(json) {
							factory.requestData( function(jsonf) { factory.acquireData(json.concat(jsonf), request); }, rangeEnd, request.end );
						}, request.start, rangeStart
				);
	
			} else { 
				factory.requestData(function(json) { factory.acquireData(json, request); }, request.start, rangeStart);
			}
		} else if ( request.end > rangeEnd ) {
			factory.requestData(function(json) { factory.acquireData(json, request); }, rangeEnd, request.end);
		}
	};

	factory.requestData = function(callback, start, end) {

		var dstart = datef(start);

		var dend = datef(end);
		factory.json(request_fullurl+"&start="+encodeURI(dstart)+"&end="+encodeURI(dend), callback);
	};

	factory.json = function(url, callback) {
		var req = new XMLHttpRequest;

		if (arguments.length < 2) callback = "application/json";
		else if ("application/json" && req.overrideMimeType) req.overrideMimeType("application/json");

		req.open("GET", url, true);
		req.onreadystatechange = function() {
			if (req.readyState === 4) {
				factory.dequeue((req.status < 300 ? JSON.parse(req.responseText) : null), callback)
			}
		};

		req.send(null);
	};

	factory.acquireData = function(json, request) {
		for (i=0; i<json.length; i++) {
			if ( typeof json[i] != 'undefined' ) {
				json[i].date = datef.parse(json[i].date);
				json[i].tstamp = json[i].date.getTime();
				data.push(json[i]);
			}
		}

		if ( request.start < rangeStart ) {
			request.start.setHours(0,0,0,0);
			rangeStart = request.start;
		}

		if ( request.end > rangeEnd ) {
			request.end.setHours(23,59,59,999);
			rangeEnd = request.end;
		}

		factory.doCallback(request);
	};

	factory.mergeData = function(json, callback) {
		for (i=0; i<json.length; i++) {
			if ( typeof json[i] != 'undefined' ) {
				json[i].date = datef.parse(json[i].date);
				json[i].tstamp = json[i].date.getTime();
				data.push(json[i]);
			}
		}

		callback;
	};

	factory.doCallback = function(request) {
		var selection = data.filter( function(x){ return (x.date >= request.start) && (x.date <= request.end); });

		if ( typeof request.parameters == 'undefined' ) {
			request.parameters = {};
		}

		if ( typeof request.parameters.start == 'undefined' ) {
			request.parameters.start = request.start;
			request.parameters.end = request.end;
		}

		if ( typeof request.parameters.update == 'undefined' ) {
			charts[request.target] = d3.chart[request.type]()
			.parent(request.target, request.pos)
			.margin([request.margin, request.margin, request.margin, request.margin])
			.size([request.width, request.height])
			.params(request.parameters)
			.data(selection);
		} else {
			charts[request.parameters.update].params(request.parameters).data(selection);
		}

		factory.triggerqueue();
	};

	factory.dequeue = function(data, callback) {
		callback(data);
	};

	return factory;
}
