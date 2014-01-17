/*Text*/
var lang ={};
lang.NotFound = "No se encontraron cursos";
lang.IncompleteProfileHead = "Perfil incompleto, por favor complete los siguientes datos en su pefil para poder continuar:";
lang.Name = "Nombre";
lang.Lastname = "Apellido";
lang.Email = "Correo electronico";
lang.Id = "Cedula";
lang.GoToProfile = "Click aqui para ir a modificar su perfil";
lang.TakeCourse = "Inscribirse";
lang.Accept ="Aceptar";
lang.Cancel = "Cancelar";
lang.Forums ="Foros";
lang.Podcast = "Podcast";
lang.Webcast = "Webcast";
lang.Blogs = "Blogs";
lang.Wiki = "Wiki";
lang.Group = "Grupo";
lang.AvailableResources = "Recursos disponibles para este curso:";
lang.TestYourself = "Pruebate!";
lang.TakeTest = "Ver Curso!";
lang.PrintCertificate = "Imprimir certificado";
lang.NotRequirements = "En este momento no cumples con los requerimientos para matricular este curso.";
lang.SuccessfulInscription = "Se ha inscrito en el curso {courseName} con exito";
lang.goToCoursePage = "Ir a la pagina del curso!";
lang.CoursePageNotAvailable = "La pagina del curso no esta disponible aun.";
/********************/
/*Static URL*/
var url={};
url.Profile = "index.php?option=com_comprofiler&task=userDetails";
url.Service = "components/com_comprofiler/plugin/user/plug_kmecertificates/proxy.php";
url.Service2 = "components/com_comprofiler/plugin/user/plug_kmecertificates/kmews.php";
url.TOS = "http://training.mykme.com/Instancias_2_5_0/borrador/terminosCondiciones.txt";
url.CertificatePrinter = "http://training.mykme.com/WK/Kme/forms/formCertificarse?pI={instance}&pL={user}&pC={code}";
url.TakeExam = "http://training.mykme.com/WK/Kme/forms/formIngresoCursos?pI={instance}&pL={user}&pC={code}";
url.TestYourself = "http://training.mykme.com/WK/Kme/forms/formIngresoCursos?pI={instance}&pL={user}&pC={code}&pO=1";
/********************/
/*Courses States*/
var courseStates ={};
courseStates.registered = "inscrito";
courseStates.approved = "aprobado";
/********************/
/*Main configuration*/
var proxy = new Proxy(url.Service);
var kmeWS = new Proxy(url.Service2);
var bdInstance = 'pwc';
var content = "#contentkme";
var preloader = document.createElement("div");
$(preloader).addClass("preloader");
/*******************/
/*Specific object constructors*/
function Course(data, opts){
    var opt = {showProgress:opts && opts.showProgress?opts.showProgress:false, coursePage:opts && opts.coursePage?opts.coursePage:true, inscriptionButton: opts && opts.inscriptionButton? opts.inscriptionButton:true, resourceLinks: opts && opts.resourceLinks ? opts.resourceLinks : false, certificateLink: opts && opts.certificateLink ? opts.certificateLink : false , selfTestButton: opts && opts.selfTestButton ? opts.selfTestButton : false, examButton: opts && opts.examButton ? opts.examButton : false };
	var a = document.createElement('a');
	var container = document.createElement('div');
	jQuery(a).addClass("course");
	var code =  data.codCurso;
	var name = data.nombre;
	var group = data.grupo? data.grupo : "";
	jQuery(container).append(a);
	if(opt.certificateLink){ 
		var aPrintCert = document.createElement("a");
		var hrefUrl = url.CertificatePrinter.replace("{instance}", bdInstance);
		hrefUrl = hrefUrl.replace("{user}", currentUser.Email);
		hrefUrl = hrefUrl.replace("{code}",code);	
		jQuery(aPrintCert).attr("href",hrefUrl).text(lang.PrintCertificate);
		jQuery(container).append(aPrintCert);
	}
	jQuery(a).attr("href","#");
	jQuery(a).text(name + (group && group != "" ? " "+lang.Group +" "+ group : ""));
	if(opt.coursePage){
		jQuery(a).click(function(){
			var page = new CoursePage({Name:name,Code:code},{inscriptionButton:opts.inscriptionButton, resourceLinks:opts.resourceLinks, showProgress:opt.showProgress, selfTestButton:opt.selfTestButton, examButton:opt.examButton});
			jQuery(content).empty().append(page.getElement());
		});
	}

			var extraStuffs = document.createElement('div');
			var table = document.createElement('table');
			var tbody = document.createElement('tbody');
			jQuery(table).append(tbody);
			jQuery(extraStuffs).append(table);
			var appendIt =false;
			if(opt.showProgress){
				var td1 = document.createElement("td");
				var tr1 = document.createElement("tr");
				jQuery(tr1).append(td1);
				jQuery(extraStuffs).addClass("extra-panel");
			    var rateBarContainer = document.createElement('div');
				jQuery(td1).append(rateBarContainer);		
				jQuery(tbody).append(tr1);
			    getCurrentRate(currentUser.Email, code, rateBarContainer);
				appendIt = true;
			}
			/*if(opt.selfTestButton){
				var examUrl1 = url.TestYourself.replace("{instance}", bdInstance);
				examUrl1 = examUrl1.replace("{user}", currentUser.Email);
				examUrl1 = examUrl1.replace("{code}",code);	
				var td2 = document.createElement("td");
				var tr2 = document.createElement("tr");
				jQuery(tr2).append(td2);
				var stb = document.createElement('a');
				var divstb = document.createElement("div");
				jQuery(divstb).addClass("button").text(lang.TestYourself);
				jQuery(stb).attr({"border":"none","href":examUrl1,"target":"_blank"});
				jQuery(stb).append(divstb);
				jQuery(td2).append(stb);
				jQuery(tbody).append(tr2);
				appendIt = true;
			}*/
			if(opt.examButton){
				var examUrl = url.TakeExam.replace("{instance}", bdInstance);
				examUrl = examUrl.replace("{user}", currentUser.Email);
				examUrl = examUrl.replace("{code}",code);	
				var td3 = document.createElement("td");
				var tr3 = document.createElement("tr");
				jQuery(tr3).append(td3);
				var ev = document.createElement('a');
				var divev = document.createElement("div");
				jQuery(divev).addClass("button").text(lang.TakeTest);
				jQuery(ev).attr({"border":"none","href":examUrl,"target":"_blank"})
				jQuery(ev).append(divev);
				jQuery(td3).append(ev);
				jQuery(tbody).append(tr3);
				appendIt = true;
			}
			if(appendIt) {
				jQuery(container).append(extraStuffs);
				jQuery(container).addClass("course-extra-panel");
			}
		
	
	this.getElement = function(){
		return container;
		}
}
function CoursesList(data,opts){
	var opt = {showProgress:opts && opts.showProgress?opts.showProgress:false, state:opts && opts.state?opts.state:"", coursePage:opts && opts.coursePage?opts.coursePage:false};
	var items = data;
	var ul=document.createElement('ul');
	var categories = [];
	jQuery(ul).addClass("course-list");
	for (var i = 0; items.length > i; i++) {
		if (items[i].nombre && items[i].codCurso) {
			if(opt.state =="" || (opt.state != "" && items[i].estado == opt.state)){
				opt.inscriptionButton = opt.state == courseStates.registered || (opt.state == courseStates.approved && currentUser.Viewer == currentUser.Email) ? false : true;
				opt.resourceLinks = !opt.inscriptionButton;
				opt.selfTestButton = opt.state == courseStates.registered ? true : false;
				opt.examButton = opt.state == courseStates.registered ? true : false;
				opt.certificateLink = opt.state == courseStates.approved ? true: false; 
				var course = new Course(items[i], opt);
				var indexof = jQuery.inArray("kmecert_category_"+items[i].categoria, categories);
				if(indexof < 0){
					var cateli = document.createElement("li");
					var catul = document.createElement("ul");
					var h1 = document.createElement("h1");
					jQuery(cateli).attr("id","kmecert_category_"+items[i].categoria);
					jQuery(cateli).append(h1).append(catul);
					jQuery(h1).text(items[i].categoria);
					jQuery(ul).append(cateli);
					categories[categories.length] = "kmecert_category_"+items[i].categoria;
				}

				jQuery(ul).find("li").each(function(){
					if(jQuery(this).attr("id") == "kmecert_category_"+items[i].categoria){
						var li = document.createElement("li");
						jQuery(this).find("ul").append(li);
						jQuery(li).addClass("course-list-item");
						jQuery(li).append(course.getElement());
					}
				});

			}
		}

	}
	this.getElement=function(){
		return ul;
	};	
}

function RateBar(currentRate){
	var container = document.createElement('div');
	var rate = document.createElement('div');
	var progress = document.createElement('div');
	var span = document.createElement('span');
	jQuery(span).addClass("rate-bar-numbers");
	jQuery(rate).addClass("rate-bar");
	jQuery(progress).addClass("rate-bar-progress");
	jQuery(rate).append(progress);
	jQuery(progress).css("width",currentRate+"%").empty();
	jQuery(container).append(rate).append(jQuery(span).empty().text(currentRate.toFixed(2)+"%"));
	this.setRate = function(r){
		jQuery(span).empty().text(r.toFixed(2)+"%");
		jQuery(progress).css("width",r+"%");
		};
	this.getElement=function(){
	return container;
	};
}

function SuccessPage(course){
	var code = course.Code;
	var name = course.Name;
	var panel = document.createElement("div");
	var message = document.createElement("div");
	var goToCoursePage = document.createElement("input");
	jQuery(message).text(lang.SuccessfulInscription.replace("{courseName}", name));
	jQuery(goToCoursePage).attr({"type":"button"}).addClass("button").val(lang.goToCoursePage);
	jQuery(goToCoursePage).click(function(){
		var page = new CoursePage(course,{inscriptionButton:false, resourceLinks:true, showProgress:true, selfTestButton:true, examButton:true});
			jQuery(content).empty().append(page.getElement());					  
	});
	jQuery(panel).addClass("kmecertificates-message");
	jQuery(panel).append(message).append(goToCoursePage);
	
	this.getElement = function(){
		return panel;	
	}
}

function TOS(user, course){
	var code = course.Code;
	var name = course.name;
	var tosSelf = this;
	var tos = document.createElement('div');
	this.agree = document.createElement('input');
	this.cancel = document.createElement('input');
	var agreeCheck = document.createElement('input');
	var tosContent =  document.createElement('iframe');
	var buttons = document.createElement('div');
	jQuery(tosContent).attr({src:url.TOS})
	$(tosContent).addClass("tos-content")
	jQuery(tosSelf.agree).attr({"type":"button","value":lang.Accept, "disabled":!jQuery(agreeCheck).is(":checked")});
	jQuery(tosSelf.cancel).attr({"type":"button","value":lang.Cancel});
	jQuery(agreeCheck).attr({"type":"checkbox"});

	jQuery(agreeCheck).click(function(){
		var self = this;
		jQuery(tosSelf.agree).attr("disabled",!jQuery(self).is(":checked"));
	});
	jQuery(tos).append(tosContent);
	jQuery(tos).append(buttons);
	jQuery(buttons).append(agreeCheck);
	jQuery(buttons).append(this.cancel);
	jQuery(buttons).append(this.agree);
	jQuery(tosSelf.agree).click(function(){
		applyToGroup(user, course);								 
	});
	
	this.getElement= function(){
		return tos;	
	}
}

function CoursePage(course, optns){
	var code = course.Code;
	var name = course.Name;
	var opt = {inscriptionButton:optns && optns.inscriptionButton?optns.inscriptionButton:false, resourceLinks: optns && optns.resourceLinks ? optns.resourceLinks : false, selfTestButton: optns && optns.selfTestButton ? optns.selfTestButton : false, examButton: optns && optns.examButton ? optns.examButton : false, showProgress:optns && optns.showProgress?optns.showProgress:false };
	var panel = document.createElement('div');
	var tableMain = document.createElement('table');
	var tbodyMain = document.createElement('tbody');
	var trMain = document.createElement('tr');
	var tdMain = document.createElement('td');
	var content = document.createElement('div');
	jQuery(panel).addClass("course-page-content");
			var extraStuffs = document.createElement('div');
			var table = document.createElement('table');
			var tbody = document.createElement('tbody');
			jQuery(table).append(tbody);
			jQuery(extraStuffs).append(table);
			var appendIt =false;
			if(opt.showProgress){
				var td1 = document.createElement("td");
				var tr1 = document.createElement("tr");
				jQuery(tr1).append(td1);
				jQuery(extraStuffs).addClass("extra-panel");
			    var rateBarContainer = document.createElement('div');
				jQuery(td1).append(rateBarContainer);		
				jQuery(tbody).append(tr1);
			    getCurrentRate(currentUser.Email, code, rateBarContainer);
				appendIt = true;
			}
			/*if(opt.selfTestButton){
				var examUrl1 = url.TestYourself.replace("{instance}", bdInstance);
				examUrl1 = examUrl1.replace("{user}", currentUser.Email);
				examUrl1 = examUrl1.replace("{code}",code);	
				var td2 = document.createElement("td");
				var tr2 = document.createElement("tr");
				jQuery(tr2).append(td2);
				var stb = document.createElement('a');
				var divstb = document.createElement("div");
				jQuery(divstb).addClass("button").text(lang.TestYourself);
				jQuery(stb).attr({"border":"none","href":examUrl1,"target":"_blank"});
				jQuery(stb).append(divstb);
				jQuery(td2).append(stb);
				jQuery(tbody).append(tr2);
				appendIt = true;
			}*/
			if(opt.examButton){
				var examUrl = url.TakeExam.replace("{instance}", bdInstance);
				examUrl = examUrl.replace("{user}", currentUser.Email);
				examUrl = examUrl.replace("{code}",code);	
				var td3 = document.createElement("td");
				var tr3 = document.createElement("tr");
				jQuery(tr3).append(td3);
				var ev = document.createElement('a');
				var divev = document.createElement("div");
				jQuery(divev).addClass("button").text(lang.TakeTest);
				jQuery(ev).attr({"border":"none","href":examUrl,"target":"_blank"})
				jQuery(ev).append(divev);
				jQuery(td3).append(ev);
				jQuery(tbody).append(tr3);

				appendIt = true;
			}			

	jQuery(tableMain).append(tbodyMain);
	jQuery(tbodyMain).append(trMain);
	jQuery(trMain).append(tdMain);
	jQuery(tdMain).append(content);
	if(appendIt) {
		var tdExtra = document.createElement("td");
		jQuery(tdExtra).append(extraStuffs);
		jQuery(tdExtra).addClass("extra-td");
		jQuery(trMain).append(tdExtra);
	}
	jQuery(panel).append(tableMain);
	jQuery(content).empty().append(preloader);
	kmeWS.get("get",{code:code},function(res){
		if(!res.html && !res.resources){
			jQuery(content).empty().text(lang.CoursePageNotAvailable);
			}
		else{
			var resources = new Resources(code,res.resources,{resourceLinks:optns.resourceLinks});
			jQuery(content).empty();
			jQuery(content).html("<span class=\"contentheading\">"+name+"</span><br/><br/>"+res.html);
			jQuery(content).append(resources.getElement());	
		}
	});
	
	if(opt.inscriptionButton){
		var button = document.createElement('input');
		jQuery(button).attr({"type":"button", "value":lang.TakeCourse});
		jQuery(button).addClass("button");
		jQuery(button).click(function(){
			if(currentUser.Viewer == currentUser.Email){									  
				var tos = new TOS(currentUser.Email,course);
				var modal = jQuery(tos.getElement()).modal();									  
				jQuery(tos.cancel).click(function(){
					modal.close();
				});
				jQuery(tos.agree).click(function(){
					modal.close();
				});
			}
			else{
		  proxy.get("cursosDisponiblesUsuario", { bdInstancia: bdInstance, idUsuario: currentUser.Viewer }, function(res) {
			var equalCodes = false;																												 			for(var i = 0; res.resultado.length > i;i++){
					if(res.resultado[i].codCurso == code){
						var tosViewer = new TOS(currentUser.Viewer,course);
						var modalViewer = jQuery(tosViewer.getElement()).modal();									  
					jQuery(tosViewer.cancel).click(function(){
						modalViewer.close();
					});
					jQuery(tosViewer.agree).click(function(){
						modalViewer.close();
					});
					equalCodes = true;
					break;
					}
				}
				if(!equalCodes){
					alert(lang.NotRequirements);
				}
			});
			}
		});
		jQuery(panel).append(button);
	}
	
	this.getElement=function(){
		return panel;	
	}
}

function Resources(code,items,optns){
	var opts = {resourceLinks: optns && optns.resourceLinks ? optns.resourceLinks : false };
	var self = this;
	var panel = document.createElement("div");
	jQuery(panel).addClass("resources");
	var legend = document.createElement("div");
	jQuery(legend).addClass("contentheading");
	var ul = document.createElement("ul");
	for(resource in items){
		var list = items[resource].items;
		if(list.length > 0){
			var li = document.createElement("li");
			var titleResource = document.createElement("div");
			var ulinc= document.createElement("ul");
			jQuery(panel).data(resource,ulinc);
			jQuery(titleResource).addClass(resource).text(lang[items[resource].name.replace("lang.","")]);
			jQuery(li).append(titleResource);
			jQuery(li).append(ulinc);
			jQuery(li).data("resource",resource);
			jQuery(ul).append(li);		
	
			jQuery(ulinc).empty();
			for(var i = 0; list.length > i; i++){
				var lichild = document.createElement("li");
				jQuery(lichild).addClass("resource");
				if(opts.resourceLinks){
					var a = document.createElement("a");
					jQuery(a).attr({"href":list[i].Url, "target":"_blank"});
					jQuery(a).text(list[i].Name);
					jQuery(a).data("resource",{Name:resource, Item:list[i]});
					jQuery(a).click(function(){
						consumeResource(currentUser.Email,code, jQuery(this).data("resource").Item,jQuery(this).data("resource").Name);
					});
					jQuery(lichild).append(a);					
				}
				else
					jQuery(lichild).text(list[i].Name);
				jQuery(lichild)
				jQuery(ulinc).append(lichild);			
			}
		}
	}
	
	jQuery(panel).append(jQuery(legend).text(lang.AvailableResources));
	jQuery(panel).append(ul);

	
	
	this.getElement = function(){
		return panel;	
	};	
}

function Proxy(url){
	var service = url;
	function onError(obj) {
	if (obj.faultcode != null) {
		alert("error " + obj.faultcode + ": " + obj.faultstring);
		return true;
	}
	else
		return false;
	
	}
	
	function handleResponse(res,fn) {
		var response = eval("(" + res + ")");
		if(response.resultado){
			for(obj in response){
				response[obj]	= eval("("+response[obj]+")");
			}
		}
		if (!onError(response)) {
			fn(response);
		}
	}
	this.get= function(action,vars,fn){
		var varsString="";
		for(v in vars){
			varsString += "&"+v+"="+vars[v];
		}
		jQuery.ajax({
			url: service+"?action="+action+varsString+"&_r="+Number(new Date()),
			success: function(r) {
				handleResponse(r, function(res) {
					fn(res)
				})
			}
		});
	}	
}
/****************************/

/*Ajax calls to services*/
function verifyUser(user,fn) {
	if(user.Firstname && user.Lastname && user.Email && user.Id){
	jQuery(content).empty().append(preloader);
	proxy.get("existeUsuario",{idUsuario:user.Email,bdInstancia:bdInstance},function(res) {
                if (res.resultado != 1) createBasicAccount(user,fn);
                else {
					fn(user.Email);
				}
            });
	}
	else{
		var header = document.createElement("div");
		jQuery(header).text(lang.IncompleteProfileHead);
		var incompleteParts =[];
		if(!user.Firstname) incompleteParts[incompleteParts.length] = lang.Name;
		if(!user.Lastname) incompleteParts[incompleteParts.length] = lang.Lastname;
		if(!user.Email) incompleteParts[incompleteParts.length] = lang.Email;
    	if(!user.Id) incompleteParts[incompleteParts.length] = lang.Id;
		var ul = document.createElement("ul");
		for(var j=0; j<incompleteParts.length; j++){
			var li = document.createElement("li");
			jQuery(li).text(incompleteParts[j]);
			jQuery(ul).append(li);
		}
		var footer = document.createElement("div");
		var profileLink = document.createElement("a");
		jQuery(profileLink).text(lang.GoToProfile);
		jQuery(profileLink).attr("href", url.Profile);
		jQuery(footer).append(profileLink);
		var incomplete = document.createElement("div");
		jQuery(incomplete).append(header);
		jQuery(incomplete).append(ul);
		jQuery(incomplete).append(footer);
		jQuery(incomplete).modal();
	}
}


function verifyUserBasic(user,fn) {
	if(user.Firstname && user.Lastname && user.Email){
	jQuery(content).empty().append(preloader);
	proxy.get("existeUsuario",{idUsuario:user.Email,bdInstancia:bdInstance},function(res) {
                if (res.resultado != 1){ createBasicAccount(user,fn);
				applyToGroupNew(user.Email, "S_003_003");
				}
                else {
					applyToGroupNew(user.Email, "S_003_003");
					fn(user.Email);
				}
            });
	}
	else{
		var header = document.createElement("div");
		jQuery(header).text(lang.IncompleteProfileHead);
		var incompleteParts =[];
		if(!user.Firstname) incompleteParts[incompleteParts.length] = lang.Name;
		if(!user.Lastname) incompleteParts[incompleteParts.length] = lang.Lastname;
		if(!user.Email) incompleteParts[incompleteParts.length] = lang.Email;
    	if(!user.Id) incompleteParts[incompleteParts.length] = lang.Id;
		var ul = document.createElement("ul");
		for(var j=0; j<incompleteParts.length; j++){
			var li = document.createElement("li");
			jQuery(li).text(incompleteParts[j]);
			jQuery(ul).append(li);
		}
		var footer = document.createElement("div");
		var profileLink = document.createElement("a");
		jQuery(profileLink).text(lang.GoToProfile);
		jQuery(profileLink).attr("href", url.Profile);
		jQuery(footer).append(profileLink);
		var incomplete = document.createElement("div");
		jQuery(incomplete).append(header);
		jQuery(incomplete).append(ul);
		jQuery(incomplete).append(footer);
		jQuery(incomplete).modal();
	}
}





function createBasicAccount(user,fn) {
	proxy.get("crearUsuarioBasico",{email:user.Email,apellidos:user.Lastname,nombres:user.Firstname,bdInstancia:bdInstance},function(res) {
		if(res.resultado > 0){ 
			verifyUser(user,fn);
		}
    });
}

function getUserDetails(user) {
proxy.get("detalleUsuario",{idUsuario:user,bdInstancia:bdInstance},function(res) {
                alert(res.toSource());
            });
}

function getAvailableGroupsForUser(user) {
	jQuery(content).empty().append(preloader);
    proxy.get("cursosDisponiblesUsuario", { bdInstancia: bdInstance, idUsuario: user }, function(res) {
	var options = { coursePage:true};
	var courseList = new CoursesList(res.resultado, options);
		jQuery(content).empty().append(courseList.getElement());
    });
}

function getCurrentGroupsForUser(user) {
	jQuery(content).empty().append(preloader);
    proxy.get("cursosMatriculados", { bdInstancia: bdInstance, idUsuario: user }, function(res) {
        var options = { showProgress: true, state:courseStates.registered };
        var courseList = new CoursesList(res.resultado, options);
		jQuery(content).empty().append(courseList.getElement());
    });
}

function getCertificatesForUser(user) {
	jQuery(content).empty().append(preloader);
    proxy.get("cursosMatriculados", { bdInstancia: bdInstance, idUsuario: user }, function(res) {
        var options = {state:courseStates.approved };
		var courseList = new CoursesList(res.resultado, options);
		jQuery(content).empty().append("<br/><span><b>Cursos que he aprobado:</b></span><br/><br/>");
		jQuery(content).append(courseList.getElement());
    });
}

function getCurrentRate(user, course, parentElement) {
    proxy.get("porcentajeEnCurso", { idUsuario: user, codGrupo: course, bdInstancia: bdInstance }, function(res) {
		var courseCompletedKME = parseFloat(res.resultado);
		kmeWS.get("getCompletedPercentage",{code:course, user:user}, function(r){
			var totalCompletedResources = r;
			if(courseCompletedKME == 100)
				r = 100;
			var rate = new RateBar(parseFloat(totalCompletedResources));
				jQuery(parentElement).replaceWith(rate.getElement());
		});
    });
}

function applyToGroup(user, course){
		jQuery(content).empty().append(preloader);
	proxy.get("postularEstudianteGrupo",{idUsuario:user,codGrupo:course.Code, bdInstancia:bdInstance},function(res){
		if(res.resultado == 1){
			var successPage = new SuccessPage(course);
			jQuery(content).empty().append(successPage.getElement());
		}			
		else
			getCurrentGroupsForUser(user);			
	});
}

function applyToGroupNew(user, course){
		jQuery(content).empty().append(preloader);
	proxy.get("postularEstudianteGrupo",{idUsuario:user,codGrupo:course, bdInstancia:bdInstance},function(res){
		if(res.resultado == 1){
			var successPage = new SuccessPage(course);
			jQuery(content).empty().append(successPage.getElement());
		}			
		else
			getCurrentGroupsForUser(user);			
	});
}

function consumeResource(user, code, resource, resourceName){
	kmeWS.get("click",{user:user,code:code, id:resource.Id, catid:resource.CatId, resource:resourceName},function(res){
		getCurrentRate(user, code, jQuery(".rate-bar"));
	});
}
/***********************/
/*Draw on document*/
/*****************/