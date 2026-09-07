/**
 * ============================================================================
 * EXAMFORT UNIVERSAL & FULLY DYNAMIC CODE JUDGE ENGINE
 * ============================================================================
 * 
 * 100% Database-Driven: Works for ANY question added to database.
 * - Function name, parameters, public test cases, hidden test cases,
 *   and reference oracle are all loaded LIVE from database table `questions`.
 * - No hardcoded questions, no hardcoded arrays!
 * ============================================================================
 */

const vm = require('vm');

class UniversalCodeJudgeEngine {
    constructor() {
        this.DEFAULT_TIMEOUT_MS = 1500; // 1.5s execution timeout
    }

    /**
     * Universal Evaluation for ANY question definition retrieved from database
     * @param {object} questionRecord Row from database `questions` table
     * @param {string} candidateCode Candidate's source code
     * @param {string} language 'javascript' | 'python' | 'cpp'
     */
    evaluateSubmission(questionRecord, candidateCode, language = 'javascript') {
        if (!candidateCode || candidateCode.trim().length === 0) {
            return {
                success: true,
                score: 0,
                maxScore: parseFloat(questionRecord.max_marks || 50),
                verdict: 'NO_CODE_PROVIDED',
                summary: 'No solution code was provided.',
                sets: {}
            };
        }

        const maxMarks = parseFloat(questionRecord.max_marks || 50.0);
        const entryFunctionName = questionRecord.entry_function || this.detectFunctionName(questionRecord.coding_starter_code) || 'solve';

        const publicWeight = parseFloat(questionRecord.public_weightage_marks || 10.0);
        const hiddenWeight = parseFloat(questionRecord.hidden_weightage_marks || 40.0);

        // Parse test cases from database
        const publicCases = this.parseJsonField(questionRecord.public_test_cases, []);
        const hiddenCases = this.parseJsonField(questionRecord.hidden_test_cases, []);
        const randomConfig = this.parseJsonField(questionRecord.random_input_schema, null);
        const referenceSolution = questionRecord.reference_solution || null;

        // Anti-Hardcoding Heuristic Check
        const antiHardcode = this.checkAntiHardcoding(candidateCode);

        // Prepare Candidate Sandbox
        let candidateFn = null;
        try {
            const context = {
                require: (mod) => require(mod),
                console: { log: () => {}, error: () => {}, warn: () => {} },
                Buffer,
                process,
                Math,
                JSON,
                Map,
                Set,
                Array,
                Object,
                parseInt,
                parseFloat,
                isNaN,
                isFinite
            };
            vm.createContext(context);
            const script = new vm.Script(`
                ${candidateCode}
                if (typeof ${entryFunctionName} === 'function') {
                    __candidateFn = ${entryFunctionName};
                } else {
                    throw new Error("Entry function '${entryFunctionName}' was not found in your submission.");
                }
            `);
            script.runInContext(context, { timeout: this.DEFAULT_TIMEOUT_MS });
            candidateFn = context.__candidateFn;
        } catch (compErr) {
            return {
                success: true,
                score: 0,
                maxScore: maxMarks,
                verdict: 'COMPILATION_OR_SYNTAX_ERROR',
                summary: `Syntax / Compilation Error: ${compErr.message}`,
                sets: {}
            };
        }

        // Prepare Reference Oracle if provided in database
        let oracleFn = null;
        if (referenceSolution && referenceSolution.trim().length > 0) {
            try {
                const oracleContext = {};
                vm.createContext(oracleContext);
                const oracleScript = new vm.Script(`
                    ${referenceSolution}
                    if (typeof ${entryFunctionName} === 'function') {
                        __oracleFn = ${entryFunctionName};
                    }
                `);
                oracleScript.runInContext(oracleContext, { timeout: 1000 });
                oracleFn = oracleContext.__oracleFn;
            } catch (_) {}
        }

        // Test sets breakdown
        const testReport = {
            publicSet: { name: 'Public Sample Test Cases', total: publicCases.length, passed: 0, marks: publicWeight, earned: 0 },
            hiddenSet: { name: 'Hidden Edge & Boundary Cases (High Weight)', total: hiddenCases.length, passed: 0, marks: hiddenWeight, earned: 0 },
            randomSet: { name: 'Dynamic Randomized Tests (Anti-Hardcode)', total: 0, passed: 0, marks: 0, earned: 0 }
        };

        // 1. Evaluate Public Test Cases from database
        publicCases.forEach(tc => {
            const passed = this.runSingleTestCase(candidateFn, tc);
            if (passed) testReport.publicSet.passed++;
        });
        testReport.publicSet.earned = publicCases.length > 0 ? (testReport.publicSet.passed / publicCases.length) * publicWeight : publicWeight;

        // 2. Evaluate Hidden Test Cases from database
        hiddenCases.forEach(tc => {
            const passed = this.runSingleTestCase(candidateFn, tc);
            if (passed) testReport.hiddenSet.passed++;
        });
        testReport.hiddenSet.earned = hiddenCases.length > 0 ? (testReport.hiddenSet.passed / hiddenCases.length) * hiddenWeight : hiddenWeight;

        // 3. Dynamic Randomized Generation (If oracle and schema present in database)
        if (oracleFn && randomConfig) {
            const count = randomConfig.count || 10;
            testReport.randomSet.total = count;
            for (let i = 0; i < count; i++) {
                const randomInputs = this.generateRandomInputs(randomConfig);
                try {
                    const expected = oracleFn(...this.deepClone(randomInputs));
                    const actual = candidateFn(...this.deepClone(randomInputs));
                    const passed = this.compareOutputs(actual, expected);
                    if (passed) testReport.randomSet.passed++;
                } catch (_) {}
            }
        }

        const totalEarned = Math.min(maxMarks, Math.round(testReport.publicSet.earned + testReport.hiddenSet.earned));
        const totalCases = testReport.publicSet.total + testReport.hiddenSet.total + testReport.randomSet.total;
        const totalPassed = testReport.publicSet.passed + testReport.hiddenSet.passed + testReport.randomSet.passed;

        let verdict = 'ACCEPTED';
        if (antiHardcode.isHardcoded) {
            verdict = 'FLAGGED_HARDCODED_LOGIC';
        } else if (totalPassed === 0) {
            verdict = 'WRONG_ANSWER';
        } else if (totalPassed < totalCases) {
            verdict = 'PARTIALLY_ACCEPTED';
        }

        return {
            success: true,
            score: totalEarned,
            maxScore: maxMarks,
            totalPassed,
            totalCases,
            verdict,
            summary: `Passed ${totalPassed}/${totalCases} database test cases (${totalEarned}/${maxMarks} Marks).`,
            sets: testReport
        };
    }

    runSingleTestCase(fn, tc) {
        try {
            let inputs = tc.inputs || tc.args || tc.params;
            if (!Array.isArray(inputs)) {
                // If single input or structured { nums, target } or { arr }
                if (tc.nums !== undefined && tc.target !== undefined) {
                    inputs = [tc.nums, tc.target];
                } else if (tc.arr !== undefined) {
                    inputs = [tc.arr];
                } else if (tc.s !== undefined) {
                    inputs = [tc.s];
                } else if (tc.input !== undefined) {
                    inputs = Array.isArray(tc.input) ? tc.input : [tc.input];
                } else {
                    inputs = [];
                }
            }

            const actual = fn(...this.deepClone(inputs));
            const expected = tc.expected;

            return this.compareOutputs(actual, expected);
        } catch (_) {
            return false;
        }
    }

    compareOutputs(actual, expected) {
        if (actual === expected) return true;

        if (Array.isArray(actual) && Array.isArray(expected)) {
            if (actual.length !== expected.length) return false;
            // Check direct match
            const exact = actual.every((val, idx) => val === expected[idx]);
            if (exact) return true;
            // Check sorted match if 2-element index pair
            if (actual.length === 2) {
                return (actual[0] === expected[1] && actual[1] === expected[0]);
            }
            return false;
        }

        if (typeof actual === 'object' && typeof expected === 'object' && actual !== null && expected !== null) {
            return JSON.stringify(actual) === JSON.stringify(expected);
        }

        return String(actual) === String(expected);
    }

    generateRandomInputs(config) {
        const params = config.params || [];
        return params.map(p => {
            if (p.type === 'array_number') {
                const len = p.len || Math.floor(Math.random() * (p.maxLen || 50)) + (p.minLen || 5);
                const min = p.min || -1000;
                const max = p.max || 1000;
                return Array.from({ length: len }, () => Math.floor(Math.random() * (max - min + 1)) + min);
            }
            if (p.type === 'number') {
                const min = p.min || 0;
                const max = p.max || 1000;
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            if (p.type === 'string') {
                const chars = 'abcdefghijklmnopqrstuvwxyz';
                const len = p.len || 10;
                let s = '';
                for (let i = 0; i < len; i++) s += chars.charAt(Math.floor(Math.random() * chars.length));
                return s;
            }
            return 0;
        });
    }

    detectFunctionName(starterCode) {
        if (!starterCode) return 'solve';
        const match = starterCode.match(/function\s+([a-zA-Z0-9_$]+)\s*\(/);
        return match ? match[1] : 'solve';
    }

    parseJsonField(val, fallback) {
        if (!val) return fallback;
        if (typeof val === 'object') return val;
        try {
            return JSON.parse(val);
        } catch (_) {
            return fallback;
        }
    }

    deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    checkAntiHardcoding(code) {
        const cleaned = code.replace(/\/\*[\s\S]*?\*\/|\/\/.*/g, '');
        const literalArrayReturns = (cleaned.match(/return\s*\[\s*-?\d+\s*,\s*-?\d+\s*\]/g) || []).length;
        const ifElseChains = (cleaned.match(/if\s*\(.*?\)/g) || []).length;
        const hasLoops = /for\s*\(|while\s*\(|forEach|\.map|Map\(|Set\(|\.reduce/i.test(cleaned);

        if (literalArrayReturns >= 3 && ifElseChains >= 3 && !hasLoops) {
            return { isHardcoded: true, reason: 'Detected multiple static return branches without iterative logic.' };
        }
        return { isHardcoded: false };
    }
}

module.exports = new UniversalCodeJudgeEngine();
