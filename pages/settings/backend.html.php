<?php GlobalHeader(); ?>

<script type="text/javascript">
$(document).ready(function(){

  updateSettingsSelection();
  $("select").change(updateSettingsSelection);

  $('#SVNAuthFileTest').click(function(){
    testSettings("SVNAuthFile", {SVNAuthFile: $("#SVNAuthFile").val()}, "#SVNAuthFileTestResult");
  });

  $('#SVNUserFileTest').click(function(){
    testSettings("SVNUserFile", {SVNUserFile: $("#SVNUserFile").val()}, "#SVNUserFileTestResult");
  });

  $('#SVNUserDigestFileTest').click(function(){
    testSettings("SVNUserDigestFile", {SVNUserDigestFile: $("#SVNUserDigestFile").val()}, "#SVNUserDigestFileTestResult");
  });

  $('#SVNParentPathTest').click(function(){
    testSettings("SVNParentPath", {SVNParentPath: $("#SVNParentPath").val()}, "#SVNParentPathTestResult");
  });

  $('#SvnExecutableTest').click(function(){
    testSettings("SvnExecutable", {SvnExecutable: $("#SvnExecutable").val()}, "#SvnExecutableTestResult");
  });

  $('#SvnAdminExecutableTest').click(function(){
    testSettings("SvnAdminExecutable", {SvnAdminExecutable: $("#SvnAdminExecutable").val()}, "#SvnAdminExecutableTestResult");
  });

  $('#LdapConnectTest').click(function(){
    testSettings("LdapConnection",
    {LdapHostAddress: $("#LdapHostAddress").val(), LdapProtocolVersion: $("#LdapProtocolVersion").val(),
     LdapBindDN: $("#LdapBindDN").val(), LdapBindPassword: $("#LdapBindPassword").val()},
    "#LdapConnectTestResult");
  });

  $('#LdapUserTest').click(function(){
    testSettings("LdapUser",
    {LdapHostAddress: $("#LdapHostAddress").val(), LdapProtocolVersion: $("#LdapProtocolVersion").val(),
     LdapBindDN: $("#LdapBindDN").val(), LdapBindPassword: $("#LdapBindPassword").val(),
     LdapUserBaseDn: $("#LdapUserBaseDn").val(), LdapUserSearchFilter: $("#LdapUserSearchFilter").val(),
     LdapUserAttributes: $("#LdapUserAttributes").val()},
    "#LdapUserTestResult");
  });

  $('#LdapGroupTest').click(function(){
    testSettings("LdapGroup",
    {LdapHostAddress: $("#LdapHostAddress").val(), LdapProtocolVersion: $("#LdapProtocolVersion").val(),
     LdapBindDN: $("#LdapBindDN").val(), LdapBindPassword: $("#LdapBindPassword").val(),
     LdapGroupBaseDn: $("#LdapGroupBaseDn").val(), LdapGroupSearchFilter: $("#LdapGroupSearchFilter").val(),
     LdapGroupAttributes: $("#LdapGroupAttributes").val(), LdapGroupsToUserAttribute: $("#LdapGroupsToUserAttribute").val(),
     LdapGroupsToUserAttributeValue: $("#LdapGroupsToUserAttributeValue").val()},
    "#LdapGroupTestResult");
  });

});
</script>

<h1><?php Translate("Settings"); ?></h1>
<form method="post" action="settings.php?save=1">

<!-- Basics -->
<table class="datatable settings" id="tbl_basic">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("Subversion authorization"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td id="td_desc_authz">
        <?php Translate("Subversion authorization file"); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("AuthzSVNAccessFile", "http.conf", "mod_svn")); ?><br>
        <small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SVNAuthFileEx"); ?></small>
      </td>
      <td id="td_desc_authz_group">
        <?php Translate("Subversion authorization global group file"); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("AuthzSVNGroupsFile", "http.conf", "mod_svn")); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("AuthzVisualSVNSubversionGroupsFile", "http.conf", "visualsvn-server")); ?><br>
        <small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("AuthzSVNGroupsFileEx"); ?></small>
      </td>
      <td>
        <input type="text" name="SVNAuthFile" id="SVNAuthFile" value="<?php PrintStringValue("SVNAuthFile"); ?>">
        <input type="button" id="SVNAuthFileTest" value="<?php Translate("Test"); ?>">
        <span id="SVNAuthFileTestResult" style="display:none;"></span>
      </td>
    </tr>
    <tr id="tr_svn_authz_relative">
      <td>
        <?php Translate("Subversion authorization file in repository, relative to &lt;SVNParentPath&gt;/&lt;repo1&gt;/conf"); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("AuthzSVNReposRelativeAccessFile", "http.conf", "mod_svn")); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("AuthzVisualSVNSubversionReposRelativeAccessFile", "http.conf", "visualsvn-server")); ?><br>
        <small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("AuthzSVNReposRelativeAccessFileEx"); ?></small>
      </td>
      <td>
        <input type="text" name="AuthzSVNReposRelativeAccessFile" id="AuthzSVNReposRelativeAccessFile" value="<?php PrintStringValue("AuthzSVNReposRelativeAccessFile"); ?>">
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- Provider settings -->
<table class="datatable settings" id="tbl_providers">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("Data providers"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>User view provider type:</td>
      <td>
        <select name="UserViewProviderType" id="UserViewProviderType">
          <?php foreach(GetArrayValue("userViewProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>User edit provider type:</td>
      <td>
        <select name="UserEditProviderType" id="UserEditProviderType">
          <?php foreach(GetArrayValue("userEditProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Group view provider type:</td>
      <td>
        <select name="GroupViewProviderType" id="GroupViewProviderType">
          <?php foreach(GetArrayValue("groupViewProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Group edit provider type:</td>
      <td>
        <select name="GroupEditProviderType" id="GroupEditProviderType">
          <?php foreach(GetArrayValue("groupEditProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Repository view provider type:</td>
      <td>
        <select name="RepositoryViewProviderType" id="RepositoryViewProviderType">
          <?php foreach(GetArrayValue("repositoryViewProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Repository edit provider type:</td>
      <td>
        <select name="RepositoryEditProviderType" id="RepositoryEditProviderType">
          <?php foreach(GetArrayValue("repositoryEditProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Access-Path view provider type:</td>
      <td>
        <select name="AccessPathViewProviderType" id="AccessPathViewProviderType">
          <?php foreach(GetArrayValue("accessPathViewProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Access-Path edit provider type:</td>
      <td>
        <select name="AccessPathEditProviderType" id="AccessPathEditProviderType">
          <?php foreach(GetArrayValue("accessPathEditProviderTypes") as $t): ?>
          <option><?php print($t); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- SVNUserFile -->
<table class="datatable settings" id="tbl_userfile">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("User authentication"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <?php Translate("User authentication file (SVNUserFile)"); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("AuthUserFile", "http.conf", "mod_svn,visualsvn-server")); ?><br>
        <small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SVNUserFileEx"); ?></small></td>
      <td>
        <input type="text" name="SVNUserFile" id="SVNUserFile" value="<?php PrintStringValue("SVNUserFile"); ?>">
        <input type="button" id="SVNUserFileTest" value="<?php Translate("Test"); ?>">
        <span id="SVNUserFileTestResult" style="display:none;"></span>
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- SVNUserDigestFile -->
<table class="datatable settings" id="tbl_userdigestfile">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("User digest authentication"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php Translate("User authentication file (SVNUserDigestFile)"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SVNUserDigestFileEx"); ?></small></td>
      <td>
        <input type="text" name="SVNUserDigestFile" id="SVNUserDigestFile" value="<?php PrintStringValue("SVNUserDigestFileEx"); ?>">
        <input type="button" id="SVNUserDigestFileTest" value="<?php Translate("Test"); ?>">
        <span id="SVNUserDigestFileTestResult" style="display:none;"></span>
      </td>
    </tr>
	<tr>
		<td><?php Translate("Digest realm"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SVNDigestRealmEx"); ?></small></td>
		<td>
			<input type="text" name="SVNDigestRealm" id="SVNDigestRealm" value="<?php PrintStringValue("SVNDigestRealm"); ?>">
		</td>
	</tr>
  </tbody>
</table>
<br>

<!-- Subversion configuration -->
<table class="datatable settings" id="tbl_subversion">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("Subversion settings"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <?php Translate("Parent directory of the repositories (SVNParentPath)"); ?><br>
        <?php Translate("<b>%0</b> in %1 for %2", array("SVNParentPath", "http.conf", "mod_svn,visualsvn-server")); ?><br>
        <small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SVNParentPathEx"); ?></small></td>
      <td>
        <input type="text" name="SVNParentPath" id="SVNParentPath" value="<?php PrintStringValue("SVNParentPath"); ?>">
        <input type="button" id="SVNParentPathTest" value="<?php Translate("Test"); ?>">
        <span id="SVNParentPathTestResult" style="display:none;"></span>
      </td>
    </tr>
    <tr>
      <td><?php Translate("Subversion client executable"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SvnExecutableEx"); ?></small></td>
      <td>
        <input type="text" name="SvnExecutable" id="SvnExecutable" value="<?php PrintStringValue("SvnExecutable"); ?>">
        <input type="button" id="SvnExecutableTest" value="<?php Translate("Test"); ?>">
        <span id="SvnExecutableTestResult" style="display:none;"></span>
      </td>
    </tr>
    <tr>
      <td><?php Translate("Subversion admin executable"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("SvnAdminExecutableEx"); ?></small></td>
      <td>
        <input type="text" name="SvnAdminExecutable" id="SvnAdminExecutable" value="<?php PrintStringValue("SvnAdminExecutable"); ?>">
        <input type="button" id="SvnAdminExecutableTest" value="<?php Translate("Test"); ?>">
        <span id="SvnAdminExecutableTestResult" style="display:none;"></span>
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- GUI configuration -->
<table class="datatable settings" id="tbl_gui">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("GUI settings"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="2">
      <?php Translate("ApacheDirectoryListing and CustomDirectoryListing's Placeholders: <br/> %1=Repository name; %2=Relative path (no leading slash)") ?>
      </td>
    </tr>
    <tr>
      <td><?php Translate("The web url to the Apache WebDAV directory listing (ApacheDirectoryListing)"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("ApacheDirectoryListingEx"); ?></small></td>
      <td>
        <input type="text" name="ApacheDirectoryListing" id="ApacheDirectoryListing" value="<?php PrintStringValue("ApacheDirectoryListing"); ?>">
      </td>
    </tr>
    <tr>
      <td><?php Translate("The web url to the custom web-application to browse the subversion repository <br/> (e.g. ViewVC, WebSVN, ...) (CustomDirectoryListing)"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("CustomDirectoryListingEx"); ?></small></td>
      <td>
        <input type="text" name="CustomDirectoryListing" id="CustomDirectoryListing" value="<?php PrintStringValue("CustomDirectoryListing"); ?>">
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- LDAP connection -->
<table class="datatable settings" id="tbl_ldapconnection">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("LDAP connection information"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php Translate("Host address"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapHostAddressEx"); ?></small></td>
      <td><input type="text" name="LdapHostAddress" id="LdapHostAddress" value="<?php PrintStringValue("LdapHostAddress"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Protocol version"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapProtocolVersionEx"); ?></small></td>
      <td><input type="text" name="LdapProtocolVersion" id="LdapProtocolVersion" value="<?php PrintStringValue("LdapProtocolVersion"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Bind DN"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapBindDNEx"); ?></small></td>
      <td><input type="text" name="LdapBindDN" id="LdapBindDN" value="<?php PrintStringValue("LdapBindDN"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Bind password"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapBindPasswordEx"); ?></small></td>
      <td>
        <input type="password" name="LdapBindPassword" id="LdapBindPassword" value="<?php PrintStringValue("LdapBindPassword"); ?>">
        <input type="button" id="LdapConnectTest" value="<?php Translate("Test"); ?>">
        <span id="LdapConnectTestResult" style="display:none;"></span>
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- LDAP user provider -->
<table class="datatable settings" id="tbl_ldapuser">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("LDAP user provider information"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php Translate("Base DN"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapUserBaseDnEx"); ?></small></td>
      <td><input type="text" name="LdapUserBaseDn" id="LdapUserBaseDn" value="<?php PrintStringValue("LdapUserBaseDn"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Search filter"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapUserSearchFilterEx"); ?></small></td>
      <td><input type="text" name="LdapUserSearchFilter" id="LdapUserSearchFilter" value="<?php PrintStringValue("LdapUserSearchFilter"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Attributes"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapUserAttributesEx"); ?></small></td>
      <td>
        <input type="text" name="LdapUserAttributes" id="LdapUserAttributes" value="<?php PrintStringValue("LdapUserAttributes"); ?>">
        <input type="button" id="LdapUserTest" value="<?php Translate("Test"); ?>">
        <span id="LdapUserTestResult" style="display:none;"></span>
      </td>
    </tr>
  </tbody>
</table>
<br>

<!-- LDAP Group provider -->
<table class="datatable settings" id="tbl_ldapgroup">
  <colgroup>
    <col width="50%">
    <col width="50%">
  </colgroup>
  <thead>
    <tr>
      <th colspan="2"><?php Translate("LDAP group provider information"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php Translate("Base DN"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapGroupBaseDnEx"); ?></small></td>
      <td><input type="text" name="LdapGroupBaseDn" id="LdapGroupBaseDn" value="<?php PrintStringValue("LdapGroupBaseDn"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Search filter"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapGroupSearchFilterEx"); ?></small></td>
      <td><input type="text" name="LdapGroupSearchFilter" id="LdapGroupSearchFilter" value="<?php PrintStringValue("LdapGroupSearchFilter"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Attributes"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapGroupAttributesEx"); ?></small></td>
      <td><input type="text" name="LdapGroupAttributes" id="LdapGroupAttributes" value="<?php PrintStringValue("LdapGroupAttributes"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Groups to user attribute"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapGroupsToUserAttributeEx"); ?></small></td>
      <td><input type="text" name="LdapGroupsToUserAttribute" id="LdapGroupsToUserAttribute" value="<?php PrintStringValue("LdapGroupsToUserAttribute"); ?>"></td>
    </tr>
    <tr>
      <td><?php Translate("Groups to user attribute value"); ?><br><small><b><?php Translate("Example"); ?>:</b> <?php PrintStringValue("LdapGroupsToUserAttributeValueEx"); ?></small></td>
      <td>
        <input type="text" name="LdapGroupsToUserAttributeValue" id="LdapGroupsToUserAttributeValue" value="<?php PrintStringValue("LdapGroupsToUserAttributeValue"); ?>">
        <input type="button" id="LdapGroupTest" value="<?php Translate("Test"); ?>">
        <span id="LdapGroupTestResult" style="display:none;"></span>
      </td>
    </tr>
  </tbody>
</table>
<br>

<input type="submit" value="<?php Translate("Save configuration"); ?>">

</form>

<?php GlobalFooter(); ?>