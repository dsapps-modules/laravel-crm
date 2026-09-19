<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\TeamMember;

final class AssignTeamMember
{
    public function execute(int $teamId, int $userId, bool $eligible = true): TeamMember
    {
        return TeamMember::updateOrCreate(['team_id' => $teamId, 'user_id' => $userId], ['eligible_for_round_robin' => $eligible]);
    }
}
