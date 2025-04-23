<?php

namespace App\Services;

use App\Models\FaqCluster;
use App\Models\Message;
use App\Models\QuestionEmbedding;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\DB;

class FaqService
{
    protected OpenAIService $openAI;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

    /**
     * Re-cluster all user-question embeddings, then
     * craft a single “FAQ” question via the LLM for each cluster.
     */
    public function recluster(float $similarityThreshold = 0.85): void
    {
        // 1) load embeddings + texts
        $items = QuestionEmbedding::with('message')
            ->get()
            ->map(fn($qe) => [
                'id'     => $qe->message_id,
                'vector' => $qe->embedding,
                'text'   => $qe->message->content,
            ]);

        $clusters = [];
        $assigned = [];

        // 2) greedy single-pass clustering
        foreach ($items as $item) {
            if (in_array($item['id'], $assigned, true)) {
                continue;
            }

            $clusters[] = [
                'members'  => [$item],
                'centroid' => $item['vector'],
            ];
            $clusterKey = array_key_last($clusters);
            $assigned[] = $item['id'];

            foreach ($items as $other) {
                if (in_array($other['id'], $assigned, true)) {
                    continue;
                }
                $sim = $this->cosineSimilarity(
                    $clusters[$clusterKey]['centroid'],
                    $other['vector']
                );
                if ($sim >= $similarityThreshold) {
                    $clusters[$clusterKey]['members'][] = $other;
                    $assigned[] = $other['id'];

                    // update centroid
                    $clusters[$clusterKey]['centroid'] = $this->averageVectors(
                        $clusters[$clusterKey]['centroid'],
                        $other['vector'],
                        count($clusters[$clusterKey]['members'])
                    );
                }
            }
        }

        // 3) truncate old clusters
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('faq_cluster_message')->truncate();
        FaqCluster::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4) craft & persist each cluster
        foreach ($clusters as $cluster) {
            $members = $cluster['members'];

            // skip singletons if desired
            if (count($members) < 2) {
                continue;
            }

            // pull up to 8 sample questions
            $sampleTexts = collect($members)
                ->pluck('text')
                ->shuffle()
                ->take(8)
                ->all();

            // ask the LLM for one representative FAQ question
            $repQuestion = $this->craftRepresentativeQuestion($sampleTexts);

            // persist
            $faq = FaqCluster::create([
                'representative_text' => $repQuestion,
                'frequency'           => count($members),
            ]);

            // pivot links
            foreach ($members as $m) {
                DB::table('faq_cluster_message')->insert([
                    'cluster_id' => $faq->id,
                    'message_id' => $m['id'],
                ]);
            }
        }
    }

    /**
     * Given up to ~8 user-submitted questions, ask the LLM to
     * write one clear, concise FAQ‐style question that captures them.
     */
    protected function craftRepresentativeQuestion(array $sampleQuestions): string
    {
        $bulletList = implode("\n", array_map(
            fn(string $q) => "- “{$q}”",
            $sampleQuestions
        ));

        $prompt = <<<PROMPT
We have grouped these user‐submitted questions because they all ask about the same topic:
{$bulletList}

Please write **one** clear, concise FAQ‐style question that covers the overall topic of all these submissions.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant that writes FAQ questions.'],
            ['role' => 'user',   'content' => $prompt],
        ];

        return trim($this->openAI->getChatResponse($messages));
    }

    protected function dot(array $a, array $b): float
    {
        return array_sum(array_map(fn($x, $y) => $x * $y, $a, $b));
    }

    protected function magnitude(array $v): float
    {
        return sqrt(array_sum(array_map(fn($x) => $x * $x, $v)));
    }

    public function cosineSimilarity(array $a, array $b): float
    {
        return $this->dot($a, $b) / ($this->magnitude($a) * $this->magnitude($b)) ?: 0;
    }

    protected function averageVectors(array $current, array $next, int $n): array
    {
        return array_map(
            fn($old, $new) => (($old * ($n - 1)) + $new) / $n,
            $current,
            $next
        );
    }
}
