<?php 
namespace RZ\Renzo\Core\Handlers;

use RZ\Renzo\Core\Entities\Document;
use RZ\Renzo\Core\Kernel;
use Symfony\Component\Finder\Finder;


class DocumentHandler
{
	protected $document;

	function __construct( Document $document )
	{
		$this->document = $document;
	}

	/**
	 * Remove document assets and db row 
	 * @return boolean
	 */
	public function removeWithAssets()
	{
		Kernel::getInstance()->em()->remove($this->document);

		if (file_exists($this->document->getAbsolutePath())) {
			if (unlink($this->document->getAbsolutePath())) {
				$this->cleanParentDirectory();
				Kernel::getInstance()->em()->flush();
				return true;
			}
			else {
				throw new Exception("document.cannot_delete", 1);
			}
		}
		else {
			/*
			 * Only remove from DB
			 * and check directory
			 */
			$this->cleanParentDirectory();
			Kernel::getInstance()->em()->flush();
			return true;
		}
	}


	public function cleanParentDirectory()
	{
		$dir = dirname($this->document->getAbsolutePath());

		$finder = new Finder();
		$finder->files()->in($dir);

		if (count($finder) <= 0) {
			/*
			 * Directory is empty
			 */
			if (rmdir($dir)) {
				return true;
			}
			else {
				throw new Exception("document.cannot_delete.parent_folder", 1);
			}
		}

		return false;
	}
}