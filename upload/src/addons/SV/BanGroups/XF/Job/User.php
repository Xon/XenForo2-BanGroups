<?php

namespace SV\BanGroups\XF\Job;

use XF\Service\User\UserGroupChange as UserGroupChangeService;

/**
 * Extends \XF\Job\User
 */
class User extends XFCP_User
{
    protected function rebuildById($id)
    {
        parent::rebuildById($id);

        /** @var \XF\Entity\User $user */
        $userId = (int)$id;
        $user = $this->app->em()->findCached('XF:User', $userId);
        if (!$user)
        {
            return;
        }

        $options = \XF::options();
        /** @var UserGroupChangeService $userGroupChangeService */
        $userGroupChangeService = \XF::app()->service('XF:User\UserGroupChange');
        $rejectGroup = (int)($options->sv_addRejectUserGroup ?? 0);
        $disableGroup = (int)($options->sv_addDisableUserGroup ?? 0);

        $userId = $user->user_id;
        if ($user->user_state === 'rejected' && $rejectGroup !== 0)
        {
            $userGroupChangeService->addUserGroupChange($userId, 'svRejectedUserGroup', $rejectGroup);
        }
        else
        {
            $userGroupChangeService->removeUserGroupChange($userId, 'svRejectedUserGroup');
        }

        if ($user->user_state === 'disabled' && $disableGroup !== 0)
        {
            $userGroupChangeService->addUserGroupChange($userId, 'svDisabledUserGroup', $disableGroup);
        }
        else
        {
            $userGroupChangeService->removeUserGroupChange($userId, 'svDisabledUserGroup');
        }
    }
}