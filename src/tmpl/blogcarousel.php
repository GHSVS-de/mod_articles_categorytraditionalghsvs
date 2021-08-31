<?php defined('_JEXEC') or die;
// Unterseiten-Pagination der Startseite ausschließen.
$start = (int) JUri::getInstance()->getVar('start');
if ($start)
{
 return;
}

// Register LibHelpGhsvs
if (!defined('GHSVS_LIBRARY_INCLUDED') && ((@include_once(JPATH_LIBRARIES . '/ghsvs/include.php')) === false))
{
	$msg = JText::sprintf('COULD_NOT_LOAD_NEEDED_LIBRARY_LIB_GHSVS', str_replace(JPATH_SITE, '', __FILE__), __LINE__);
	$app = JFactory::getApplication();
	if ($app->isSite()) throw new Exception($msg, 500);
	$app->enqueueMessage($msg, 'error');
}

// $list hat immer Items, wenn hier überhaupt ankommt.
$introcount = count($list);
$counter = 0;
$blogcolumns = (int) $params->get('blogcolumns', 3);
/*
	Wenn bei vertikaler Ansicht des BlogCarousels das letzte
	Carousel-.item	nicht vollständig gefüllt ist, hüpft die Seite.
 Ich muss also $rest fehlende, leere Spans hinzufügen.
	Damit sie nach Möglichkeit nicht leer sind, versuche zufällige
	bei hype-View nicht gehypte Haupteinträge zuzuladen. 
	$rest gibt z.B. 1 bei 10 Items und 3 Spalten.
	Fehlen also noch 2, um das .item zu füllen.
*/
if (
 ($rest = ($introcount % $blogcolumns))
	&& $params->get('articles_ids_exclude', '') == 'hyped'
){
 $rest = $blogcolumns - $rest;
	$params->set('article_ordering', 'rand()');
	$params->set('show_front', 'only');
	$params->set('articles_ids', implode("\r\n", PlgSystemArticleSubtitleGhsvs::getHypedArticles()));
	$params->set('articles_ids_exclude', 'exclude');
	
	$params->set('blogcount', $rest);
	$restList = ModArticlesCategoryTraditionalGhsvsHelper::getList($params);
	if (!empty($restList))
	{
		foreach ($restList as $r)
		{
			$r->params->set('itemClassGhsvs', $r->params->get('itemClassGhsvs', '').' randomGhsvs');
			$list[] = $r;
		}
		$introcount = count($list);
		// Hat nicht geklappt? Dann halt leere Container.
		if (($rest = ($introcount % $blogcolumns)))
		{
			$rest = $blogcolumns - $rest;
		}
	}
}

$needPagination = ($introcount > $blogcolumns);
$carouselSelector = 'blogcarousel'.$module->id;
$carouselSelectorJS = '".' . $carouselSelector . '"';

JHtml::_('bootstrap.framework');
// Joomla-Bug. Startet eh nicht.
#JHtmlBootstrap::carousel($carouselSelector, array('interval'=>3000, 'pause'=>'hover'));

	$js = '
(function($){';
if ($needPagination)
{
	$js .= '
	$(document).ready(function(){
		$("div.carouselButtons' . $module->id . '").clone().appendTo(' . $carouselSelectorJS . ');
	});';
}
		$js .= '
 $(window).load(function(){
  $(' . $carouselSelectorJS . ').carousel({"interval": 6000,"pause": "false"});
		
		$(".cyclePauseButton' . $carouselSelector . '").click(function(){
			var aktion = $(this).attr("data-playpause");
			$(' . $carouselSelectorJS . ').carousel(aktion);
			$buttons = $(".cyclePauseButton' . $carouselSelector . '");
			$buttons.attr("data-playpause", (aktion == "cycle" ? "pause" : "cycle"));
			$buttons.html((aktion == "cycle" ? "Stoppe Artikelslider" : "Starte Artikelslider"));
			$buttons
			 .removeClass((aktion == "cycle" ? "btn-success" : "btn-danger"))
				.addClass((aktion == "cycle" ? "btn-danger" : "btn-success"));;
		});';
	 
if (JPluginHelper::isEnabled('system', 'lazyloadforjoomla'))
{
	$js .= '
  $.fn.lazyloadPluginOff(' . $carouselSelectorJS . ');';
}
if ($needPagination)
{
  $js .= '
		// Init: Da Joomla 3.4.3 immer noch Bug mit nicht startendem Carousel hat.
		$(' . $carouselSelectorJS . ').carousel("cycle");';
}
		$js .= '
		$.fn.bootstrapCarouselResize(' . $carouselSelectorJS . ', true);
		$(window).resize(function(){
   $.fn.waitForFinalEvent(function(){
				isCycling = ($(".cyclePauseButton' . $carouselSelector . '")
				 .attr("data-playpause") == "pause" ? true : false);
    $.fn.bootstrapCarouselResize(' . $carouselSelectorJS . ', isCycling);
   }, 250, "' . $carouselSelector . '"); //waitForFinalEvent braucht unique-id
		});
	});
})(jQuery);
';
JFactory::getDocument()->addScriptDeclaration($js);
?>
<?php
if($introcount) : ?>
<div id="myCarousel<?php echo $module->id;?>" class="blogcarousel <?php echo $carouselSelector;?> slide">
<?php
 if ($needPagination){ ?>
	<div class="carouselPagination carouselButtons<?php echo $module->id;?>">
		<a class="btn btn-small" href="#myCarousel<?php echo $module->id;?>" data-slide="prev">
			<span class="icon-chevron-left"> </span>
		</a>
		<button type="button" class="btn btn-small btn-danger cyclePauseButton<?php echo $carouselSelector;?>" data-playpause="pause">
			Stoppe Artikelslider
		</button>
		<a class="btn btn-small" href="#myCarousel<?php echo $module->id;?>" data-slide="next">
			<span class="icon-chevron-right"> </span>
		</a>
	</div><!--/carouselPagination--><?php
	} //if $needPagination ?>
 <div class="carousel-inner">
<?
 foreach ($list as $key => &$item) :
  $item->params->merge($params);
		$itemClassGhsvs = $item->params->get('itemClassGhsvs', '');
		$itemClassGhsvs = $itemClassGhsvs ? ' '.$itemClassGhsvs : '';
		if ($item->params->get('access-view')) :
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
		
  $rowcount = ((int) $key % $blogcolumns) + 1;
  if($rowcount == 1){
   $row = $counter / $blogcolumns;
?>
 <div class="item<?php echo (!$counter ? ' active' : '');?>">
  <div class="row-fluid">
<?php
  }#if $rowcount==1
?>
  <div class="span<?php echo round((12 / $blogcolumns))?> itemContainer">
		<div class="singleitem BOOTSTRAPCAROUSELRESIZE<?php echo (!$counter ? ' CAROUSELLIMITER' : '');?><?php echo $itemClassGhsvs;?>">
<?php
  if(
   $item->state == 0 ||
   strtotime($item->publish_up) > strtotime(JFactory::getDate()) ||
   (
    (strtotime($item->publish_down) < strtotime(JFactory::getDate())) &&
    $item->publish_down != '0000-00-00 00:00:00'
   )
  ){
   continue;
  }
?>
<?php
if ($params->get('show_title', 1))
{
 $item->params->set('mask_pageheaderclass_ghsvs', 1);
	$item->params->set('show_print_icon', false);
	$item->params->set('show_email_icon', false);
 echo JLayoutHelper::render('ghsvs.page_header_n_icons', array('item' => $item, 'print' => false));
}
?>
<?php
if ($params->get('show_image_intro', 0))
{
	$item->params->set('show_caption_ghsvs', false);
 echo JLayoutHelper::render('ghsvs.intro_image_readmore', array('item' => $item, 'link'=> $link));
}
?>
<div class="item-text">
<?php if (!$params->get('show_intro', 1)) : ?>
	<?php echo $item->afterDisplayTitle; ?>
<?php endif; ?>
<?php
// Alle Bilder entfernen.
if ($params->get('clearimgtag_ghsvs', 1))
{
	$item->introtext = JFilterOutput::stripImages($item->introtext);
}
// Introtext kürzen
$limit = (int) $params->get('introtext_limit', 250);
if ($limit > 20)
{
 $truncated = JHtml::_(
	 'string.truncateComplex',
	 $item->introtext,
	 $limit
 );
 // Bisschen plump:
 if (!$item->readmore && mb_substr($truncated, -3) == '...')
 {
	 $item->readmore = true;
 }
 // Und jetzt statt Introtext:
 echo $truncated;
}
else
{
	echo $item->introtext;
}
?>
<?php if ($params->get('show_readmore') && $item->readmore) :
 $item->params->set('readmore_inline_ghsvs', true);
 echo JLayoutHelper::render('joomla.content.readmore', array('item' => $item, 'params' => $item->params, 'link' => $link)); ?>
<?php endif; ?>
   <?php $counter++; ?>
			<div class="clearfix"></div>
			</div><!--/item-text-->
		</div><!--/singleitem-->
  </div><!--/span<?php echo round((12 / $blogcolumns))?>-->
  <?php
		$ende = ($counter == $introcount);
		if ($ende && $rest)
		{
			// LEERE Container!!
			for ($i=1; $i <= $rest; $i++)
			{?>
  <div class="span<?php echo round((12 / $blogcolumns))?> itemContainer">
		<div class="singleitem BOOTSTRAPCAROUSELRESIZE<?php echo (!$counter ? ' CAROUSELLIMITER' : '');?>">
		</div><!--/singleitem-->
  </div><!--/span<?php echo round((12 / $blogcolumns))?>-->				
			<?php
			}
		}
		if (($rowcount == $blogcolumns) || $ende) : ?>
 </div><!--/row-fluid-->
</div><!--/item-->
<?php
  endif;
 endforeach; ?>
 </div><!--/carousel-inner-->
 
  
  

</div><!--/myCarousel<?php echo $module->id;?>" blogcarousel<?php echo $module->id;?> slide-->
<?php
endif;#$introcount ?>

