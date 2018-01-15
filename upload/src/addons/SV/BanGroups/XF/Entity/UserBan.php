<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\BanGroups\XF\Entity;


use SV\BanGroups\Globals;

class UserBan extends XFCP_UserBan
{
    protected function setIsBanned($isBanned)
    {
        if (Globals::$isSpamCleaningBan)
        {
            $this->setOption('ban_user_group', \XF::options()->sv_addBanUserGroupSpam);
        }
        else if (!$this->end_date)
        {
            $this->setOption('ban_user_group', \XF::options()->sv_addBanUserGroupPerm);
        }

        try
        {
            return parent::setIsBanned($isBanned);
        }
        finally
        {
            $this->resetOption('ban_user_group');
        }
    }
}
