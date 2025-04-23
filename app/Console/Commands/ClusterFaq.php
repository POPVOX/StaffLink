<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FaqService;

class ClusterFaq extends Command
{
    protected $signature   = 'faq:cluster {--t=0.85 : similarity threshold}';
    protected $description = 'Rebuild FAQ clusters from stored embeddings';

    public function handle(FaqService $faq)
    {
        $t = (float) $this->option('t');
        $this->info("Clustering FAQ with threshold {$t}…");

        $faq->recluster($t);

        $this->info('Done.');
    }
}
