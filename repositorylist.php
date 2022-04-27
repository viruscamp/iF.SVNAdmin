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
include("include/config.inc.php");

//
// Authentication
//

$engine = \svnadmin\core\Engine::getInstance();

if (!$engine->isProviderActive(PROVIDER_REPOSITORY_VIEW)) {
	$engine->forwardError(ERROR_INVALID_MODULE);
}

$engine->checkUserAuthentication(true, ACL_MOD_REPO, ACL_ACTION_VIEW);
$appTR->loadModule("repositorylist");

//
// Actions
//

if (check_request_var("delete")) {
	$engine->handleAction("delete_repository");
}
else if (check_request_var('dump')) {
	$engine->handleAction('dump_repository');
	exit(0);
}
else if (check_request_var('load')) {
	
	exit(0);
}

//
// View data
//

$repositoryParentList = array();
$repositoryList = array();
try {
	// Repository parent locations.
	$repositoryParentList = $engine->getRepositoryViewProvider()->getRepositoryParents();

	$accessPathViewProvider = $appEngine->getAccessPathViewProvider();
	if ($accessPathViewProvider->hasChildren()) {
		$accessPathList = array();
	} else {
		$accessPathList = $accessPathViewProvider->getPaths();
	}

	// Repositories of all locations.
	foreach ($repositoryParentList as $rp) {
		$repositoryList[$rp->identifier] = $engine->getRepositoryViewProvider()->getRepositoriesOfParent($rp);
		usort($repositoryList[$rp->identifier], array('\svnadmin\core\entities\Repository', 'compare'));
		
		foreach ($repositoryList[$rp->identifier] as $r) {
			$dirPath = $r->getEncodedName().':/';
			$val->hasAccessPath = false;
			foreach ($accessPathList as &$accessPath) {
				if ($accessPath->getPath() == $dirPath) {
					$r->hasAccessPath = true;
					break;
				}
			}
		}
	}
	
	// Show options column?
	if (($engine->isProviderActive(PROVIDER_REPOSITORY_EDIT)
		&& $engine->hasPermission(ACL_MOD_REPO, ACL_ACTION_DUMP)
		&& $engine->getConfig()->getValueAsBoolean('GUI', 'RepositoryDumpEnabled', false))
		){
		SetValue('ShowOptions', true);
		SetValue('ShowDumpOption', true);
	}

	SetValue('RepositoryParentList', $repositoryParentList);
	SetValue('RepositoryList', $repositoryList);
	SetValue('ShowDeleteButton', $engine->getConfig()->getValueAsBoolean('GUI', 'RepositoryDeleteEnabled', true));
	SetValue("SVNBaseUrl", $appEngine->getConfig()->getValue("Subversion","SVNBaseUrl"));
}
catch (Exception $ex) {
	$engine->addException($ex);
}
ProcessTemplate('repository/repositorylist.html.php');
?>