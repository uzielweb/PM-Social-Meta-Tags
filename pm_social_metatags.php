<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.pm_social_metatags
 *
 * @copyright
 * @license     GNU/Public
 */

defined('_JEXEC') or die;

/**
 * Pm_social_metatags plugin class.
 *
 * @since  2.5
 * @link https://docs.joomla.org/Plugin/Events/System
 */
class PlgSystemPm_social_metatags extends JPlugin
{
    /**
     * After initialise.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onAfterInitialise()
    {

    }
    /**
     * After route.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function onAfterRoute()
    {

    }
    /**
     * After Dispatch.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function onAfterDispatch()
    {

    }
    /**
     * Before Render.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function onBeforeRender()
    {

    }
    /**
     * After Render.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function onAfterRender()
    {

        $application = JFactory::getApplication('site');
        if ($application->isSite()) {

			$document = JFactory::getDocument();
			$title = $document->getTitle();

            $menu = JFactory::getApplication()->getMenu();
            $config = JFactory::getConfig();
            $option = JRequest::getCmd('option');
           
            $view = JRequest::getCmd('view');
			$sitename = $config->get('sitename');
            $content_description = $document->getDescription();
            $text = $content_description;
            $link = JUri::current();
            $type_of_content = "website";
			$timage = JUri::base().$this->params->get('default_image');

            if ($menu->getActive() == $menu->getDefault()) {

            }
            if ($option == "com_content" && $view == "article") {
                $ids = explode(':', JRequest::getString('id'));
                $article_id = $ids[0];
                $article = JTable::getInstance("content");
                $article->load($article_id);

                $type_of_content = "article";
                $link = rtrim(JUri::base(), '/') . JRoute::_(ContentHelperRoute::getArticleRoute($article->get('id'), $article->get('catid'), $article->get('language')));
                $content_description = strip_tags(html_entity_decode($article->get("introtext") . $article->get("fulltext")));

                //Limite de caracteres
                $maxLimit = "144";
                $text = preg_replace("/\r\n|\r|\n/", " ", $content_description);
                // Em seguida, troca os marcadores <br /> com \n
                $text = preg_replace("/<BR[^>]*>/i", " ", $text);
                // Troca os marcadores <p> com \n\n
                $text = preg_replace("/<P[^>]*>/i", " ", $text);
                // Remove todos os marcadores
                $text = strip_tags($text);
                // Trunca o texto pelo limite de caracteres
                $text = substr($text, 0, $maxLimit);
                //$text = String::truncate($text, $maxLimit, '...', true);
                // Deixa visível a última palavra que, no caso, foi cortada no meio
                $text = preg_replace("/[.,!?:;]? [^ ]*$/", " ", $text);
                // Adiciona reticências ao fim do texto
                if ($text) {
                    $text = trim($text) . '...';
                }

                // Troca \n com <br />
				$text = str_replace("\n", " ", $text);
				preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $article->introtext . $article->fulltext, $matches);
                $images = json_decode($article->images);
                // var_dump( $images);
				if (isset($images->image_intro) and !empty($images->image_intro))
				{
				$timage= htmlspecialchars(JURI::base().$images->image_intro);

				}

				elseif (isset($images->image_fulltext) and !empty($images->image_fulltext))
				{
				$timage= htmlspecialchars(JURI::base().$images->image_fulltext);
				}
				elseif ($matches[1])
				{



						$timage = JURI::base().$matches[1];


				}
				else{
					$timage = JURI::base().$this->params->get('default_image');
				}





			}

			// JEA
			if ($view == "property")
            {
                $jeaitemsModel = JModelLegacy::getInstance('JeaModelProperty');
                $jeaitemId = JRequest::getInt('id', 0);
                JFactory::getLanguage()->load('com_jea', JPATH_BASE . '/components/com_jea');
                $jeaitem = $jeaitemsModel->getItem($jeaitemId);
                $jeaparams = JFactory::getApplication('site')->getParams('com_jea');
                $jeaprice = number_format($jeaitem->price, $jeaparams->get('decimals_number') , $jeaparams->get('decimals_separator') , $jeaparams->get('thousands_separator'));
                if ($jeaparams->get('symbol_position') == '0')
                {
                    $jeapricecurrency = $jeaparams->get('currency_symbol') . ' ' . $jeaprice;
                }
                if ($jeaparams->get('symbol_position') == '1')
                {
                    $jeapricecurrency = $jeaitem->price . ' ' . $jeaparams->get('currency_symbol');
                }
                $text = JText::_('COM_JEA_FIELD_REF_LABEL') . ': ' . $jeaitem->ref . '. ' . JText::_('COM_JEA_FIELD_PRICE_LABEL') . ': ' . $jeapricecurrency . '. ' . strip_tags($jeaitem->description);
				$title = $jeaitem->title;

				$dbjea = JFactory::getDbo();
                $queryjea = $dbjea->getQuery(true);
                $queryjea->select('images');
                $queryjea->from('#__jea_properties');
                $queryjea->where('id=' . $jeaitem->id);
                $dbjea->setQuery($queryjea);
                $jeaimages = $dbjea->loadObjectList();
                foreach ($jeaimages as $jeaimage)
                {
                    $images = json_decode($jeaimage->images);
                    $first_image = $images[0];
                    $timage = JUri::base().$first_image->name;
                }
			}


			            //end of check if view is JEA property
            //check category view
            if ($view == "category")
            {
                $categoriesModel = JModelLegacy::getInstance('ContentModelCategories');
                $category = JRequest::getVar('id');
                $category = '9';
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('alias');
                $query->from('#__categories');
                $query->where('id=' . $category);
                $db->setQuery($query);
                $results = $db->loadObjectList();
                //       echo 'xx '.$results.' xx';
			};


			$buffer = JResponse::getBody();

			$hdTwitter_Title = '<meta name="twitter:title" content="'.$title.'">'.PHP_EOL;
			$hdTwitter_Card = '<meta name="twitter:card" content="summary_large_image">'.PHP_EOL;
			$hdTwitter_Site = '<meta name="twitter:site" content="'.$sitename.'">'.PHP_EOL;
			$hdTwitter_Creator = '<meta name="twitter:creator" content="@'.$this->params->get('twitter_creator').'">'.PHP_EOL;
			$hdTwitter_Url = '<meta name="twitter:url" content="'.$link.'">'.PHP_EOL;
			$hdTwitter_Descrition = '<meta name="twitter:description" content="'.$text.'">'.PHP_EOL;
			$hdTwitter_Image = '<meta name="twitter:image" content="'.$timage.'">'.PHP_EOL;
			$hdog_title = '<meta property="og:title" content="'.$title.'"/>'.PHP_EOL;
			$hdog_type = '<meta property="og:type" content="article"/>'.PHP_EOL;
			$hdog_email = '<meta property="og:email" content="'.$this->params->get('email').'" />'.PHP_EOL;
			$hdog_url = '<meta name="twitter:url" content="'.$link.'">'.PHP_EOL;
			$hdog_image = '<meta property="og:image" content="'.$timage.'"/>'.PHP_EOL;
			$hdog_site_name = '<meta property="og:site_name" content="'.$sitename.'"/>'.PHP_EOL;
			$hdog_admins = '<meta property="fb:admins" content="'.$this->params->get('fb_admins').'"/>';
			$hdog_description = '<meta property="og:description" content="'.$text.'"/>'.PHP_EOL;


			$hdog_all = PHP_EOL.$hdog_type.$hdog_email.$hdog_url.$hdog_image.$hdog_site_name.$hdog_admins.$hdog_description.$hdTwitter_Title.$hdTwitter_Card.$hdTwitter_Site.$hdTwitter_Creator.$hdTwitter_Url.$hdTwitter_Descrition.$hdTwitter_Image ;

            $disable_in = explode(',',$this->params->get('disable_in'));
           
			if (in_array($option,$disable_in)){
				$hdog_all = '';
			}
			$buffer = str_replace('<html xmlns="http://www.w3.org/1999/xhtml"', '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2019/fbml" ', $buffer);
            $buffer = str_replace('</title>', '</title>' . $hdog_all, $buffer);
            JResponse::setBody($buffer);
            return true;


        }
    }
    /**
     * Before Compile Head.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function onBeforeCompileHead()
    {

    }
    /**
     * Search.
     *
     * @return  void
     *
     * @since   3.4
     */
    /**
     * Search
     * @param  string $searchword   The target search string
     * @param  string $searchphrase A string matching option (exact|any|all).
     * @param  string $ordering     A string ordering option (newest|oldest|popular|alpha|category)
     * @param  array $areas        An array if restricted to areas, null if search all.
     * @return array              Array of stdClass objects with members as described above.
     */
    public function onSearch($searchword, $searchphrase, $ordering, $areas)
    {
        return [];
    }

    /**
     * Determine areas searchable by this plugin.
     *
     * @return  array  An array of search areas.
     *
     * @since   1.6
     */
    public function onContentSearchAreas()
    {
        return [];
    }

}
