<?php 
/*

 Unless otherwise noted, code in this file is copyright (C) 2017 Capitol Technology University
 Kenneth Mayer 
 distlearn@captechu.edu 
 ken.i.mayer@gmail.com
*/
include '../common/int_config.php';
include '../common/adobe.php';
include '../common/sess.php';

function exportFile($table)
	{
	header("Content-Type: text/csv");
	header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	header("Content-Transfer-Encoding: binary\n");
	header('Content-Disposition: attachment; filename="'.$table.'.csv"');
	
	$query = "SELECT * FROM ".$table;
	
	$export = mysql_query ($query ) or die ( "Sql error : " . mysql_error( ) );
	
	$fields = mysql_num_fields ( $export );
	
	for ( $i = 0; $i < $fields; $i++ )
	{
		$topheader .= mysql_field_name( $export , $i ) . ",";
	}
	
	while( $row = mysql_fetch_row( $export ) )
	{
		$line = '';
		foreach( $row as $value )
		{                                            
			if ( ( !isset( $value ) ) || ( $value == "" ) )
			{
				$value = ",";
			}
			else
			{
				$value = str_replace( '"' , '""' , $value );
				$value = '"' . $value . '"' . ",";
			}
			$line .= $value;
		}
		$data .= trim( $line ) . "\n";
	}
	$data = str_replace( "\r" , "" , $data );
	
	if ( $data == "" )
	{
		$data = "\n(0) Records Found!\n";                        
	}
	
	print ("$topheader\n$data");
	
	}
	
exportFile("views");
//exportFile("logins");
	
?>

