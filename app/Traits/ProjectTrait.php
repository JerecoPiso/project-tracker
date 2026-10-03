<?php

namespace App\Traits;

use App\Http\Requests\ProjectListRequest;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;

trait ProjectTrait
{
    public function list(ProjectListRequest $request)
    {
        try {
            $projects = $this->projectRepo->list($request->validated());
            return api_response($projects, true, "Success", 200);
        } catch (\Exception $e) {
            return api_response([], false, $e->getMessage(), 500);
        }
    }

    public function options()
    {
        return api_response([
            "statuses" => Project::STATUSES,
            "priorities" => Project::PRIORITIES,
        ], true, "Success", 200);
    }

    public function view($pid)
    {
        try {
            $project = $this->projectRepo->searchByPid($pid);
            if (!$project) {
                return api_response([], false, "Project not found", 404);
            }
            return api_response($project, true, "Success", 200);
        } catch (\Exception $e) {
            return api_response([], false, $e->getMessage(), 500);
        }
    }

    public function create(ProjectRequest $request)
    {
        try {
            $project = $this->projectRepo->create($request->validated());
            return api_response($project, true, "Project created successfully", 201);
        } catch (\Exception $e) {
            return api_response([], false, $e->getMessage(), 500);
        }
    }

    public function update($pid, ProjectRequest $request)
    {
        try {
            $project = $this->projectRepo->searchByPid($pid);
            if (!$project) {
                return api_response([], false, "Project not found", 404);
            }
            $project = $this->projectRepo->update($project, $request->validated());
            return api_response($project, true, "Project updated successfully", 200);
        } catch (\Exception $e) {
            return api_response([], false, $e->getMessage(), 500);
        }
    }

    public function delete($pid)
    {
        try {
            $project = $this->projectRepo->searchByPid($pid);
            if (!$project) {
                return api_response([], false, "Project not found", 404);
            }
            $this->projectRepo->delete($project);
            return api_response([], true, "Project deleted successfully", 200);
        } catch (\Exception $e) {
            return api_response([], false, $e->getMessage(), 500);
        }
    }
}
