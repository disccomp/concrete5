<? defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<div class="ccm-pane-controls">

<script type="text/javascript">
	function makeAlias(value, formInputID) {
		alias = value.replace(/[&]/gi, "and");
		alias = alias.replace(/[\s|.]+/gi, "_");
		alias = alias.replace(/[^0-9A-Za-z-]/gi, "_");
		alias = alias.replace(/--/gi, '_');
		alias = alias.toLowerCase();
		
		formObj = document.getElementById(formInputID);
		formObj.value = alias;
	}
	
	function addOption(akID) {
		akOptions = document.getElementById("akID" + akID);
		akValue = document.getElementById("TEXTakID" + akID).value;
		akValue = akValue.replace(/^\s*|\s*$/g,"");
		if (akValue) {
			i = akOptions.length;
			akOptions.options[i] = new Option(akValue, akValue);
			akOptions.options[i].selected = true;		
		}
	}
	
</script>

<? 

Loader::model('collection_attributes');
Loader::model('collection_types');
$dh = Loader::helper('date');

$ctArray = CollectionType::getList($c->getAllowedSubCollections());
	?>
	
	<h1><?=t('Add Page')?></h1>

	<form method="post" action="<?=$c->getCollectionAction()?>" id="ccmAddPage">		
	<input type="hidden" name="rel" value="<?=$_REQUEST['rel']?>" />
	<input type="hidden" name="ctID" value="0" />
	 
	<div class="ccm-form-area">
	
		<h2><?=t('Choose a Page Type')?></h2>
		
		<div class="ccm-scroller" current-page="1" current-pos="0" num-pages="<?=ceil(count($ctArray)/4)?>">
			<a href="javascript:void(0)" class="ccm-scroller-l"><img src="<?=ASSETS_URL_IMAGES?>/button_scroller_l.png" width="28" height="79" alt="l" /></a>
			<a href="javascript:void(0)" class="ccm-scroller-r"><img src="<?=ASSETS_URL_IMAGES?>/button_scroller_r.png" width="28" height="79" alt="l" /></a>
			
			<div class="ccm-scroller-inner">
				<ul id="ccm-select-page-type" style="width: <?=count($ctArray) * 132?>px">
					<? 
					foreach($ctArray as $ct) { ?>
						
						<? $class = ($ct->getCollectionTypeID() == $ctID) ? 'ccm-item-selected' : ''; ?>
				
						<li class="<?=$class?>"><a href="javascript:void(0)" ccm-page-type-id="<?=$ct->getCollectionTypeID()?>"><img src="<?=REL_DIR_FILES_COLLECTION_TYPE_ICONS?>/<?=$ct->getCollectionTypeIcon()?>" /></a>
						<span><?=$ct->getCollectionTypeName()?></span>
						</li> 
					
					<? } ?>
				
				</ul>
			</div>
		
		</div>

		<h2><?=t('Page Information')?></h2>

		<div class="ccm-field">	
			<div class="ccm-field-one" style="width: 400px">
				<label><?=t('Name')?></label> <input type="text" name="cName" value="" class="text" style="width: 100%" onBlur="makeAlias(this.value, 'cHandle')">
			</div>
			
			<div class="ccm-field-two" style="width: 200px"	>
				<label><?=t('Alias')?></label> <input type="text" name="cHandle" style="width: 100%" value="" id="cHandle">
			</div>
		
			<div class="ccm-spacer">&nbsp;</div>
		</div>

	
		<div class="ccm-field">
			<label><?=t('Description')?></label> <textarea name="cDescription" style="width: 100%; height: 80px"></textarea>
		</div>
	
	</div>

	<div class="ccm-buttons">
	<!--	<a href="javascript:void(0)" onclick="ccm_hidePane()" class="ccm-button-left cancel"><span><em class="ccm-button-close">Cancel</em></span></a>//-->
	<a href="javascript:void(0)" onclick="ccm_doAddSubmit()" class="ccm-button-right accept"><span><?=t('Add')?></span></a>
	</div>	
	<input type="hidden" name="add" value="1" />
	<input type="hidden" name="processCollection" value="1">
	<div class="ccm-spacer">&nbsp;</div>
	 
	
</form>
</div>

<? $pageTypeMSG = t('You must choose a page type.'); ?>

<script type="text/javascript">
$(function() {
	$("a.ccm-scroller-l").hover(function() {
		$(this).children('img').attr('src', '<?=ASSETS_URL_IMAGES?>/button_scroller_l_active.png');
	}, function() {
		$(this).children('img').attr('src', '<?=ASSETS_URL_IMAGES?>/button_scroller_l.png');
	});

	$("a.ccm-scroller-r").hover(function() {
		$(this).children('img').attr('src', '<?=ASSETS_URL_IMAGES?>/button_scroller_r_active.png');
	}, function() {
		$(this).children('img').attr('src', '<?=ASSETS_URL_IMAGES?>/button_scroller_r.png');
	});
	
	var numThumbs = 4;	
	var thumbWidth = 132;
	
	$('a.ccm-scroller-r').click(function() {
		var item = $(this).parent().children('div.ccm-scroller-inner').children('ul');

		var currentPage = $(this).parent().attr('current-page');
		var currentPos = $(this).parent().attr('current-pos');
		var numPages = $(this).parent().attr('num-pages');
		
		var migratePos = numThumbs * thumbWidth;
		currentPos = parseInt(currentPos) - migratePos;
		currentPage++;
		
		$(this).parent().attr('current-page', currentPage);
		$(this).parent().attr('current-pos', currentPos);
		
		if (currentPage == numPages) {
			$(this).hide();
		}
		if (currentPage > 1) {
			$(this).siblings('a.ccm-scroller-l').show();
		}
		/*
		$(item).animate({
			left: currentPos + 'px'
		}, 300);*/
		
		$(item).css('left', currentPos + "px");
		
	});
	
	ccm_testAddSubmit = function() {
		if ($("input[name=ctID]").val() < 1) {
			alert("<?=$pageTypeMSG?>");
			return false;
		}
		return true;
	}
	
	$("#ccmAddPage").submit(function() {
		return ccm_testAddSubmit();
	});
	
	ccm_doAddSubmit = function() {
		if (ccm_testAddSubmit()) {
			$("#ccmAddPage").get(0).submit();
		}
	}
	
	$('a.ccm-scroller-l').click(function() {
		var item = $(this).parent().children('div.ccm-scroller-inner').children('ul');
		var currentPage = $(this).parent().attr('current-page');
		var currentPos = $(this).parent().attr('current-pos');
		var numPages = $(this).parent().attr('num-pages');
		
		var migratePos = numThumbs * thumbWidth;
		currentPos = parseInt(currentPos) + migratePos;
		currentPage--;

		$(this).parent().attr('current-page', currentPage);
		$(this).parent().attr('current-pos', currentPos);
		
		if (currentPage == 1) {
			$(this).hide();
		}
		
		if (currentPage < numPages) {
			$(this).siblings('a.ccm-scroller-r').show();
		}
		/*
		$(item).animate({
			left: currentPos + 'px'
		}, 300);*/
		$(item).css('left', currentPos + "px");
		
	});
	$('a.ccm-scroller-l').hide();
	$('a.ccm-scroller-r').each(function() {
		if (parseInt($(this).parent().attr('num-pages')) == 1) {
			$(this).hide();
		}
	});
	
	$("#ccm-select-page-type a").click(function() {
		$("#ccm-select-page-type li").each(function() {
			$(this).removeClass('ccm-item-selected');
		});
		$(this).parent().addClass('ccm-item-selected');
		$("input[name=ctID]").val($(this).attr('ccm-page-type-id'));
	});


});
</script>