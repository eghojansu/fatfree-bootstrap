<?php

namespace App\Core;

use App\Service\ReportSetup;
use Nutrition\SQL\Criteria;

trait ReportFilterTrait
{
    /**
     * Modify criteria for report
     * @param  Criteria    $criteria
     * @param  ReportSetup $setup
     * @param  string $filterOn
     * @return void
     */
    public function modifyReportFilter(
        Criteria $criteria,
        ReportSetup $setup,
        $filterOn = 'CreatedAt'
    ) {
        $dateA = $setup->getDateA();
        $dateB = $setup->getDateB();

        if ($dateA && $setup->isDaysMode()) {
            $criteria->addCriteria(
                "DATE($filterOn) = :rep_d",
                ['rep_d' => $dateA->format('Y-m-d')]
            );
        } elseif ($dateA && $setup->isMonthsMode()) {
            $criteria->addCriteria(
                "(MONTH($filterOn) = :rep_m and YEAR($filterOn) = :rep_y)",
                [
                    'rep_m' => $dateA->format('m'),
                    'rep_y' => $dateA->format('Y'),
                ]
            );
        } elseif ($dateA && $setup->isYearsMode()) {
            $criteria->addCriteria(
                "YEAR($filterOn) = :rep_y",
                ['rep_y' => $dateA->format('Y')]
            );
        } elseif ($dateA && $dateB && $setup->isPeriodsMode()) {
            $criteria->addCriteria(
                "DATE($filterOn) BETWEEN :rep_a AND :rep_b",
                [
                    'rep_a' => $dateA->format('Y-m-d'),
                    'rep_b' => $dateB->format('Y-m-d'),
                ]
            );
        }
    }
}
