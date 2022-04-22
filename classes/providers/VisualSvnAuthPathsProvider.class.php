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

function splitAccessPath($accesspath)
{
	return explode(":", $accesspath);
}

function makeAccessPath($accesspath) {
	return new \svnadmin\core\entities\AccessPath($accesspath);
}

class VisualSvnAuthPathsProviderChild implements	\svnadmin\core\interfaces\IPathsViewProvider,
												\svnadmin\core\interfaces\IPathsEditProvider
{
	private $m_repo_name = null;

	// only for VisualSvnAuthPathsProvider
	public $m_auth_provider = null;

	// only for VisualSvnAuthPathsProvider
	public $m_dirty = false;

	public function getRepoName() {
		return $this->m_repo_name;
	}

	/**
	 * Constructor.
	 */
	public function __construct($repo_name)
	{
		$this->m_repo_name = $repo_name;
	}
	
		/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IProvider::init()
	 */
	public function init()
	{
		if ($this->m_auth_provider != null) {
			return true;
		}
		global $appEngine;
		$svnParentPath = $appEngine->getConfig()->getValue("Repositories:svnclient", "SVNParentPath");
		$relativePath = $appEngine->getConfig()->getValue("VisualSVN", "AuthzVisualSVNSubversionReposRelativeAccessFile");
		$auth_provider = new AuthFileGroupAndPathsProvider();
		$auth_provider->init($svnParentPath.'/'.$this->m_repo_name.'/conf/'.$relativePath);
		$this->m_auth_provider = $auth_provider;
		$this->m_dirty = false;
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IPathsEditProvider::reset()
	 */
	public function reset()
	{
		$this->m_auth_provider->reset();
		$this->m_dirty = false;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IViewProvider::isUpdateable()
	 */
	public function isUpdateable()
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IViewProvider::update()
	 */
	public function update()
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IEditProvider::save()
	 */
	public function save()
	{
		if ($this->m_auth_provider->save()) {
			$this->m_dirty = false;
			return true;
		}
		return false;
	}

	public function hasChildren()
	{
		return false;
	}

	private function normalizePaths($paths)
	{
		foreach($paths as $o) {
			$o->path = $this->m_repo_name.':'.$o->path;
		}
		return $paths;
	}

	public function getPaths()
	{
		return $this->normalizePaths($this->m_auth_provider->getPaths());
	}

	public function getPathsOfUser($oUser)
	{
		return $this->normalizePaths($this->m_auth_provider->getPathsOfUser($oUser));
	}

	public function getPathsOfGroup($oGroup)
	{
		return $this->normalizePaths($this->m_auth_provider->getPathsOfGroup($oGroup));
	}

	public function getPathsOfRepository($oR)
	{
		$repo_name = $oR->getName();
		if ($this->m_repo_name == $repo_name) {
			return $this->normalizePaths($this->m_auth_provider->getPaths());
		} else {
			return array();
		}
	}

	public function safeCall($accessPath, $defaultValue, $method)
	{
		$splits = splitAccessPath($accessPath);
		$repo_name = $splits[0];
		$path = $splits[1];

		if ($this->m_repo_name == $repo_name) {
			return $method($this, $path);
		} else {
			return $defaultValue;
		}
	}

	public function getUsersOfPath($objAccessPath)
	{
		return $this->safeCall($objAccessPath->getPath(), array(), function($c, $path) {
			return $c->m_auth_provider->getUsersOfPath(makeAccessPath($path));
		});
	}

	public function getGroupsOfPath($objAccessPath)
	{
		return $this->safeCall($objAccessPath->getPath(), array(), function($c, $path) {
			return $c->m_auth_provider->getGroupsOfPath(makeAccessPath($path));
		});
	}

	public function createAccessPath($objAccessPath)
	{
		return $this->safeCall($objAccessPath->getPath(), false, function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->createAccessPath(makeAccessPath($path));
		});
	}

	public function createAccessPathIfNotExists($accessPath)
	{
		return $this->safeCall($accessPath, false, function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->createAccessPathIfNotExists($path);
		});
	}

	public function deleteAccessPath($objAccessPath)
	{
		return $this->safeCall($objAccessPath->getPath(), false, function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->deleteAccessPath(makeAccessPath($path));
		});
	}

	public function deleteAccessPathIfEmpty($accessPath)
	{
		return $this->safeCall($accessPath, false, function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->deleteAccessPathIfEmpty($path);
		});
	}

	public function assignGroupToAccessPath($objGroup, $objAccessPath, $objPermission)
	{
		return $this->safeCall($objAccessPath->getPath(), false, function($c, $path) use($objGroup, $objPermission) {
			$c->m_dirty = true;
			return $c->m_auth_provider->assignGroupToAccessPath($objGroup, makeAccessPath($path), $objPermission);
		});
	}

	public function removeGroupFromAccessPath($objGroup, $objAccessPath)
	{
		return $this->safeCall($objAccessPath->getPath(), false, function($c, $path) use($objGroup) {
			$c->m_dirty = true;
			return $c->m_auth_provider->removeGroupFromAccessPath($objGroup, makeAccessPath($path));
		});
	}

	public function removeGroupFromAllAccessPaths($objGroup)
	{
		$this->m_dirty = true;
		return $this->m_auth_provider->removeGroupFromAllAccessPaths($objGroup);
	}

	public function assignUserToAccessPath($objUser, $objAccessPath, $objPermission)
	{
		return $this->safeCall($objAccessPath->getPath(), false, function($c, $path) use($objUser, $objPermission) {
			$c->m_dirty = true;
			return $c->m_auth_provider->assignUserToAccessPath($objUser, makeAccessPath($path), $objPermission);
		});
	}

	public function removeUserFromAccessPath($objUser, $objAccessPath)
	{
		return $this->safeCall($objAccessPath->getPath(), false, function($c, $path) use($objUser) {
			$c->m_dirty = true;
			return $c->m_auth_provider->removeUserFromAccessPath($objUser, makeAccessPath($path));
		});
	}

	public function removeUserFromAllAccessPaths($objUser)
	{
		$this->m_dirty = true;
		return $this->m_auth_provider->removeUserFromAllAccessPaths($objUser);
	}
}

// <svnrepo>/<lib1>/conf/VisualSVN-SvnAuthz.ini contains pathes and permission only
class VisualSvnAuthPathsProvider implements	\svnadmin\core\interfaces\IPathsViewProvider,
												\svnadmin\core\interfaces\IPathsEditProvider
{
	/**
	 * The singelton instance of this class.
	 * @var \svnadmin\providers\VisualSvnAuthPathsProvider
	 */
	private static $m_instance = null;

	/**
	 * cache of child repo's VisualSvnAuthPathsProviderChild
	 * array( repo1 => VisualSvnAuthPathsProviderChild );
	 */
	private $m_children = null;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Gets the singelton instance of this object.
	 *
	 * @return \svnadmin\providers\VisualSvnAuthPathsProvider
	 */
	public static function getInstance()
	{
		if (self::$m_instance == null) {
			self::$m_instance = new VisualSvnAuthPathsProvider();
		}
		return self::$m_instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IProvider::init()
	 */
	public function init()
	{
		if($this->m_children != null) {
			return true;
		}
		$this->m_children = array();
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IPathsEditProvider::reset()
	 */
	public function reset()
	{
		$this->m_children = array();
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IViewProvider::isUpdateable()
	 */
	public function isUpdateable()
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IViewProvider::update()
	 */
	public function update()
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IEditProvider::save()
	 */
	public function save()
	{
		$ret = true;
		foreach ($this->m_children as $repo_name => $c) {
			if ($c->m_dirty) {
				if (!$c->save()) {
					$ret = false;
				}
			}
		}
		return $ret;
	}

	public function hasChildren()
	{
		return true;
	}

	private static function createChild($repo_name)
	{
		$repo = new VisualSvnAuthPathsProviderChild($repo_name);
		$repo->init();
		return $repo;
	}

	public function getChild($repo_name)
	{
		$child = $this->m_children[$repo_name];
		if ($child == null) {
			$child = self::createChild($repo_name);
			$this->m_children[$repo_name] = $child;
		}
		return $child;
	}

	public function getChildFromAccessPath($accesspath)
	{
		$splits = splitAccessPath($accesspath);
		return $this->getChild($splits[0]);
	}

	private static function getRepositories()
	{
		global $appEngine;
		$repositoryParentList = $appEngine->getRepositoryViewProvider()->getRepositoryParents();
		$rp = $repositoryParentList[0]; // TODO multiple SVNParentPath
		return $appEngine->getRepositoryViewProvider()->getRepositoriesOfParent($rp);
	}

	private function callChildren($method)
	{
		$repos = self::getRepositories();
		foreach ($repos as $r) {
			$c = $this->getChild($r->getName());
			$method($c);
		}
	}

	private function callChildrenSuccess($method)
	{
		$ret = true;
		$repos = self::getRepositories();
		foreach ($repos as $r) {
			$c = $this->getChild($r->getName());
			if (!$method($c)) {
				$ret = false;
			}
		}
		return $ret;
	}

	private function callChildrenFlat($method)
	{
		$ret = [];
		$repos = self::getRepositories();
		foreach ($repos as $r) {
			$c = $this->getChild($r->getName());
			foreach ($method($c) as $item) {
				$ret[] = $item;
			}
		}
		return $ret;
	}

	public function getPaths()
	{
		return $this->callChildrenFlat(function($c) {
			return $c->getPaths();
		});
	}

	public function getPathsOfUser($oUser)
	{
		return $this->callChildrenFlat(function($c) use($oUser) {
			return $c->getPathsOfUser($oUser);
		});
	}

	public function getPathsOfGroup($oGroup)
	{
		return $this->callChildrenFlat(function($c) use($oGroup) {
			return $c->getPathsOfGroup($oGroup);
		});
	}

	public function getPathsOfRepository($repo)
	{
		$c = $this->getChild($repo->getName());
		return $c->getPaths();
	}

	public function callChild($accessPath, $method)
	{
		$splits = splitAccessPath($accessPath);
		$repo_name = $splits[0];
		$path = $splits[1];

		$c = $this->getChild($repo_name);
		return $method($c, $path);
	}

	public function getUsersOfPath($objAccessPath)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) {
			return $c->m_auth_provider->getUsersOfPath(makeAccessPath($path));
		});
	}

	public function getGroupsOfPath($objAccessPath)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) {
			return $c->m_auth_provider->getGroupsOfPath(makeAccessPath($path));
		});
	}

	public function createAccessPath($objAccessPath)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->createAccessPath(makeAccessPath($path));
		});
	}

	public function createAccessPathIfNotExists($accessPath)
	{
		return $this->callChild($accessPath, function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->createAccessPathIfNotExists($path);
		});
	}

	public function deleteAccessPath($objAccessPath)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->deleteAccessPath(makeAccessPath($path));
		});
	}

	public function deleteAccessPathIfEmpty($accessPath)
	{
		return $this->callChild($accessPath, function($c, $path) {
			$c->m_dirty = true;
			return $c->m_auth_provider->deleteAccessPathIfEmpty($path);
		});
	}

	public function assignGroupToAccessPath($objGroup, $objAccessPath, $objPermission)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) use($objGroup, $objPermission) {
			$c->m_dirty = true;
			return $c->m_auth_provider->assignGroupToAccessPath($objGroup, makeAccessPath($path), $objPermission);
		});
	}

	public function removeGroupFromAccessPath($objGroup, $objAccessPath)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) use($objGroup) {
			$c->m_dirty = true;
			return $c->m_auth_provider->removeGroupFromAccessPath($objGroup, makeAccessPath($path));
		});
	}

	public function removeGroupFromAllAccessPaths($objGroup)
	{
		return $this->callChildrenSuccess(function($c) use($objGroup) {
			$c->m_dirty = true;
			return $c->removeGroupFromAllAccessPaths($objGroup);
		});
	}

	public function assignUserToAccessPath($objUser, $objAccessPath, $objPermission)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) use($objUser, $objPermission) {
			$c->m_dirty = true;
			return $c->m_auth_provider->assignUserToAccessPath($objUser, makeAccessPath($path), $objPermission);
		});
	}

	public function removeUserFromAccessPath($objUser, $objAccessPath)
	{
		return $this->callChild($objAccessPath->getPath(), function($c, $path) use($objUser) {
			$c->m_dirty = true;
			return $c->m_auth_provider->removeUserFromAccessPath($objUser, makeAccessPath($path));
		});
	}

	public function removeUserFromAllAccessPaths($objUser)
	{
		return $this->callChildrenSuccess(function($c) use($objUser) {
			$c->m_dirty = true;
			return $c->removeUserFromAllAccessPaths($objUser);
		});
	}
}