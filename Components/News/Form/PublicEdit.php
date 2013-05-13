<?php

using('Framework.System.Html');

/**
 * @property ComNewsChannel_Model_Article DataBindedObject
 */
class ComNewsChannel_Form_PublicEdit extends Html_Form
{
    protected $flash = null;

    protected $images = null;

    protected $categoriesTab = null;

    protected $categories = null;

    protected $languageTabs = null;

    public function __construct()
    {
        parent::__construct('news');

        $this->addElement('validationsummary', 'errors');

        $this->flash = $this->addElement('alert');

        $this->addElement('optiongroup', 'item_type')
            ->label(__('Type of article', ComNewsChannel::getName()))
            ->options(array(
            '0' => __('Default article', ComNewsChannel::getName()),
            '1' => __('Article with photos', ComNewsChannel::getName())
        ))
            ->defaultValue(0);

        $tab = $this->languageTabs = $this->addElement('languageTabs', 'langs');

        $tab->addElement('text', 'title')
            ->label(__('Title', ComNewsChannel::getName()))
            ->addClass('ui-large-input')
            ->addRuleNonEmpty();

        $tab->addElement('wysiwyg', 'body')
            ->label(__('Text', ComNewsChannel::getName()))
            ->addClass('ui-input')
            ->addRuleNonEmpty();

        $this->addElement('text', 'mainimage')
            ->label(__('Main image', ComNewsChannel::getName()))
            ->addRuleNonEmpty();

        $this->addElement('text', 'category')
            ->label(__('Category', ComNewsChannel::getName()))
            ->addRuleNonEmpty();

        $this->addElement('tags', 'tags')
            ->label(__('Tags, separated by "," (comma)', ComNewsChannel::getName()));

        $this->images = $this->addElement(new CMS_Form_Element_ImageUploader('news_images'), 'news_images')
             ->limit(20);

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
            $i = 0;
            foreach ($this->dataBindedObject->Images->get() as $image) {
                if ($i++ > 0) {
                    $images []= $image->image;
                } else {
                    $this['mainimage']->value($image->image);
                }
            }
            $this->images->value($images);

            //$categories = $this->dataBindedObject->Categories->get();
            //$this->categories->setCategories($categories);
            //$this->categoriesTab->title($this->categoriesTab->title() . '  (' . count($categories) . ')');


            // Tags
            $tags = $obj->Tags->get();
            $tagsName = array();
            if ($tags) {
                foreach ($tags as $tag) {
                    $tagsName[] = $tag->title;
                }
                $this['tags']->value(implode(',', $tagsName));
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
        $this->dataBindedObject->title = strip_tags($this['languages_tabs']['uk']['title']->value());

        parent::save();
        $article = $this->dataBindedObject;
        $article->replaceImages();

        if ($article) {
            $article->Images->removeAll();
            $images = $this->images->value();
            $images = array_merge(array($this['mainimage']->value()), $images);
            foreach($images as $n => $imageFile) {
                $image = new ComNewsChannel_Model_ArticleImage();
                $image->image = $imageFile;
                $image->order = $n;
                $article->Images->add($image);
            }
        }
        $category = CMS_Model_Category::getById($this['category']->value());
        if ($category) {
            $this->dataBindedObject->Categories->removeAll();
            $this->dataBindedObject->Categories->add($category);
        }

        $tags = explode(',', mb_strtolower($this['tags']->value()));
        $this->DataBindedObject->tagsSave($tags);

        return $article;
    }
}