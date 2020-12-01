<?php

namespace SV\BanGroups\XF\Entity;

use XF\Service\User\UserGroupChange;

/**
 * Extends \XF\Entity\User
 */
class User extends XFCP_User
{
    /** @var \Closure[] */
    protected $svBanUserSavableFn = [];

    protected function _postSave()
    {
        if ($this->isUpdate() && $this->isChanged('user_state'))
        {
            $options = \XF::options();
            /** @var UserGroupChange $userGroupChangeService */
            $userGroupChangeService = $this->app()->service('XF:User\UserGroupChange');

            $rejectionChange = $this->isStateChanged('user_state', 'rejected');
            if ($rejectionChange)
            {
                $rejectGroup = isset($options->sv_addRejectUserGroup) ? (int)$options->sv_addRejectUserGroup : 0;
                $this->svBanUserSavableFn[] = function () use ($rejectionChange, $rejectGroup, $userGroupChangeService) {
                    if ($rejectGroup && $rejectionChange === 'enter')
                    {
                        $userGroupChangeService->addUserGroupChange($this->user_id, 'svRejectedUserGroup', $rejectGroup);
                    }
                    else if (!$rejectGroup || $rejectionChange === 'leave')
                    {
                        $userGroupChangeService->removeUserGroupChange($this->user_id, 'svRejectedUserGroup');
                    }
                };
            }

            $disabledChange = $this->isStateChanged('user_state', 'disabled');
            if ($disabledChange)
            {
                $disableGroup = isset($options->sv_addDisableUserGroup) ? (int)$options->sv_addDisableUserGroup : 0;
                $this->svBanUserSavableFn[] = function () use ($disabledChange, $disableGroup, $userGroupChangeService) {
                    if ($disableGroup && $disabledChange === 'enter')
                    {
                        $userGroupChangeService->addUserGroupChange($this->user_id, 'svDisabledUserGroup', $disableGroup);
                    }
                    else if (!$disableGroup || $disabledChange === 'leave')
                    {
                        $userGroupChangeService->removeUserGroupChange($this->user_id, 'svDisabledUserGroup');
                    }
                };
            }
        }

        parent::_postSave();
    }

    protected function _saveCleanUp(array $newDbValues)
    {
        parent::_saveCleanUp($newDbValues);

        // XF2.1 whenSavable emulation
		// need to run any pending callbacks now that writing is complete
		while ($fn = array_shift($this->svBanUserSavableFn))
        {
            $fn($this);
        }
    }
}