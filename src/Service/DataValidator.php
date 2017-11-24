<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Service\Setting;
use Base;
use Bumbon\Validation\Constraint\Choice;
use Bumbon\Validation\Constraint\Email;
use Bumbon\Validation\Constraint\IsTrue;
use Bumbon\Validation\Constraint\Length;
use Bumbon\Validation\Constraint\NotBlank;
use Bumbon\Validation\Validation;
use Nutrition\Constraint\Unique;
use Nutrition\Constraint\UserPassword;
use Nutrition\Utils\FlashMessage;
use Prefab;
use RuntimeException;

class DataValidator extends Prefab
{
    private $violations = [];
    private $data = [];


    public static function optionOnOff()
    {
        return [
            'On' => 'on',
            'Off' => 'off',
        ];
    }

    public function handle($name, $success, ...$params)
    {
        $app = Base::instance();

        array_unshift($params, $name);
        if ($app['VERB'] === 'POST' && call_user_func_array([$this, 'validate'], $params)) {
            call_user_func_array($success, [$app, $this->data($name)]);
        } elseif ($this->hasViolation($name)) {
            $app['violations'] = $this->violation($name);
            FlashMessage::instance()->add('warning', 'Ada data yang tidak valid');
        }
    }

    public function validate($name, array $groups = null, ...$params)
    {
        $method = 'validator'.ucfirst($name);
        if (method_exists($this, $method)) {
            $groups = $groups ?: ['Default'];
            $validation = call_user_func_array([$this, $method], $params);
            $this->violations[$name] = $validation->validate($this->postData(), $groups);
            $this->data[$name] = $validation->getData();

            return $this->violations[$name]->noViolation();
        }

        return null;
    }

    public function hasViolation($name)
    {
        return isset($this->violations[$name]);
    }

    public function violation($name)
    {
        if (empty($this->violations[$name])) {
            return null;
        }

        return $this->violations[$name];
    }

    public function data($name)
    {
        if (empty($this->data[$name])) {
            return [];
        }

        return $this->data[$name];
    }

    private function postData()
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            $data[$key] = '' === $value ? null : $value;
        }

        return $data;
    }

    private function validatorUser(User $user)
    {
        return Validation::create([
            'Name' => [
                new NotBlank(),
                new Length(['max'=>100])
            ],
            'Username' => [
                new NotBlank(),
                new Length(['max'=>50]),
                new Unique(['mapper'=>$user,'current_id'=>$user->ID,'field'=>'Username'])
            ],
            'Email' => [
                new NotBlank(),
                new Length(['max'=>100]),
                new Email(),
                new Unique(['mapper'=>$user,'current_id'=>$user->ID,'field'=>'Email'])
            ],
            'NewPassword' => [
                new Length(['min'=>5,'trim'=>false]),
            ],
            'Blocked' => [
                new NotBlank(),
                new Choice(['choices'=>self::optionOnOff()]),
            ],
            'UserRoles' => [
                new NotBlank(),
                new Choice(['choices'=>User::getAvailableRoles(),'multiple'=>true]),
            ],
        ]);
    }

    private function validatorAccount(User $user)
    {
        return Validation::create([
            'Name' => [
                new NotBlank(),
                new Length(['max'=>100])
            ],
            'Username' => [
                new NotBlank(),
                new Length(['max'=>50]),
                new Unique(['mapper'=>$user,'current_id'=>$user->ID,'field'=>'Username'])
            ],
            'NewPassword' => [
                new Length(['min'=>5,'trim'=>false]),
            ],
            'CurrentPassword' => [
                new NotBlank(),
                new UserPassword(),
            ],
        ]);
    }

    private function validatorSetting()
    {
        return Validation::create([
            Setting::SET_TITLE => [
                new NotBlank(),
                new Length(['max'=>100])
            ],
            Setting::SET_ALIAS => [
                new NotBlank(),
                new Length(['max'=>30]),
            ],
        ]);
    }

    private function validatorMaintenance()
    {
        return Validation::create([
            Setting::SET_MAINTENANCE => [
                new NotBlank(),
                new Choice(['choices'=>self::optionOnOff()])
            ],
        ]);
    }

    private function validatorConfirm()
    {
        return Validation::create([
            'Confirm' => [
                new NotBlank(),
                new IsTrue(),
            ],
        ]);
    }

    private function validatorPost()
    {
        return Validation::create([
            'Title' => [
                new NotBlank(),
                new Length(['max'=>200]),
            ],
            'Headline' => [
                new NotBlank(),
                new Length(['max'=>250]),
            ],
            'Content' => [
                new NotBlank(),
                new Length(['max'=>1000]),
            ],
            'Type' => [
                new NotBlank(),
                new Choice(['choices'=>Post::getEditablePostTypes()]),
            ],
        ]);
    }
}
