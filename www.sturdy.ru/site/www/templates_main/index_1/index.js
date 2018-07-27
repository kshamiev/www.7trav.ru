//<!--
//	проверка формы на определнные символы
function chek_frm1(n)
	{ eval ("document.all." + n + ".value=document.all." + n + ".value.replace(/[^a-z|а-я|\\-|_|0-9]/i,'')"); }
function chek_frm2(n)
	{ eval ("document.all." + n + ".value=document.all." + n + ".value.replace(/[^a-z|\\-|_|0-9]/i,'')"); }
//	убийство фреймов
function killer() 
	{
	if (self.parent.frames.length != 0)
	if (self.parent.location != document.location)
		{ self.parent.location=document.location; }
	}
//	новое окно
function system_window(path, target, w, h, pos, face)
	{
	if ( pos==1 )
		{ posx=screen.width/2-w/2; posy=screen.height/2-h/2; }
	else
		{ posx=0; posy=0; }
	if ( face==1 )
		{ newWindow=window.open(path,target,"left="+posx+", top="+posy+", width="+w+"px, height="+h+"px, toolbar=1, location=1, directories=1, status=1, menubar=1, scrollbars=1, resizable=1"); }
	else
		{ newWindow=window.open(path,target,"left="+posx+", top="+posy+", width="+w+"px, height="+h+"px, toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1"); }
	newWindow.focus();
	}
//	новое окно 2
function system_window2(path,w,h,pos,face)
	{
	if ( pos==1 )
		{ posx=screen.width/2-w/2; posy=screen.height/2-h/2; }
	else
		{ posx=0; posy=0; }
	if ( face==1 )
		{ window.open(path,"","left="+posx+", top="+posy+", width="+w+"px, height="+h+"px, toolbar=1, location=1, directories=1, status=1, menubar=1, scrollbars=1, resizable=1"); }
	else
		{ window.open(path,"","left="+posx+", top="+posy+", width="+w+"px, height="+h+"px, toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1"); }
	}
//	линии
function line_move(n)
	{
	if ( n.style.getExpression("backgroundColor")!="'#ffffff'" )
		{ n.style.setExpression("backgroundColor","'#BDC9D5'"); }
	}
function line_out(n)
	{
	if ( n.style.getExpression("backgroundColor")!="'#ffffff'" )
		{ n.style.setExpression("backgroundColor","'#E8E9FA'"); }
	}
function line_clik(n)
	{
	if ( n.style.getExpression("backgroundColor")!="'#ffffff'" )
		{ n.style.setExpression("backgroundColor","'#ffffff'"); }
	else
		{ n.style.setExpression("backgroundColor","'#E8E9FA'"); }
	}
//	скрытие, раскрытие блока
function show(obj)
	{
	if( "none" == obj.style.display )
		{ obj.style.display = ""; }
	else
		{ obj.style.display = "none"; }
	}
//	календарь
function calendar(obj)
	{
	//	x=event.x+10; y=event.y+90; //	e.clientX or e.pageX 
	x=0; y=0;
	newWindow=window.open("calendar.php?obj="+obj,"calk","left="+x+", top="+y+", width=247px, height=192px, toolbar=0, location=0, directories=0, status=1, menubar=0, scrollbars=1, resizable=1");
	newWindow.focus(); return false;
	}
// When the page loads:	//	для flash
window.onload = function(){
  if (document.getElementsByTagName) {
    // Get all the tags of type object in the page.
      var objs = document.getElementsByTagName("object");
      for (i=0; i<objs.length; i++) {
        // Get the HTML content of each object tag
        // and replace it with itself.
        objs[i].outerHTML = objs[i].outerHTML;
      }
   }
}
// When the page unloads:	//	для flash
window.onunload = function() {
  if (document.getElementsByTagName) {
    //Get all the tags of type object in the page.
    var objs = document.getElementsByTagName("object");
    for (i=0; i<objs.length; i++) {
      // Clear out the HTML content of each object tag
      // to prevent an IE memory leak issue.
      objs[i].outerHTML = "";
    }
  }
}
//-->

///////////////////////////////////////////////////////////////	AJAX BEGIN
function createRequest()
	{
	try
		{
    var request = new XMLHttpRequest();
		}
	catch(trymicrosoft)
		{
    try
			{
      var request = new ActiveXObject("Msxml2.XMLHTTP");
			}
		catch(othermicrosoft)
			{
      try
				{
				var request = new ActiveXObject("Microsoft.XMLHTTP");
				}
			catch(failed)
				{
        var request = false;
				}
			}
		}
	if ( !request ) alert("Error initializing XMLHttpRequest!");
	return request;
	}
function ajax_xml(url, function_name, data)
	{
  var request=createRequest();
	//
	request.onreadystatechange = function () {
		if ( 4 == request.readyState ) {
			if ( request.status != 200 ) {
				alert("Произошла ошибка "+ request.status+":\n" + request.statusText);
			} else {
				function_name(request.responseXML);
			}
		}
	}
	//
	if ( '' == data || 'undefined' == typeof data ) {
	  request.open("GET", url, true);
	  request.send(null);
	} else {
	  request.open("POST", url, true);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset='+document.charset);
	  request.send(data);
	}
	return true;
	}
function ajax_htm(url, function_name, data)
	{
  var request=createRequest();
	//
	request.onreadystatechange = function () {
		if ( 4 == request.readyState ) {
			if ( request.status != 200 ) {
				alert("Произошла ошибка "+ request.status+":\n" + request.statusText);
			} else {
				function_name(request.responseText);
			}
		}
	}
	//
	if ( '' == data || 'undefined' == typeof data ) {
	  request.open("GET", url, true);
	  request.send(null);
	} else {
	  request.open("POST", url, true);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset='+document.charset);
	  request.send(data);
	}
	return true;
	}
///////////////////////////////////////////////////////////////	AJAX END

// отображение адреса email
function make_email(login,sc)
{
	var serv = new Array;
	serv[0] = "sturdy.ru";
	eml = login +  "@" + serv[sc];
	return eml;
}

function write_email(login,sc)
{
	document.write(make_email(login,sc));
}

function mailme(login,sc,sub)
{
	eml = "mailto:" + make_email(login,sc);
	if (sub != "") eml += "?subject=" + sub;
	window.location.href = eml;
}
