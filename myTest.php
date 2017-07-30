<?php
	// Get real path for our folder
	function createZip($folder)
	{
		if(!file_exists($folder))
			return false;
		$rootPath = realpath("$folder/");

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open("$folder.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator($rootPath),
		    RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
		    // Skip directories (they would be added automatically)
		    if (!$file->isDir())
		    {
		        // Get real and relative path for current file
		        $filePath = $file->getRealPath();
		        $relativePath = substr($filePath, strlen($rootPath) + 1);

		        // Add current file to archive
		        $zip->addFile($filePath, $relativePath);
		    }
		}
		// Zip archive will be created only after closing object
		$zip->close();
		return true;
	}
?>

<?php
	if(!empty($_GET["file"]) && file_exists($_GET["file"]."/"))
		var_dump(createZip($_GET['file']));
	else
		echo "none";
?>