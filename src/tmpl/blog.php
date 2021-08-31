<?php defined('_JEXEC') or die;

// Register LibHelpGhsvs
if (!defined('GHSVS_LIBRARY_INCLUDED') && ((@include_once(JPATH_LIBRARIES . '/ghsvs/include.php')) === false))
{
	$msg = JText::sprintf('COULD_NOT_LOAD_NEEDED_LIBRARY_LIB_GHSVS', str_replace(JPATH_SITE, '', __FILE__), __LINE__);
	$app = JFactory::getApplication();
	if ($app->isSite()) throw new Exception($msg, 500);
	$app->enqueueMessage($msg, 'error');
}

?>
<div class="blog<?php echo $moduleclass_tmpl; ?>">
<?php
$introcount = (count($list));
$counter =0;
$blogcolumns = (int)$params->get('blogcolumns', 3);
?>
<?php
if($introcount) :
 foreach ($list as $key => &$item) :

	 $item->params->merge($params);
  $rowcount = ((int) $key % $blogcolumns) + 1;
		if($rowcount == 1){
		 $row = $counter / $blogcolumns;
?>
 <div class="items-row cols-<?php echo $blogcolumns.' row-'.$row?> row-fluid clearfix">
<?php
  }#if $rowcount==1
?>
		<div class="span<?php echo round((12 / $blogcolumns))?>">
		 <div class="item column-<?php echo $rowcount?><?php echo $item->state==0?' system-unpublished':''?>">
<?php
  if(
		 $item->state==0 ||
			strtotime($item->publish_up) > strtotime(JFactory::getDate()) ||
			(
			 (strtotime($item->publish_down) < strtotime(JFactory::getDate())) &&
				$item->publish_down != '0000-00-00 00:00:00'
			)
		){
		 echo '<div class="system-unpublished">';
		}
?>
<?php
  echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item);
?>
<?php
  echo JLayoutHelper::render(
		 'joomla.content.icons',
			array('params'=>$item->params, 'item' => $item, 'print' => false)
		);
?>
<?php
  $useDefList=(
   $item->params->get('show_modify_date') ||
	  $item->params->get('show_publish_date') ||
	  $item->params->get('show_create_date') ||
	  $item->params->get('show_hits') ||
	  $item->params->get('show_category') ||
	  $item->params->get('show_parent_category') ||
	  $item->params->get('show_author')
  );
?>
<?php
  if($useDefList){
   echo JLayoutHelper::render(
	   'joomla.content.info_block.block',
	   array(
	    'item' => $item,
		   'params' => $item->params,
		   'position' => 'above'
				)
	  );
  }
?>
<?php
	 if ($item->params->get('show_concated_tags', 0) )
		{
	  LibHelpGhsvs::setTagsNamesToItem($item);
			if ($item->concatedTagsGhsvs)
			{
		  echo '<p><span class="label label-info">'.JText::_('JTAG') . ':</span> ' . $item->concatedTagsGhsvs.'</p>';
			}
		}
  if($useDefList){
   echo JLayoutHelper::render(
	   'joomla.content.info_block.block',
	   array(
	    'item' => $item,
		   'params' => $item->params,
		   'position' => 'above'
				)
	  );
  }
?>
<?php 
  if($item->params->get('show_image_intro')){
   echo JLayoutHelper::render(
	   'joomla.content.intro_image',
		  $item
	  );
	 }
?>

<?php
if($item->params->get('show_intro')){
?>


		  <div class="DIV4BLOGITEMTEXT"><!--See JS prependHeaderGhsvs-->
<?php
  if(!$item->params->get('show_intro')) :
		 echo $item->afterDisplayTitle;
		endif;
?>
<?php
  echo $item->beforeDisplayContent;
?>
<?php
  echo $item->introtext;
?>
<?php
  if($useDefList){
   echo JLayoutHelper::render(
	   'joomla.content.info_block.block',
	   array(
	    'item' => $item,
		   'params' => $item->params,
		   'position' => 'below'
				)
	  );
  }
?>
<?php
  if($item->params->get('show_readmore') && $item->readmore){
	  if($item->params->get('access-view')) :
		  $link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
	  else :
		  $menu = JFactory::getApplication()->getMenu();
		  $active = $menu->getActive();
		  $itemId = $active->id;
		  $link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
		  $returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
		  $link = new JUri($link1);
		  $link->setVar('return', base64_encode($returnURL));
	  endif;
?>
		   <p class="readmore">
      <a class="btn" href="<?php echo $link; ?>">
<?php
   if(!$item->params->get('access-view')) :
		  echo JText::_('MOD_ARTICLES_CATEGORYTRADITIONALGHSVS_REGISTER_TO_READ_MORE');
	  elseif ($readmore = $item->alternative_readmore) :
		  echo $readmore;
		  if ($item->params->get('show_readmore_title', 0) != 0) :
		   echo JHtml::_('string.truncate', ($item->title), $item->params->get('readmore_limit'));
		  endif;
	  elseif ($item->params->get('show_readmore_title', 0) == 0) :
		  echo JText::sprintf('MOD_ARTICLES_CATEGORYTRADITIONALGHSVS_READ_MORE_TITLE');
	  else :
		  echo JText::_('MOD_ARTICLES_CATEGORYTRADITIONALGHSVS_READ_MORE');
		  echo JHtml::_('string.truncate', ($item->title), $item->params->get('readmore_limit'));
	  endif;
?>
      </a>
		   </p><!--/readmore-->
<?php
  }#show_readmore
?>
<?php
  if(
		 $item->state==0 ||
			strtotime($item->publish_up) > strtotime(JFactory::getDate()) ||
			(
			 (strtotime($item->publish_down) < strtotime(JFactory::getDate())) &&
				$item->publish_down != '0000-00-00 00:00:00'
			)
		){
		 echo '</div><!--/system-unpublished-->';
		}
?>
<?php
  echo $item->afterDisplayContent;
?>    
    </div><!--/DIV4BLOGITEMTEXT-->
<?php
} //show_intro
?>    
			</div><!--/item column-<?php echo $rowcount?>-->
			<?php $counter++; ?>
		</div><!--/span<?php echo round((12 / $blogcolumns))?>-->
		<?php if (($rowcount == $blogcolumns) or ($counter == $introcount)) : ?>
	</div><!--/items-row cols-<?php echo $blogcolumns?> <?php echo 'row-'.$row?> row-fluid clearfix-->
<?php
  endif;
 endforeach;
endif;#$introcount ?>
</div><!--/blog<?php echo $moduleclass_sfx?>-->
