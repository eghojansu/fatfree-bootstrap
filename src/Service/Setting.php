<?php

namespace App\Service;

use Nutrition\MagicService;
use Nutrition\SQL\Criteria;
use RuntimeException;

class Setting extends MagicService
{
    const SET_TITLE = 'AppTitle';
    const SET_ALIAS = 'AppAlias';
    const SET_MAINTENANCE = 'Maintenance';

    const E_KEY = 'No configuration for key "%s"';

    /** @var boolean */
    private $dry = true;

    private $labels = [
        self::SET_TITLE => 'Nama Aplikasi',
        self::SET_ALIAS => 'Alias',
        self::SET_MAINTENANCE => 'Maintenance',
    ];

    private $properties;


    public function __construct()
    {
        $this->properties = $this->getDefaultContent();
    }

    public function &get($name, $default = null, $raw = false)
    {
        $this->checkKey($name);
        $this->load();

        $value = $raw ? $this->properties[$name] : $this->parse($name);

        return $value===null ? $default : $value;
    }

    public function set($name, $value)
    {
        $this->checkKey($name);

        if ($value === $this->properties[$name]) {
            return $this;
        }

        $setting = EntityLoader::instance()->configuration(true);
        $setting->load(['Name = ?', $name]);
        $setting->set('Name', $name);
        $setting->set('Content', $value ?? '~');
        $setting->save();

        $this->properties[$name] = $setting->get('Content');

        return $this;
    }

    public function exists($name)
    {
        return array_key_exists($name, $this->properties);
    }

    public function clear($name)
    {
        // no empty value
    }

    public function getValues($raw = false, array $names = null)
    {
        $this->load();

        $properties = [];
        if ($names) {
            foreach ($names ?? [] as $name) {
                $properties[$name] = $this->properties[$name];
            }
        } else {
            $properties = $this->properties;
        }

        if ($raw) {
            return $properties;
        }

        foreach ($this->properties as $key => $value) {
            $properties[$key] = $this->parse($key);
        }

        return $properties;
    }

    public function setValues(array $values)
    {
        if (empty($values)) {
            return $this;
        }

        foreach ($values as $key => $value) {
            $this->checkKey($key);
        }

        $mapper = EntityLoader::instance()->configuration(true);
        foreach ($mapper->findByName(array_keys($values)) ?: [] as $setting) {
            $key = $setting->Name;
            $setting->Content = $values[$key] ?? '~';
            $setting->save();
            $this->properties[$key] = $setting->Content;
            unset($values[$key]);
        }

        foreach ($values as $key => $value) {
            $mapper->reset();
            $mapper->Name = $key;
            $mapper->Content = $value ?? '~';
            $mapper->save();
            $this->properties[$key] = $mapper->Content;
        }

        return $this;
    }

    public function getDefaultContent()
    {
        return [
            self::SET_TITLE => 'App',
            self::SET_ALIAS => 'App',
            self::SET_MAINTENANCE => 'off',
        ];
    }

    public function setDefaultContent()
    {
        return $this->setValues($this->getDefaultContent());
    }

    private function parse($name)
    {
        $value = $this->properties[$name];
        if (is_null($value) || '~' === $value) {
            return null;
        } elseif (in_array($name, [self::SET_MAINTENANCE])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } else {
            return $value;
        }
    }

    private function checkKey($key)
    {
        if (!array_key_exists($key, $this->properties)) {
            throw new RuntimeException(sprintf(self::E_KEY, $key));
        }
    }

    private function load()
    {
        if ($this->dry) {
            foreach (EntityLoader::instance()->configuration(true)->findByName(
                array_keys($this->properties)
            ) ?: [] as $setting) {
                $this->properties[$setting->Name] = $setting->Content;
            }
            $this->dry = false;
        }
    }
}
