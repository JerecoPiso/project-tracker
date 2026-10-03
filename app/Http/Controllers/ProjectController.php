<?php

namespace App\Http\Controllers;

use App\Traits\ProjectTrait;
use App\Repositories\ProjectRepositories;

class ProjectController extends Controller
{
    use ProjectTrait;

    public $projectRepo;

    public function __construct(ProjectRepositories $projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }
}
