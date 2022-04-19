<?php GlobalHeader(); ?>

<?php
$redirectTo = GetValue("RedirectTo");
if ($redirectTo) {
?>
	<div class="top-message top-message-info">
		<div class="top-message-header">
			<h3><?php Translate("Message list"); ?></h3>
		</div>
		<div class="top-message-content">
			<ul>
				<li>Will redirect</li>
			</ul>
		</div>
	</div>
	<script>setTimeout(function() { window.location.href='accesspathview.php?accesspath=<?php print($redirectTo); ?>'; }, 0);</script>
<?php
}
?>

<h1><?php Translate("Create access-path"); ?></h1>
<p class="hdesc"><?php Translate("Create a new access-path to grant access to users and groups."); ?></p>

<div>
	<form method="POST" action="accesspathcreate.php">
		<input type="hidden" name="redirect" value="<?php print($_REQUEST["redirect"]) ?>">
		<div class="form-field">
			<label for="path"><?php Translate("Path"); ?>:</label>
			<input type="text" name="path" id="path" class="lineedit" value="<?php PrintStringValue("DefaultAccessPath"); ?>">
			<p>
				<b><?php Translate("Syntax"); ?>:</b> <i>&lt;RepoName&gt;:/&lt;path&gt;</i>
				<br><?php Translate("An \"/\" as access path holds permissions over all repositories."); ?>
			</p>
		</div>

		<?php if (IsProviderActive(PROVIDER_REPOSITORY_VIEW) && HasAccess(ACL_MOD_REPO, ACL_ACTION_VIEW)) : ?>
		<p style="margin-top:0; margin-bottom:20px;">
			<img src="templates/icons/addpath.png" border="0" alt="<?php Translate("Browse..."); ?>">
			<a href="repositorylist.php"><?php Translate("Browse..."); ?></a>
		</p>
		<?php endif; ?>

		<div class="formsubmit">
			<input type="submit" name="create" value="<?php Translate("Create"); ?>" class="addbtn">
		</div>

	</form>
	<p>
		<a href="accesspathslist.php">&#xAB; <?php Translate("Back to overview"); ?></a>
	</p>
</div>

<?php GlobalFooter(); ?>