<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Setting;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\UserLog;
use Prefab;
use RuntimeException;

class EntityLoader extends Prefab
{
    private $map = [
        Post::class => 'post',
        Setting::class => 'setting',
        User::class => 'user',
        UserLog::class => 'userLog',
        Task::class => 'task',
    ];
    private $instance = [];

    public function __get($name)
    {
        return $this->load($name);
    }

    public function __call($name, array $params)
    {
        return $this->load($name, array_shift($params));
    }

    public function load($name, $force = false)
    {
        $class = array_search($name, $this->map);
        if ($class === false) {
            throw new RuntimeException(sprintf(
                '%s was not defined in %s',
                $name,
                static::class
            ));
        }

        if (empty($this->instance[$name]) || $force) {
            $this->instance[$name] = call_user_func("$class::create");
        }

        return $this->instance[$name];
    }
}
