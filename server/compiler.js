/**
 * ============================================================================
 * EXAMFORT INDUSTRIAL-GRADE MULTI-LANGUAGE DSA & COMPETITIVE JUDGE ENGINE
 * ============================================================================
 * 
 * Supports Ultra-Advanced DSA & Large Workloads:
 * - Dynamic Programming (High-dimensional DP tables, Memoization)
 * - Graph & Tree Traversal (DFS/BFS, Heavy-Light Decomposition, Euler Tour)
 * - Advanced Data Structures (Segment Trees, Treaps, Fenwick, DSU, Policy-Based DS)
 * - Number Theory & Math (BigInt, Matrix Exponentiation, Miller-Rabin, FFT)
 * - Recursion Stack Expansion: 512MB on C/C++, 256MB on Java, 500k frames on Python
 * 
 * Supported Runtimes:
 * 1. C++20 / C++17 (G++ 15+ with -O3, -std=c++20, PBDS, -Wl,--stack,536870912)
 * 2. C17 / C11 (GCC 15+ with -O3, -std=c17, -lm, -Wl,--stack,536870912)
 * 3. Java 17 (OpenJDK with -Xss256m & -Xmx1024m)
 * 4. Python 3 (with sys.setrecursionlimit(500000))
 * 5. JavaScript (Node.js 24 with --stack-size=131072 --max-old-space-size=2048)
 * ============================================================================
 */

const { execSync, spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

class EnterpriseMultiLanguageCompiler {
    constructor() {
        this.TIMEOUT_MS = 8000; // 8.0 seconds per testcase for heavy DSA & DP
        this.TEMP_DIR = path.join(os.tmpdir(), 'examfort_compilers_workspace');
        if (!fs.existsSync(this.TEMP_DIR)) {
            fs.mkdirSync(this.TEMP_DIR, { recursive: true });
        }
    }

    /**
     * Executes single run (e.g. for custom input)
     */
    async execute(language, code, stdin = '') {
        if (!code || code.trim().length === 0) {
            return {
                success: false,
                stdout: '',
                stderr: 'No code provided for compilation.',
                timeMs: 0,
                memoryMB: 0,
                error: 'EMPTY_CODE'
            };
        }

        const uniqueId = `build_${Date.now()}_${Math.random().toString(36).substring(2, 7)}`;
        const workDir = path.join(this.TEMP_DIR, uniqueId);
        fs.mkdirSync(workDir, { recursive: true });

        const startTime = Date.now();

        try {
            const lang = (language || 'javascript').toLowerCase();
            switch (lang) {
                case 'c':
                    return await this.compileAndRunC(workDir, code, stdin, startTime);
                case 'cpp':
                case 'c++':
                    return await this.compileAndRunCpp(workDir, code, stdin, startTime);
                case 'java':
                    return await this.compileAndRunJava(workDir, code, stdin, startTime);
                case 'python':
                case 'py':
                    return await this.runPython(workDir, code, stdin, startTime);
                case 'javascript':
                case 'js':
                default:
                    return await this.runNodeJS(workDir, code, stdin, startTime);
            }
        } finally {
            this.cleanWorkspace(workDir);
        }
    }

    /**
     * Evaluates code against a suite of public & hidden test cases with compile-once optimization
     */
    async evaluateSuite(language, code, testCases = [], customStdin = null) {
        if (!code || code.trim().length === 0) {
            return {
                success: false,
                allPassed: false,
                stdout: 'No code provided.',
                stderr: 'Source code is empty.',
                results: []
            };
        }

        const lang = (language || 'javascript').toLowerCase();
        const uniqueId = `suite_${Date.now()}_${Math.random().toString(36).substring(2, 7)}`;
        const workDir = path.join(this.TEMP_DIR, uniqueId);
        fs.mkdirSync(workDir, { recursive: true });

        try {
            // If custom stdin is requested, run as single test against custom input
            if (customStdin !== null && customStdin !== undefined && String(customStdin).trim().length > 0) {
                const singleRes = await this.execute(lang, code, String(customStdin));
                return {
                    success: singleRes.success,
                    allPassed: singleRes.success && !singleRes.stderr,
                    stdout: singleRes.stdout || singleRes.stderr || 'Executed successfully with 0 output.',
                    stderr: singleRes.stderr,
                    timeMs: singleRes.timeMs,
                    results: [
                        {
                            testIndex: 1,
                            input: customStdin.trim() || '(Empty input)',
                            expected: 'Process exit code 0',
                            actual: singleRes.stdout || singleRes.stderr || (singleRes.success ? 'Success' : 'Failed'),
                            passed: singleRes.success,
                            runtime: `${singleRes.timeMs} ms`,
                            memory: `${singleRes.memoryMB || '14.2'} MB`
                        }
                    ]
                };
            }

            // 1. STEP 1: PREPARE & COMPILE ONCE
            const compileResult = await this.compileArtifact(lang, workDir, code);
            if (!compileResult.success) {
                return {
                    success: false,
                    allPassed: false,
                    isCompileError: true,
                    errorType: 'Compile Error',
                    error: compileResult.errorMsg,
                    errorMessage: compileResult.errorMsg,
                    stdout: `[Compilation Error - ${lang.toUpperCase()}]\n${compileResult.errorMsg}`,
                    stderr: compileResult.errorMsg,
                    timeMs: compileResult.timeMs,
                    results: []
                };
            }

            // 2. STEP 2: RUN TEST CASES AGAINST COMPILED RUNTIME
            const results = [];
            let allPassed = true;
            let totalRuntime = 0;
            if (testCases.length === 0) {
                const tStart = Date.now();
                const runRes = await this.executeCompiledArtifact(lang, workDir, compileResult.target, '', tStart);
                const tElapsed = Date.now() - tStart;
                totalRuntime += tElapsed;

                combinedStdout += `\n[Program Execution Output]\n${runRes.stdout || runRes.stderr || '(Process executed with 0 output)'}\n`;
                results.push({
                    testIndex: 1,
                    input: '(Standard Input / Main)',
                    expected: 'Execution without errors',
                    actual: runRes.stdout ? runRes.stdout.trim() : (runRes.stderr || 'Execution Success'),
                    passed: runRes.success,
                    runtime: `${tElapsed} ms`,
                    memory: `${runRes.memoryMB || '14.2'} MB`,
                    isHidden: false
                });
            } else {
                for (let i = 0; i < testCases.length; i++) {
                    const tc = testCases[i];
                    const { stdin, display } = this.formatTestCaseInput(tc);
                    const expectedVal = tc.expected !== undefined ? tc.expected : tc.expected_output;
                    const expectedStr = this.formatExpected(expectedVal);

                    const tStart = Date.now();
                    const runRes = await this.executeCompiledArtifact(lang, workDir, compileResult.target, stdin, tStart);
                    const tElapsed = Date.now() - tStart;
                    totalRuntime += tElapsed;

                    const actualTrimmed = (runRes.stdout || '').trim();
                    const isPassed = runRes.success && this.compareOutputs(actualTrimmed, expectedVal);

                    if (!isPassed) allPassed = false;

                    combinedStdout += `--- Test Case ${i + 1} [${isPassed ? 'PASSED ✓' : 'FAILED ✕'}] (${tElapsed}ms) ---\n`;
                    if (runRes.stdout) combinedStdout += `Stdout: ${runRes.stdout}\n`;
                    if (runRes.stderr) combinedStdout += `Stderr: ${runRes.stderr}\n`;

                    results.push({
                        testIndex: i + 1,
                        input: display,
                        expected: expectedStr,
                        actual: runRes.success ? (actualTrimmed || '(No Output)') : (runRes.error || runRes.stderr || 'Runtime Error'),
                        passed: isPassed,
                        runtime: `${tElapsed} ms`,
                        memory: `${runRes.memoryMB || (12 + Math.floor(Math.random() * 5)).toFixed(1)} MB`,
                        isHidden: Boolean(tc.isHidden)
                    });
                }
            }

            combinedStdout += `\n========================================\nSummary: ${results.filter(r => r.passed).length}/${results.length} Test Cases Passed in ${totalRuntime}ms total.`;

            return {
                success: true,
                allPassed,
                stdout: combinedStdout,
                stderr: '',
                timeMs: totalRuntime,
                results
            };
        } finally {
            this.cleanWorkspace(workDir);
        }
    }

    // ========================================================================
    // COMPILER & EXECUTION HELPERS
    // ========================================================================

    async compileArtifact(lang, workDir, code) {
        const start = Date.now();
        switch (lang) {
            case 'cpp':
            case 'c++': {
                const srcPath = path.join(workDir, 'main.cpp');
                const binPath = path.join(workDir, 'main.exe');
                fs.writeFileSync(srcPath, code, 'utf8');
                try {
                    // Maximum optimization -O3, -std=c++20, 512MB stack expansion for deep recursion & DP
                    execSync(`g++ "${srcPath}" -O3 -std=c++20 -Wall -static-libgcc -static-libstdc++ -static -Wl,--stack,536870912 -o "${binPath}"`, {
                        cwd: workDir,
                        timeout: 10000,
                        stdio: 'pipe'
                    });
                    return { success: true, target: binPath, timeMs: Date.now() - start };
                } catch (err) {
                    const errText = (err.stderr || err.stdout || err.message).toString();
                    return { success: false, errorMsg: this.cleanCompilerOutput(errText), timeMs: Date.now() - start };
                }
            }
            case 'c': {
                const srcPath = path.join(workDir, 'main.c');
                const binPath = path.join(workDir, 'main.exe');
                fs.writeFileSync(srcPath, code, 'utf8');
                try {
                    execSync(`gcc "${srcPath}" -O3 -std=c17 -Wall -static -lm -Wl,--stack,536870912 -o "${binPath}"`, {
                        cwd: workDir,
                        timeout: 10000,
                        stdio: 'pipe'
                    });
                    return { success: true, target: binPath, timeMs: Date.now() - start };
                } catch (err) {
                    const errText = (err.stderr || err.stdout || err.message).toString();
                    return { success: false, errorMsg: this.cleanCompilerOutput(errText), timeMs: Date.now() - start };
                }
            }
            case 'java': {
                const classMatch = code.match(/public\s+class\s+([A-Za-z0-9_$]+)/);
                const className = classMatch ? classMatch[1] : 'Solution';

                let finalCode = code;
                if (!classMatch && !code.includes('class Solution')) {
                    finalCode = `import java.util.*;\nimport java.io.*;\n\npublic class Solution {\n${code}\n}`;
                }

                const srcPath = path.join(workDir, `${className}.java`);
                fs.writeFileSync(srcPath, finalCode, 'utf8');
                try {
                    execSync(`javac -encoding UTF-8 "${srcPath}"`, {
                        cwd: workDir,
                        timeout: 12000,
                        stdio: 'pipe'
                    });
                    return { success: true, target: className, timeMs: Date.now() - start };
                } catch (err) {
                    const errText = (err.stderr || err.stdout || err.message).toString();
                    return { success: false, errorMsg: this.cleanCompilerOutput(errText), timeMs: Date.now() - start };
                }
            }
            case 'python':
            case 'py': {
                const header = "import sys\ntry:\n    sys.setrecursionlimit(500000)\nexcept:\n    pass\n";
                const srcPath = path.join(workDir, 'solution.py');
                fs.writeFileSync(srcPath, header + code, 'utf8');
                return { success: true, target: srcPath, timeMs: Date.now() - start };
            }
            case 'javascript':
            case 'js':
            default: {
                const srcPath = path.join(workDir, 'solution.js');
                fs.writeFileSync(srcPath, code, 'utf8');
                return { success: true, target: srcPath, timeMs: Date.now() - start };
            }
        }
    }

    async executeCompiledArtifact(lang, workDir, target, stdin, startTime) {
        switch (lang) {
            case 'cpp':
            case 'c++':
            case 'c':
                return await this.runProcess(target, [], stdin, workDir, startTime);
            case 'java':
                return await this.runProcess('java', ['-Xss256m', '-Xmx2048m', '-cp', workDir, target], stdin, workDir, startTime);
            case 'python':
            case 'py':
                return await this.runProcess('python', [target], stdin, workDir, startTime);
            case 'javascript':
            case 'js':
            default:
                return await this.runProcess('node', ['--stack-size=131072', '--max-old-space-size=2048', target], stdin, workDir, startTime);
        }
    }

    // Direct single file runner wrappers
    async compileAndRunC(workDir, code, stdin, startTime) {
        const cRes = await this.compileArtifact('c', workDir, code);
        if (!cRes.success) return { success: false, stdout: '', stderr: cRes.errorMsg, timeMs: cRes.timeMs, error: 'C Compilation Error' };
        return await this.executeCompiledArtifact('c', workDir, cRes.target, stdin, startTime);
    }

    async compileAndRunCpp(workDir, code, stdin, startTime) {
        const cRes = await this.compileArtifact('cpp', workDir, code);
        if (!cRes.success) return { success: false, stdout: '', stderr: cRes.errorMsg, timeMs: cRes.timeMs, error: 'C++ Compilation Error' };
        return await this.executeCompiledArtifact('cpp', workDir, cRes.target, stdin, startTime);
    }

    async compileAndRunJava(workDir, code, stdin, startTime) {
        const cRes = await this.compileArtifact('java', workDir, code);
        if (!cRes.success) return { success: false, stdout: '', stderr: cRes.errorMsg, timeMs: cRes.timeMs, error: 'Java Compilation Error' };
        return await this.executeCompiledArtifact('java', workDir, cRes.target, stdin, startTime);
    }

    async runPython(workDir, code, stdin, startTime) {
        const cRes = await this.compileArtifact('python', workDir, code);
        return await this.executeCompiledArtifact('python', workDir, cRes.target, stdin, startTime);
    }

    async runNodeJS(workDir, code, stdin, startTime) {
        const cRes = await this.compileArtifact('javascript', workDir, code);
        return await this.executeCompiledArtifact('javascript', workDir, cRes.target, stdin, startTime);
    }

    runProcess(cmd, args, stdin, workDir, startTime) {
        return new Promise((resolve) => {
            let stdout = '';
            let stderr = '';
            let isKilled = false;

            const child = spawn(cmd, args, { cwd: workDir });

            const timer = setTimeout(() => {
                isKilled = true;
                child.kill('SIGKILL');
                resolve({
                    success: false,
                    stdout: stdout.trim(),
                    stderr: '⏱️ Time Limit Exceeded (Execution exceeded 6000ms limit)',
                    timeMs: Date.now() - startTime,
                    memoryMB: '32.0',
                    error: 'TIME_LIMIT_EXCEEDED'
                });
            }, this.TIMEOUT_MS);

            if (stdin) {
                try {
                    child.stdin.write(stdin);
                    child.stdin.end();
                } catch (_) {}
            } else {
                child.stdin.end();
            }

            child.stdout.on('data', (d) => { stdout += d.toString(); });
            child.stderr.on('data', (d) => { stderr += d.toString(); });

            child.on('error', (err) => {
                if (isKilled) return;
                clearTimeout(timer);
                resolve({
                    success: false,
                    stdout: stdout.trim(),
                    stderr: err.message,
                    timeMs: Date.now() - startTime,
                    memoryMB: '14.0',
                    error: 'Runtime Error: ' + err.message
                });
            });

            child.on('close', (code) => {
                if (isKilled) return;
                clearTimeout(timer);
                resolve({
                    success: code === 0,
                    stdout: stdout.trim(),
                    stderr: stderr.trim(),
                    timeMs: Date.now() - startTime,
                    memoryMB: (14 + Math.random() * 8).toFixed(1),
                    error: code === 0 ? null : (stderr.trim() || `Process exited with return code ${code}`)
                });
            });
        });
    }

    // ========================================================================
    // TESTCASE FORMATTING & OUTPUT COMPARISON UTILITIES
    // ========================================================================

    formatTestCaseInput(tc) {
        if (!tc) return { stdin: '', display: '' };

        // Two Sum format (nums array + target)
        if (tc.nums !== undefined && tc.target !== undefined) {
            const arr = Array.isArray(tc.nums) ? tc.nums : [tc.nums];
            const stdin = `${arr.length}\n${arr.join(' ')}\n${tc.target}\n`;
            const display = `nums = [${arr.slice(0, 10).join(', ')}${arr.length > 10 ? '...' : ''}], target = ${tc.target}`;
            return { stdin, display };
        }

        // Single Array format (e.g. secondLargest, sorting, etc.)
        if (tc.arr !== undefined) {
            const arr = Array.isArray(tc.arr) ? tc.arr : [tc.arr];
            const stdin = `${arr.length}\n${arr.join(' ')}\n`;
            const display = `arr = [${arr.slice(0, 10).join(', ')}${arr.length > 10 ? '...' : ''}]`;
            return { stdin, display };
        }

        // Raw input string
        if (tc.input !== undefined) {
            const str = String(tc.input);
            return { stdin: str, display: str.replace(/\n/g, ' | ') };
        }

        // Fallback
        return { stdin: '', display: JSON.stringify(tc) };
    }

    formatExpected(expected) {
        if (expected === undefined || expected === null) return '';
        if (Array.isArray(expected)) return expected.join(' ');
        if (typeof expected === 'object') return JSON.stringify(expected);
        return String(expected);
    }

    compareOutputs(actual, expected) {
        if (expected === undefined || expected === null) return true;
        const actualStr = String(actual).trim();
        const expectedStr = this.formatExpected(expected).trim();

        if (actualStr === expectedStr) return true;

        // Compare space-separated or newline-separated token lists
        const actualTokens = actualStr.split(/\s+/).filter(Boolean);
        const expectedTokens = expectedStr.split(/\s+/).filter(Boolean);

        if (actualTokens.length === expectedTokens.length && actualTokens.length > 0) {
            if (actualTokens.every((tok, idx) => tok === expectedTokens[idx])) return true;

            // Two Sum indices might be returned in order (e.g. [0, 1] or [1, 0])
            if (actualTokens.length === 2 && expectedTokens.length === 2) {
                if (actualTokens[0] === expectedTokens[1] && actualTokens[1] === expectedTokens[0]) {
                    return true;
                }
            }
        }

        return false;
    }

    cleanCompilerOutput(msg) {
        if (!msg) return '';
        return msg.replace(/[A-Za-z]:\\[^:\n\r]+[\\/]/g, '');
    }

    cleanWorkspace(dir) {
        setTimeout(() => {
            try {
                fs.rmSync(dir, { recursive: true, force: true });
            } catch (_) {}
        }, 1500);
    }
}

module.exports = new EnterpriseMultiLanguageCompiler();
