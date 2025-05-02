<?php

namespace Database\Seeders;

use App\Models\Correction;
use App\Models\Keyword;
use App\Services\OpenAIService;
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
                'keywords'         => ['internship', 'months'],
                'answer_text'      => 'House internships are limited to 4 months; Senate up to 5.',
                'priority'         => 10,
                'active'           => true,
            ],
        ];

        foreach ($rows as $data) {
            $corr = Correction::create([
                'question_pattern' => $data['question_pattern'],
                'answer_text'      => $data['answer_text'],
                'priority'         => $data['priority'],
                'active'           => $data['active'],
            ]);

            // sync keywords pivot
            $keywordIds = collect($data['keywords'])
                ->map(fn(string $kw) => Keyword::firstOrCreate(['name' => $kw])->id)
                ->all();
            $corr->keywords()->sync($keywordIds);

            // generate & persist the example_embedding
            $corr->example_embedding = $openAI->getEmbeddingVector($corr->question_pattern);
            $corr->save();
        }
    }
}
