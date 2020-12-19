<?php


namespace AliSyria\LDOG\OrganizationManager;


use Illuminate\Support\Collection;

class Branch extends Sector
{
    const LDOG_CLASS='Branch';
    const LDOG_PARENT_PROPERTY='isBranchOf';
}