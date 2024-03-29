<?php

namespace Database\Seeders;

use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Workflow::create(['workflow' => json_encode(['to do', 'testing', 'complete'])]);
    }
}
