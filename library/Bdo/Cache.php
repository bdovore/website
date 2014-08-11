<?php

/**
 *
 * @author laurent
 *        
 */
class Bdo_Cache {
	
	public $filename = null;
	public $fileCache = null;
	public $dateCreaCache=false;
	
	public $data = null;
	public $dataCache = null;

	public $mode = 'serial';

	public function __construct($filename)
	{
		$this->filename = $filename;

		$this->mode = pathinfo($filename,PATHINFO_EXTENSION);

		$this->fileCache = BDO_DIR_CACHE.$filename;
	}

	public function setData($data)
	{
		$this->data = $data;
	}


	public function write()
	{
		switch($this->mode)
		{
			case "serial" : {
				$this->dataCache = serialize($this->data);
				break;
			}
		}

		if(file_put_contents($this->fileCache, $this->dataCache)) {
			return true;
		}

		exit('erreur fatale : sauvegarde fichier cache ['.$this->filename.'] impossible.');
	}

	public function delete()
	{
		if (is_file($this->fileCache))
		{
			if (@unlink($this->fileCache)) {
				return true;
			}
			exit('erreur fatale : suppression fichier cache ['.$this->filename.'] impossible.');
		}
		return true;
	}

	public function read()
	{

		if ($this->dataCache = @file_get_contents($this->fileCache)) {
			switch($this->mode)
			{
				case "serial" : {
					$this->data = unserialize($this->dataCache);

					break;
				}
			}

			return true;
		}
		return false;
	}

	public function load()
	{
		if ($this->read()) {
			$this->dateCreaCache = filemtime($this->fileCache);
			return $this->data;
		}

		return false;
	}


	public function save($data)
	{
		$this->data = $data;
		if ($this->write()) {
			return true;
		}

		return false;
	}
	
}

?>