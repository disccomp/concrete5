<?
defined('C5_EXECUTE') or die("Access Denied.");
$u = new User();
$uh = Loader::helper('concrete/user');
$txt = Loader::helper('text');
$vals = Loader::helper('validation/strings');
$valt = Loader::helper('validation/token');
$valc = Loader::helper('concrete/validation');
$dtt = Loader::helper('form/date_time');
$form = Loader::helper('form');
$ih = Loader::helper('concrete/interface');
$av = Loader::helper('concrete/avatar');

if ($_POST['create']) {

	$username = trim($_POST['uName']);
	$username = preg_replace("/\s+/", " ", $username);
	$_POST['uName'] = $username;	
	
	$password = $_POST['uPassword'];
	
	if (!$vals->email($_POST['uEmail'])) {
		$error[] = t('Invalid email address provided.');
	} else if (!$valc->isUniqueEmail($_POST['uEmail'])) {
		$error[] = t("The email address '%s' is already in use. Please choose another.",$_POST['uEmail']);
	}
	
	if (strlen($username) < USER_USERNAME_MINIMUM) {
		$error[] = t('A username must be between at least %s characters long.',USER_USERNAME_MINIMUM);
	}

	if (strlen($username) > USER_USERNAME_MAXIMUM) {
		$error[] = t('A username cannot be more than %s characters long.',USER_USERNAME_MAXIMUM);
	}

	if (strlen($username) >= USER_USERNAME_MINIMUM && !$valc->username($username)) {
		if(USER_USERNAME_ALLOW_SPACES) {
			$error[] = t('A username may only contain letters, numbers and spaces.');
		} else {
			$error[] = t('A username may only contain letters or numbers.');
		}
	}

	if (!$valc->isUniqueUsername($username)) {
		$error[] = t("The username '%s' already exists. Please choose another",$username);
	}		

	if ($username == USER_SUPER) {
		$error[] = t('Invalid Username');
	}

	
	if ((strlen($password) < USER_PASSWORD_MINIMUM) || (strlen($password) > USER_PASSWORD_MAXIMUM)) {
		$error[] = t('A password must be between %s and %s characters',USER_PASSWORD_MINIMUM,USER_PASSWORD_MAXIMUM);
	}
		
	if (strlen($password) >= USER_PASSWORD_MINIMUM && !$valc->password($password)) {
		$error[] = t('A password may not contain ", \', >, <, or any spaces.');
	}

	if (!$valt->validate('create_account')) {
		$error[] = $valt->getErrorMessage();
	}

	Loader::model("attribute/categories/user");
	$aks = UserAttributeKey::getRegistrationList();

	foreach($aks as $uak) {
		if ($uak->isAttributeKeyRequiredOnRegister()) {
			$e1 = $uak->validateAttributeForm();
			if ($e1 == false) {
				$error[] = t('The field "%s" is required', $uak->getAttributeKeyName());
			} else if ($e1 instanceof ValidationErrorHelper) {
				$error[] = $e1->getList();
			}
		}
	}
	
	if (!$error) {
		// do the registration
		$data = array('uName' => $username, 'uPassword' => $password, 'uEmail' => $_POST['uEmail'], 'uDefaultLanguage' => $_POST['uDefaultLanguage']);
		$uo = UserInfo::add($data);
		
		if (is_object($uo)) {

			if (is_uploaded_file($_FILES['uAvatar']['tmp_name'])) {
				$uHasAvatar = $av->updateUserAvatar($_FILES['uAvatar']['tmp_name'], $uo->getUserID());
			}
			
			foreach($aks as $uak) {
				$uak->saveAttributeForm($uo);				
			}

			$uo->updateGroups($_POST['gID']);
			$uID = $uo->getUserID();
			$this->controller->redirect('/dashboard/users/search?uID=' . $uID . '&user_created=1');
		} else {
			$error[] = t('An error occurred while trying to create the account.');
		}
		
	}		
}

?>
	<h1><span><?=t('Create Account')?></span></h1>
	
	<div class="ccm-dashboard-inner"> 
	
	<div class="actions">
	<span class="required">*</span> - <?=t('required field')?>
	</div>
	
	<form method="post" enctype="multipart/form-data" id="ccm-user-form" action="<?=$this->url('/dashboard/users/add')?>">
	<?=$valt->output('create_account')?>
	
	<input type="hidden" name="_disableLogin" value="1">

	<h2><?=t('Required Information')?></h2>
	
	<div style="margin:0px; padding:0px; width:100%; height:auto" >
	<table class="entry-form" border="0" cellspacing="1" cellpadding="0">
	<tr>
		<td class="subheader" width="50%"><?=t('Username')?> <span class="required">*</span></td>
		<td class="subheader" width="50%"><?=t('Password')?> <span class="required">*</span></td>
	</tr>
	<tr>
		<td><input type="text" name="uName" autocomplete="off" value="<?=$_POST['uName']?>" style="width: 95%"></td>
		<td><input type="password" autocomplete="off" name="uPassword" value="" style="width: 95%"></td>
	</tr>
	<tr>
		<td class="subheader"><?=t('Email Address')?> <span class="required">*</span></td>
		<td class="subheader"><?=t('User Avatar')?></td>
	</tr>	
	<tr>
		<td><input type="text" name="uEmail" autocomplete="off" value="<?=$_POST['uEmail']?>" style="width: 95%"></td>
		<td><input type="file" name="uAvatar" style="width: 95%"/></td>
	</tr>
	<?
	$languages = Localization::getAvailableInterfaceLanguages();
	if (count($languages) > 0) { ?>

	<tr>
		<td class="subheader" colspan="2"><?=t('Default Language')?></td>
	</tr>	
	<tr>
		<Td colspan="2">
		<?
			array_unshift($languages, 'en_US');
			$locales = array();
			Loader::library('3rdparty/Zend/Locale');
			foreach($languages as $lang) {
				$loc = new Zend_Locale($lang);
				$locales[$lang] = Zend_Locale::getTranslation($loc->getLanguage(), 'language', ACTIVE_LOCALE);
			}
			print $form->select('uDefaultLanguage', $locales, ACTIVE_LOCALE);
		?>
		</td>
	</tr>	
	<? } ?>

	</table>
	</div>

	<?
	Loader::model('attribute/categories/user');
	$attribs = UserAttributeKey::getRegistrationList();
	if (count($attribs) > 0) { ?>
	
	<table class="entry-form" border="0" cellspacing="1" cellpadding="0">
	<tr>
		<td class="header"><?=t('Registration Data')?></td>
	</tr>
	<? foreach($attribs as $ak) { ?>
	<tr>
		<td class="subheader"><?=$ak->getAttributeKeyName()?> <? if ($ak->isAttributeKeyRequiredOnRegister()) { ?><span class="ccm-required">*</span><? } ?></td>
	</tr>
	<tr>
		<td width="100%"><? $ak->render('form', $caValue, false)?></td>
	</tr>
	<? } ?>
	</table>
	
	
	<? } ?>
<?
	Loader::model("search/group");
	$gl = new GroupSearch();
	if ($gl->getTotal() < 1000) { 
		$gl->setItemsPerPage(1000);
		?>
		<h2><?=t('Groups')?></h2>
		<table class="entry-form" border="0" cellspacing="1" cellpadding="0">
		<tr>
			<td class="header">
				<?=t('Groups')?>
			</td>
		</tr>
		<? 
		$gArray = $gl->getPage(); ?>
		<tr>
			<td>
			<? foreach ($gArray as $g) { ?>
				<input type="checkbox" name="gID[]" value="<?=$g['gID']?>" style="vertical-align: middle" <? 
					if (is_array($_POST['gID'])) {
						if (in_array($g['gID'], $_POST['gID'])) {
							echo(' checked ');
						}
					}
				?> /> <?=$g['gName']?><br>
			<? } ?>
			
			<div id="ccm-additional-groups"></div>
			
			</td>
		</tr>
		</table>
	<? } ?>	

	<div class="ccm-buttons">
		<input type="hidden" name="create" value="1" />
		<?=$ih->submit(t('Create User'))?>

	</div>	

	<div class="ccm-spacer">&nbsp;</div>
	
	</div>
	</form>
