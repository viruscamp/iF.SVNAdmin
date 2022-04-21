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
class VisualSvnAuthPathsProvider extends AuthFileGroupAndPathsProvider
									implements	\svnadmin\core\interfaces\IPathsViewProvider,
												\svnadmin\core\interfaces\IPathsEditProvider
{
	/**
	 * The singelton instance of this class.
	 * @var \svnadmin\providers\VisualSvnAuthPathsProvider
	 */
	private static $m_instance = NULL;

	/**
	 * cache of child repo's VisualSvnAuthPathsProvider
	 * array( repo1 => repo1VisualSvnAuthPathsProvider );
	 */
	private $m_children = NULL;

	private $m_repo_name = null;

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
	public function init($repo_name = null)
	{
		global $appEngine;
		if($this->m_init_done) {
			return true;
		}
		$this->m_repo_name = $repo_name;
		if ($repo_name == null) {
			$this->m_children = array();
			$this->m_init_done = true;
			return true;
		} else {
			$this->m_children = null;
			$svnParentPath = $appEngine->getConfig()->getValue("Repositories:svnclient", "SVNParentPath");
			$relativePath = $appEngine->getConfig()->getValue("VisualSVN", "AuthzVisualSVNSubversionReposRelativeAccessFile");
			return parent::init($svnParentPath.'/'.$repo_name.'/conf/'.$relativePath);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see svnadmin\core\interfaces.IPathsEditProvider::reset()
	 */
	public function reset()
	{
		if ($this->m_repo_name == null) {
			$this->m_children = array();
		} else {
			parent::reset();
		}
	}

	public function hasChildren()
	{
		return $this->m_repo_name == null;
	}

	private static function createChild($repoName)
	{
		$repo = new VisualSvnAuthPathsProvider();
		$repo->init($repoName);
		return $repo;
	}

	public function getChild($repoName)
	{
		$p = self::getInstance();
		$child = $p->m_children[$repoName];
		if ($child == null) {
			$child = self::createChild($repoName);
			$p->m_children[$repoName] = $child;
		}
		return $child;
	}

	public function splitAccessPath($accesspath)
	{
		return explode(":", $accesspath);
	}

	public function getChildFromAccessPath($accesspath)
	{
		$p = self::getInstance();
		return $p->getChild($splitAccessPath[0]);
	}

	private static function getRepositories()
	{
		global $appEngine;
		$repositoryParentList = $appEngine->getRepositoryViewProvider()->getRepositoryParents();
		$rp = $repositoryParentList[0]; // TODO multiple SVNParentPath
		return $appEngine->getRepositoryViewProvider()->getRepositoriesOfParent($rp);
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
		if ($this->m_repo_name) {
			return $this->normalizePaths(parent::getPaths());
		}
		$p = self::getInstance();
		$repos = self::getRepositories();
		$ret = [];
		foreach ($repos as $r) {
			$pr = $p->getChild($r->getName());
			foreach ($pr->getPaths() as $accessPath) {
				$ret[] = $accessPath;
			}
		}
		return $ret;
	}

	public function getPathsOfUser($oUser)
	{
		if ($this->m_repo_name) {
			return $this->normalizePaths(parent::getPathsOfUser($oUser));
		}
		$p = self::getInstance();
		$repos = getRepositories();
		$ret = [];
		foreach ($repos as $r) {
			$pr = $p->getChild($r->getName());
			foreach ($pr->getPathsOfUser($oUser) as $accessPath) {
				$ret[] = $accessPath;
			}
		}
		return $ret;
	}

	public function getPathsOfGroup($oGroup)
	{
		if ($this->m_repo_name) {
			return $this->normalizePaths(parent::getPathsOfGroup($oGroup));
		}
		$p = self::getInstance();
		$repos = self::getRepositories();
		$ret = [];
		foreach ($repos as $r) {
			$pr = $p->getChild($r->getName());
			foreach ($pr->getPathsOfGroup($oGroup) as $accessPath) {
				$ret[] = $accessPath;
			}
		}
		return $ret;
	}

	public function getPathsOfRepository($oR)
	{
		$repo_name = $oR->getName();
		if ($this->m_repo_name) {
			if ($this->m_repo_name == $repo_name) {
				return $this->normalizePaths(parent::getPaths());
			} else {
				return array();
			}
		}
		$p = self::getInstance();
		$c = $p->getChild($repo_name);
		return $c->getPathsOfRepository($oR);
	}

	private function getUsersOfPathInner($path)
	{
		$nap = new \svnadmin\core\entities\AccessPath;
		$nap->path = $path;
		return parent::getUsersOfPath($nap);
	}

	public function getUsersOfPath($objAccessPath)
	{
		$splits = self::splitAccessPath($objAccessPath->getPath());
		$repo_name = $splits[0];
		$path = $splits[1];
		if ($this->m_repo_name) {
			if ($this->m_repo_name == $repo_name) {
				return $this->getUsersOfPathInner($path);
			} else {
				return array();
			}
		}
		$p = self::getInstance();
		$c = $p->getChild($repo_name);
		return $c->getUsersOfPathInner($path);
	}

	private function getGroupsOfPathInner($path)
	{
		$nap = new \svnadmin\core\entities\AccessPath;
		$nap->path = $path;
		return parent::getGroupsOfPath($nap);
	}

	public function getGroupsOfPath( $objAccessPath )
	{
		$splits = self::splitAccessPath($objAccessPath->getPath());
		$repo_name = $splits[0];
		$path = $splits[1];
		if ($this->m_repo_name) {
			if ($this->m_repo_name == $repo_name) {
				return $this->getGroupsOfPathInner($path);
			} else {
				return array();
			}
		}
		$p = self::getInstance();
		$c = $p->getChild($repo_name);
		return $c->getGroupsOfPathInner($path);
	}
}