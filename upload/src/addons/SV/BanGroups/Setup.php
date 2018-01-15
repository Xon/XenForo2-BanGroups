<?php

namespace SV\BanGroups;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    /**
     * Applies database schema changes.
     */
    public function installStep1()
    {
        if (\XF::options()->addBanUserGroup)
        {
            // options haven't been created yet. Create a deferred task todo it after this
            \XF::app()->jobManager()->enqueueUnique(
                'SV\BanGroups',
                'SV\BanGroups:InstallHelper',
                [],
                false
            );
        }
    }

}
