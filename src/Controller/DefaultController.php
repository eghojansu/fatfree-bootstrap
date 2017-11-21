<?php

namespace App\Controller;

use App\Core\Controller;
use Base;

class DefaultController extends Controller
{
    public function homepageAction(Base $app)
    {
        $app['welcome'] = $this->entity->post->getWelcomeContent();

        $this->renderHome('default/homepage.html');
    }

    public function newsAction(Base $app)
    {
        $app['news'] = $this->entity->post->getPosts();

        $this->renderHome('default/news.html');
    }

    public function newsItemAction(Base $app, array $params)
    {
        $app['item'] = $this->entity->post->getPost($params['slug']);

        $this->renderHome('default/news_item.html');
    }

    public function aboutAction(Base $app)
    {
        $app['about'] = $this->entity->post->getAboutContent();

        $this->renderHome('default/about.html');
    }
}
