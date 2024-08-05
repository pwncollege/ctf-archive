//var qa_default_text = 'Ask a question related to software...';
//var qa_default_text = 'Ask a software-related question. It will be answered by our experts for free.';
var qa_default_text = 'Ask a software-related question. You will get a free expert answer.';
//var loc = window.location.toString();

i = 0;
a = document.getElementsByTagName("div");
while (element = a[i++]) {
	if (element.className == "content") {
		a[i-1].className = 'content content_ask';
		continue;
	}
}

var txt = ''
txt += '<style>'
txt += '#ask div      {background-color: #CDE6FF; margin: 15px 5px 0 0; padding: 6px 2px 6px 7px; position: relative;}'
txt += '#ask #qa_ask  {border: 1px solid #BCBCBC; border-bottom-left-radius: 8px; border-top-left-radius: 8px; color: #BCBCBC; font-size: 16px; height: 23px; padding: 5px; position: relative; top: 0; width: 845px;}'
txt += '#ask #ask_btn {background: url("/images/btn_ask.png") no-repeat scroll 0 0 transparent; border: medium none; cursor: pointer; height: 35px; padding: 4px; position: absolute; top: 6px; width: 102px;}'
txt += '</style>'

txt += '<form id="ask" action="http://answers.informer.com/ask" method="post" onsubmit="return ask_submit()">'
txt += '<div>'
txt += '<input type="text" name="title" id="qa_ask" value="' +qa_default_text+ '" autocomplete="off" onmousedown="qa_s()" onfocus="qa_s()" onchange="qa_s()" onblur="qa_s(1)" onclick="qa_s();"/>'
txt += '<input type="submit" id="ask_btn" name="" value=" "/>'
txt += '</div>'
txt += '</form>'

if (document.getElementById("for_askform"))
{
	var div = document.getElementById("for_askform");
	//div.style.marginTop = '85px';
	//div.style.marginRight = '7px';
	div.innerHTML = txt;
}
else
	document.write(txt)


function qa_s(out)
{
	var srch = document.getElementById('qa_ask');

	if (out)
	{
		if (srch.value=='')
		{
			srch.value = qa_default_text;
			srch.style.color = '#BCBCBC';
		}
	}
	else
	{
		if (srch.value==qa_default_text)
		{
			srch.value = '';
			srch.style.color = 'black';
		}
	}
}

function ask_submit()
{
	var val = document.getElementById('qa_ask').value;
	if (val==qa_default_text || val=='' || val.length<=5)
	{
		alert('Please enter your question');
		return false;
	}
	return true;
}
