<?php

namespace SV\BanGroups\XF\Entity;

use SV\StandardLib\Helper;
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
        $userGroupChangeService = Helper::service(UserGroupChangeService::class);
        $options = \XF::options();
        $userId = $this->user_id;

        if ($rejectionChange !== null)
        {
            $rejectGroup = (int)($options->sv_addRejectUserGroup ?? 0);
            if ($rejectGroup !== 0 && $rejectionChange === 'enter')
            {
                $userGroupChangeService->addUserGroupChange($userId, 'svRejectedUserGroup', $rejectGroup);
            }
            else
            {
                $userGroupChangeService->removeUserGroupChange($userId, 'svRejectedUserGroup');
            }
        }

        if ($disabledChange !== null)
        {
            $disableGroup = (int)($options->sv_addDisableUserGroup ?? 0);
            if ($disableGroup !== 0 && $disabledChange === 'enter')
            {
                $userGroupChangeService->addUserGroupChange($userId, 'svDisabledUserGroup', $disableGroup);
            }
            else
            {
                $userGroupChangeService->removeUserGroupChange($userId, 'svDisabledUserGroup');
            }
        }

        if ($banChanged !== null)
        {
            /** @var UserBan $ban */
            $ban = $this->Ban;
            $banGroupId = $ban !== null ? $ban->getSvBanGroup() : 0;

            if ($banGroupId !== 0 && $banChanged === 'enter')
            {
                $userGroupChangeService->addUserGroupChange($userId, 'banGroup', $banGroupId);
            }
            else
            {
                $userGroupChangeService->removeUserGroupChange($userId, 'banGroup');
            }
        }
    }
}