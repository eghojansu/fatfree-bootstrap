<?php

namespace App\Entity;

use Nutrition\SQL\Criteria;
use Nutrition\SQL\Mapper;
use Nutrition\Security\UserManager;
use Nutrition\Utils\PaginationSetup;

class Task extends Mapper
{
    const TYPE_BACKUP = 'Backup';
    const TYPE_RESTORE = 'Restore';


    public function isBackup()
    {
        return $this->Task === self::TYPE_BACKUP;
    }

    public function isRestore()
    {
        return $this->Task === self::TYPE_RESTORE;
    }

    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('CreatedAt')) {
            $that->set('CreatedAt', self::sqlTimestamp());
        }
    }

    public function getBackupRestoreHistory()
    {
        $setup = PaginationSetup::instance();
        $criteria = Criteria::create()
            ->add('Task', [self::TYPE_BACKUP,self::TYPE_RESTORE]);

        return $this->createPagination(
            $setup->getRequestPage() - 1,
            $setup->getPerPage(),
            $criteria->get()
        );
    }

    public function isComplete()
    {
        return $this->Progress == 100;
    }
}
