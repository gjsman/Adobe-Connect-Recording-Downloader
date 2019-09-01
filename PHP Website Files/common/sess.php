<?php 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/

function legitCheck()
	{
	decodeBlock();
	return (isset($_SESSION['url']) && isset($_SESSION['login']) );
	}

/*
Shared cleaning function
*/

function clearChar($string)
	{
			
	// Adobe automatically replaces reserved characters with an underscore when saving
	// file names. We need to replace them in the stored recording name or we cannot
	// locate the files later
	// We also need to remove apostrophes from the names since they mess with SQL queries
	$res_chars  = "\/:*?<>'|".chr(34);
	for($charNo=0; $charNo<strlen($res_chars); $charNo++)
	  {
	  $ResChar= substr($res_chars,$charNo,1);
	  $string =  str_replace($ResChar,'_', $string);
	  }
	return $string;
	}
	
function shutDown()
		{
		if (debug)
			{print("<p >SESSION vars</p>"); 
			print(var_dump($_SESSION)); 
			print("<p >REQUEST vars</p>"); 
			print(var_dump($_REQUEST)); 
			}
		print("<p title='Authentication error' >Sorry there has been an error or incomplete authentication. </p>"); 
		print("<p title='Authentication error' >Please contact ".INST_DEPT." if the problem persists. </p>"); 
		print("<p>".INST_PH. ' or '.INST_MAIL."</p>"); 
		
		die();
		exit();		
		}

function logit($word)
	{

	$filename = str_replace("/",'',$_SERVER['PHP_SELF']);
	$filename = str_replace('.php','',$filename);
	$log = fopen('C:\\errors\\'.$filename.'_log.txt', "a");
	$line = $word.' , '.date('c').lf;
	fwrite($log, $line);
	fclose($log);
	}

?>
