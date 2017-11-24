<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;
use Nutrition\Utils\CommonUtil;
use Web;

class PostController extends Controller
{
    public function __construct()
    {
        $this->breadcrumb->add('post', null, 'Post');
        $this->setup->prefixTitle('Post');
    }

    public function indexAction(Base $app)
    {
        $app['pagination'] = $this->entity->post->getEditablePosts();

        $this->renderAdmin('post/index.html');
    }

    public function createAction(Base $app)
    {
        $post = $this->entity->post;
        $this->validator->handle('post', function($app, $data) use ($post) {
            $post->copyfrom($data);
            $post->Slug = Web::instance()->slug($post->Title);
            $post->UserID = $this->user->ID;
            $post->save();
            $this->flash->add('success', 'Data sudah disimpan');

            $app->reroute('post');
        });
        $app['post'] = $post;
        $app['title'] = 'Create';

        $this->renderAdmin('post/form.html');
    }

    public function updateAction(Base $app, array $params)
    {
        $post = $this->entity->post->findOneByID($params['post']);
        $this->notFoundIfFalse($post)->validator->handle('post', function($app, $data) use ($post) {
            $post->copyfrom($data);
            $post->save();
            $this->flash->add('success', 'Data sudah disimpan');

            $app->reroute('post');
        });
        $app['post'] = $post;
        $app['title'] = 'Update';

        $this->renderAdmin('post/form.html');
    }

    public function deleteAction(Base $app, array $params)
    {
        $post = $this->entity->post->findOneByID($params['post']);
        $this->notFoundIfFalse($post)->validator->handle('confirm', function($app) use ($post) {
            $post->erase();
            $this->flash->add('info', 'Data sudah dihapus');

            $app->reroute('post');
        });
        $app['post'] = $post;

        $this->renderAdmin('post/delete.html');
    }

    public function uploadAction(Base $app)
    {
        $files = array_filter(Web::instance()->receive(null, true, function($fileBaseName) {
            return CommonUtil::random(8).strrchr($fileBaseName, '.');
        }));
        if ($files) {
            $path = 'assets/images';
            if (!is_dir($path)) {
                @mkdir($path);
            }
            reset($files);
            $file = key($files);
            $basename = basename($file);
            $fullpath = $path.'/'.$basename;

            rename($file, $fullpath);

            $this->json([
                'message' => 'Upload success',
                'success' => true,
                'url' => $this->route->path($fullpath)
            ]);
        } else {
            $this->json([
                'message' => 'Upload fail',
                'success' => false
            ]);
        }
    }
}
