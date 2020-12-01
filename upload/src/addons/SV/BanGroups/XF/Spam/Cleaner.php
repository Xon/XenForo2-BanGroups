<?php

namespace SV\BanGroups\XF\Spam;

use SV\BanGroups\Globals;

class Cleaner extends XFCP_Cleaner
{
    public function banUser()
    {
        Globals::$isSpamCleaningBan = true;
        try
        {
            parent::banUser();
        }
        finally
        {
            Globals::$isSpamCleaningBan = false;
        }
    }
}
