<?php
defined('_JEXEC') or die;

if (version_compare(JVERSION, '4', 'lt'))
{
  JLoader::registerNamespace(
    'Joomla\Module\ArticlesCategoryTraditionalGhsvs\Site',
    __DIR__ . '/src',
    false, false, 'psr4'
  );
}

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\ArticlesCategoryTraditionalGhsvs\Site\Helper\ArticlesCategoryTraditionalGhsvsHelper;

$input = Factory::getApplication()->input;
$blog = $params->get('blog', 1);
$mode = ($blog ? 'normal' : $params->get('mode', 'normal'));
$article_grouping = ($blog ? 'none' : $params->get('article_grouping', 'none'));
$idbase = null;

switch($mode)
{
	case 'dynamic':
		$option = $input->get('option');
		$view = $input->get('view');
		if ($option === 'com_content')
		{
			switch($view)
			{
				case 'category':
					$idbase = $input->getInt('id');
					break;
				case 'categories':
					$idbase = $input->getInt('id');
					break;
				case 'article':
					if ($params->get('show_on_article_page', 1))
					{
						$idbase = $input->getInt('catid');
					}
					break;
			}
		}
		break;
	case 'normal':
	default:
		$idbase = $params->get('catid');
		break;
}

$cacheid = md5(serialize([$idbase, $module->module, $module->id]));
$cacheparams = new \stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class = ArticlesCategoryTraditionalGhsvsHelper::class;
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = $cacheid;

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (!empty($list))
{
	$grouped = false;

	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''));

	$moduleclass_tmpl = htmlspecialchars($params->get('moduleclass_tmpl', ''));

	$item_heading = $params->get('item_heading');

	if ($article_grouping !== 'none')
	{
		$article_grouping_direction = $params->get('article_grouping_direction', 'ksort');
		$grouped = true;
		switch($article_grouping)
		{
			case 'year':
			case 'month_year':
				$list = ArticlesCategoryTraditionalGhsvsHelper::groupByDate($list, $article_grouping, $article_grouping_direction, $params->get('month_year_format', 'F Y'));
				break;
			case 'author':
			case 'category_title':
				$list = ArticlesCategoryTraditionalGhsvsHelper::groupBy($list, $article_grouping, $article_grouping_direction);
				break;
			default:
				break;
		}
	}

	require ModuleHelper::getLayoutPath('mod_articles_categorytraditionalghsvs', $params->get('layout', 'default'));
}
