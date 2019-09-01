$(document).ready(function(){
$("#mail_checked").attr("checked", true);

});

function orgValidate()
{

	$("#org_name_msg").html("")
	$("#org_contactperson_name_msg").html("");
	$("#org_no_of_installations_msg").html("");
	$("#org_email_msg").html("");

	var oValue=true, pValue=true, iValue=true, eValue=true;
	if($("#org_name").val() =="" || $("#org_name").val() ==" ")
	{
		$("#org_name_msg").html("It is required");
		$("#org_name_msg").css("color","red");
		oValue=false;
	}
	if($("#org_contactperson_name").val() =="" || $("#org_contactperson_name").val() ==" ")
	{
		$("#org_contactperson_name_msg").html("It is required");
		$("#org_contactperson_name_msg").css("color","red");
		pValue=false;
	}
	if($("#org_no_of_installations").val() =="" || $("#org_no_of_installations").val() ==" ")
	{
		$("#org_no_of_installations_msg").html("It is required");
		$("#org_no_of_installations_msg").css("color","red");
		iValue=false;
	}
	else if(IsNumeric($("#org_no_of_installations").val()) && parseInt($("#org_no_of_installations").val()) >=1 && $("#org_no_of_installations").val().indexOf(".")==-1)
	{
		//iValue=true
	}
	else
	{
		$("#org_no_of_installations_msg").html("Value must be number");
		$("#org_no_of_installations_msg").css("color","red");
		iValue=false;
	}
	var x=$("#org_email").val();
	var atpos=x.indexOf("@");
	var dotpos=x.lastIndexOf(".");
		if(x == " " || x == "")
	{
		$("#org_email_msg").html("It is required");
		$("#org_email_msg").css("color","red");
		eValue=false;
	}
    else if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
	{
		$("#org_email_msg").html("Not a valid email");
		$("#org_email_msg").css("color","red");
		eValue=false;
	}
	if(oValue==false || pValue==false || iValue==false || eValue==false)
	{
		return false;
	}
	else
	{
		return true;
	}
}

function jsReset()
{
	$("#org_name_msg").html("");
	$("#org_contactperson_name_msg").html("");
	$("#org_no_of_installations_msg").html("");
	$("#org_email_msg").html("");
}


function jsCheckInstallations(iVal)
{
	$("#errMsg").html("");

	if($("#org_installations").val()== "" || $("#org_installations").val()== " ")
	{
		$("#errMsg").html("Number of allowed Installations should not be empty.");
		$("#errMsg").css("color","red");
		return false;
	}
	else if(parseInt($("#org_installations").val()) < parseInt(iVal))
	{
		$("#errMsg").html("Number of allowed Installations should be greater than or equal to existed one.");
		$("#errMsg").css("color","red");
		return false;
	}
	else
	{
		return true;
	}
}
function managesalesValidate()
{
	var sValue=true, pValue=true;
	if($("#salesrep_name").val() =="" || $("#salesrep_name").val() ==" ")
	{
		$("#salesrep_name_msg").html("It is required");
		$("#salesrep_name_msg").css("color","red");
		sValue=false;
	}
	if($("#salesrep_promocode").val() =="" || $("#salesrep_promocode").val() ==" ")
	{
		$("#salesrep_promocode_msg").html("It is required");
		$("#salesrep_promocode_msg").css("color","red");
		pValue=false;
	}
	if(sValue==false || pValue==false)
	{
		return false;
	}
	else
	{
		return true;
	}
	
}

function jsTrim(s)
{
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{ l++; }
	while(r > l && s[r] == ' ')
	{ r-=1; }
	return s.substring(l, r+1);
}


function IsNumeric(strString)
{
   var strValidChars = "123456789";
   var strChar;
   var blnResult = true;

   if (strString.length == 0) return false;

   for (i = 0; i < strString.length && blnResult == true; i++)
      {
      strChar = strString.charAt(i);
      if (strValidChars.indexOf(strChar) == -1)
         {
         blnResult = false;
         }
      }
   return blnResult;
   }



function jsClearReport()
{

	$('#example_wrapper').hide();
}


function jsShowDailog(pageURL)
{
	window.open(pageURL,'AdditionalInstalls','width=800,height=500');
}
