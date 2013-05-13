<?php

using('Framework.System.Html');

/**
 * @property ComNewsChannel_Model_Article DataBindedObject
 */
class ComNewsChannel_Form_SubscribeCategories extends Html_Form
{
    protected $flash = null;

    protected $images = null;

    protected $region = null;

    protected $category = null;

    protected $languageTabs = null;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $component = CMS_Bazalt::getComponent('ComNewsChannel');
        $component->addWebservice('ComNewsChannel_Webservice_Region');

        $this->addElement('validationsummary');

        $this->flash = $this->addElement('alert');

        $this->addElement('optiongroup', 'item_type')
            ->label(__('Type of article', ComNewsChannel::getName()))
            ->options(array(
            '0' => __('Default article', ComNewsChannel::getName()),
            '1' => __('Article with photos', ComNewsChannel::getName())
        ))
            ->defaultValue(0);

        $this->addElement('text', 'source')
            ->label(__('Source', ComNewsChannel::getName()))
            ->addClass('ui-input');

        $tab = $this->languageTabs = $this->addElement('languageTabs');

        $tab->addElement('text', 'title')
            ->label(__('Title', ComNewsChannel::getName()))
            ->addClass('ui-large-input')
            ->addRuleNonEmpty();

        $tab->addElement('wysiwyg', 'body')
            ->label(__('Text', ComNewsChannel::getName()))
            ->addClass('ui-input')
            ->addRuleNonEmpty();

        $this->addElement('tags', 'tags')
            ->label(__('Tags, separated by "," (comma)', ComNewsChannel::getName()));

        $this->images = $this->addElement(new CMS_Form_Element_ImageUploader('images'))
            ->limit(100);

        $categorySelect = $this->addElement('select', 'category_id')
            ->label(__('Category', ComNewsChannel::getName()))
            ->addRuleNonEmpty();
        $categorySelect->addOption('-', '');
        $categories = ComNewsChannel_Model_Category::getCategories();
        foreach ($categories as $category) {
            $categorySelect->addOption(str_repeat('&nbsp;&nbsp;&nbsp;', $category->depth - 1) . $category->title, $category->id);
        }

        $regions = ComNewsChannel_Model_Article::getRegions();
        $this->region = $this->addElement('optiongroup', 'region_id')
            ->label(__('Region', ComNewsChannel::getName()))
            ->options($regions);

        $this->addElement('checkbox', 'is_top')
            ->label(__('Top news', ComNewsChannel::getName()));

        $this->addElement('checkbox', 'publish')
            ->label(__('Publish news', ComNewsChannel::getName()))
            ->defaultValue(true);

        $group = $this->addElement('group');

        $group->addElement('button', 'post')
            ->content(__('Save', ComNewsChannel::getName()))
            ->addClass('btn-primary btn-large');

        $group->addElement('button', 'cancel')
            ->content(__('Cancel', ComNewsChannel::getName()))
            ->reset();
    }

    public function dataBind($obj)
    {
        parent::dataBind($obj);

        if (!$this->isPostBack()) {
            $images = array();
            foreach ($this->dataBindedObject->Images->get() as $image) {
                $images []= $image->image;
            }
            $this->images->value($images);

            // Tags
            $tags = $obj->Tags->get();
            $tagsName = array();
            if ($tags) {
                foreach ($tags as $tag) {
                    $tagsName[] = $tag->title;
                }
                $this['tags']->value(implode(',', $tagsName));
            }

            if (!$this->region->value()) {
                $this->region->value('world');
            }
        }
    }


    public function save()
    {
        /*$text = __('Article successfully saved.', ComArticles::getName());
        if (!$this->dataBindedObject->publish) {
            $text .= '<p>' . __('An article should be published before it will be visible on the site', ComArticles::getName());

            $this->flash->addAction(__('Publish the article', ComArticles::getName()), 'javascript:void(0);')
                        ->addAction(__('Not now', ComArticles::getName()), CMS_Form_Element_Alert::getCloseAction());
        }
        $text .= '<p><a href="' . $this->dataBindedObject->getUrl() . '" target="_blank">' . __('View the article', ComArticles::getName()) . '</a>';
        $this->flash->text($text);
        */
        if ($this->dataBindedObject->is_top === null) {
            $this->dataBindedObject->is_top = '0';
        }

        if ($this->dataBindedObject->region_id == 'world' || !$this->dataBindedObject->region_id) {
            $this->dataBindedObject->region_id = null;
        }

        parent::save();
        $article = $this->dataBindedObject;
        $article->replaceImages();

        if ($article) {
            $article->Images->removeAll();
            foreach($this->images->value() as $n => $imageFile) {
                $image = new ComNewsChannel_Model_ArticleImage();
                $image->image = $imageFile;
                $image->order = $n;
                $article->Images->add($image);
            }
        }

        $tags = explode(',', mb_strtolower($this['tags']->value()));
        $this->DataBindedObject->tagsSave($tags);

        return $article;
    }
}