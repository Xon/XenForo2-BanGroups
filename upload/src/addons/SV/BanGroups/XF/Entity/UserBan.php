<?php

namespace SV\BanGroups\XF\Entity;

use SV\BanGroups\Globals;

class UserBan extends XFCP_UserBan
{
    protected function setIsBanned($isBanned)
    {
        if (Globals::$isSpamCleaningBan ?? false)
        {
            $this->setOption('ban_user_group', \XF::options()->sv_addBanUserGroupSpam);
        }
        else if (!$this->end_date)
        {
            $this->setOption('ban_user_group', \XF::options()->sv_addBanUserGroupPerm);
        }

        try
        {
            parent::setIsBanned($isBanned);
        }
        finally
        {
            $this->resetOption('ban_user_group');
        }
    }
}
