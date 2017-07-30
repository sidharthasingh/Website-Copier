<?php
	if(!empty($_GET["url"]))
	{
		$url = $_GET["url"];
		$url = str_replace("http://", "", $url);
	}
	else
	{
		echo "0"; // url not present
	}
?>