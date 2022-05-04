<?php
if (!defined('ACTION_HANDLING')) {
	die("HaHa!");
}

$engine = \svnadmin\core\Engine::getInstance();

//
// Authentication
//

if (!$engine->isProviderActive(PROVIDER_REPOSITORY_EDIT)) {
	$engine->forwardError(ERROR_INVALID_MODULE);
}

$engine->checkUserAuthentication(true, ACL_MOD_REPO, ACL_ACTION_ADD);

//
// HTTP Request Vars
//

$varParentIdentifierEnc = get_request_var('pi');
$reponame = get_request_var("reponame");
$repotype = get_request_var("repotype");

$varParentIdentifier = rawurldecode($varParentIdentifierEnc);

//
// Validation
//

if ($reponame == NULL) {
	$engine->addException(new ValidationException(tr("You have to fill out all fields.")));
}
else {
	$r = new \svnadmin\core\entities\Repository($reponame, $varParentIdentifier);

	// Create repository.
	try {
		$engine->getRepositoryEditProvider()->create($r, $repotype);
		$engine->getRepositoryEditProvider()->save();
		$engine->addMessage(tr("The repository %0 has been created successfully", array($reponame)));

		$repositoryTemplate = null;
		// Create a initial repository structure.
		try {
			$repoPredefinedStructure = get_request_var("repostructuretype");
			if ($repoPredefinedStructure != NULL) {
				switch ($repoPredefinedStructure) {
					case "simple:single":
						$engine->getRepositoryEditProvider()
							->mkdir($r, array('trunk', 'branches', 'tags'));
						break;

					case "simple:multi":
						$projectName = get_request_var("projectname");
						if ($projectName != NULL) {
							$engine->getRepositoryEditProvider()
								->mkdir($r, array(
									$projectName . '/trunk',
									$projectName . '/branches',
									$projectName . '/tags'
								));
						}
						else {
							throw new ValidationException(tr("Missing project name"));
						}
						break;

					default: 
						$templatePrefix = "template:";
						if (strpos($repoPredefinedStructure, $templatePrefix) === 0) {
							$repositoryTemplate = substr($repoPredefinedStructure, strlen($templatePrefix));
						}
				}
			}
		}
		catch (Exception $e1) {
			$engine->addException($e1);
		}

		// Apply repository template, must before permission assign
		try {
			if ($repositoryTemplate) {
				$repotmpl = \svnadmin\providers\DirRepositoryTemplateProvider::getInstance();
				if ($repotmpl->copyHooks($repositoryTemplate, $r)) {
					$engine->addMessage(tr("The repository %0 copy hooks/ successfully", array($reponame)));
				}
				if ($repotmpl->copyConf($repositoryTemplate, $r)) {
					$engine->addMessage(tr("The repository %0 copy conf/ successfully", array($reponame)));
				}
				if ($repotmpl->initFilesAndProps($repositoryTemplate, $r)) {
					$engine->addMessage(tr("The repository %0 add init files and set init props successfully", array($reponame)));
				}
			}
		}
		catch (Exception $e2) {
			$engine->addException($e2);
		}

		// Create a group with the same name of the repository
		try {
			$perm = get_request_var("groupcreate");
			if ($perm
				&& $engine->isProviderActive(PROVIDER_ACCESSPATH_EDIT)
				&& $engine->isProviderActive(PROVIDER_GROUP_EDIT)) {

				$objAccessPath = new \svnadmin\core\entities\AccessPath($reponame . ':/');
				$objGroup = new \svnadmin\core\entities\Group($reponame, $reponame);
				$objPermission = new \svnadmin\core\entities\Permission;
				if ($perm == "r") {
					$objPermission->perm = \svnadmin\core\entities\Permission::$PERM_READ;
				} else if ($perm == "rw") {
					$objPermission->perm =  \svnadmin\core\entities\Permission::$PERM_READWRITE;
				} else {
					throw new ValidationException(tr("Invalid Permission for created group"));
				}

				$engine->getGroupEditProvider()->addGroup($objGroup);
				$engine->getGroupEditProvider()->save();

				$engine->getAccessPathEditProvider()->createAccessPath($objAccessPath);
				$engine->getAccessPathEditProvider()->assignGroupToAccessPath($objGroup, $objAccessPath, $objPermission);
				$engine->getAccessPathEditProvider()->save();
			}
		}
		catch (Exception $e3) {
			$engine->addException($e3);
		}

		// Assign permissions after repository created
		try {
			$permissioncreate = get_request_var("permissioncreate");
			if ($permissioncreate
				&& $engine->isProviderActive(PROVIDER_ACCESSPATH_EDIT)) {

				$objAccessPath = new \svnadmin\core\entities\AccessPath($reponame . ':/');
				$engine->getAccessPathEditProvider()->createAccessPath($objAccessPath);

				foreach(explode(",",$permissioncreate) as $line) {
					$values = explode("=", $line);
					if (count($values) != 2) {
						throw new ValidationException(tr("Invalid permissions to assign"));
					}

					$perm = $values[1];
					$objPermission = new \svnadmin\core\entities\Permission;
					if ($perm == "") {
						$objPermission->perm = \svnadmin\core\entities\Permission::$PERM_NONE;
					} else if ($perm == "r") {
						$objPermission->perm = \svnadmin\core\entities\Permission::$PERM_READ;
					} else if ($perm == "rw") {
						$objPermission->perm =  \svnadmin\core\entities\Permission::$PERM_READWRITE;
					} else {
						throw new ValidationException(tr("Invalid permissions to assign"));
					}

					$group_user = $values[0];
					if(strpos($group_user, "@" ) === 0){
						$group = explode("@",$group_user)[1];
						$objGroup = new \svnadmin\core\entities\Group($group, $group);
						if (!$engine->getGroupViewProvider()->groupExists($objGroup)){
							throw new ValidationException(tr("Group:'%0' does not exist.", array($group)));
						}
						$engine->getAccessPathEditProvider()->assignGroupToAccessPath($objGroup, $objAccessPath, $objPermission);
					} else {
						$objUser = new \svnadmin\core\entities\User($group_user, $group_user);
						if ($group_user != "*" && !$engine->getUserViewProvider()->userExists($objUser)){
							throw new ValidationException(tr("User:'%0' does not exist.", array($group_user)));
						}
						$engine->getAccessPathEditProvider()->assignUserToAccessPath($objUser, $objAccessPath, $objPermission);
					}
				}
				$engine->getAccessPathEditProvider()->save();
			}
		}
		catch (Exception $e4) {
			$engine->addException($e4);
		}
	}
	catch (Exception $e) {
		$engine->addException($e);
	}
}
?>