<?php

namespace SV\BanGroups\XF\Entity;

use XF\Service\User\UserGroupChange as UserGroupChangeService;
use function func_get_args;

/**
 * Extends \XF\Entity\User
 */
class User extends XFCP_User
{
    protected $svRebuildStateGroupsWhenSavableQueued = false;

    protected function _postSave()
    {
        parent::_postSave();

        if (!$this->svRebuildStateGroupsWhenSavableQueued)
        {
            $rejectionChange = $this->isStateChanged('user_state', 'rejected');
            $disabledChange = $this->isStateChanged('user_state', 'disabled');

            if ($rejectionChange !== false || $disabledChange !== false)
            {
                $this->svRebuildStateGroupsWhenSavable($rejectionChange, $disabledChange, null);
            }
        }
    }

    public function rebuildUserGroupRelations($newTransaction = true)
    {
        parent::rebuildUserGroupRelations($newTransaction);

        $userState = $this->user_state;
        $this->svRebuildStateGroupsWhenSavable(
            $userState === 'rejected' ? 'enter' : 'leave',
            $userState === 'disabled' ? 'enter' : 'leave',
            $this->is_banned ? 'enter' : null
        );
    }

    protected function svRebuildStateGroupsWhenSavable(?string $rejectionChange, ?string $disabledChange, ?string $banChanged): void
    {
        if ($this->svRebuildStateGroupsWhenSavableQueued)
        {
            return;
        }
        $this->svRebuildStateGroupsWhenSavableQueued = true;

        $args = func_get_args();
        $this->whenSaveable(function() use ($args) {
            $this->svRebuildStateGroups(...$args);
        });
    }

    protected function svRebuildStateGroups(?string $rejectionChange, ?string $disabledChange, ?string $banChanged): void
    {
        /** @var UserGroupChangeService $userGroupChangeService */
        $userGroupChangeService = \XF::service('XF:User\UserGroupChange');
        $options = \XF::options();
        $userId = $this->user_id;

        $rejectGroup = (int)($options->sv_addRejectUserGroup ?? 0);
        if ($rejectGroup !== 0 && $rejectionChange === 'enter')
        {
            $userGroupChangeService->addUserGroupChange($userId, 'svRejectedUserGroup', $rejectGroup);
        }
        else if ($rejectionChange !== null)
        {
            $userGroupChangeService->removeUserGroupChange($userId, 'svRejectedUserGroup');
        }

        $disableGroup = (int)($options->sv_addDisableUserGroup ?? 0);
        if ($disableGroup !== 0 && $disabledChange === 'enter')
        {
            $userGroupChangeService->addUserGroupChange($userId, 'svDisabledUserGroup', $disableGroup);
        }
        else if ($disabledChange !== null)
        {
            $userGroupChangeService->removeUserGroupChange($userId, 'svDisabledUserGroup');
        }

        $permBanGroup = (int)($options->sv_addBanUserGroupPerm ?? 0);
        $tempBanGroup = (int)($options->addBanUserGroup ?? 0);
        if ($banChanged === 'enter')
        {
            $userGroupChangeService->addUserGroupChange($userId, 'svDisabledUserGroup', $disableGroup);
        }
        else if ($banChanged !== null)
        {
            $userGroupChangeService->removeUserGroupChange($userId, 'svDisabledUserGroup');
        }
    }
}