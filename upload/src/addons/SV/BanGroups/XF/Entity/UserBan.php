<?php

namespace SV\BanGroups\XF\Entity;

use SV\BanGroups\Globals;
use SV\StandardLib\Helper;
use XF\Service\User\UserGroupChange as UserGroupChangeService;

class UserBan extends XFCP_UserBan
{
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isUpdate() && $this->isChanged('end_date') && ($this->end_date === 0 || $this->getExistingValue('end_date') === 0))
        {
            // change from being temp to perma-banned or the reverse
            $this->whenSaveable(function() {
                $userId = $this->user_id;
                $userGroupChangeService = Helper::service(UserGroupChangeService::class);

                $banGroupId = $this->getSvBanGroup();
                $userGroupChangeService->removeUserGroupChange($userId, 'banGroup');
                if ($banGroupId !== 0)
                {
                    $userGroupChangeService->addUserGroupChange($userId, 'banGroup', $banGroupId);
                }
            });
        }
    }

    public function getSvBanGroup(): int
    {
        if (Globals::$isSpamCleaningBan ?? false)
        {
            return (int)(\XF::options()->sv_addBanUserGroupSpam ?? 0);
        }
        else if (!$this->end_date)
        {
            return (int)(\XF::options()->sv_addBanUserGroupPerm ?? 0);
        }

        return (int)(\XF::options()->addBanUserGroup ?? 0);
    }

    protected function setIsBanned($isBanned)
    {
        $this->setOption('ban_user_group', $this->getSvBanGroup());
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
