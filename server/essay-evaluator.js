/**
 * ============================================================================
 * EXAMFORT SMART MULTI-SIGNAL DESCRIPTIVE & ESSAY EVALUATION ENGINE
 * ============================================================================
 * 
 * 1. Multi-Signal Scoring:
 *    - Concept Coverage (40%)
 *    - Explanation Depth & Cause-Effect Relationships (30%)
 *    - Coherence & Paragraph Structure (15%)
 *    - Real-world Examples / Case Studies (15%)
 * 
 * 2. Anti-Keyword Stuffing Detection:
 *    - Detects comma-separated or list-dumped keywords lacking explanation
 *    - Penalizes raw keyword dropping with strict partial credit (max 30-40%)
 *    - Rewards concise, deep, cause-effect sentences without length bias
 * 
 * 3. Soft Word Constraints:
 *    - Conciseness is respected (no arbitrary length penalties for good answers)
 *    - Evidence quotes extracted for audit and instructor review
 * ============================================================================
 */

class DescriptiveAnswerEvaluator {
    constructor() {
        // Default Rubrics for Common Technical Descriptive Assessments
        this.defaultRubrics = {
            'system_architecture': {
                max_marks: 20,
                min_words_soft_limit: 40,
                concepts: [
                    {
                        id: 'c1_telemetry',
                        name: 'Real-Time Telemetry & Transport (WebSockets / gRPC)',
                        weight: 4.5,
                        keywords: ['websocket', 'websockets', 'grpc', 'webrtc', 'bidirectional', 'streaming', 'heartbeat', 'tcp'],
                        explanation_indicators: ['low latency', 'duplex', 'real-time connection', 'event-driven', 'instant delivery', 'multiplexing', 'sub-second', 'packet']
                    },
                    {
                        id: 'c2_caching',
                        name: 'In-Memory Distributed Caching (Redis / Memcached)',
                        weight: 4.5,
                        keywords: ['redis', 'memcached', 'cache', 'caching', 'in-memory', 'ttl', 'cache-aside', 'eviction'],
                        explanation_indicators: ['reduces database load', 'absorbs spikes', 'fast lookup', 'low latency', 'session state', 'rate limiting', 'key-value store', 'throughput']
                    },
                    {
                        id: 'c3_queue',
                        name: 'Decoupled Message Queues & Streaming (Kafka / RabbitMQ)',
                        weight: 4.5,
                        keywords: ['kafka', 'rabbitmq', 'message queue', 'pub/sub', 'producer', 'consumer', 'asynchronous', 'async queue', 'sqs'],
                        explanation_indicators: ['buffer spikes', 'decouple', 'asynchronous processing', 'backpressure', 'event log', 'worker pool', 'fault tolerance', 'stream processing']
                    },
                    {
                        id: 'c4_db_sharding',
                        name: 'Database Partitioning, Replication & Sharding',
                        weight: 4.5,
                        keywords: ['sharding', 'read replica', 'replication', 'partitioning', 'indexing', 'write-heavy', 'postgresql', 'mysql', 'mongodb'],
                        explanation_indicators: ['horizontal scaling', 'isolates writes', 'distributes load', 'query optimization', 'audit trail', 'consistency', 'failover', 'connection pooling']
                    }
                ],
                coherence_weight: 1.0,
                example_weight: 1.0
            }
        };
    }

    /**
     * Evaluates a student's descriptive / paragraph answer against a question rubric
     * @param {string} studentAnswer 
     * @param {object} questionData 
     * @returns {object} Evaluation report with score, breakdown, evidence quotes, and feedback
     */
    evaluate(studentAnswer, questionData = {}) {
        if (!studentAnswer || typeof studentAnswer !== 'string' || studentAnswer.trim().length === 0) {
            return {
                score: 0,
                maxMarks: questionData.max_marks || 20,
                percentage: 0,
                status: 'NOT_ATTEMPTED',
                wordCount: 0,
                feedback: 'No descriptive answer submitted.',
                conceptBreakdown: [],
                isListDumpDetected: false
            };
        }

        const maxMarks = parseFloat(questionData.max_marks || 20);
        const textRaw = studentAnswer.trim();
        const textLower = textRaw.toLowerCase();

        // Extract clean sentences and natural language words
        const sentences = textRaw.split(/[.!?\n\r]+/).map(s => s.trim()).filter(s => s.length > 0);
        const words = textRaw.match(/[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu) || [];
        const wordCount = words.length;

        // 1. Detect Keyword-Stuffing / Raw List Dumps
        const isListDump = this.detectListDumping(sentences, textRaw);

        // Fetch matching rubric dynamically from MySQL questionData.rubric_json
        let rubric = null;
        if (questionData.rubric_json) {
            try {
                rubric = typeof questionData.rubric_json === 'string'
                    ? JSON.parse(questionData.rubric_json)
                    : questionData.rubric_json;
            } catch (_) {}
        }
        if (!rubric) {
            rubric = questionData.rubric || this.defaultRubrics['system_architecture'];
        }

        let totalScore = 0;
        const conceptBreakdown = [];

        // 2. Evaluate Each Core Concept with Explanation Depth & Evidence Extraction
        if (rubric && Array.isArray(rubric.concepts)) {
            rubric.concepts.forEach((concept) => {
                let conceptMentioned = false;
                let explanationPresent = false;
                let matchedKeyword = '';
                let evidenceSentence = '';

                // Find if any concept keyword is present
                for (const kw of concept.keywords) {
                    const regex = new RegExp(`\\b${this.escapeRegex(kw)}\\b`, 'i');
                    if (regex.test(textLower)) {
                        conceptMentioned = true;
                        matchedKeyword = kw;
                        break;
                    }
                }

                if (conceptMentioned) {
                    // Check sentences containing the keyword for causal depth and indicators
                    for (const sent of sentences) {
                        const sLower = sent.toLowerCase();
                        if (sLower.includes(matchedKeyword.toLowerCase())) {
                            const commasInSent = (sent.match(/,/g) || []).length;
                            const isSentenceListDump = commasInSent >= 3 && sent.split(/\s+/).length < 20;

                            const hasIndicator = concept.explanation_indicators.some(ind => sLower.includes(ind.toLowerCase()));
                            const hasCausalConjunction = /\b(because|in order to|so that|which allows|which reduces|leads to|results in|ensures|facilitates|designed to|used for|prevents)\b/i.test(sent);
                            
                            // A sentence with at least 6 words, no list-dumping, and causal/indicator phrasing represents real understanding
                            if (!isListDump && !isSentenceListDump && (hasIndicator || hasCausalConjunction) && sent.split(/\s+/).length >= 6) {
                                explanationPresent = true;
                                evidenceSentence = sent;
                                break;
                            }
                        }
                    }
                }

                // Marks Calculation per Concept:
                let awardedMarks = 0;
                let status = 'MISSING';

                if (conceptMentioned && explanationPresent) {
                    awardedMarks = concept.weight; // Full marks: Concept present with causal depth
                    status = 'DEEP_EXPLANATION';
                } else if (conceptMentioned && !explanationPresent) {
                    // Mention only without explanation -> Strict partial credit
                    awardedMarks = isListDump ? (concept.weight * 0.2) : (concept.weight * 0.4);
                    status = isListDump ? 'KEYWORD_DUMP_PENALTY' : 'MENTION_ONLY';
                }

                totalScore += awardedMarks;

                conceptBreakdown.push({
                    conceptId: concept.id,
                    conceptName: concept.name,
                    awardedMarks: Math.round(awardedMarks * 10) / 10,
                    maxMarks: concept.weight,
                    status,
                    matchedKeyword: matchedKeyword || null,
                    evidenceQuote: evidenceSentence || null
                });
            });
        }

        // 3. Coherence & Structure Score (15%)
        const coherenceScore = this.evaluateCoherence(sentences, wordCount, isListDump);
        totalScore += coherenceScore;

        // 4. Practical Application & Architecture Examples (15%)
        const exampleScore = this.evaluateExamples(textLower);
        totalScore += exampleScore;

        // 5. Soft Brevity Constraint
        if (wordCount < 15 && totalScore > 4) {
            totalScore = totalScore * 0.4; // Extremely sparse answer penalty
        }

        const finalScore = Math.min(maxMarks, Math.max(0, Math.round(totalScore * 10) / 10));
        const percentage = Math.round((finalScore / maxMarks) * 100);

        // Construct Feedback Summary
        let feedback = '';
        if (percentage >= 85) {
            feedback = '🌟 Outstanding response! Demonstrates deep architectural reasoning with clear cause-and-effect clarity.';
        } else if (percentage >= 65) {
            feedback = '👍 Solid technical understanding. Core concepts addressed with good explanation.';
        } else if (percentage >= 40) {
            feedback = '⚠️ Moderate answer. Several core concepts mentioned, but deeper explanation of mechanisms/trade-offs recommended.';
        } else {
            feedback = '❌ Basic or incomplete answer. Add more explanation on how components interact rather than just listing terms.';
        }

        if (isListDump) {
            feedback += ' (Note: Raw keyword list-dumping detected and penalized; provide full explanatory sentences).';
        }

        return {
            score: finalScore,
            maxMarks,
            percentage,
            status: finalScore > 0 ? 'EVALUATED' : 'ZERO_SCORE',
            wordCount,
            isListDumpDetected: isListDump,
            feedback,
            coherenceScore: Math.round(coherenceScore * 10) / 10,
            exampleScore: Math.round(exampleScore * 10) / 10,
            conceptBreakdown
        };
    }

    /**
     * Detects if text contains list dumping (e.g. comma-separated keyword list without verbs)
     */
    detectListDumping(sentences, textRaw) {
        if (!sentences || sentences.length === 0) return false;
        let commaHeavyCount = 0;
        sentences.forEach(s => {
            const commas = (s.match(/,/g) || []).length;
            const words = s.split(/\s+/).filter(Boolean).length;
            if (commas >= 4 && words < 18) {
                commaHeavyCount++;
            }
        });

        // Also check if text has very high density of commas relative to total length
        const totalCommas = (textRaw.match(/,/g) || []).length;
        const totalWords = textRaw.split(/\s+/).filter(Boolean).length;
        if (totalWords > 0 && (totalCommas / totalWords) > 0.35) {
            return true;
        }

        return commaHeavyCount > 0;
    }

    /**
     * Evaluates structural coherence and flow
     */
    evaluateCoherence(sentences, wordCount, isListDump) {
        if (isListDump) return 0.2;
        if (sentences.length >= 3 && wordCount >= 45) return 1.0;
        if (sentences.length >= 2 && wordCount >= 25) return 0.7;
        if (wordCount >= 10) return 0.4;
        return 0.2;
    }

    /**
     * Evaluates whether practical examples or architectural case patterns are referenced
     */
    evaluateExamples(textLower) {
        const hasExampleIndicator = /\b(for example|e\.g\.|such as|in practice|case study|scenario|production|amazon|netflix|uber|real-world|use-case|demonstrated by)\b/i.test(textLower);
        return hasExampleIndicator ? 1.0 : 0.4;
    }

    escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
}

module.exports = new DescriptiveAnswerEvaluator();
