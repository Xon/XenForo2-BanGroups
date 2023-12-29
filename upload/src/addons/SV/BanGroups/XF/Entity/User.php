<?php

namespace SV\BanGroups\XF\Entity;

use XF\Service\User\UserGroupChange;

/**
 * Extends \XF\Entity\User
 */
class User extends XFCP_User
{
    protected function _postSave()
    {
        $rejectionChange = $this->isStateChanged('user_state', 'rejected');
        $disabledChange = $this->isStateChanged('user_state', 'disabled') ;

        if ($rejectionChange !== false || $disabledChange !== false)
        {
            $options = \XF::options();
            /** @var UserGroupChange $userGroupChangeService */
            $userGroupChangeService = $this->app()->service('XF:User\UserGroupChange');
            if ($rejectionChange !== false)
            {
                $rejectGroup = (int)($options->sv_addRejectUserGroup?? 0);
                $this->whenSaveable(function () use ($rejectionChange, $rejectGroup, $userGroupChangeService) {
                    if ($rejectGroup !== 0 && $rejectionChange === 'enter')
                    {
                        $userGroupChangeService->addUserGroupChange($this->user_id, 'svRejectedUserGroup', $rejectGroup);
                    }
                    else if ($rejectGroup === 0 || $rejectionChange === 'leave')
                    {
                        $userGroupChangeService->removeUserGroupChange($this->user_id, 'svRejectedUserGroup');
                    }
                });
            }
            if ($disabledChange !== false)
            {
                $disableGroup = (int)($options->sv_addDisableUserGroup ?? 0);
                $this->whenSaveable(function () use ($disabledChange, $disableGroup, $userGroupChangeService) {
                    if ($disableGroup !== 0 && $disabledChange === 'enter')
                    {
                        $userGroupChangeService->addUserGroupChange($this->user_id, 'svDisabledUserGroup', $disableGroup);
                    }
                    else if ($disableGroup === 0 || $disabledChange === 'leave')
                    {
                        $userGroupChangeService->removeUserGroupChange($this->user_id, 'svDisabledUserGroup');
                    }
                });
            }
        }

        parent::_postSave();
    }
}