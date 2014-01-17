if (typeof d3.chart != "object") d3.chart = {};

var sunburst_color = d3.scale.category20();

Array.prototype.getUnique = function(){
	var u = {}, a = [];
	for(var i = 0, l = this.length; i < l; ++i){
		if(this[i] in u)
			continue;
		if(this[i] == 0)
			continue;
		a.push(this[i]);
		u[this[i]] = 1;
	}
	return a;
}

d3.chart.sunburst = function () {

	var chart = {},
	data= [],
	params= {},
	label= [],
	parent,
	group,
	w, h,
	x, y,
	chartW, chartH,
	duration = 1000,
	margin = [20, 20, 20, 20],
	gap = 30,
	variant = "standard",
	console,chead,cbody;

	chart.parent = function(p,id) {
		if (!arguments.length) return parent.node();	   
		parent = d3.select(p);
		w = parent.node().clientWidth;
		h = parent.node().clientHeight;
		x = -w/2;
		y = -h/2;
		if(d3.ns.prefix.xhtml == parent.node().namespaceURI) {
			parent = parent.append("svg:svg")
				.attr("viewBox", "0 0 "+w+" "+h)
				.attr("preserveAspectRatio", "none");					  
		}
		group = parent.append("svg:g")
			.attr("class", "group")
			.attr("id", "group"+id);
		group.append("svg:rect")
		.attr("class", "panel")
		.call(resize);

		chart.console();

		return chart;
	};

	chart.console = function() {
		var r = w / 2 - margin[0];

		console = group
			.append("svg:path")
			.attr("d",d3.svg.arc()
				.startAngle(0)
				.endAngle(360)
				.innerRadius(0)
				.outerRadius(2*r/3)
			)
			.attr("class", "sunburst-middle")
			.style("fill", "#fff")
			.style("opacity", "1.0");

		chead = group
			.append("svg:text")
			.attr("class", "console-amt")
			.attr("text-anchor", "middle")
			.attr("transform", "translate(0,-6)")
			.text("Total:")
			.style("opacity", "0.5");

		cbody = group
			.append("svg:text")
			.attr("class", "console-val")
			.attr("text-anchor", "middle")
			.attr("transform", "translate(0,10)")
			.text("0.00€")
			.style("opacity", "0.5");
	}

	chart.margin = function(m) {
		if (!arguments.length) return margin;
		margin = m;
		group.select("rect.panel")
			.call(resize);
		return redraw();
	};

	chart.size = function(s) {
		if (!arguments.length) return [w, h];
		w = s[0];
		h = s[1];
		group.select("rect.panel")
			.call(resize);
		return redraw();
	};

	chart.position = function(p, other) {
		if (!arguments.length) return [x, y];
		if(typeof p == "string") {
			var otherPos = other.position();
			var otherSize = other.size();	
			if(p == "after") {
				x = otherPos[0]+otherSize[0];
				y = otherPos[1]+otherSize[1]-h;
			}else if(p == "under") {
				x = otherPos[0];
				y = otherPos[1]+otherSize[1];
			}
		}else{
			x = p[0];
			y = p[1];
		}
		group.select("rect.panel")
			.call(resize);
		return redraw();
	};

	chart.transition = function(d) {
		if (!arguments.length) return duration;
		duration = d;
		return redraw();
	};

	chart.data = function(d) {
		if (!arguments.length) return data;
		data = d;
		return redraw();
	};

	chart.params = function(d) {
		if (!arguments.length) return params;
		params = d;
		return redraw();
	};

	chart.gap = function(g) {
		if (!arguments.length) return gap;
		gap = g;
		return redraw();
	};

	chart.variant = function(v) {
		if (!arguments.length) return variant;
		variant = v;
		return redraw();
	};

	function resize() {
		chartW = w - margin[1] - margin[3];
		chartH = h - margin[0] - margin[2];
		this.attr("width", chartW)
			.attr("height", chartH)
			.attr("x", x+margin[3])
			.attr("y", y+margin[0]);
		return chart;
	}

	function redraw() {
		chart.tochart();		  
		return chart;
	};

	chart.tochart = function() {
		var r = w / 2 - margin[0];

		var yScale = d3.scale.linear().domain([0, d3.max(data)+h/100]).range([chartH, 0]);
		var xScale = d3.scale.linear().domain([0, data.length]).range([0, chartW]);
		var gapW = chartW/data.length*(gap/100);
		
		var markX = function(d, i) {return x+xScale(i)+margin[3]+gapW/2;};
		var markY = function(d, i) {return y+yScale(d)+margin[0];};
		var markW = chartW/data.length-gapW;
		var markH = function(d, i) {return chartH-yScale(d);};

		group.attr("transform", "translate("+(r+margin[0])+","+(r+margin[1])+")");

		if ( data.length > 0 ) {
			drawSVG(markX, markY, markW, markH);
		}

		return chart;
	};

	function drawSVG(markX, markY, markW, markH) {
		var r = w / 2 - margin[0],
		format = d3.time.format("%Y-%m-%d %X"),
		selector = "#"+group.attr("id");

		var partition = d3.layout.partition()
			.sort(function(a,b) { return b.values-a.values; })
			.size([2 * Math.PI, r * r])
			.value(function(d) { return d.values; })
			.children(function (d) { return d.values; });

		var arc = d3.svg.arc()
			.startAngle(function(d) { return d.x; })
			.endAngle(function(d) { return (d.x + d.dx)-0.01; })
			.innerRadius(function(d) { return Math.sqrt(d.y)+1; })
			.outerRadius(function(d) { return Math.sqrt(d.y + d.dy+1); });

		var pre = new Object;
		pre.key = 0;
		pre.values = d3.nest()
			.key(function(d) { return d.group; })
			.rollup(function(v) {
				return d3.nest()
					.key(function(d) { return d.plan; })
					.rollup(function(v) { return Math.max(d3.sum(v.map(function(d) { return d.amount; })), 0); })
					.entries(v);
			})
			.entries(data);

		var path = group.data([pre]).selectAll("path")
			.data(partition.nodes).enter()
			.append("svg:path")
				.attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
				.attr("d", arc)
				.attr("fill-rule", "evenodd")
				.style("opacity", "0.3")
				.style("stroke", "#fff")
				.style("stroke-width", "0")
				.style("fill", function(d) { return sunburst_color(( (typeof d.values != 'object') ? d.parent : d).key); });

		path.transition().ease("bounce")
				.delay(function(d, i) { return (i * 50); })
				.duration(300)
				.style("opacity", function(d) { return (typeof d.values != 'object') ? "0.6" : "0.9"; });

		path.on("mouseover", function(d) {
				if (typeof d.values != 'object') {
					name = plan_names[d.key] + ": ";
					amount = amount_format(d.values) + amount_currency; 
				} else {
					name = group_names[d.key] + ": ";
					amount = amount_format(d3.sum(d.values.map(function(v) { return v.values; }))) + amount_currency;
				}
				d3.select(this)
					.transition().ease("cubic-out").duration(500)
					.style("opacity", "1.0")
					.style("stroke-width", "1");
				d3.select(selector+" .console-amt")
					.style("opacity", "0.2")
					.text(name)
					.transition().ease("cubic-out").duration(500)
					.style("opacity", "1.0");
				d3.select(selector+" .console-val")
					.style("opacity", "0.2")
					.text(amount)
					.transition().ease("cubic-out").duration(500)
					.style("opacity", "1.0");
			})
			.on("mouseout", function() {
				d3.select(this)
					.transition().ease("cubic-out").duration(500)
					.style("opacity", function(d) { return (typeof d.values != 'object') ? "0.6" : "0.9"; })
					.style("stroke-width", "0");

				center_console();
			})
			;

		var total = d3.sum(data, function(v) { return v.amount; });

		var center_console = function() {
			chead
			.style("opacity", "0.2")
			.text("Total:")
			.transition().ease("cubic-out").duration(500)
			.style("opacity", "1.0");

			cbody
			.style("opacity", "0.2")
			.text(amount_format(total) + amount_currency)
			.transition().ease("cubic-out").duration(500)
			.style("opacity", "1.0");
			}

		center_console();
	}

	return chart;
};

d3.chart.cellular = function () {

	var chart = {},
	data= [],
	params= {},
	label= [],
	parent,
	group,
	w, h,
	x, y,
	chartW, chartH,
	duration = 1000,
	margin = [20, 20, 20, 20],
	gap = 30,
	variant = "standard";

	var ccolor = d3.scale.quantize()
		.domain([0, avg_sale])
		.range(d3.range(9));

	var z = 14,
		day = d3.time.format("%w"),
		week = d3.time.format("%U"),
		month = d3.time.format("%m"),
		mname = d3.time.format("%B");

	chart.parent = function(p,id) {
		if (!arguments.length) return parent.node();	   
		parent = d3.select(p);
		w = parent.node().clientWidth;
		h = parent.node().clientHeight;
		x = 0;
		y = 0;
		if(d3.ns.prefix.xhtml == parent.node().namespaceURI) {
			parent = parent.append("svg:svg")
				.attr("viewBox", "0 0 "+w+" "+h)
				.attr("preserveAspectRatio", "none");					  
		}
		group = parent.append("svg:g")
			.attr("class", "group svg-crisp")
			.attr("id", "group"+id);
		group.append("svg:rect")
		.attr("class", "panel")
		.call(resize);

		return chart;
	};

	chart.margin = function(m) {
		if (!arguments.length) return margin;
		margin = m;
		group.select("rect.panel")
			.call(resize);
		return redraw();
	};

	chart.size = function(s) {
		if (!arguments.length) return [w, h];
		w = s[0];
		h = s[1];
		group.select("rect.panel")
			.call(resize);
		return redraw();
	};

	chart.position = function(p, other) {
		if (!arguments.length) return [x, y];
		if(typeof p == "string") {
			var otherPos = other.position();
			var otherSize = other.size();	
			if(p == "after") {
				x = otherPos[0]+otherSize[0];
				y = otherPos[1]+otherSize[1]-h;
			}else if(p == "under") {
				x = otherPos[0];
				y = otherPos[1]+otherSize[1];
			}
		}else{
			x = p[0];
			y = p[1];
		}
		group.select("rect.panel")
			.call(resize);
		return redraw();
	};

	chart.transition = function(d) {
		if (!arguments.length) return duration;
		duration = d;
		return redraw();
	};

	chart.data = function(d) {
		if (!arguments.length) return data;
		data = d;
		return redraw();
	};

	chart.params = function(d) {
		if (!arguments.length) return params;
		params = d;
		return redraw();
	};

	chart.gap = function(g) {
		if (!arguments.length) return gap;
		gap = g;
		return redraw();
	};

	chart.variant = function(v) {
		if (!arguments.length) return variant;
		variant = v;
		return redraw();
	};

	function resize() {
		chartW = w - 4;
		chartH = h - 4;
		this.attr("width", chartW)
			.attr("height", chartH)
			.attr("x", x+margin[3])
			.attr("y", y+margin[0]);
		return chart;
	}

	function redraw() {
		chart.tochart();		  
		return chart;
	};

	chart.tochart = function() {
		var yScale = d3.scale.linear().domain([0, d3.max(data)+h/100]).range([chartH, 0]);
		var xScale = d3.scale.linear().domain([0, data.length]).range([0, chartW]);
		var gapW = chartW/data.length*(gap/100);
		
		var markX = function(d, i) {return x+xScale(i)+margin[3]+gapW/2;};
		var markY = function(d, i) {return y+yScale(d)+margin[0];};
		var markW = chartW/data.length-gapW;
		var markH = function(d, i) {return chartH-yScale(d);};

		if ( data.length > 0 ) {
			drawSVG(markX, markY, markW, markH);
		}

		return chart;
	};

	function drawSVG(markX, markY, markW, markH) {
		var format = d3.time.format("%Y-%m-%d");

		var numyear = data[0].date.getFullYear();

		var year = group.selectAll("g.year")
			.data([numyear])
			.enter()
			.append("svg:g")
			.attr("class", "year y-"+numyear+" RdYlGn")
			.attr("transform", "translate(" + (x+margin[3]) + "," + (y+margin[0]) + ")")
			.style("opacity", "1.0");

		var nsales = d3.nest()
			.key(function(d) { return format(d.date); })
			.rollup(function(v) { return d3.sum(v.map(function(d) { return d.amount; })); })
			.map(data);

		year.selectAll("rect.day")
			.data(d3.time.days(new Date(numyear, 0, 1), new Date(numyear + 1, 0, 1)))
			.enter()
			.append("svg:rect")
				.attr("class", function(d) { return "day q" + ccolor(nsales[format(d)]) + "-9 bstooltip"; })
				.attr("ry", 0).attr("rx", 0)
				.attr("rel", "tooltip")
				.attr("data-original-title", function(d) { return format(d) + (format(d) in nsales ? ": " + amount_format(nsales[format(d)]) + amount_currency : ""); })
				.attr("width", 1).attr("height", 1)
				.attr("x", function(d) { return (week(d) * z)+z/2; })
				.attr("y", function(d) { return (day(d) * z)+z/2; })
				.on("mouseover", function(){
					d3.select(this)
						.transition().ease("elastic").duration(500)
							.attr("ry", z/3.33).attr("rx", z/3.33).attr("width", z-2).attr("height", z-2)
							.attr("x", function(d) { return (week(d) * z)+1; })
							.attr("y", function(d) { return (day(d) * z)+1; });
				})
				.on("mouseout", function(){
					d3.select(this)
						.transition().ease("bounce").delay(100).duration(500)
							.attr("ry", 0).attr("rx", 0).attr("width", z).attr("height", z)
							.attr("x", function(d) { return week(d) * z; })
							.attr("y", function(d) { return day(d) * z; });
				});
		
		// Eye-Candy fade-in
		year.selectAll("rect.day")
			.transition().ease("bounce")
			.delay(function(d, i) { return nsales[format(d)] ? (i * (8-ccolor(nsales[format(d)])))+(Math.random()*8) : 0; })
			.duration(600)
			.attr("width", z).attr("height", z)
			.attr("ry", 0).attr("rx", 0)
			.attr("x", function(d) { return week(d) * z; })
			.attr("y", function(d) { return day(d) * z; });

		year.selectAll("path.month")
			.data(d3.time.months(new Date(numyear, 0, 1), new Date(numyear+1, 0, 1)))
			.enter()
			.append("svg:path")
				.attr("class", "month")
				.attr("d", monthPath);

		var msales = d3.nest()
			.key(function(d) { return month(d.date); })
			.rollup(function(v) { return d3.sum(v.map(function(d) { return d.amount; })); })
			.map(data);

		var mcolor = d3.scale.quantize()
			.domain([0, avg_sale*30])
			.range(d3.range(9));

		year.selectAll("rect.month")
			.data(d3.time.months(new Date(numyear, 0, 1), new Date(numyear+1, 0, 1)))
			.enter()
			.append("svg:rect")
				.attr("class", function(d) { return "month q" + mcolor(msales[month(d)]) + "-9 bstooltip"; })
				.attr("ry", 0).attr("rx", 0)
				.attr("x", function(d) { return ( week(d) * z ) + 1; })
				.attr("y", 7*z+3)
				.attr("width", monthWidth )
				.attr("height", z)
				.attr("rel", "tooltip")
				.attr("stroke", "rgba(0, 0, 0, 0)").attr("stroke-width", "2px")
				.attr("data-original-title", function(d) { return mname(d) + " " + numyear + (month(d) in msales ? ": " + amount_format(msales[month(d)]) + amount_currency : ": 0.00€"); })
				.on("mouseover", function(){
					d3.select(this)
						.transition().duration(200)
							.attr("stroke", "rgba(0, 0, 0, 0.7)");
				})
				.on("mouseout", function(){
					d3.select(this)
						.transition().duration(400)
							.attr("stroke", "rgba(0, 0, 0, 0)");
				});

		jQuery('svg').tooltip({placement: "top", selector: '.bstooltip', delay: { show: 300, hide: 100 }});
	}

	function monthPath(t0) {
		var t1 = new Date(t0.getUTCFullYear(), t0.getUTCMonth() + 1, 0),
			d0 = +day(t0), w0 = +week(t0),
			d1 = +day(t1), w1 = +week(t1);
		return "M" + (w0 + 1) * z + "," + d0 * z
			+ "H" + w0 * z + "V" + 7 * z
			+ "H" + w1 * z + "V" + (d1 + 1) * z
			+ "H" + (w1 + 1) * z + "V" + 0
			+ "H" + (w0 + 1) * z + "Z";
	}

	function monthWidth(t0) {
		var x = new Date(t0.getUTCFullYear(), t0.getUTCMonth()+2, 1);

		if ( x.getUTCMonth() == 11 ) {
			return ( 53 - week(t0) ) * z - 2;
		} else {
			var t1 = new Date(t0.getUTCFullYear(), t0.getUTCMonth() + 2, 1);

			return ( week(t1) - week(t0) ) * z - 2;
		}
	}

	return chart;
};

d3.chart.rickshaw = function () {

	var chart = {},
	has_graph = false,
	data = [],
	params = {},
	label = [],
	parent,
	element,
	group,
	w, h,
	x, y,
	chartW, chartH,
	duration = 1000,
	margin = [20, 20, 20, 20],
	gap = 30,
	variant = "standard";

	var ccolor = d3.scale.quantize()
		.domain([0, avg_sale])
		.range(d3.range(9));

	chart.parent = function(p,id) {
		if (!arguments.length) return parent.node();
		element = p;
		parent = d3.select(p);
		w = parent.node().clientWidth;
		h = parent.node().clientHeight;
		x = 0;
		y = 0;

		return chart;
	};

	chart.margin = function(m) {
		if (!arguments.length) return margin;
		margin = m;
		return redraw();
	};

	chart.size = function(s) {
		if (!arguments.length) return [w, h];
		w = s[0];
		h = s[1];
		return redraw();
	};

	chart.position = function(p, other) {
		if (!arguments.length) return [x, y];
		if(typeof p == "string") {
			var otherPos = other.position();
			var otherSize = other.size();	
			if(p == "after") {
				x = otherPos[0]+otherSize[0];
				y = otherPos[1]+otherSize[1]-h;
			}else if(p == "under") {
				x = otherPos[0];
				y = otherPos[1]+otherSize[1];
			}
		}else{
			x = p[0];
			y = p[1];
		}
		return redraw();
	};

	chart.transition = function(d) {
		if (!arguments.length) return duration;
		duration = d;
		return redraw();
	};

	chart.data = function(d) {
		if (!arguments.length) return data;
		data = d;
		return redraw();
	};

	chart.params = function(pa) {
		if (!arguments.length) return params;
		params = pa;
		return redraw();
	};

	chart.gap = function(g) {
		if (!arguments.length) return gap;
		gap = g;
		return redraw();
	};

	chart.variant = function(v) {
		if (!arguments.length) return variant;
		variant = v;
		return redraw();
	};

	function resize() {
		chartW = w - 4;
		chartH = h - 4;
		this.attr("width", chartW)
			.attr("height", chartH)
			.attr("x", x+margin[3])
			.attr("y", y+margin[0]);
		return chart;
	}

	function redraw() {
		chart.tochart();		  
		return chart;
	};

	chart.tochart = function() {
		if ( data.length > 0 ) {
			drawSVG();
		}

		return chart;
	};

	function drawSVG() {
		var z = 14,
		day = d3.time.format("%Y-%m-%w"),
		week = d3.time.format("%Y-%U"),
		month = d3.time.format("%Y-%m"),
		monthn = d3.time.format("%b %Y"),
		year = d3.time.format("%Y"),
		mname = d3.time.format("%B"),
		hour = d3.time.format("%H"),
		format = d3.time.format("%Y-%m-%d");
		formatw = d3.time.format("%Y-%m");

		var groups = data.map(function(d){ return Number( d.group ) } ).getUnique();

		if ( params.unit == "day" ) {
			params.end.setDate(params.end.getDate()+1);
			params.end.setHours(0,0,0,0);

			var units = d3.time.days(params.start, params.end);

			var mdata = d3.nest()
				.key(function(d) { return d.group; })
				.rollup(function(v) {
					return d3.nest()
					.key(function(d) { return format(d.date); })
					.rollup(function(d) { return d3.sum(d.map(function(di) { return di.amount; })); })
					.map(v);
				})
				.map(data);

			var gety = function( mdata, xdate, xgroup ){ return ( typeof mdata[xgroup][format(xdate)] == 'undefined' ) ? 0 : mdata[xgroup][format(xdate)]  };
		} else if ( params.unit == "week" ) {
			params.end.setDate(params.end.getDate()+1);
			params.end.setHours(0,0,0,0);

			var units = d3.time.weeks(params.start, params.end);

			var mdata = d3.nest()
				.key(function(d) { return d.group; })
				.rollup(function(v) {
				return d3.nest()
					.key(function(d) { return week(d.date); })
					.rollup(function(d) { return d3.sum(d.map(function(di) { return di.amount; })); })
					.map(v);
				})
				.map(data);

			var gety = function( mdata, xdate, xgroup ){ return ( typeof mdata[xgroup][week(xdate)] == 'undefined' ) ? 0 : mdata[xgroup][week(xdate)]  };
		} else if ( params.unit == "month" ) {
			params.end.setDate(params.end.getDate()+1);
			params.end.setHours(0,0,0,0);

			var units = d3.time.months(params.start, params.end);

			var mdata = d3.nest()
				.key(function(d) { return d.group; })
				.rollup(function(v) {
				return d3.nest()
					.key(function(d) { return month(d.date); })
					.rollup(function(d) { return d3.sum(d.map(function(di) { return di.amount; })); })
					.map(v);
				})
				.map(data);

			var gety = function( mdata, xdate, xgroup ){ return ( typeof mdata[xgroup][month(xdate)] == 'undefined' ) ? 0 : mdata[xgroup][month(xdate)]  };
		} else if ( params.unit == "year" ) {
			params.end.setDate(params.end.getDate()+1);
			params.end.setHours(0,0,0,0);

			var units = d3.time.years(params.start, params.end);

			var mdata = d3.nest()
				.key(function(d) { return d.group; })
				.rollup(function(v) {
				return d3.nest()
					.key(function(d) { return year(d.date); })
					.rollup(function(d) { return d3.sum(d.map(function(di) { return di.amount; })); })
					.map(v);
				})
				.map(data);

			var gety = function( mdata, xdate, xgroup ){ return ( typeof mdata[xgroup][year(xdate)] == 'undefined' ) ? 0 : mdata[xgroup][year(xdate)]  };
		} else if ( params.unit == "hour" ) {
			params.end.setDate(params.end.getDate()+1);
			params.end.setHours(0,0,0,0);

			var units = d3.time.hours(params.start, params.end);

			var mdata = d3.nest()
				.key(function(d) { return d.group; })
				.rollup(function(v) {
					return d3.nest()
					.key(function(d) { return hour(d.date); })
					.rollup(function(d) { return d3.sum(d.map(function(di) { return di.amount; })); })
					.map(v);
				})
				.map(data);

			var gety = function( mdata, xdate, xgroup ){ var xx2 = new Date();

			return ( typeof mdata[xgroup][hour(xdate)] == 'undefined' ) ? 0 : mdata[xgroup][hour(xdate)] };
		}

		var series = groups.map( function(v) { return {
													groupid: v,
													name: group_names[v],
													color: sunburst_color(v),
													data: units.map( function(dv) { return {x:dv.getTime()/1000,y:gety(mdata, dv, v)};	} )
												};
									}
								);

		if ( typeof params.renderer == 'undefined' ) {
			params.renderer = 'bar';
		}

		if ( has_graph ) {
			jQuery(element).children().remove();
			jQuery("#legend ul").remove();
		}

		var graph = new Rickshaw.Graph( {
			element: document.querySelector(element),
			renderer: params.renderer,
			series: series
		} );

		if ( typeof params.offset != 'undefined' ) {
			var config = {
					renderer: params.renderer,
					interpolation: params.interpolation
				};

			if (params.offset == 'value') {
				config.unstack = true;
				config.offset = 'zero';
			} else {
				config.unstack = false;
				config.offset = params.offset;
			}

			graph.configure(config);
		}

		graph.render();

		has_graph = true;

		if ( typeof params.legend == 'undefined' ) {
			params.legend = false;
		}

		if ( params.legend ) {
			var legend = new Rickshaw.Graph.Legend( {
				graph: graph,
				element: document.getElementById('legend')
			} );

			var shelving = new Rickshaw.Graph.Behavior.Series.Toggle( {
				graph: graph,
				legend: legend
			} );

			var order = new Rickshaw.Graph.Behavior.Series.Order( {
				graph: graph,
				legend: legend
			} );

			var highlighter = new Rickshaw.Graph.Behavior.Series.Highlight( {
				graph: graph,
				legend: legend
			} );
		}

		if ( params.unit == "week" ) {
			var xFormatter = function(d){ return "Week " + week( new Date( d * 1000 ) ); };
		} else if ( params.unit == "month" ) {
			var xFormatter = function(d){ return monthn( new Date( d * 1000 ) ); };
		} else if ( params.unit == "year" ) {
			var xFormatter = function(d){ return + year( new Date( d * 1000 ) ); };
		} else if ( params.unit == "hour" ) {
			var xFormatter = function(d){ return hour( new Date( d * 1000 ) ) + ":00"; };
		} else {
			var xFormatter = function(d){ return format( new Date( d * 1000 ) ); };
		}

		if ( typeof params.axes_time == 'undefined' ) {
			params.axes_time = true;
		}

		if ( params.axes_time ) {
			var hoverDetail = new Rickshaw.Graph.HoverDetail( {
				graph: graph,
				xFormatter: xFormatter
			} );

			var xAxis = new Rickshaw.Graph.Axis.Time( {graph: graph, ticks:1, ticksTreatment:'xtick-shifted-'+params.unit } );

			xAxis.render();
		}

		var yAxis = new Rickshaw.Graph.Axis.Y( {graph: graph} );

		yAxis.render();

		if ( !params.axes_time ) {
			d3.selectAll(element+" g.y_ticks").attr("transform", "translate(85,0)");
		}

		if ( params.legend ) {
			var controls = new RenderControls( {
				element: document.querySelector('#adminForm'),
				graph: graph
				} ); 
		}
	}

	return chart;
};

var RenderControls = function(args) {

	this.initialize = function() {

		this.element = args.element;
		this.graph = args.graph;
		this.settings = this.serialize();

		this.inputs = {
			renderer: this.element.elements.renderer,
			interpolation: false,
			offset: this.element.elements.offset
		};

		that = this;

		jQuery("#adminForm input").change( function(e) {
			e.stopImmediatePropagation();

			that.settings = that.serialize();

			var fallback = false;
			if (e.target.name == 'renderer' ) {
				fallback = that.setDefaultOffset(e.target.value);
			}

			if ( ( e.target.name == 'unit' ) || 
					( ( e.target.name == 'offset' ) && ( that.graph.offset == 'expand' ) && ( that.settings.offset != 'expand' ) ) ||
					( ( e.target.name == 'renderer' ) && ( that.graph.offset == 'expand' ) && fallback )
				) {

				var options = that.rendererOptions[that.settings.renderer];

				cf.update({	unit: that.settings.unit,
							renderer: that.settings.renderer,
							interpolation: that.settings.interpolation,
							offset: fallback ? options.defaults.offset : that.settings.offset });

				return;
			}

			that.syncOptions();
			that.settings = that.serialize();

			var config = {
				renderer: that.settings.renderer,
				interpolation: that.settings.interpolation
			};

			if (that.settings.offset == 'value') {
				config.unstack = true;
				config.offset = 'zero';
			} else {
				config.unstack = false;
				config.offset = that.settings.offset;
			}

			that.graph.configure(config);
			that.graph.render();

		});
	}

	this.serialize = function() {

		var values = {};
		var pairs = jQuery(this.element).serializeArray();

		pairs.forEach( function(pair) {
			values[pair.name] = pair.value;
		} );

		return values;
	};

	this.syncOptions = function() {

		var options = this.rendererOptions[this.settings.renderer];

		Array.prototype.forEach.call(this.inputs.interpolation, function(input) {

			if (options.interpolation) {
				input.disabled = false;
				input.parentNode.classList.remove('disabled');
			} else {
				input.disabled = true;
				input.parentNode.classList.add('disabled');
			}
		});

		Array.prototype.forEach.call(this.inputs.offset, function(input) {

			if (options.offset.filter( function(o) { return o == input.value } ).length) {
				input.disabled = false;
				input.parentNode.classList.remove('disabled');

			} else {
				input.disabled = true;
				input.parentNode.classList.add('disabled');
			}

		}.bind(this));

	};

	this.setDefaultOffset = function(renderer) {

		var options = this.rendererOptions[renderer];	

		var ret = false;
		if (options.defaults && options.defaults.offset && ( jQuery.inArray( this.settings.offset, options.offset ) == -1 )) {

			Array.prototype.forEach.call(this.inputs.offset, function(input) {
				if (input.value == options.defaults.offset) {
					input.checked = true;
				} else {
					input.checked = false;
				}

			}.bind(this));
			
			ret = true;
		}

		return ret;
	};

	this.rendererOptions = {

		area: {
			interpolation: true,
			offset: ['zero', 'wiggle', 'expand', 'value'],
			defaults: { offset: 'zero' }
		},
		line: {
			interpolation: true,
			offset: ['value'],
			defaults: { offset: 'value' }
		},
		bar: {
			interpolation: false,
			offset: ['zero', 'expand', 'value'],
			defaults: { offset: 'zero' }
		},
		scatterplot: {
			interpolation: false,
			offset: ['value'],
			defaults: { offset: 'value' }
		}
	};

	this.initialize();
};

