<?php

namespace App\Console\Commands;

use App\Models\Championship;
use App\Modules\Championship\ChampionshipAnalyticService;
use App\Modules\Championship\PlayoffGeneratorService;
use Illuminate\Console\Command;

class GeneratePlayoffs extends Command
{
    protected $signature = 'playoffs:generate {championship_id}';
    protected $description = 'Generate playoffs for a championship';

    public function handle()
    {
        $championshipId = $this->argument('championship_id');
        
        $championship = Championship::find($championshipId);
        
        if (!$championship) {
            $this->error("Championship {$championshipId} not found");
            return 1;
        }
        
        $this->info("Generating playoffs for {$championship->name}...");
        
        $analyticService = new ChampionshipAnalyticService($championship);
        $playoffService = new PlayoffGeneratorService($analyticService);
        
        try {
            $playoffService->generatePlayoffs($championshipId);
            $this->info("Playoffs generated successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}

