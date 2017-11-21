<?php

namespace App\Entity;

use Nutrition\SQL\Mapper;

class Post extends Mapper
{
    const TYPE_ABOUT = 'about';
    const TYPE_WELCOME = 'welcome';
    const TYPE_PUBLISHED = 'published';
    const TYPE_DRAFT = 'draft';
    const TYPE_HIDDEN = 'hidden';


    public function getWelcomeContent()
    {
        return $this->findOneByTipe(self::TYPE_WELCOME);
    }

    public function getAboutContent()
    {
        return $this->findOneByTipe(self::TYPE_ABOUT);
    }

    public function getPosts()
    {
        return $this->findByTipe(self::TYPE_PUBLISHED);
    }

    public function getPost($slug)
    {
        return $this->findone(['tipe = ? and slug = ?', self::TYPE_PUBLISHED, $slug]);
    }

    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('created_at')) {
            $that->set('created_at', self::sqlTimestamp());
        }
    }

    public function onMapBeforeUpdate($that, array $pkeys)
    {
        $that->set('updated_at', self::sqlTimestamp());
    }
}
