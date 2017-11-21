<?php

namespace App\Service;

use DateTime;
use Prefab;
use Base;

class ReportSetup extends Prefab
{
    const MODE_DAYS = 'days';
    const MODE_MONTHS = 'months';
    const MODE_YEARS = 'years';
    const MODE_PERIODS = 'periods';

    /** @var string */
    private $modeKey = 'report';

    /** @var string */
    private $dateAKey = 'datea';

    /** @var string */
    private $dateBKey = 'dateb';

    /** @var string */
    private $mode;

    /** @var string */
    private $dateFormat;

    /** @var DateTime */
    private $dateA;

    /** @var DateTime */
    private $dateB;


    public function getAllMode()
    {
        return [
            'Harian' => self::MODE_DAYS,
            'Bulanan' => self::MODE_MONTHS,
            'Tahunan' => self::MODE_YEARS,
            'Periode' => self::MODE_PERIODS,
        ];
    }

    public function getModeLabel()
    {
        return array_search($this->getMode(), $this->getAllMode());
    }

    public function getPeriodLabel()
    {
        switch ($this->getMode()) {
            case self::MODE_DAYS:
            case self::MODE_MONTHS:
            case self::MODE_YEARS:
                return $this->getDateA(true);
            case self::MODE_PERIODS:
                return $this->getDateA(true) . ' s/d ' . $this->getDateB(true);
        }
    }

    public function setModeKey($modeKey)
    {
        $this->modeKey = $modeKey;

        return $this;
    }

    public function getModeKey()
    {
        return $this->modeKey;
    }

    public function setDateAKey($dateAKey)
    {
        $this->dateAKey = $dateAKey;

        return $this;
    }

    public function getDateAKey()
    {
        return $this->dateAKey;
    }

    public function setDateBKey($dateBKey)
    {
        $this->dateBKey = $dateBKey;

        return $this;
    }

    public function getDateBKey()
    {
        return $this->dateBKey;
    }

    public function getMode()
    {
        if (empty($this->mode)) {
            $this->mode = Base::instance()->get('GET.'.$this->modeKey);
        }

        return $this->mode;
    }

    public function isMode($mode)
    {
        return $mode === $this->getMode();
    }

    public function isDaysMode()
    {
        return $this->getMode() === self::MODE_DAYS;
    }

    public function isMonthsMode()
    {
        return $this->getMode() === self::MODE_MONTHS;
    }

    public function isYearsMode()
    {
        return $this->getMode() === self::MODE_YEARS;
    }

    public function isPeriodsMode()
    {
        return $this->getMode() === self::MODE_PERIODS;
    }

    public function getDateFormat()
    {
        if (empty($this->dateFormat)) {
            switch ($this->getMode()) {
                case self::MODE_MONTHS:
                    $this->dateFormat = 'm/Y';
                    break;
                case self::MODE_YEARS:
                    $this->dateFormat = 'Y';
                    break;
                default:
                    $this->dateFormat = 'd/m/Y';
                    break;
            }
        }

        return $this->dateFormat;
    }

    public function getDateA($asString = false)
    {
        if (is_null($this->dateA)) {
            $this->dateA = DateTime::createFromFormat(
                $this->getDateFormat(),
                Base::instance()->get('GET.'.$this->dateAKey)
            );
        }

        if ($asString) {
            return $this->dateA ? $this->dateA->format(
                $this->getDateFormat()
            ) : null;
        }

        return $this->dateA;
    }

    public function getDateB($asString = false)
    {
        if (is_null($this->dateB)) {
            $this->dateB = DateTime::createFromFormat(
                $this->getDateFormat(),
                Base::instance()->get('GET.'.$this->dateBKey)
            );
        }

        if ($asString) {
            return $this->dateB ? $this->dateB->format(
                $this->getDateFormat()
            ) : null;
        }

        return $this->dateB;
    }
}
