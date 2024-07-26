<?php

namespace SV\BanGroups;

use SV\StandardLib\Helper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Entity\Option;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    public function postInstall(array &$stateChanges): void
    {
        parent::postInstall($stateChanges);

        $options = \XF::options();
        $val = (int)($options->addBanUserGroup ?? 0);
        if ($val === 0)
        {
            return;
        }

        $options->sv_addBanUserGroupSpam = $val;
        $entity = Helper::finder(\XF\Finder\Option::class)
                     ->whereId('sv_addBanUserGroupSpam')
                     ->fetchOne();
        if ($entity !== null)
        {
            $entity->option_value = $val;
            $entity->save();
        }

        $options->sv_addBanUserGroupPerm = $val;
        $entity = Helper::finder(\XF\Finder\Option::class)
                     ->whereId('sv_addBanUserGroupPerm')
                     ->fetchOne();
        if ($entity !== null)
        {
            $entity->option_value = $val;
            $entity->save();
        }
    }
}
