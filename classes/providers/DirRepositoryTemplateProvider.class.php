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

function custom_copy($src, $dst, $modes=null) {
	// open the source directory
	$dir = opendir($src);

	// Make the destination directory if not exist
	@mkdir($dst);

	// Loop through the files in source directory
	while( $file = readdir($dir) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				// Recursively calling custom copy function
				// for sub directory
				custom_copy($src . '/' . $file, $dst . '/' . $file, $modes);
			} else {
				copy($src . '/' . $file, $dst . '/' . $file);
				if ($modes != null) {
					chmod($dst . '/' . $file, $modes);
				}
			}
		}
	}

	closedir($dir);
}

function del_tree($dir) {
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? del_tree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

class DirRepositoryTemplateProvider implements \svnadmin\core\interfaces\IRepositoryTemplateProvider
{
	private static $_instance = NULL;

	private $_svnClient = NULL;
	
	private $tmplroot;

	public function __construct($tmplroot = null)
	{
		$engine = \svnadmin\core\Engine::getInstance();
		$config = $engine->getConfig();

		$this->_svnClient = new \IF_SVNClientC($engine->getConfig()
				->getValue('Repositories:svnclient', 'SvnExecutable'));

		if ($tmplroot == null) $tmplroot = self::$TEMPLATES_DIR;
		if (!file_exists($tmplroot) || !is_dir($tmplroot)) {
			throw new \Exception('The repository templates parent directory is invalid: '.$tmplroot);
		}
		$this->tmplroot = realpath($tmplroot);
	}

	public static function getInstance()
	{
		if (self::$_instance == null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function init()
	{
		return true;
	}

	public static $TEMPLATES_DIR = "./data/repotmpl";

	public function getTemplates()
	{
		$ret = array();

		$hd = opendir($this->tmplroot);
		while (($file = readdir($hd)) !== false)
		{
			if ($file == "." || $file == "..")
			{
				continue;
			}

			if (is_dir($this->tmplroot."/".$file)) {
				$ret[] = $file;
			}
		}
		closedir($hd);

		return $ret;
	}

	protected function copyFiles($templateName, $objRepository, $relativePath, $modes=null)
	{
		global $appEngine;
		$reporoot = $appEngine->getRepositoryViewProvider()->getRepositoryPath($objRepository);
		$destdir = $reporoot."/".$relativePath;
		$srcdir = $this->tmplroot."/".$templateName."/".$relativePath;
		if (is_dir($srcdir) && is_readable($srcdir) && is_dir($destdir) && is_writable($destdir)) {
			custom_copy($srcdir, $destdir, $modes);
			return true;
		}
		return false;
	}

	public function copyHooks($templateName, $objRepository)
	{
		return $this->copyFiles($templateName, $objRepository, "hooks", 0755);
	}

	public function copyConf($templateName, $objRepository)
	{
		return $this->copyFiles($templateName, $objRepository, "conf");
	}

	private function svn_exec($command)
	{
		$output = null;
		$return_var = 0;
		exec($command, $output, $return_var);
		if ($return_var != 0)
		{
			throw new \IF_SVNCommandExecutionException('Command='.$command.'; Return='.$return_var.'; Output='.$output.';');
		}
	}

	private function applyProps($propsfile, $additionArgs = "")
	{
		$svnexe = $this->_svnClient->getSvnExe();
		$config = new \IF_Config($propsfile);
		foreach($config->getSections() as $filename) {
			$localfilename = $this->_svnClient->encode_local_path(".$filename");
			foreach($config->getSectionKeys($filename) as $propname) {
				$propval = $config->getValue($filename, $propname);
				// svn propset $propname $propval $localfilename --force -q -R --depth infinity -q
				//echo "\"$svnexe\" propset $propname $propval $localfilename --force -q $additionArgs<br/>";
				$this->svn_exec("\"$svnexe\" propset $propname $propval $localfilename --force -q $additionArgs");
			}
		}
	}

	public function initFilesAndProps($templateName, $objRepository)
	{
		global $appEngine;
		$files = $this->tmplroot."/".$templateName."/files";
		$files_exists = file_exists($files);
		$props_recursive = $this->tmplroot."/".$templateName."/props-recursive.conf";
		$props_recursive_exists = file_exists($props_recursive);
		$props = $this->tmplroot."/".$templateName."/props.conf";
		$props_exists = file_exists($props);

		if (!files_exists && !props_recursive_exists && !props_exists) {
			return false;
		}

		$cwd = getcwd();
		$temppath = rtrim(sys_get_temp_dir(), '/') . '/svnadmin' . mt_rand() . microtime(true);
		try {
			if (!mkdir($temppath)) {
				throw new \Exception("Cannot create temp dir: ".$temppath);
			}
			if (!chdir($temppath)) {
				throw new \Exception("Cannot chdir to temp dir: ".$temppath);
			}

			$svnexe = $this->_svnClient->getSvnExe();
			$reporoot = $appEngine->getRepositoryViewProvider()->getRepositoryPath($objRepository);
			$repourl = $this->_svnClient->encode_url_path($reporoot);

			// svn checkout [URL] . --force --depth infinity -q
			$this->svn_exec("\"$svnexe\" checkout $repourl . --force --depth infinity -q");

			if ($files_exists) {
				custom_copy($files, $temppath);
				// svn add . --force --auto-props --parents --depth infinity -q
				$this->svn_exec("\"$svnexe\" add . --force --auto-props --parents --depth infinity -q", $output, $return_var);
			}

			if ($props_recursive_exists) {
				$this->applyProps($props_recursive, "-R --depth infinity");
			}

			if ($props_exists) {
				$this->applyProps($props);
			}

			// svn commit -m 'Adding a file'
			$msg = escapeshellarg("add files from repository template: $templateName");
			$this->svn_exec("\"$svnexe\" commit -m $msg -q", $output, $return_var);

			chdir($cwd);
			del_tree($temppath);

			return true;
		} catch(\Exception $ex1) {
			try {
				chdir($cwd);
				del_tree($temppath);
			} catch (\Exception $ex2) {
			}
			throw $ex1;
		}
	}
}
?>