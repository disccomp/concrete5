<? defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
	<div id="footer">
			&copy; <?=date('Y')?> <a href="<?=DIR_REL?>/"><?=SITE?></a>.
			&nbsp;&nbsp;
			<?=t('All rights reserved.')?>	
			<span class="sign-in"><a href="<?=$this->url('/login')?>"><?=t('Sign In to Edit this Site')?></a></span>
	</div>

</div>

</body>
</html>