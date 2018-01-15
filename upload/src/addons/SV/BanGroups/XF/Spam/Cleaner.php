<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\BanGroups\XF\Spam;

use SV\BanGroups\Globals;

class Cleaner extends XFCP_Cleaner
{
    public function banUser()
    {
        Globals::$isSpamCleaningBan = true;
        parent::banUser();
    }
}
