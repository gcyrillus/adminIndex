<form action="index.php" method="post" id="form_articles">
<div class="inline-form action-bar">
	<h2><?php echo L_ARTICLES_LIST ?></h2>
	<ul class="menu">
		<li><a <?php echo ($_SESSION['sel_get']=='all')?'class="selected" ':'' ?>href="index.php?sel=all&amp;page=1"><?php echo L_ALL ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('all', "$userId").')' ?></li>
		<li><a <?php echo ($_SESSION['sel_get']=='published')?'class="selected" ':'' ?>href="index.php?sel=published&amp;page=1"><?php echo L_ALL_PUBLISHED ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('published', $userId, '').')' ?></li>
		<li><a <?php echo ($_SESSION['sel_get']=='draft')?'class="selected" ':'' ?>href="index.php?sel=draft&amp;page=1"><?php echo L_ALL_DRAFTS ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('draft', $userId).')' ?></li>
		<li><a <?php echo ($_SESSION['sel_get']=='mod')?'class="selected" ':'' ?>href="index.php?sel=mod&amp;page=1"><?php echo L_ALL_AWAITING_MODERATION ?></a><?php echo '&nbsp;('.$plxAdmin->nbArticles('all', $userId, '_').')' ?></li>
	</ul>
	<?php
	echo plxToken::getTokenPostMethod();
	if($_SESSION['profil']<=PROFIL_MODERATOR) {
		plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, false, 'id_selection');
		echo '<input name="sel" type="submit" value="'.L_OK.'" onclick="return confirmAction(this.form, \'id_selection\', \'delete\', \'idArt[]\', \''.L_CONFIRM_DELETE.'\')" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>';
	}
	?>
	<?php plxUtils::printInput('page',1,'hidden'); ?>
</div>

<div class="grid">
	<div class="col sml-6">
		<?php plxUtils::printSelect('sel_cat', $aFilterCat, $_SESSION['sel_cat']) ?>
		<input class="<?php echo $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" value="<?php echo L_ARTICLES_FILTER_BUTTON ?>" />
	</div>
	<div class="col sml-6 text-right">
		<input id="index-search" placeholder="<?php echo L_SEARCH_PLACEHOLDER ?>" type="text" name="artTitle" value="<?php echo plxUtils::strCheck($_GET['artTitle']) ?>" />
		<input class="<?php echo (!empty($_GET['artTitle'])?' select':'') ?>" type="submit" value="<?php echo L_SEARCH ?>" />
	</div>
</div>

<div class="scrollable-table">
	<table id="articles-table" class="full-width">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
				<th><?php echo L_ID ?></th>
				<th><?php echo L_ARTICLE_LIST_DATE ?></th>
				<th><?php echo L_ARTICLE_LIST_TITLE ?></th>
				<th><?php echo L_ARTICLE_TITLE_HTMLTAG ?></th>
				<th><?php echo L_ARTICLE_META_DESCRIPTION ?></th>
				<th><?php echo L_ARTICLE_LIST_CATEGORIES ?></th>
				<th><?php echo L_ARTICLE_LIST_NBCOMS ?></th>
				<th><?php echo L_ARTICLE_LIST_AUTHOR ?></th>
				<th class="action"><?php echo L_ARTICLE_LIST_ACTION ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		# On va lister les articles
		if($arts) { # On a des articles
			# Initialisation de l'ordre
			$num=0;
			$datetime = date('YmdHi');
			while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
				$author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
				$publi = (boolean)!($plxAdmin->plxRecord_arts->f('date') > $datetime);
				# Cat??gories : liste des libell??s de toutes les categories
				$draft='';
				$libCats='';
				$aCats = array();
				$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
				if(sizeof($catIds)>0) {
					foreach($catIds as $catId) {
						$selected = ($catId==$_SESSION['sel_cat'] ? ' selected="selected"' : '');
						if($catId=='draft') $draft = ' - <strong>'.L_CATEGORY_DRAFT.'</strong>';
						elseif($catId=='home') $aCats['home'] = '<option value="home"'.$selected.'>'.L_CATEGORY_HOME.'</option>';
						elseif($catId=='000') $aCats['000'] = '<option value="000"'.$selected.'>'.L_UNCLASSIFIED.'</option>';
						elseif(isset($plxAdmin->aCats[$catId])) $aCats[$catId] = '<option value="'.$catId.'"'.$selected.'>'.plxUtils::strCheck($plxAdmin->aCats[$catId]['name']).'</option>';
					}

				}
				# en attente de validation ?
				$idArt = $plxAdmin->plxRecord_arts->f('numero');
				$awaiting = $idArt[0]=='_' ? ' - <strong>'.L_AWAITING.'</strong>' : '';
				# Commentaires
				$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$idArt.'.(.*).xml$/','all');
				$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$idArt.'.(.*).xml$/','all');
				# On affiche la ligne
				echo '<tr>';
				echo '<td><input type="checkbox" name="idArt[]" value="'.$idArt.'" /></td>';
				echo '<td>'.$idArt.'</td>';
				echo '<td>'.plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')).'&nbsp;</td>';
				echo '<td style="width:auto;"><a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.plxUtils::strCheck($plxAdmin->plxRecord_arts->f('title')).'</a>'.$draft.$awaiting.'&nbsp;</td>';
				echo '<td><div class="metacell">'.$plxAdmin->plxRecord_arts->f('title_htmltag').'&nbsp;</div></td>';
				echo '<td><div class="metacell">'.$plxAdmin->plxRecord_arts->f('meta_description').'&nbsp;</div></td>';
				echo '<td>';
				if(sizeof($aCats)>1) {
					echo '<select name="sel_cat2" class="ddcat" onchange="this.form.sel_cat.value=this.value;this.form.submit()">';
					echo implode('', $aCats);
					echo '</select>';
				}
				else echo strip_tags(implode('', $aCats));
				echo '&nbsp;</td>';
				echo '<td><a title="'.L_NEW_COMMENTS_TITLE.'" href="comments.php?sel=offline&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsToValidate.'</a> / <a title="'.L_VALIDATED_COMMENTS_TITLE.'" href="comments.php?sel=online&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsValidated.'</a>&nbsp;</td>';
				echo '<td>'.plxUtils::strCheck($author).'&nbsp;</td>';
				echo '<td>';
				echo '<a href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.L_ARTICLE_EDIT.'</a>';
				if($publi AND $draft=='') # Si l'article est publi??
					echo ' <a href="'.$plxAdmin->urlRewrite('?article'.intval($idArt).'/'.$plxAdmin->plxRecord_arts->f('url')).'" title="'.L_ARTICLE_VIEW_TITLE.'">'.L_VIEW.'</a>';
				echo "&nbsp;</td>";
				echo "</tr>";
			}
		} else { # Pas d'article
			echo '<tr><td colspan="10" class="center">'.L_NO_ARTICLE.'</td></tr>';
		}
		?>
		</tbody>
	</table>
</div>

</form>

<p id="pagination">
	<?php
	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminIndexPagination'));
	# Affichage de la pagination
	if($arts) { # Si on a des articles (hors page)
		# Calcul des pages
		$last_page = ceil($nbArtPagination/$plxAdmin->bypage);
		$stop = $plxAdmin->page + 2;
		if($stop<5) $stop=5;
		if($stop>$last_page) $stop=$last_page;
		$start = $stop - 4;
		if($start<1) $start=1;
		# G??n??ration des URLs
		$artTitle = (!empty($_GET['artTitle'])?'&amp;artTitle='.urlencode($_GET['artTitle']):'');
		$p_url = 'index.php?page='.($plxAdmin->page-1).$artTitle;
		$n_url = 'index.php?page='.($plxAdmin->page+1).$artTitle;
		$l_url = 'index.php?page='.$last_page.$artTitle;
		$f_url = 'index.php?page=1'.$artTitle;
		# Affichage des liens de pagination
		printf('<span class="p_page">'.L_PAGINATION.'</span>', '<input style="text-align:right;width:35px" onchange="window.location.href=\'index.php?page=\'+this.value+\''.$artTitle.'\'" value="'.$plxAdmin->page.'" />', $last_page);
		$s = $plxAdmin->page>2 ? '<a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'">&laquo;</a>' : '&laquo;';
		echo '<span class="p_first">'.$s.'</span>';
		$s = $plxAdmin->page>1 ? '<a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'">&lsaquo;</a>' : '&lsaquo;';
		echo '<span class="p_prev">'.$s.'</span>';
		for($i=$start;$i<=$stop;$i++) {
			$s = $i==$plxAdmin->page ? $i : '<a href="'.('index.php?page='.$i.$artTitle).'" title="'.$i.'">'.$i.'</a>';
			echo '<span class="p_current">'.$s.'</span>';
		}
		$s = $plxAdmin->page<$last_page ? '<a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'">&rsaquo;</a>' : '&rsaquo;';
		echo '<span class="p_next">'.$s.'</span>';
		$s = $plxAdmin->page<($last_page-1) ? '<a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'">&raquo;</a>' : '&raquo;';
		echo '<span class="p_last">'.$s.'</span>';
	}
	?>
</p>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include PLX_ROOT.'core/admin/foot.php';
exit;
?>
