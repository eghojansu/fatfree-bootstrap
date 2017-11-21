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
        return $this->task === self::TYPE_BACKUP;
    }

    public function isRestore()
    {
        return $this->task === self::TYPE_RESTORE;
    }

    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('created_at')) {
            $that->set('created_at', self::sqlTimestamp());
        }
    }

    public function getBackupRestoreHistory()
    {
        $setup = PaginationSetup::instance();
        $criteria = Criteria::create()
            ->add('task', [self::TYPE_BACKUP,self::TYPE_RESTORE]);

        return $this->createPagination(
            $setup->getRequestPage() - 1,
            $setup->getPerPage(),
            $criteria->get()
        );
    }

    public function isComplete()
    {
        return $this->progress == 100;
    }
}
