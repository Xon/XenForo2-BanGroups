<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\BanGroups\Job;

use XF\Entity\Option;

class InstallHelper extends \XF\Job\AbstractJob
{

    public function run($maxRunTime)
    {
        $options = \XF::options();

        $val = $options->addBanUserGroup;

        $options->sv_addBanUserGroupSpam = $val;
        /** @var Option $entity */
        $entity = \XF::finder('XF:Option')->whereId('sv_addBanUserGroupSpam')->fetchOne();
        if ($entity)
        {
            $entity->option_value = $val;
            $entity->save();
        }

        $options->sv_addBanUserGroupPerm = $val;
        /** @var Option $entity */
        $entity = \XF::finder('XF:Option')->whereId('sv_addBanUserGroupPerm')->fetchOne();
        if ($entity)
        {
            $entity->option_value = $val;
            $entity->save();
        }

        return $this->complete();
    }

    public function getStatusMessage()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canTriggerByChoice()
    {
        return false;
    }
}
