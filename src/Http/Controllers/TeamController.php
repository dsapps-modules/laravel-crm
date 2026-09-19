<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Team;
use DsApps\LaravelCrm\Services\AssignTeamMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamController extends Controller
{
    public function index(): mixed { return Team::with('members')->where('active', true)->orderBy('name')->paginate(100); }
    public function store(Request $request): Team { return Team::create($request->validate(['name' => ['required', 'string', 'max:160'], 'active' => ['sometimes', 'boolean']])); }
    public function show(Team $team): Team { return $team->load('members'); }
    public function assign(Request $request, Team $team, AssignTeamMember $assign): mixed
    {
        $data = $request->validate(['user_id' => ['required', 'integer', 'min:1'], 'eligible_for_round_robin' => ['sometimes', 'boolean']]);
        return $assign->execute($team->id, $data['user_id'], $data['eligible_for_round_robin'] ?? true);
    }
}
