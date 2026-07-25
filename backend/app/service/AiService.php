<?php

declare(strict_types=1);

namespace app\service;

use app\model\AiConfig;
use app\model\Prediction;
use GuzzleHttp\Client;
use think\facade\Log;

/**
 * AI服务类
 * 支持多AI服务商（OpenAI、DeepSeek、百度等），默认使用mock模式
 */
class AiService
{
    protected $config;
    protected $provider;
    protected $client;

    /**
     * 构造函数
     * @param string|null $provider 指定服务商，为空则使用默认配置
     */
    public function __construct(?string $provider = null)
    {
        if ($provider) {
            $this->config = AiConfig::getConfigByProvider($provider);
            $this->provider = $provider;
        } else {
            $this->config = AiConfig::getDefaultConfig();
            $this->provider = $this->config ? $this->config->provider : 'mock';
        }

        if ($this->config && $this->provider !== 'mock') {
            $this->client = new Client([
                'timeout' => 30,
                'verify' => false,
            ]);
        }
    }

    /**
     * 获取AI配置列表（用户端，不返回密钥）
     * @return array
     */
    public function getConfigList(): array
    {
        $list = AiConfig::getActiveList();
        return $list->toArray();
    }

    /**
     * AI生成试卷
     * @param string $subject 科目
     * @param string $difficulty 难度：easy/medium/hard
     * @param int $questionCount 题目数量
     * @param array $options 其他选项
     * @return array
     */
    public function generatePaper(string $subject, string $difficulty = 'medium', int $questionCount = 10, array $options = []): array
    {
        if ($this->provider === 'mock' || !$this->config) {
            return $this->mockGeneratePaper($subject, $difficulty, $questionCount, $options);
        }

        try {
            switch ($this->provider) {
                case 'openai':
                    return $this->callOpenAIGeneratePaper($subject, $difficulty, $questionCount, $options);
                case 'deepseek':
                    return $this->callDeepSeekGeneratePaper($subject, $difficulty, $questionCount, $options);
                case 'baidu':
                    return $this->callBaiduGeneratePaper($subject, $difficulty, $questionCount, $options);
                default:
                    return $this->mockGeneratePaper($subject, $difficulty, $questionCount, $options);
            }
        } catch (\Exception $e) {
            Log::error('AI生成试卷失败: ' . $e->getMessage());
            return $this->mockGeneratePaper($subject, $difficulty, $questionCount, $options);
        }
    }

    /**
     * AI成绩预测
     * @param int $userId 用户ID
     * @param string $subject 科目
     * @param array $historyScores 历史成绩记录
     * @return array
     */
    public function predictScore(int $userId, string $subject, array $historyScores = []): array
    {
        if ($this->provider === 'mock' || !$this->config) {
            $result = $this->mockPredictScore($subject, $historyScores);
        } else {
            try {
                switch ($this->provider) {
                    case 'openai':
                        $result = $this->callOpenAIPredictScore($subject, $historyScores);
                        break;
                    case 'deepseek':
                        $result = $this->callDeepSeekPredictScore($subject, $historyScores);
                        break;
                    case 'baidu':
                        $result = $this->callBaiduPredictScore($subject, $historyScores);
                        break;
                    default:
                        $result = $this->mockPredictScore($subject, $historyScores);
                }
            } catch (\Exception $e) {
                Log::error('AI成绩预测失败: ' . $e->getMessage());
                $result = $this->mockPredictScore($subject, $historyScores);
            }
        }

        $result['provider'] = $this->provider;
        $result['subject'] = $subject;

        Prediction::createRecord($userId, $result);

        return $result;
    }

    /**
     * 获取预测记录列表
     * @param int $userId 用户ID
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param string|null $subject 科目筛选
     * @return array
     */
    public function getPredictionList(int $userId, int $page = 1, int $limit = 10, ?string $subject = null): array
    {
        return Prediction::getUserList($userId, $page, $limit, $subject);
    }

    /**
     * Mock生成试卷
     */
    protected function mockGeneratePaper(string $subject, string $difficulty, int $questionCount, array $options): array
    {
        $questions = [];
        $questionTypes = ['single_choice', 'multiple_choice', 'true_false', 'fill_blank', 'short_answer'];

        for ($i = 1; $i <= $questionCount; $i++) {
            $type = $questionTypes[array_rand($questionTypes)];
            $question = [
                'id' => $i,
                'type' => $type,
                'subject' => $subject,
                'difficulty' => $difficulty,
                'score' => $this->getScoreByType($type),
                'content' => $this->getMockQuestionContent($subject, $i, $type),
                'options' => $this->getMockOptions($type),
                'answer' => $this->getMockAnswer($type),
                'analysis' => "本题考查{$subject}相关知识点，难度{$difficulty}。需要掌握基本概念和解题方法。",
            ];
            $questions[] = $question;
        }

        $totalScore = array_sum(array_column($questions, 'score'));

        return [
            'subject' => $subject,
            'difficulty' => $difficulty,
            'question_count' => $questionCount,
            'total_score' => $totalScore,
            'duration' => $questionCount * 3,
            'provider' => 'mock',
            'questions' => $questions,
        ];
    }

    /**
     * Mock成绩预测
     */
    protected function mockPredictScore(string $subject, array $historyScores): array
    {
        $predictedScore = 75;
        $confidence = 0.75;
        $suggestions = [];

        if (!empty($historyScores)) {
            $scores = array_column($historyScores, 'score');
            $avgScore = array_sum($scores) / count($scores);
            $trend = 0;

            if (count($scores) >= 2) {
                $firstHalf = array_slice($scores, 0, floor(count($scores) / 2));
                $secondHalf = array_slice($scores, floor(count($scores) / 2));
                $firstAvg = array_sum($firstHalf) / count($firstHalf);
                $secondAvg = array_sum($secondHalf) / count($secondHalf);
                $trend = $secondAvg - $firstAvg;
            }

            $predictedScore = round($avgScore + $trend * 0.5, 1);
            $predictedScore = max(0, min(100, $predictedScore));
            $confidence = count($scores) >= 5 ? 0.85 : 0.65;
        }

        if ($predictedScore < 60) {
            $suggestions[] = "加强{$subject}基础知识的学习，建议从教材基础章节开始系统复习";
            $suggestions[] = "每天安排30分钟专项基础训练，重点攻克薄弱知识点";
            $suggestions[] = "建议寻求老师或同学帮助，及时解决学习中遇到的问题";
        } elseif ($predictedScore < 75) {
            $suggestions[] = "巩固基础的同时，增加中等难度题目的练习";
            $suggestions[] = "建立错题本，定期复习做错的题目";
            $suggestions[] = "建议每周做一套模拟试卷，熟悉考试题型和节奏";
        } elseif ($predictedScore < 90) {
            $suggestions[] = "保持现有学习节奏，挑战更高难度的题目";
            $suggestions[] = "注重知识体系的构建，形成完整的知识网络";
            $suggestions[] = "适当进行拓展学习，培养举一反三的能力";
        } else {
            $suggestions[] = "成绩优秀，继续保持良好的学习习惯";
            $suggestions[] = "可以尝试竞赛类题目，进一步提升能力";
            $suggestions[] = "建议帮助其他同学，教学相长";
        }

        return [
            'predicted_score' => round($predictedScore, 1),
            'confidence' => round($confidence, 2),
            'suggestion' => implode("；", $suggestions),
            'suggestions' => $suggestions,
            'prediction_data' => [
                'history_count' => count($historyScores),
                'history_avg' => !empty($historyScores) ? round(array_sum(array_column($historyScores, 'score')) / count($historyScores), 1) : null,
                'subject' => $subject,
            ],
        ];
    }

    /**
     * 调用OpenAI生成试卷
     */
    protected function callOpenAIGeneratePaper(string $subject, string $difficulty, int $questionCount, array $options): array
    {
        $prompt = "请生成一份{$subject}科目试卷，难度：{$difficulty}，共{$questionCount}道题。"
            . "包含选择题、判断题、填空题等题型，并提供答案和解析。"
            . "请以JSON格式返回，包含questions数组，每个元素有type、content、options、answer、analysis、score字段。";

        $response = $this->client->post($this->config->api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->config->model ?: 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['choices'][0]['message']['content'] ?? '';
        $result = json_decode($content, true) ?: $this->mockGeneratePaper($subject, $difficulty, $questionCount, $options);
        $result['provider'] = 'openai';

        return $result;
    }

    /**
     * 调用OpenAI成绩预测
     */
    protected function callOpenAIPredictScore(string $subject, array $historyScores): array
    {
        $prompt = "根据以下历史考试成绩，预测下次{$subject}考试分数：" . json_encode($historyScores)
            . "请以JSON格式返回，包含predicted_score（预测分数）、confidence（置信度0-1）、suggestion（学习建议）字段。";

        $response = $this->client->post($this->config->api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->config->model ?: 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['choices'][0]['message']['content'] ?? '';
        $result = json_decode($content, true);

        if (!$result || !isset($result['predicted_score'])) {
            return $this->mockPredictScore($subject, $historyScores);
        }

        $result['prediction_data'] = [
            'history_count' => count($historyScores),
            'subject' => $subject,
        ];

        return $result;
    }

    /**
     * 调用DeepSeek生成试卷
     */
    protected function callDeepSeekGeneratePaper(string $subject, string $difficulty, int $questionCount, array $options): array
    {
        $prompt = "请生成一份{$subject}科目试卷，难度：{$difficulty}，共{$questionCount}道题。"
            . "包含选择题、判断题、填空题等题型，并提供答案和解析。"
            . "请以JSON格式返回，包含questions数组，每个元素有type、content、options、answer、analysis、score字段。";

        $response = $this->client->post($this->config->api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->config->model ?: 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['choices'][0]['message']['content'] ?? '';
        $result = json_decode($content, true) ?: $this->mockGeneratePaper($subject, $difficulty, $questionCount, $options);
        $result['provider'] = 'deepseek';

        return $result;
    }

    /**
     * 调用DeepSeek成绩预测
     */
    protected function callDeepSeekPredictScore(string $subject, array $historyScores): array
    {
        $prompt = "根据以下历史考试成绩，预测下次{$subject}考试分数：" . json_encode($historyScores)
            . "请以JSON格式返回，包含predicted_score（预测分数）、confidence（置信度0-1）、suggestion（学习建议）字段。";

        $response = $this->client->post($this->config->api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->config->model ?: 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['choices'][0]['message']['content'] ?? '';
        $result = json_decode($content, true);

        if (!$result || !isset($result['predicted_score'])) {
            return $this->mockPredictScore($subject, $historyScores);
        }

        $result['prediction_data'] = [
            'history_count' => count($historyScores),
            'subject' => $subject,
        ];

        return $result;
    }

    /**
     * 调用百度生成试卷
     */
    protected function callBaiduGeneratePaper(string $subject, string $difficulty, int $questionCount, array $options): array
    {
        $prompt = "请生成一份{$subject}科目试卷，难度：{$difficulty}，共{$questionCount}道题。"
            . "包含选择题、判断题、填空题等题型，并提供答案和解析。"
            . "请以JSON格式返回，包含questions数组，每个元素有type、content、options、answer、analysis、score字段。";

        $response = $this->client->post($this->config->api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['result'] ?? '';
        $result = json_decode($content, true) ?: $this->mockGeneratePaper($subject, $difficulty, $questionCount, $options);
        $result['provider'] = 'baidu';

        return $result;
    }

    /**
     * 调用百度成绩预测
     */
    protected function callBaiduPredictScore(string $subject, array $historyScores): array
    {
        $prompt = "根据以下历史考试成绩，预测下次{$subject}考试分数：" . json_encode($historyScores)
            . "请以JSON格式返回，包含predicted_score（预测分数）、confidence（置信度0-1）、suggestion（学习建议）字段。";

        $response = $this->client->post($this->config->api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['result'] ?? '';
        $result = json_decode($content, true);

        if (!$result || !isset($result['predicted_score'])) {
            return $this->mockPredictScore($subject, $historyScores);
        }

        $result['prediction_data'] = [
            'history_count' => count($historyScores),
            'subject' => $subject,
        ];

        return $result;
    }

    /**
     * 根据题型获取分值
     */
    protected function getScoreByType(string $type): int
    {
        $scores = [
            'single_choice' => 5,
            'multiple_choice' => 6,
            'true_false' => 3,
            'fill_blank' => 5,
            'short_answer' => 10,
        ];
        return $scores[$type] ?? 5;
    }

    /**
     * 获取Mock题目内容
     */
    protected function getMockQuestionContent(string $subject, int $index, string $type): string
    {
        $contents = [
            '数学' => [
                "已知函数f(x)=x²+2x+1，求f(3)的值",
                "解方程：2x+5=15",
                "计算：sin²30°+cos²30°",
                "等差数列{aₙ}中，a₁=2，d=3，求a₁₀",
                "已知向量a=(1,2)，b=(3,4)，求a·b",
            ],
            '语文' => [
                "下列词语中，加点字读音全部正确的一项是",
                "下列句子中，没有语病的一项是",
                "默写《静夜思》全诗",
                "解释下列文言文中加点词的意思",
                "下列关于文学常识的表述，正确的一项是",
            ],
            '英语' => [
                "Choose the correct answer: He ___ to school every day.",
                "Fill in the blank: I have ___ apple.",
                "Translate: 我喜欢读书。",
                "Which sentence is correct?",
                "What is the past tense of 'go'?",
            ],
            '物理' => [
                "一物体从静止开始做匀加速直线运动，加速度为2m/s²，求5s末的速度",
                "下列说法正确的是",
                "计算：质量为2kg的物体受到10N的力，求加速度",
                "关于牛顿第一定律，下列说法正确的是",
                "电路中电流为2A，电阻为5Ω，求电压",
            ],
            '化学' => [
                "下列物质中，属于纯净物的是",
                "写出下列反应的化学方程式",
                "下列实验操作正确的是",
                "原子的结构包括哪些部分",
                "下列变化中，属于化学变化的是",
            ],
        ];

        $subjectContents = $contents[$subject] ?? $contents['数学'];
        return $subjectContents[$index % count($subjectContents)];
    }

    /**
     * 获取Mock选项
     */
    protected function getMockOptions(string $type): array
    {
        if ($type === 'single_choice' || $type === 'multiple_choice') {
            return [
                'A' => '选项A的内容',
                'B' => '选项B的内容',
                'C' => '选项C的内容',
                'D' => '选项D的内容',
            ];
        }
        return [];
    }

    /**
     * 获取Mock答案
     */
    protected function getMockAnswer(string $type): string
    {
        if ($type === 'single_choice') {
            return 'A';
        }
        if ($type === 'multiple_choice') {
            return 'AB';
        }
        if ($type === 'true_false') {
            return '正确';
        }
        if ($type === 'fill_blank') {
            return '答案内容';
        }
        return '参考答案要点';
    }
}
