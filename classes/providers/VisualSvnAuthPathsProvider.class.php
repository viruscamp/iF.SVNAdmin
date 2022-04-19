<?php
/**
 * iF.SVNAdmin
 * Copyright (c) 2010 by Manuel Freiholz
 * http://www.insanefactory.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 */

namespace svnadmin\providers;

// <svnrepo>/<lib1>/conf/VisualSVN-SvnAuthz.ini contains pathes and permission only
class VisualSvnAuthPathProvider extends AuthFileGroupAndPathProvider
									implements	\svnadmin\core\interfaces\IPathsViewProvider,
												\svnadmin\core\interfaces\IPathsEditProvider
{
	/**
	 * The singelton instance of this class.
	 * @var \svnadmin\providers\VisualSvnAuthPathProvider
	 */
	private static $m_instance = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Gets the singelton instance of this object.
	 *
	 * @return \svnadmin\providers\VisualSvnAuthPathProvider
	 */
	public static function getInstance()
	{
		if (self::$m_instance == null)
		{
			self::$m_instance = new VisualSvnAuthPathProvider();
		}
		return self::$m_instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IProvider::init()
	 */
	public function init($path = null)
	{
		if ($path == null) {
			$this->m_init_done = true;
			return true;
		} else {
			return parent::init($path);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IPathsEditProvider::reset()
	 */
	public function reset()
	{
		if ($this->m_path) {
			parent::reset();
		}
	}

	public function isSplitByRepository()
	{
		return true;
	}
	
	public getInstanceByRepository($objRepository)
	{
		global $appEngine;
		$svnParentPath = $appEngine->getConfig()->getValue("Repositories:svnclient", "SVNParentPath");
		$relativePath = $appEngine->getConfig()->getValue("VisualSVN", "AuthzVisualSVNSubversionReposRelativeAccessFile");
		$repo = new VisualSvnAuthPathProvider();
		$repo->init($svnParentPath.'/'.$objRepository.getName().'/'.$relativePath);
		return $repo;
	}
}