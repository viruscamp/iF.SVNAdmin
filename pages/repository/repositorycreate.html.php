<?php GlobalHeader(); ?>

<script type="text/javascript">
$(document).ready(function(){

  $("#repostructuretype").change(function(){
    var eSingle = $("#repostructuretype-single");
    var eMulti  = $("#repostructuretype-multi");

    eSingle.hide();
    eMulti.hide();

    if ($(this).val() == "simple:single"){ eSingle.show(); }
    else if ($(this).val() == "simple:multi"){ eMulti.show(); }
  });
});
</script>

<h1><?php Translate("Create repository"); ?></h1>
<p class="hdesc"><?php Translate("Create a new repository to manage your sources."); ?></p>
<div>
  <form method="POST" action="repositorycreate.php">
	  
	<div class="form-field">
		<label for="pi"><?php Translate('Repository location'); ?></label>
		<select name="pi" id="pi" class="">
			<?php foreach (GetArrayValue('RepositoryParentList') as $rp) : ?>
				<option value="<?php print($rp->getEncodedIdentifier()); ?>">
					<?php print($rp->path); ?>
					<?php
					if (!empty($rp->description)) {
						print(' - ');
						print($rp->description);
					}
					?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

    <div class="form-field">
      <label for="reponame"><?php Translate("Repository name"); ?></label>
      <input type="text" name="reponame" id="reponame" class="lineedit">
      <p>
        <b><?php Translate("Valid signs for repository name are"); ?>:</b> A-Z, a-z, 0-9, <?php Translate("Underscore"); ?>(_), <?php Translate("Hyphen"); ?>(-) <i><?php Translate("No space!"); ?></i>
      </p>
    </div>

    <div class="form-field">
      <label for="repotype"><?php Translate("Type"); ?></label>
      <select name="repotype">
        <option value="fsfs" selected="selected"><?php Translate("File System (Recommended)"); ?></option>
        <option value="bdb"><?php Translate("Berkly DB"); ?></option>
      </select>
    </div>

    <div class="form-field">
      <label for="groupcreate"><?php Translate("Whether to create a group with the same name of the repository"); ?></label>
      <input type="radio" name="groupcreate" value="" checked><?php Translate("Don't create"); ?></input>
      <input type="radio" name="groupcreate" value="r"><?php Translate("Create with Read only"); ?></input>
      <input type="radio" name="groupcreate" value="rw"><?php Translate("Create with Read &amp; Write"); ?></input>
    </div>

    <div class="form-field">
      <label for="permissioncreate"><?php Translate("Assign permissions after repository created"); ?></label>
      <input type="text" name="permissioncreate" id="permissioncreate" list="permissioncreate-datalist"></input>
      <datalist id="permissioncreate-datalist">
        <option value="" />
        <option value="admin=rw" />
        <option value="*=rw" />
        <option value="*=r,admin=rw" />
      </datalist>
    </div>

    <div class="form-field">
      <label for="repostructuretype"><?php Translate("Pre-defined repository structure"); ?></label>
      <select name="repostructuretype" id="repostructuretype">
        <option value=""><?php Translate("No pre-defined structure"); ?></option>
        <option value="simple:single"><?php Translate("Single project structure"); ?></option>
        <option value="simple:multi"><?php Translate("Multi project structure"); ?></option>
        <?php foreach (GetArrayValue('RepositoryTemplateList') as $rt) : ?>
        <option value="template:<?php print($rt); ?>"><?php Translate("Template") ?> : <?php print($rt); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-field" id="repostructuretype-single" style="display:none;">
      <p>
        <b><?php Translate("Single project structure"); ?></b><br>
        <?php Translate("Creates the folders 'trunk', 'branches' and 'tags' in the root of the repository."); ?>
      </p>
    </div>

    <div class="form-field" id="repostructuretype-multi" style="display:none;">
      <label for="projectname"><?php Translate("Project name"); ?></label>
      <input type="text" name="projectname" id="projectname" value="" class="lineedit">
      <p>
        <b><?php Translate("Multi project structure"); ?>:</b><br>
        <?php Translate("The folders 'trunk', 'branches' and 'tags' will be created in a subfolder, which is named by the project's name."); ?>
      </p>
    </div>

    <div class="formsubmit">
      <input type="submit" name="create" value="<?php Translate("Create"); ?>" class="addbtn">
    </div>
  </form>

  <p>
    <a href="repositorylist.php">&#xAB; <?php Translate("Back to overview"); ?></a>
  </p>

</div>

<?php GlobalFooter(); ?>
