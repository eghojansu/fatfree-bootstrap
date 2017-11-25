<?php

namespace App\Entity;

use Nutrition\SQL\Criteria;
use Nutrition\SQL\Mapper;
use Nutrition\Security\UserManager;
use Nutrition\Utils\PaginationSetup;
use Web;

class Post extends Mapper
{
    const TYPE_ABOUT = 'about';
    const TYPE_WELCOME = 'welcome';
    const TYPE_PUBLISHED = 'published';
    const TYPE_DRAFT = 'draft';
    const TYPE_HIDDEN = 'hidden';


    public static function getEditablePostTypes()
    {
        return [
            'Published' => self::TYPE_PUBLISHED,
            'Draft' => self::TYPE_DRAFT,
            'Hidden' => self::TYPE_HIDDEN
        ];
    }

    public function typeLabel()
    {
        return array_search($this->Type, self::getEditablePostTypes());
    }

    public function getWelcomeContent()
    {
        return $this->findOneByType(self::TYPE_WELCOME);
    }

    public function getAboutContent()
    {
        return $this->findOneByType(self::TYPE_ABOUT);
    }

    public function getPosts()
    {
        return $this->findByType(self::TYPE_PUBLISHED);
    }

    public function getEditablePosts()
    {
        $setup = PaginationSetup::instance();
        $criteria = Criteria::create()->add(
            'Type',
            self::getEditablePostTypes()
        );

        if ($keyword = $setup->getRequestArg('keyword')) {
            $criteria->addCriteria(
                '(Title like :keyword or Headline like :keyword)',
                ['keyword'=>'%'.$keyword.'%']
            );
        }

        return $this->createPagination(
            $setup->getRequestPage() - 1,
            $setup->getPerPage(),
            $criteria->get()
        );
    }

    public function getPost($slug)
    {
        return $this->findone(['Type = ? and Slug = ?', self::TYPE_PUBLISHED, $slug]);
    }

    public function onMapBeforeInsert($that, array $pkeys)
    {
        $that->Slug = Web::instance()->slug($that->Title);
        $user = UserManager::instance()->getUser();
        if ($user) {
            $that->UserID = $user->ID;
        }
        if (!$that->get('CreatedAt')) {
            $that->set('CreatedAt', self::sqlTimestamp());
        }
    }

    public function onMapBeforeUpdate($that, array $pkeys)
    {
        $that->set('UpdatedAt', self::sqlTimestamp());
    }
}
