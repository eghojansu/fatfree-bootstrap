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
        'UserRoles' => null,
        'NewPassword' => null,
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
                '(Username like :keyword or Email like :keyword or Name like :keyword)',
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
        return $this->findone(self::filterGuard()->add('ID', $id)->get());
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
                'Roles not like "%role_developer%" and ID <> :sid',
                ['sid'=>UserManager::instance()->getUser()->get('ID')]
            );
    }

    /**
     * {@inheritdoc}
    */
    public function getUsername()
    {
        return $this->Username;
    }

    /**
     * {@inheritdoc}
    */
    public function getPassword()
    {
        return $this->Password;
    }

    /**
     * {@inheritdoc}
    */
    public function getRoles()
    {
        return $this->extras['UserRoles'];
    }

    /**
     * {@inheritdoc}
    */
    public function isExpired()
    {
        return filter_var($this->Expired, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * {@inheritdoc}
    */
    public function isBlocked()
    {
        return filter_var($this->Blocked, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * {@inheritdoc}
    */
    public function loadByUsername($username)
    {
        return $this->findone([
            '(Username = :username or Email = :username)',
            ':username'=>$username
        ]);
    }

    public function getLastLogin()
    {
        return UserLog::create()->findone([
            'UserID = ? and not Active',
            $this->ID
        ]);
    }

    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('CreatedAt')) {
            $that->set('CreatedAt', self::sqlTimestamp());
        }
        $that->updatePassword()->updateRoles();
    }

    public function onMapBeforeUpdate($that, array $pkeys)
    {
        $that->set('UpdatedAt', self::sqlTimestamp());
        $that->updatePassword()->updateRoles();
    }

    public function onMapLoad($that)
    {
        $that->set('UserRoles', explode(',', $that->Roles));
        $that->set('NewPassword', null);
    }

    private function updatePassword()
    {
        if ($this->extras['NewPassword']) {
            $this->Password = Security::instance()
                ->getPasswordEncoder()
                ->encodePassword($this->extras['NewPassword']);
        }

        return $this;
    }

    private function updateRoles()
    {
        if ($this->extras['UserRoles']) {
            $this->Roles = implode(',', $this->extras['UserRoles']);
        }

        return $this;
    }
}
