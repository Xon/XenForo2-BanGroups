<?php

namespace SV\BanGroups\XF\Job;

/**
 * Extends \XF\Job\User
 */
class User extends XFCP_User
{
    protected function rebuildById($id)
    {
        $ret = parent::rebuildById($id);

        /** @var \XF\Entity\User $user */
        $userId = (int)$id;
        $user = $this->app->em()->findCached('XF:User', $userId);
        if ($user)
        {
            $options = \XF::options();
            /** @var \XF\Service\User\UserGroupChange $userGroupChangeService */
            $userGroupChangeService = \XF::app()->service('XF:User\UserGroupChange');
            $rejectGroup = isset($options->sv_addRejectUserGroup) ? (int)$options->sv_addRejectUserGroup : 0;
            $disableGroup = isset($options->sv_addDisableUserGroup) ? (int)$options->sv_addDisableUserGroup : 0;

            $userId = $user->user_id;
            if ($user->user_state === 'rejected' && $rejectGroup)
            {
                $userGroupChangeService->addUserGroupChange($userId, 'svRejectedUserGroup', $rejectGroup);
            }
            else
            {
                $userGroupChangeService->removeUserGroupChange($userId, 'svRejectedUserGroup');
            }

            if ($user->user_state === 'disabled' && $disableGroup)
            {
                $userGroupChangeService->addUserGroupChange($userId, 'svDisabledUserGroup', $disableGroup);
            }
            else
            {
                $userGroupChangeService->removeUserGroupChange($userId, 'svDisabledUserGroup');
            }
        }

        return $ret;
    }
}