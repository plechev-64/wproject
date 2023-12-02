<?php

namespace Core\Service;

use Common\Model\ManagerIncomeModel;
use Core\ObjectManager\ObjectManagerAbstract;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @method static ManagerService init()
 */
class ManagerService extends ServiceAbstract
{
    public function getDataByIncomeModel(
        ObjectManagerAbstract $manager,
        ManagerIncomeModel $incomeModel
    ): ArrayCollection {

        $manager
            ->setFilters($incomeModel->filters)
            ->setNumber($incomeModel->number)
            ->setPage($incomeModel->page)
            ->setIsShuffle($incomeModel->isShuffle);

        if($incomeModel->isShuffle) {
            $manager->setOrderPages($incomeModel->args['pages']);
        }

        if($incomeModel->sort) {
            $manager->setOrderBy($incomeModel->sort['by'], $incomeModel->sort['order']);
        }

        return $incomeModel->queue ? $manager->getUnionData($incomeModel->queue) : $manager->getData();
    }

}
