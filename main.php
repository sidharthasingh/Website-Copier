<?php

	function download($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, false);
		// curl_setopt($curl, CURLOPT_POSTFIELDS, array());

		/*
			//can also be done as
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL => 'http://testcURL.com'
			));
		*/
		echo "starting download $url \n\n";
		$result = curl_exec($curl);
		var_dump(curl_getinfo($result));
		if(!$result)
			echo "error downloading $url \n\n";
		else
			echo "downloaded $url... \n\n";
		curl_close($curl);
		return $result;
	}

	function save($filename, $text)
	{
		if(file_exists($filename))
		{
			echo "$filename already exists...\n\n";
			return;
		}
		else
		{
			$file = fopen($filename, "w");
			if(!$file)
				echo "error opeing file $filename...\n\n";
			else
			{
				echo "file $filename opened \n\n";
				echo "writing into file...\n\n";
				fwrite($file,$text);
				echo "content written...\n\n";
			}
		}
	}

	function getHref($text)
	{
		preg_match_all('<href[[:space:]]*=[[:space:]]*("|\')([^[:space:]]*)("|\')>', $text, $href);
		return $href[0];
	}

	function getSrc($text)
	{
		preg_match_all('<src[[:space:]]*=[[:space:]]*("|\')([^[:space:]]*)("|\')>', $text, $src);
		return $src[0];
	}

	function cleanName($arr)
	{
		$count = 0; 
		foreach ($arr as $link) 
		{
			$first = strpos($link,'"');
			if(!$first)
				$first = strpos($link, "'");
			$arr[$count] = substr($link , $first+1, strlen($link)-$first-2);
			$count++;
		}
		return $arr;
	}

	function cleanRelativeUrl($arr)
	{
		$newArr = array();
		foreach($arr as $link)
		{
			if(strpos($link,"http:")!==false || false!==strpos($link,"https:"))
				if(strpos($link,SITE)===false)
					continue;
			if($link=="#")
				continue;
			if(strpos($link,"mailto:")!==false)
				continue;
			$newArr[] = $link;
		}
		return $newArr;
	}

	// ARR[0] for non recursive resources
	// ARR[1] for recursive resources
	function getActualSortUrl($arr)
	{
		$newArr = array();
		foreach($arr as $link)
		{
			if(strpos($link,"http://")!==false || strpos($link,"https://")!==false)
			{
				$link = str_replace("https://", "http://", $link);
				$newArr[] = $link;
				continue;
			}
			$link = SITE."/$link";
			$link = str_replace("//", "/", $link);
			$link = "http://".$link;
			$newArr[] = $link;
		}
		$gArr = array();
		$fArr = array();
		foreach($newArr as $key)
		{
			if(strpos($key,".html")!==false || strpos($key,".php"))
				$gArr[] = $key;
			else
				$fArr[] = $key;
		}
		return array($fArr,$gArr);
	}

	function init()
	{
		$down = download(SITE);
		save(RUN_DIR."/index.html",$down);
		$href = getHref($down);
		$src = getSrc($down);
		$links = cleanName(array_merge($href,$src));
		$links = cleanRelativeUrl($links);
		$links = getActualSortUrl($links);
		foreach($links[1] as $link)
		{
			$file = str_replace("http://".SITE."/","", $link);
			if(file_exists(RUN_DIR."/".$file))
			{
				echo "$file already exists...\n\n";
				continue;
			}
			$res = download($link);
			if(strpos($file,"/")!==false && strpos($file,"/")!==0)
			{
				$arr = explode("/",$file);
				unset($arr[count($arr)-1]);
				$loc = RUN_DIR."/".implode("/",$arr);
				if(!file_exists($loc))
					mkdir($loc,0777,true);
			}
			save(RUN_DIR."/".$file,$res);
		}
		foreach($links[0] as $link)
		{
			$file = str_replace("http://".SITE."/","", $link);
			if(strpos($file,"/")!==false && strpos($file,"/")!==0)
			{
				$arr = explode("/",$file);
				unset($arr[count($arr)-1]);
				$loc = RUN_DIR."/".implode("/",$arr);
				if(!file_exists($loc))
					mkdir($loc,0777,true);
			}
			$file = RUN_DIR."/".$file;
			if(!file_exists($file))
				shell_exec("wget -O $file $link");
			else
				echo "$file already exists...\n\n";
		}
	}

?>