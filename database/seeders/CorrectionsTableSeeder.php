<?php

namespace Database\Seeders;

use App\Models\Correction;
use App\Services\OpenAIService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CorrectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(OpenAIService $openAI)
    {
        $rows = [
            [
                'question_pattern' => 'internship length',
                'keywords'         => 'internship, months',
                'answer_text'      => 'House internships are limited to 4 months; Senate up to 5.',
                'priority'         => 10,
                'active'           => true,
            ],
        ];

        foreach ($rows as $data) {
            /** @var Correction $corr */
            $corr = Correction::create($data);

            // generate & persist the example_embedding
            $corr->example_embedding = $openAI->getEmbeddingVector($corr->question_pattern);
            $corr->save();
        }
    }
}
