<?php

namespace App\Repositories;

use App\Models\Project;

class ProjectRepositories
{
    public function list($filter = [])
    {
        $project = Project::query();

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $project->where(function ($query) use ($search) {
                $query->where('client_name', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filter['status'])) {
            $project->where('status', $filter['status']);
        }

        if (!empty($filter['priority'])) {
            $project->where('priority', $filter['priority']);
        }

        $sortBy = $filter['sort_by'] ?? 'created_at';
        $sortDir = $filter['sort_dir'] ?? 'desc';

        // Sort status/priority by their logical order instead of alphabetically
        if ($sortBy === 'status' || $sortBy === 'priority') {
            $values = $sortBy === 'status' ? Project::STATUSES : Project::PRIORITIES;
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $project->orderByRaw("FIELD({$sortBy}, {$placeholders}) {$sortDir}", $values);
        } else {
            $project->orderBy($sortBy, $sortDir);
        }

        return $project->orderBy('id', 'desc')->paginate($filter['per_page'] ?? 15);
    }

    public function searchByPid($pid)
    {
        try {
            return Project::where('pid', $pid)->first();
        } catch (\Exception $e) {
            throw new \Exception("An error has occured! " . $e->getMessage());
        }
    }

    public function create($data)
    {
        try {
            return Project::create($data);
        } catch (\Exception $e) {
            throw new \Exception("An error has occured! " . $e->getMessage());
        }
    }

    public function update($project, $data)
    {
        try {
            $project->update($data);

            return $project;
        } catch (\Exception $e) {
            throw new \Exception("An error has occured! " . $e->getMessage());
        }
    }

    public function delete($project)
    {
        try {
            $project->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception("An error has occured! " . $e->getMessage());
        }
    }
}
