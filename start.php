<?php
	include(dirname(__FILE__)."/./main.php");
	include(dirname(__FILE__)."/./zip.php");
	
	function generate_random_string($len=30)
    {
    	$str="";
    	for($i=0;$i<$len;$i++)
    	{
    		$temp = rand(0,2);
    		if($temp==0)
    			$str.= rand(1,9);
    		else if($temp==1)
    			$str .= chr(rand(65,90));
    		else
    			$str .= chr(rand(97,122));
		}
		return $str;
	}

	function getUniqueFolderName($folderName="")
	{
		if(strlen($folderName)==0)
			$folderName = generate_random_string(5);
		$fol = $folderName;
		while(file_exists($fol."/"))
			$fol =$folderName."_".generate_random_string();
		if(mkdir($fol))
			return $fol;
		else
			return false;
	}

	if(!empty($_POST["url"]) || !empty($_GET["url"]))
	{
		$url="";
		if(!empty($_GET["url"]))
			$url = $_GET["url"];
		else 
			$url = $_POST["url"];
		if(strpos($url,"www.amishouts.com")!==false)
		{
			echo "3";
			exit;
		}
		
		$url = strtolower($url);
		$url = str_replace("http://", "", $url);

		$folder = $url;
		$folder = str_replace("www.", "", $folder);
		$folder = getUniqueFolderName($folder);

		if($folder)
		{
			define("RUN_DIR", $folder);
			define("SITE",$url);
			// ob_start();
			init();
			// ob_end_clean();
			$zip = createZip($folder);
			if(!$zip)
				echo "3"; // unable to create the zip file
			else
				echo $folder.".zip";
		}
		else
		{
			echo "2"; // Unable to create directory for download
		}
	}
	else
	{
		echo "0"; // url not present
	}
?>