<?php

namespace App\Service;

use App\Entity\User;
use App\Service\Setting;
use Base;
use Nutrition\Utils\FlashMessage;
use Nutrition\Validator\Constraint\Boolean;
use Nutrition\Validator\Constraint\Choice;
use Nutrition\Validator\Constraint\Email;
use Nutrition\Validator\Constraint\IsTrue;
use Nutrition\Validator\Constraint\Length;
use Nutrition\Validator\Constraint\NotBlank;
use Nutrition\Validator\Constraint\NotInTable;
use Nutrition\Validator\Constraint\UserPassword;
use Nutrition\Validator\Validation;
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

    public function handle($name, $success, array $groups = null, ...$params)
    {
        $app = Base::instance();

        array_unshift($params, $name, $groups);
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
            $this->violations[$name] = $validation->validate($groups);
            $this->data[$name] = $validation->getData();

            return $this->violations[$name]->hasNoViolation();
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
            return null;
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
        return Validation::create($this->postData(), [
            'nama' => [
                new NotBlank(),
                new Length(['max'=>100])
            ],
            'username' => [
                new NotBlank(),
                new Length(['max'=>50]),
                new NotInTable(['mapper'=>$user,'current_id'=>$user->id,'field'=>'username'])
            ],
            'email' => [
                new NotBlank(),
                new Length(['max'=>100]),
                new Email(),
                new NotInTable(['mapper'=>$user,'current_id'=>$user->id,'field'=>'email'])
            ],
            'new_password' => [
                new Length(['min'=>5]),
            ],
            'blocked' => [
                new NotBlank(),
                new Choice(['choices'=>self::optionOnOff()]),
            ],
            'user_roles' => [
                new NotBlank(),
                new Choice(['choices'=>User::getAvailableRoles(),'multiple'=>true]),
            ],
        ]);
    }

    private function validatorAccount(User $user)
    {
        return Validation::create($this->postData(), [
            'nama' => [
                new NotBlank(),
                new Length(['max'=>100])
            ],
            'username' => [
                new NotBlank(),
                new Length(['max'=>50]),
                new NotInTable(['mapper'=>$user,'current_id'=>$user->id,'field'=>'username'])
            ],
            'new_password' => [
                new Length(['min'=>5]),
            ],
            'current_password' => [
                new NotBlank(),
                new UserPassword(),
            ],
        ]);
    }

    private function validatorSetting()
    {
        return Validation::create($this->postData(), [
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
        return Validation::create($this->postData(), [
            Setting::SET_MAINTENANCE => [
                new NotBlank(),
                new Choice(['choices'=>self::optionOnOff()])
            ],
        ]);
    }

    private function validatorConfirm()
    {
        return Validation::create($this->postData(), [
            'confirm' => [
                new NotBlank(),
                new IsTrue(),
            ],
        ]);
    }
}
