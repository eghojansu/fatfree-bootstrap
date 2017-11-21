<?php

namespace App\Entity;

use App\Core\ReportFilterTrait;
use App\Service\ReportSetup;
use Nutrition\SQL\Criteria;
use Nutrition\SQL\Mapper;
use Nutrition\Security\Security;
use Nutrition\Security\UserInterface;
use Nutrition\Security\UserManager;
use Nutrition\Security\UserProviderInterface;
use Nutrition\Utils\PaginationSetup;

class User extends Mapper implements UserInterface, UserProviderInterface
{
    use ReportFilterTrait;

    protected $extras = [
        'user_roles' => null,
        'new_password' => null,
    ];


    public static function getAvailableRoles()
    {
        return [
            'Super Admin' => 'ROLE_SUPER_ADMIN',
        ];
    }

    public function findAll()
    {
        $setup = PaginationSetup::instance();
        $criteria = self::filterGuard();

        if ($keyword = $setup->getRequestArg('keyword')) {
            $criteria->addCriteria(
                '(username like :keyword or email like :keyword)',
                ['keyword'=>'%'.$keyword.'%']
            );
        }

        return $this->createPagination(
            $setup->getRequestPage() - 1,
            $setup->getPerPage(),
            $criteria->get()
        );
    }

    public function findUser($id)
    {
        return $this->findone(self::filterGuard()->add('id', $id)->get());
    }

    public function reportRekapUser()
    {
        $report = ReportSetup::instance();
        $setup = PaginationSetup::instance();
        $criteria = self::filterGuard();

        $this->modifyReportFilter($criteria, $report);

        return $this->createPagination(
            $setup->getRequestPage() - 1,
            $setup->getPerPage(),
            $criteria->get()
        );
    }

    public function reportRekapUserPrint()
    {
        $report = ReportSetup::instance();
        $criteria = self::filterGuard();

        $this->modifyReportFilter($criteria, $report);

        return $this->find($criteria->get());
    }

    public static function filterGuard()
    {
        return Criteria::create()
            ->addCriteria(
                'roles not like "%role_developer%" and id <> :sid',
                ['sid'=>UserManager::instance()->getUser()->get('id')]
            );
    }

    /**
     * {@inheritdoc}
    */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
    */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
    */
    public function getRoles()
    {
        return $this->extras['user_roles'];
    }

    /**
     * {@inheritdoc}
    */
    public function isExpired()
    {
        return filter_var($this->expired, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * {@inheritdoc}
    */
    public function isBlocked()
    {
        return filter_var($this->blocked, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * {@inheritdoc}
    */
    public function loadByUsername($username)
    {
        return $this->findone([
            '(username = :username or email = :username)',
            ':username'=>$username
        ]);
    }

    public function getLastLogin()
    {
        return UserLog::create()->findone([
            'user_id = ? and not active',
            $this->id
        ]);
    }

    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('created_at')) {
            $that->set('created_at', self::sqlTimestamp());
        }
        $that->updatePassword()->updateRoles();
    }

    public function onMapBeforeUpdate($that, array $pkeys)
    {
        $that->set('updated_at', self::sqlTimestamp());
        $that->updatePassword()->updateRoles();
    }

    public function onMapLoad($that)
    {
        $that->set('user_roles', explode(',', $that->roles));
        $that->set('new_password', null);
    }

    private function updatePassword()
    {
        if ($this->extras['new_password']) {
            $this->password = Security::instance()
                ->getPasswordEncoder()
                ->encodePassword($this->extras['new_password']);
        }

        return $this;
    }

    private function updateRoles()
    {
        if ($this->extras['user_roles']) {
            $this->roles = implode(',', $this->extras['user_roles']);
        }

        return $this;
    }
}
