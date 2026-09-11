const { app, BrowserWindow, ipcMain, globalShortcut, screen, clipboard, systemPreferences } = require('electron');
const path = require('path');
const { exec, execSync, spawn } = require('child_process');
const os = require('os');
const fs = require('fs');
const crypto = require('crypto');

const LOG_FILE = path.join(os.tmpdir(), 'examfort_startup.log');
function logDebug(msg) {
    try {
        fs.appendFileSync(LOG_FILE, `[${new Date().toISOString()}] ${msg}\n`);
    } catch (_) {}
}

process.on('uncaughtException', (err) => {
    logDebug('FATAL uncaughtException: ' + (err?.stack || err?.message || err));
});
process.on('unhandledRejection', (reason) => {
    logDebug('FATAL unhandledRejection: ' + (reason?.stack || reason?.message || reason));
});

let mainWindow = null;

const INTEGRITY_PUBLIC_KEY = `__EXAMFORT_PUBLIC_KEY_PLACEHOLDER__`;

function verifyApplicationIntegrity() {
    logDebug('Starting verifyApplicationIntegrity...');
    const manifestPath = path.join(__dirname, 'integrity_manifest.json');
    logDebug('manifestPath: ' + manifestPath + ' (exists: ' + fs.existsSync(manifestPath) + ')');

    if (!fs.existsSync(manifestPath)) {
        if (!app.isPackaged || INTEGRITY_PUBLIC_KEY.includes('__EXAMFORT_PUBLIC_KEY_PLACEHOLDER__')) {
            logDebug('[Security] Dev/Standard environment: No signed integrity manifest required.');
            return true;
        }
        logDebug('[CRITICAL TAMPER VIOLATION] integrity_manifest.json is MISSING.');
        app.exit(101);
        return false;
    }

    try {
        const rawManifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
        const { payload, signature } = rawManifest;

        if (!payload || !signature) {
            logDebug('[CRITICAL TAMPER VIOLATION] Malformed integrity manifest.');
            app.exit(102);
            return false;
        }

        const isVerified = crypto.verify(
            null,
            Buffer.from(payload, 'utf8'),
            INTEGRITY_PUBLIC_KEY,
            Buffer.from(signature, 'base64')
        );

        logDebug('Digital signature verification result: ' + isVerified);

        if (!isVerified) {
            logDebug('[CRITICAL TAMPER VIOLATION] Digital signature verification FAILED.');
            app.exit(103);
            return false;
        }

        const manifestData = JSON.parse(payload);
        const hashes = manifestData.hashes || {};

        for (const [relPath, expectedHash] of Object.entries(hashes)) {
            let targetPath = path.join(__dirname, relPath);
            if (!fs.existsSync(targetPath)) {
                const unpackedPath = path.join(__dirname, '..', 'app.asar.unpacked', relPath);
                if (fs.existsSync(unpackedPath)) {
                    targetPath = unpackedPath;
                } else if (process.resourcesPath) {
                    const altPath = path.join(process.resourcesPath, relPath);
                    const altUnpacked = path.join(process.resourcesPath, 'app.asar.unpacked', relPath);
                    if (fs.existsSync(altPath)) {
                        targetPath = altPath;
                    } else if (fs.existsSync(altUnpacked)) {
                        targetPath = altUnpacked;
                    }
                }
            }

            if (!fs.existsSync(targetPath)) {
                logDebug(`[CRITICAL TAMPER VIOLATION] Required resource missing: ${relPath} (target: ${targetPath})`);
                app.exit(104);
                return false;
            }

            const fileBuffer = fs.readFileSync(targetPath);
            const actualHash = crypto.createHash('sha256').update(fileBuffer).digest('hex');

            if (actualHash !== expectedHash) {
                logDebug(`[CRITICAL TAMPER VIOLATION] Checksum mismatch for ${relPath}. Expected: ${expectedHash}, Actual: ${actualHash}`);
                app.exit(105);
                return false;
            }
        }

        logDebug('[Security] Cryptographic Integrity Verified (Ed25519 Signed SHA-256 Manifest OK)');
        return true;
    } catch (err) {
        logDebug('[CRITICAL TAMPER VIOLATION] Integrity verification error: ' + (err?.stack || err?.message || err));
        app.exit(106);
        return false;
    }
}

try {
    
    app.setAccessibilitySupportEnabled(false);
    app.commandLine.appendSwitch('disable-renderer-accessibility');
    app.commandLine.appendSwitch('disable-features', 'Accessibility,ScreenAI,LensOverlay');
} catch (_) {}

const PROHIBITED_PROCESSES = [
    // AI Interview Copilots & Overlay Cheat Tools (ParakeetAI, FinalRound, InterviewCoder, etc.)
    { name: 'parakeet.exe', label: 'Parakeet AI Copilot', category: 'AI Interview Assistant / Stealth Overlay' },
    { name: 'parakeetai.exe', label: 'Parakeet AI Copilot', category: 'AI Interview Assistant / Stealth Overlay' },
    { name: 'parakeet-ai.exe', label: 'Parakeet AI Copilot', category: 'AI Interview Assistant / Stealth Overlay' },
    { name: 'finalround.exe', label: 'FinalRound AI Copilot', category: 'AI Interview Assistant' },
    { name: 'finalroundai.exe', label: 'FinalRound AI Copilot', category: 'AI Interview Assistant' },
    { name: 'interviewcoder.exe', label: 'Interview Coder Overlay', category: 'AI Interview Assistant' },
    { name: 'interviewcoder-app.exe', label: 'Interview Coder Overlay', category: 'AI Interview Assistant' },
    { name: 'interview-coder.exe', label: 'Interview Coder Overlay', category: 'AI Interview Assistant' },
    { name: 'interviewsensei.exe', label: 'Interview Sensei AI', category: 'AI Interview Assistant' },
    { name: 'sensei.exe', label: 'Sensei AI Copilot', category: 'AI Interview Assistant' },
    { name: 'senseiai.exe', label: 'Sensei AI Copilot', category: 'AI Interview Assistant' },
    { name: 'copilot.exe', label: 'AI Desktop Copilot', category: 'AI Interview Assistant' },
    { name: 'cheatingdaddy.exe', label: 'Cheating Daddy Overlay', category: 'AI Interview Assistant' },
    { name: 'ghostai.exe', label: 'Ghost AI Teleprompter', category: 'AI Interview Assistant' },
    { name: 'ghost.exe', label: 'Ghost Assistant Overlay', category: 'AI Interview Assistant' },
    { name: 'ultracoder.exe', label: 'UltraCoder AI Overlay', category: 'AI Interview Assistant' },
    { name: 'claudecopilot.exe', label: 'Claude Copilot Overlay', category: 'AI Interview Assistant' },
    { name: 'chatgpt.exe', label: 'ChatGPT Desktop App', category: 'AI Assistant' },
    { name: 'claude.exe', label: 'Claude Desktop App', category: 'AI Assistant' },

    { name: 'autoit3.exe', label: 'AutoIt3 Automation', category: 'Macro / Bot' },
    { name: 'autoit.exe', label: 'AutoIt Script Runner', category: 'Macro / Bot' },
    { name: 'tesseract.exe', label: 'Tesseract OCR Engine', category: 'OCR / Screen Scraper' },
    { name: 'charles.exe', label: 'Charles Web Proxy', category: 'Proxy / Interceptor' },
    { name: 'injector.exe', label: 'DLL / Memory Injector', category: 'Memory Editor / Cheat' },
    { name: 'assistant.exe', label: 'Universal Assistant Bot', category: 'Cheat Binary' },
    { name: 'universalassistant.exe', label: 'Universal Assistant Bot', category: 'Cheat Binary' },
    
    { name: 'anydesk.exe', label: 'AnyDesk Remote Desktop', category: 'Remote Access' },
    { name: 'teamviewer.exe', label: 'TeamViewer Screen Share', category: 'Remote Access' },
    { name: 'rustdesk.exe', label: 'RustDesk Remote Desktop', category: 'Remote Access' },
    { name: 'vncviewer.exe', label: 'VNC Remote Viewer', category: 'Remote Access' },
    { name: 'ultraviewer.exe', label: 'UltraViewer Remote', category: 'Remote Access' },
    { name: 'parsec.exe', label: 'Parsec Screen Streamer', category: 'Remote Access' },
    { name: 'discord.exe', label: 'Discord (Screen Streaming)', category: 'Screen Sharing' },
    { name: 'zoom.exe', label: 'Zoom Meetings', category: 'Screen Sharing' },
    { name: 'skype.exe', label: 'Skype Video/Share', category: 'Screen Sharing' },
    { name: 'slack.exe', label: 'Slack Screen Share', category: 'Screen Sharing' },

    { name: 'obs64.exe', label: 'OBS Studio (64-bit)', category: 'Screen Capture' },
    { name: 'obs32.exe', label: 'OBS Studio (32-bit)', category: 'Screen Capture' },
    { name: 'streamlabs obs.exe', label: 'Streamlabs OBS', category: 'Screen Capture' },
    { name: 'camtasia.exe', label: 'Camtasia Studio', category: 'Screen Capture' },
    { name: 'bandicam.exe', label: 'Bandicam Screen Recorder', category: 'Screen Capture' },
    { name: 'sharex.exe', label: 'ShareX Screen Capture', category: 'Screen Capture' },
    { name: 'lightshot.exe', label: 'Lightshot Screenshot Tool', category: 'Screen Capture' },
    { name: 'snippingtool.exe', label: 'Windows Snipping Tool', category: 'Screen Capture' },
    { name: 'greenshot.exe', label: 'Greenshot Capture', category: 'Screen Capture' },

    { name: 'cheatengine-x86_64.exe', label: 'Cheat Engine (64-bit)', category: 'Memory Editor / Cheat' },
    { name: 'cheatengine-i386.exe', label: 'Cheat Engine (32-bit)', category: 'Memory Editor / Cheat' },
    { name: 'processhacker.exe', label: 'Process Hacker', category: 'Process Hijacker' },
    { name: 'procexp.exe', label: 'Process Explorer', category: 'Process Analyzer' },
    { name: 'procmon.exe', label: 'Process Monitor', category: 'Debugger' },
    { name: 'x64dbg.exe', label: 'x64 Debugger', category: 'Reverse Engineering' },
    { name: 'x32dbg.exe', label: 'x32 Debugger', category: 'Reverse Engineering' },
    { name: 'ida64.exe', label: 'IDA Pro 64-bit', category: 'Disassembler' },
    { name: 'wireshark.exe', label: 'Wireshark Packet Sniffer', category: 'Network Hijack' },
    { name: 'fiddler.exe', label: 'Fiddler Web Debugger', category: 'Proxy / Interceptor' },

    { name: 'vmware.exe', label: 'VMware Workstation', category: 'Virtual Machine' },
    { name: 'virtualbox.exe', label: 'VirtualBox Manager', category: 'Virtual Machine' },
    { name: 'vboxheadless.exe', label: 'VirtualBox Headless', category: 'Virtual Machine' },
    { name: 'bluestacks.exe', label: 'BlueStacks Android Emulator', category: 'Emulator' },
    { name: 'nox.exe', label: 'NoxPlayer Emulator', category: 'Emulator' },

    { name: 'python.exe', label: 'Python Runtime Interpreter', category: 'Script Execution' },
    { name: 'pythonw.exe', label: 'Python Windowed Runtime', category: 'Script Execution' },
    { name: 'py.exe', label: 'Python Launcher', category: 'Script Execution' },
    { name: 'autohotkey.exe', label: 'AutoHotkey Automation', category: 'Macro / Auto-Clicker' },
    { name: 'ahk.exe', label: 'AHK Script Runner', category: 'Macro / Auto-Clicker' },
    { name: 'sss.exe', label: 'Unauthorized Custom Executable', category: 'Suspicious Cheat Binary' },

    { name: 'spacedesk.exe', label: 'SpaceDesk Virtual Display', category: 'Virtual Screen Share' },
    { name: 'spacedeskservice.exe', label: 'SpaceDesk Service', category: 'Virtual Screen Share' },
    { name: 'spacedeskdriverservice.exe', label: 'SpaceDesk Driver Service', category: 'Virtual Screen Share' },
    { name: 'ndiperformancemonitor.exe', label: 'NDI Virtual Camera', category: 'Virtual Capture' },
    { name: 'ndictools.exe', label: 'NDI Tools', category: 'Virtual Capture' },
    { name: 'connectifyd.exe', label: 'Connectify Hotspot', category: 'Network Share' },
    { name: 'lgsthub.exe', label: 'LG Screen Share', category: 'Virtual Screen Share' },
    { name: 'miracastview.exe', label: 'Miracast Wireless Display', category: 'Virtual Screen Share' },
    { name: 'airserver.exe', label: 'AirServer Screen Mirror', category: 'Virtual Screen Share' },
    { name: 'reflector.exe', label: 'Reflector Screen Mirror', category: 'Virtual Screen Share' },
    { name: 'letsview.exe', label: 'LetsView Screen Mirror', category: 'Virtual Screen Share' },
    { name: 'apowermirror.exe', label: 'ApowerMirror Screen Share', category: 'Virtual Screen Share' },
    { name: 'scrcpy.exe', label: 'scrcpy Android Mirror', category: 'Virtual Screen Share' }
];

let wdaAppliedOnce = false;
function applyWDAExcludeFromCapture() {
    if (!mainWindow || mainWindow.isDestroyed() || wdaAppliedOnce) return;
    wdaAppliedOnce = true;

    try {
        mainWindow.setContentProtection(true);
        console.log('[Security] WDA Native Content Protection ACTIVE.');
    } catch (e) {
        console.warn('[Security] Native Content Protection error:', e.message);
    }
}

let keyboardSentinelProc = null;

function startKeyboardSentinel() {
    if (process.platform !== 'win32') return;
    if (keyboardSentinelProc) return;
    try {
        let scriptPath = path.join(__dirname, 'keyboard_sentinel.ps1');
        if (!fs.existsSync(scriptPath)) {
            const unpackedPath = path.join(__dirname, '..', 'app.asar.unpacked', 'keyboard_sentinel.ps1');
            if (fs.existsSync(unpackedPath)) {
                scriptPath = unpackedPath;
            } else if (process.resourcesPath) {
                const altPath = path.join(process.resourcesPath, 'keyboard_sentinel.ps1');
                const altUnpacked = path.join(process.resourcesPath, 'app.asar.unpacked', 'keyboard_sentinel.ps1');
                if (fs.existsSync(altPath)) {
                    scriptPath = altPath;
                } else if (fs.existsSync(altUnpacked)) {
                    scriptPath = altUnpacked;
                }
            }
        }
        if (fs.existsSync(scriptPath)) {
            console.log('[Security] Launching persistent Aegis Keyboard Sentinel from: ' + scriptPath);
            keyboardSentinelProc = spawn('powershell', [
                '-NoProfile',
                '-ExecutionPolicy', 'Bypass',
                '-WindowStyle', 'Hidden',
                '-File', scriptPath,
                process.pid.toString()
            ], {
                stdio: ['ignore', 'pipe', 'pipe'],
                windowsHide: true
            });

            keyboardSentinelProc.stdout?.on('data', (data) => {
                const str = data.toString().trim();
                if (str.includes('KEYBOARD_SENTINEL_ACTIVE')) {
                    console.log('[Security] Aegis Keyboard Sentinel is LIVE & ACTIVE.');
                }
                const lines = str.split(/\r?\n/);
                for (const line of lines) {
                    if (line.startsWith('OVERLAY_VIOLATION|')) {
                        console.warn('[Security] OVERLAY VIOLATION DETECTED BY SENTINEL:', line);
                        const parts = line.split('|');
                        const dataMap = {};
                        for (const part of parts.slice(1)) {
                            const [k, ...v] = part.split('=');
                            if (k) dataMap[k.trim()] = v.join('=').trim();
                        }
                        const pName = dataMap.process || 'Unknown Process';
                        const reason = dataMap.reason || 'Topmost/Layered Overlay Detected';
                        const title = dataMap.title || '';
                        const pid = dataMap.pid || '';
                        
                        if (mainWindow && !mainWindow.isDestroyed()) {
                            let vType = 'UNAUTHORIZED_SCREEN_OVERLAY';
                            if (/auto-typing|synthetic|magic/i.test(reason)) {
                                vType = 'UNAUTHORIZED_AUTO_TYPING';
                            } else if (/text extractor|ocr/i.test(reason)) {
                                vType = 'UNAUTHORIZED_TEXT_EXTRACTOR';
                            }
                            mainWindow.webContents.send('security:violation', {
                                type: vType,
                                process: pName,
                                pid: pid,
                                reason: reason,
                                title: title,
                                details: `${reason} detected from "${pName}.exe" (${title || 'Automated Tool'}). Process terminated immediately by Aegis Sentinel.`
                            });
                        }
                    }
                }
            });

            keyboardSentinelProc.on('exit', (code) => {
                console.log(`[Security] Aegis Keyboard Sentinel exited with code ${code}`);
                keyboardSentinelProc = null;
            });
        }
    } catch (err) {
        console.warn('[Security] Failed to start keyboard sentinel:', err.message);
    }
}

let overlayScannerInterval = null;
function startTopmostOverlayScanner() {
    if (process.platform !== 'win32' || overlayScannerInterval) return;

    overlayScannerInterval = setInterval(() => {
        if (!mainWindow || mainWindow.isDestroyed() || mainWindow._allowClose) return;

        const psCmd = `
            $selfPid = ${process.pid};
            Add-Type -TypeDefinition @"
            using System;
            using System.Runtime.InteropServices;
            using System.Text;
            public class WinChecker {
                [DllImport("user32.dll")] public static extern bool EnumWindows(EnumWindowsProc lpEnumFunc, IntPtr lParam);
                [DllImport("user32.dll")] public static extern int GetWindowLong(IntPtr hWnd, int nIndex);
                [DllImport("user32.dll")] public static extern uint GetWindowThreadProcessId(IntPtr hWnd, out uint lpdwProcessId);
                [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr hWnd, StringBuilder lpString, int nMaxCount);
                [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr hWnd);
                public delegate bool EnumWindowsProc(IntPtr hWnd, IntPtr lParam);
            }
"@;
            [WinChecker]::EnumWindows({
                param($hWnd, $lParam)
                if ([WinChecker]::IsWindowVisible($hWnd)) {
                    $pidVal = 0;
                    [WinChecker]::GetWindowThreadProcessId($hWnd, [ref]$pidVal);
                    if ($pidVal -gt 4 -and $pidVal -ne $selfPid) {
                        $exStyle = [WinChecker]::GetWindowLong($hWnd, -20);
                        $isTopmost = ($exStyle -band 0x00000008) -ne 0;
                        $isLayered = ($exStyle -band 0x00080000) -ne 0;
                        $isTrans = ($exStyle -band 0x00000020) -ne 0;
                        $sb = New-Object System.Text.StringBuilder 256;
                        [WinChecker]::GetWindowText($hWnd, $sb, 256);
                        $wTitle = $sb.ToString().ToLower();
                        $isOCR = $wTitle -match 'text extractor|ocr|screen clip|snipping|capture2text|textshot|snipast|crop and lock|magnifier';
                        $isAutoType = $wTitle -match 'autotype|magictype|ghosttype|cheattyper|hackertype|tinytask|jitbit|pulover|macrorecorder|autohotkey|ahk|autoit|beeftext|textexpander|murgee|typingsimulator|codepaster';
                        if ($isTopmost -or ($isLayered -and $isTrans) -or $isOCR -or $isAutoType) {
                            try {
                                $proc = Get-Process -Id $pidVal -ErrorAction SilentlyContinue;
                                if ($proc -and $proc.ProcessName -notmatch '^(explorer|dwm|system|lsass|services|svchost|powershell|pwsh|cmd|conhost|taskmgr|antigravity|code)$') {
                                    $tag = if ($isAutoType) { "AutoType" } elseif ($isOCR) { "OCR" } else { "Overlay" };
                                    Write-Output "FOUND_OVERLAY:$pidVal:$($proc.ProcessName):$($sb.ToString()):$tag";
                                }
                            } catch {}
                        }
                    }
                }
                return $true;
            }, [IntPtr]::Zero) | Out-Null

            # Also scan for background Text Extractor, OCR, Auto-Typer, and Macro processes
            $badProcs = Get-Process -ErrorAction SilentlyContinue | Where-Object {
                $_.ProcessName -match '^(powertoys\.textextractor|capture2text|textshot|easyscreenocr|screenclippinghost|snippingtool|snipaste|snipast|lightshot|greenshot|sharex|tesseract|paddleocr|easyocr|blackbox|screentotext|autotyper|magictyper|magictyping|ghosttyper|hackertyper|cheattyper|fastkeys|keytext|tinytask|jitbit|pulover|macrorecorder|autohotkey|ahk|autoit3|autoit|clavier|beeftext|textexpander|phraseexpress|murgee|keyspider|codepaster|typingsimulator)$'
            };
            if ($badProcs) {
                foreach ($op in $badProcs) {
                    $pnm = $op.ProcessName.ToLower();
                    $tag = if ($pnm -match 'autotype|magictype|ghosttype|cheattyper|hackertype|tinytask|jitbit|pulover|macrorecorder|autohotkey|ahk|autoit|beeftext|textexpander|murgee|typingsimulator|codepaster') { "AutoType" } else { "OCR" };
                    Write-Output "FOUND_OVERLAY:$($op.Id):$($op.ProcessName):Background $tag Tool:$tag";
                }
            }
        `;

        exec(`powershell -NoProfile -WindowStyle Hidden -Command "${psCmd.replace(/\r?\n/g, ' ')}"`, { timeout: 2500 }, (err, stdout) => {
            if (stdout && stdout.includes('FOUND_OVERLAY:')) {
                const lines = stdout.split(/\r?\n/);
                for (const line of lines) {
                    if (line.startsWith('FOUND_OVERLAY:')) {
                        const [, pid, procName, title, tag] = line.split(':');
                        if (procName) {
                            console.warn(`[Security] Continuous Watcher killed unauthorized tool process: ${procName} (PID: ${pid}, Type: ${tag})`);
                            exec(`taskkill /F /PID ${pid} 2>nul`);
                            exec(`taskkill /F /IM ${procName}.exe 2>nul`);
                            if (mainWindow && !mainWindow.isDestroyed() && !mainWindow._allowClose) {
                                let vType = 'UNAUTHORIZED_SCREEN_OVERLAY';
                                let vDesc = `Topmost/Layered Window detected from "${procName}.exe" (${title || 'Overlay'}). Offending process killed by system.`;
                                if (tag === 'AutoType' || /autotype|magictype|ghosttype|cheattyper|hackertype|tinytask|jitbit|pulover|macrorecorder|autohotkey|ahk|autoit|beeftext|textexpander|murgee|typingsimulator|codepaster/i.test(procName + ' ' + title)) {
                                    vType = 'UNAUTHORIZED_AUTO_TYPING';
                                    vDesc = `Unauthorized Magic Auto-Typer / Macro Typing Script detected from "${procName}.exe" (${title || 'Macro Script'}). Offending process killed by system.`;
                                } else if (tag === 'OCR' || /text extractor|ocr|screen clip|snipping|capture2text|textshot|snipast/i.test(procName + ' ' + title)) {
                                    vType = 'UNAUTHORIZED_TEXT_EXTRACTOR';
                                    vDesc = `Unauthorized Screen Text Extractor / OCR Tool detected from "${procName}.exe" (${title || 'OCR Process'}). Offending process killed by system.`;
                                }
                                mainWindow.webContents.send('security:violation', {
                                    type: vType,
                                    process: procName,
                                    pid: pid,
                                    title: title,
                                    details: vDesc
                                });
                            }
                        }
                    }
                }
            }
        });
    }, 1800);
}

function stopKeyboardSentinel() {
    if (overlayScannerInterval) {
        clearInterval(overlayScannerInterval);
        overlayScannerInterval = null;
    }
    if (keyboardSentinelProc) {
        try {
            keyboardSentinelProc.kill();
        } catch (_) {}
        keyboardSentinelProc = null;
    }
}

function startLowLevelKeyboardHook(win) {
    
    try { globalShortcut.unregisterAll(); } catch (_) {}

    const targetWin = win || mainWindow;

    const restrictedShortcuts = [
        'Alt+Escape',
        'Alt+F4',
        'Alt+Space',
        'CommandOrControl+Escape',
        'CommandOrControl+Shift+Escape',
        'CommandOrControl+Alt+Tab',
        'CommandOrControl+Shift+Tab',
        'CommandOrControl+W',
        'CommandOrControl+Q',
        'CommandOrControl+N',
        'CommandOrControl+T',
        'F11',
        'F12'
    ];

    restrictedShortcuts.forEach(shortcut => {
        try {
            globalShortcut.register(shortcut, () => {
                
            });
        } catch (_) {}
    });

    if (targetWin && targetWin.webContents) {
        targetWin.webContents.on('before-input-event', (event, input) => {
            
            if (input.meta || input.key === 'Meta' || input.key === 'OS') {
                event.preventDefault();
            }
            
            if (input.alt && (input.key === 'Tab' || input.key === 'Escape' || input.key === 'F4' || input.key === ' ')) {
                event.preventDefault();
            }
            
            if (input.control && (input.key === 'Escape' || input.key === 'Tab')) {
                event.preventDefault();
            }
        });
    }

    startKeyboardSentinel();

    console.log('[Security] Safe In-Process Keyboard Lockdown ACTIVE.');
}

function stopLowLevelKeyboardHook() {
    try {
        globalShortcut.unregisterAll();
    } catch (_) {}

    stopKeyboardSentinel();

    try {
        if (process.platform === 'win32') {
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Advanced" /v "DisabledHotkeys" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\MultitaskingView\\AllUpView" /v "Enabled" /f 2>nul`);
        }
    } catch (_) {}
}

function toggleTouchpadGestures(enable) {
    if (process.platform !== 'win32') return;
    try {
        if (!enable) {
            
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerSlideAction" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerSlideAction" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerSlideEnabled" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerSlideEnabled" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerTapAction" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerTapAction" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerPinAction" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerPinAction" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\MultitaskingView\\AllUpView" /v "Enabled" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Policies\\Microsoft\\Windows\\Explorer" /v "DisableTaskView" /t REG_DWORD /d 1 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Policies\\Microsoft\\Windows\\Explorer" /v "DisableMultiTasking" /t REG_DWORD /d 1 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Policies\\Microsoft\\Windows\\EdgeUI" /v "DisableEdgeGestures" /t REG_DWORD /d 1 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Advanced" /v "ShowTaskViewButton" /t REG_DWORD /d 0 /f 2>nul`);
            execSync(`reg add "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Advanced" /v "DisabledHotkeys" /t REG_SZ /d "TAB" /f 2>nul`);
            exec(`powershell -NoProfile -WindowStyle Hidden -Command "$c='[DllImport(\\\"user32.dll\\\")] public static extern int SendMessageTimeout(IntPtr h, uint m, IntPtr w, string l, uint f, uint u, out IntPtr r);'; Add-Type -MemberDefinition $c -Name S -Namespace N -ErrorAction SilentlyContinue; [IntPtr]$r=[IntPtr]::Zero; [N.S]::SendMessageTimeout([IntPtr]0xffff, 0x001A, [IntPtr]::Zero, 'Environment', 2, 800, [ref]$r)"`, { timeout: 1200 });
        } else {
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerSlideAction" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerSlideAction" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerSlideEnabled" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerSlideEnabled" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerTapAction" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerTapAction" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "ThreeFingerPinAction" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\PrecisionTouchPad" /v "FourFingerPinAction" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\MultitaskingView\\AllUpView" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Policies\\Microsoft\\Windows\\Explorer" /v "DisableTaskView" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Policies\\Microsoft\\Windows\\Explorer" /v "DisableMultiTasking" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Policies\\Microsoft\\Windows\\EdgeUI" /v "DisableEdgeGestures" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Advanced" /v "ShowTaskViewButton" /f 2>nul`);
            execSync(`reg delete "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Advanced" /v "DisabledHotkeys" /f 2>nul`);
        }
    } catch (_) {}
}

function clipCursorToSafeBounds(enable) {
    if (process.platform !== 'win32') return;
    try {
        
        const psUnclip = `$c = '[DllImport(\"user32.dll\")] public static extern bool ClipCursor(IntPtr r);'; Add-Type -MemberDefinition $c -Name U -Namespace N -ErrorAction SilentlyContinue; [N.U]::ClipCursor([IntPtr]::Zero);`;
        exec(`powershell -NoProfile -WindowStyle Hidden -Command "${psUnclip}"`, { timeout: 1200 });
    } catch (_) {}
}

function startActiveWindowSentinel() {
    
    startKeyboardSentinel();
}

function toggleWindowsTaskbar(show) {
    if (process.platform !== 'win32') return;
    const cmd = show ? 5 : 0; 
    const ps = `powershell -NoProfile -WindowStyle Hidden -Command "$c='[DllImport(\\\"user32.dll\\\")] public static extern int ShowWindow(int h, int c); [DllImport(\\\"user32.dll\\\")] public static extern int FindWindow(string n, string t);'; Add-Type -MemberDefinition $c -Name W -Namespace N -ErrorAction SilentlyContinue; $h=[N.W]::FindWindow('Shell_TrayWnd',''); if($h -gt 0){ [N.W]::ShowWindow($h, ${cmd}) }; $s=[N.W]::FindWindow('Shell_SecondaryTrayWnd',''); if($s -gt 0){ [N.W]::ShowWindow($s, ${cmd}) }"`;
    exec(ps, { timeout: 2000 });
}

function createWindow() {
    const primaryDisplay = screen.getPrimaryDisplay();
    const { x, y, width, height } = primaryDisplay.bounds;

    mainWindow = new BrowserWindow({
        x: x || 0,
        y: y || 0,
        width: width,
        height: height,
        backgroundColor: '#ffffff',
        fullscreen: true,
        kiosk: true,
        simpleFullscreen: false,
        alwaysOnTop: true,
        resizable: false,
        movable: false,
        minimizable: false,
        maximizable: false,
        closable: false,
        skipTaskbar: true,
        autoHideMenuBar: true,
        frame: false,
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true,
            sandbox: false,
            webSecurity: true,
            devTools: false,
            disableHtmlFullscreenWindowResize: true,
            offscreen: false,
            spellcheck: false
        }
    });

    mainWindow.setBounds({ x: x || 0, y: y || 0, width: width, height: height });

    mainWindow.setAlwaysOnTop(true, 'screen-saver', 9999);
    mainWindow.setKiosk(true);
    mainWindow.setFullScreen(true);
    toggleWindowsTaskbar(false);
    toggleTouchpadGestures(false);
    clipCursorToSafeBounds(false);
    startActiveWindowSentinel();
    startTopmostOverlayScanner();

    const overlayEnforcerInterval = setInterval(() => {
        if (mainWindow && !mainWindow.isDestroyed() && !mainWindow._allowClose) {
            try {
                mainWindow.setAlwaysOnTop(true, 'screen-saver', 99999);
                mainWindow.moveTop();
                if (!mainWindow.isFocused()) {
                    mainWindow.focus();
                }
            } catch (_) {}
        } else {
            clearInterval(overlayEnforcerInterval);
        }
    }, 100);

    try {
        mainWindow.setVisibleOnAllWorkspaces(true, { visibleOnFullScreen: true });
    } catch (_) {}

    let _isInternalNavigating = false;
    const forceWindowDominance = (reason = 'WINDOW_BLUR') => {
        if (mainWindow && !mainWindow._allowClose && !mainWindow.isDestroyed()) {
            try {
                mainWindow.focus();
                
                if (!_isInternalNavigating) {
                    mainWindow.webContents.send('security:violation', {
                        type: 'WINDOW_BLUR',
                        details: 'App switching / 3-finger gesture / window blur detected'
                    });
                }
            } catch (_) {}
        }
    };

    mainWindow.webContents.on('will-navigate', () => {
        _isInternalNavigating = true;
    });
    mainWindow.webContents.on('did-finish-load', () => {
        setTimeout(() => { _isInternalNavigating = false; }, 1500);
    });

    mainWindow.on('blur', () => {
        if (_isInternalNavigating || (mainWindow && mainWindow._allowClose)) return;
        setTimeout(() => {
            if (!_isInternalNavigating && mainWindow && !mainWindow._allowClose) {
                forceWindowDominance('WINDOW_BLUR');
            }
        }, 120);
    });

    mainWindow.on('minimize', (e) => {
        e.preventDefault();
        forceWindowDominance('MINIMIZE_ATTEMPT');
    });

    mainWindow.on('leave-full-screen', () => {
        forceWindowDominance('FULLSCREEN_EXIT');
    });

    mainWindow.on('leave-html-full-screen', () => {
        forceWindowDominance('HTML_FULLSCREEN_EXIT');
    });

    try {
        mainWindow.setContentProtection(true); 
        console.log('[Security] WDA_MONITOR baseline ACTIVE.');
    } catch (err) {
        console.error('[Security] Content Protection failed:', err);
    }

    mainWindow.webContents.once('did-finish-load', () => {
        applyWDAExcludeFromCapture();
    });

    mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL) => {
        console.error(`[Renderer] Failed to load ${validatedURL}: ${errorDescription} (${errorCode})`);
    });

    mainWindow.webContents.on('render-process-gone', (event, details) => {
        console.error('[Renderer] Process exited unexpectedly:', details);
    });

    mainWindow.loadFile(path.join(__dirname, 'src', 'index.html'));

    const ALLOWED_INTERNAL_PAGES = [
        'index.html', 'dashboard.html', 'assessment.html',
        'instructions.html', 'completed.html', 'courses.html',
        'course_details.html', 'lesson_study.html', 'exams.html',
        'exam_details.html', 'profile.html', 'support.html'
    ];
    mainWindow.webContents.on('will-navigate', (event, url) => {
        
        if (url.startsWith('file://')) {
            const basename = url.split('/').pop().split('?')[0];
            if (ALLOWED_INTERNAL_PAGES.includes(basename)) return; 
        }
        
        console.warn(`[NavLock] Blocked navigation to: ${url}`);
        event.preventDefault();
    });
    mainWindow.webContents.on('new-window', (event) => { event.preventDefault(); });

    setInterval(() => {
        try {
            const current = clipboard.readText();
            if (current && current.trim().length > 0) {
                clipboard.clear();
            }
        } catch (_) {}
    }, 1500);

    const blockedGlobalKeys = [
        'Alt+Tab', 'Alt+Shift+Tab', 'Meta+Tab', 'Alt+Escape',
        'CommandOrControl+Tab', 'CommandOrControl+Shift+Tab',
        'CommandOrControl+W', 'CommandOrControl+Q', 'CommandOrControl+N',
        'CommandOrControl+T', 'CommandOrControl+Shift+T',
        'PrintScreen', 'Alt+PrintScreen', 'Meta+PrintScreen',
        'Shift+Meta+3', 'Shift+Meta+4', 'Shift+Meta+5',
        'CommandOrControl+Shift+S', 'Alt+Shift+S',
        
        'Super+Control+Left', 'Super+Control+Right', 'Super+Control+D', 'Super+Control+F4',
        'Super+Tab', 'Super+D', 'Super+M', 'Super+E', 'Super+R', 'Super+S', 'Super+X', 'Super+A', 'Super+I',
        'Super+P', 'Super+K', 'Super+H', 'Super+V', 'Super+Down', 'Super+Up', 'Super+Left', 'Super+Right',
        'F11'
    ];
    blockedGlobalKeys.forEach(key => {
        try { globalShortcut.register(key, () => {}); } catch (_) {}
    });

    mainWindow.webContents.on('before-input-event', (event, input) => {
        const ctrl = input.control;
        const alt = input.alt;
        const meta = input.meta || input.key === 'Meta' || input.key === 'OS';
        const k = (input.key || '').toLowerCase();

        // 1. Block all Function keys (F1 to F12)
        if (/^f([1-9]|1[0-2])$/i.test(input.key)) {
            event.preventDefault();
            return;
        }

        // 2. Block PrintScreen & Snipping hotkeys
        if (input.key === 'PrintScreen' || (meta && input.shift && (k === 's' || k === '3' || k === '4' || k === '5'))) {
            event.preventDefault();
            clipboard.clear();
            mainWindow?.webContents.send('security:violation', { type: 'PRINT_SCREEN', details: 'Screenshot hotkey intercepted and blocked' });
            return;
        }

        // 3. Block all Windows (Meta/Super) keys and combinations
        if (meta) {
            event.preventDefault();
            return;
        }

        // 4. Block all Alt key combinations (Alt+Tab, Alt+F4, Alt+Space, Alt+Esc, Alt+*)
        if (alt) {
            event.preventDefault();
            return;
        }

        // 5. If Ctrl is pressed: ONLY ALLOW Ctrl+Z (Undo), block EVERYTHING else!
        if (ctrl) {
            if (!alt && !meta && !input.shift && k === 'z') {
                return; // ALLOW ONLY Ctrl+Z for undo functionality!
            }
            event.preventDefault();
            if (k === 'c' || k === 'v' || k === 'x' || k === 'a' || k === 'insert') {
                clipboard.clear();
            }
            return;
        }
    });

    const enforceFixedZoom = () => {
        try {
            mainWindow?.webContents.setZoomFactor(1.0);
            mainWindow?.webContents.setZoomLevel(0);
        } catch (_) {}
    };
    mainWindow.webContents.on('did-finish-load', enforceFixedZoom);
    mainWindow.webContents.on('did-navigate', enforceFixedZoom);

    mainWindow.webContents.on('did-finish-load', () => {
        mainWindow?.webContents.executeJavaScript(`

            document.addEventListener('wheel', (e) => {
                if (e.ctrlKey) { e.preventDefault(); e.stopPropagation(); }
            }, { passive: false, capture: true });

            window.addEventListener('touchstart', (e) => {
                if (e.touches && e.touches.length > 1) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, { passive: false, capture: true });

            window.addEventListener('touchmove', (e) => {
                if (e.touches && e.touches.length > 1) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, { passive: false, capture: true });

            window.addEventListener('touchend', (e) => {
                if (e.touches && e.touches.length > 1) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, { passive: false, capture: true });

            window.addEventListener('gesturestart', (e) => { e.preventDefault(); e.stopPropagation(); }, { capture: true });
            window.addEventListener('gesturechange', (e) => { e.preventDefault(); e.stopPropagation(); }, { capture: true });
            window.addEventListener('gestureend', (e) => { e.preventDefault(); e.stopPropagation(); }, { capture: true });

            let activePointerCount = 0;
            window.addEventListener('pointerdown', (e) => {
                activePointerCount++;
                if (activePointerCount > 1) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, { capture: true });

            window.addEventListener('pointerup', () => { activePointerCount = Math.max(0, activePointerCount - 1); }, { capture: true });
            window.addEventListener('pointercancel', () => { activePointerCount = Math.max(0, activePointerCount - 1); }, { capture: true });

            document.addEventListener('contextmenu', (e) => { e.preventDefault(); e.stopPropagation(); }, true);

            document.addEventListener('dragstart', (e) => { e.preventDefault(); }, true);
            document.addEventListener('drop', (e) => { e.preventDefault(); }, true);
        `).catch(() => {});
    });

    let _zoomLockBusy = false;
    mainWindow.webContents.on('zoom-changed', () => {
        if (_zoomLockBusy) return;
        _zoomLockBusy = true;
        try { mainWindow?.webContents.setZoomFactor(1.0); mainWindow?.webContents.setZoomLevel(0); } catch (_) {}
        setTimeout(() => { _zoomLockBusy = false; }, 300);
    });

    mainWindow.webContents.on('context-menu', (event) => { event.preventDefault(); });

    mainWindow.webContents.executeJavaScript(`
        (function() {

            const EDGE_BOUNDARY_PX = 30;
            const isWithinEdgeDeadzone = (x, y) => {
                const W = window.innerWidth;
                const H = window.innerHeight;

                if (y <= 70 || y >= H - 60) return false;
                return (x < EDGE_BOUNDARY_PX || x > W - EDGE_BOUNDARY_PX);
            };

            const deadzoneEvents = ['mousedown', 'mouseup', 'click', 'dblclick', 'auxclick', 'contextmenu', 'pointerdown', 'pointerup'];
            deadzoneEvents.forEach(evt => {
                document.addEventListener(evt, (e) => {

                    if (e.target && typeof e.target.closest === 'function') {
                        if (e.target.closest('button, .btn-top-close-box, .app-topbar, .modal-box, .modal-card, .modal-overlay, a, input, select, textarea, .detected-apps-card, .chip-trash-btn, .app-chip-item, .card-white, .bottom-dock-section, .btn-primary-action')) {
                            return; // Allow legitimate user interaction
                        }
                    }
                    if (isWithinEdgeDeadzone(e.clientX, e.clientY)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }, true);
            });

            document.addEventListener('contextmenu', e => e.preventDefault(), true);
            document.addEventListener('selectstart', e => {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }
            }, true);
            document.addEventListener('dragstart', e => e.preventDefault(), true);
            document.addEventListener('auxclick', e => e.preventDefault(), true);

            const CLICK_WINDOW_MS = 1200;     // Window to count repeated clicks
            const MAX_CLICKS_ALLOWED = 4;     // >4 clicks in window = suspicious trigger
            const CORNER_MARGIN = 50;         // px from screen edge = "corner zone"
            const CORNER_DWELL_MS = 800;      // How long in corner before flagging

            let clickLog = [];                // Timestamps of recent clicks
            let cornerTimer = null;
            let patternViolationCount = 0;

            function reportMouseTrigger(type, detail) {
                patternViolationCount++;
                console.warn('[AntiCheat] Mouse trigger pattern blocked:', type, detail);

                try { navigator.clipboard.writeText('').catch(() => {}); } catch(_) {}

                const flash = document.createElement('div');
                flash.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(220,38,38,0.18);z-index:2147483647;pointer-events:none;animation:fadeOut 0.7s ease forwards;';
                document.body.appendChild(flash);
                setTimeout(() => flash.remove(), 700);
            }

            document.addEventListener('mousedown', (e) => {
                const now = Date.now();
                clickLog.push({ t: now, btn: e.button, x: e.clientX, y: e.clientY });

                clickLog = clickLog.filter(c => now - c.t < CLICK_WINDOW_MS);

                const leftClicks = clickLog.filter(c => c.btn === 0).length;
                const rightClicks = clickLog.filter(c => c.btn === 2).length;

                if (leftClicks > MAX_CLICKS_ALLOWED) {
                    reportMouseTrigger('RAPID_LEFT_CLICK', leftClicks + ' left clicks in ' + CLICK_WINDOW_MS + 'ms');
                    clickLog = []; // Reset after flagging
                }
                if (rightClicks > 3) {
                    reportMouseTrigger('RAPID_RIGHT_CLICK', rightClicks + ' right clicks in ' + CLICK_WINDOW_MS + 'ms');
                    clickLog = [];
                }

                if (clickLog.length >= 5) {
                    const pattern = clickLog.slice(-5).map(c => c.btn).join(',');

                    if (['0,0,0,2,2', '2,2,0,0,0', '0,2,0,2,0', '0,0,2,0,0'].includes(pattern)) {
                        reportMouseTrigger('SEQUENTIAL_PATTERN', 'Pattern: ' + pattern);
                        clickLog = [];
                    }
                }
            }, true);

            document.addEventListener('mousemove', (e) => {
                const W = window.innerWidth;
                const H = window.innerHeight;
                const x = e.clientX;
                const y = e.clientY;
                const inCorner = (
                    (x < CORNER_MARGIN && y < CORNER_MARGIN) ||           // Top-Left
                    (x > W - CORNER_MARGIN && y < CORNER_MARGIN) ||       // Top-Right
                    (x < CORNER_MARGIN && y > H - CORNER_MARGIN) ||       // Bottom-Left
                    (x > W - CORNER_MARGIN && y > H - CORNER_MARGIN)      // Bottom-Right
                );

                if (inCorner) {
                    if (!cornerTimer) {
                        cornerTimer = setTimeout(() => {
                            reportMouseTrigger('CORNER_DWELL', 'Mouse dwelled in corner for ' + CORNER_DWELL_MS + 'ms');
                            cornerTimer = null;
                        }, CORNER_DWELL_MS);
                    }
                } else {
                    if (cornerTimer) { clearTimeout(cornerTimer); cornerTimer = null; }
                }
            }, true);

            const style = document.createElement('style');
            style.textContent = '@keyframes fadeOut { from { opacity:1; } to { opacity:0; } }';
            document.head.appendChild(style);
        })();
    `).catch(() => {});

    mainWindow.webContents.once('did-finish-load', () => {
        
        mainWindow?.webContents.executeJavaScript(`

            document.querySelectorAll('input, textarea, select, button').forEach(el => {
                el.setAttribute('aria-hidden', 'true');
                el.setAttribute('role', 'presentation');
                el.setAttribute('autocomplete', 'off');
                el.setAttribute('autocorrect', 'off');
                el.setAttribute('autocapitalize', 'off');
                el.setAttribute('spellcheck', 'false');

                try {
                    const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
                    Object.defineProperty(el, 'value', {
                        get: descriptor.get,
                        set: descriptor.set,
                        configurable: true
                    });
                } catch (_) {}
            });

            if (navigator.clipboard) {
                navigator.clipboard.readText = async () => { throw new Error('Blocked'); };
                navigator.clipboard.read = async () => { throw new Error('Blocked'); };
                navigator.clipboard.writeText = async () => { throw new Error('Blocked'); };
            }

            document.addEventListener('copy', e => { e.preventDefault(); e.clipboardData?.clearData(); }, true);
            document.addEventListener('cut', e => { e.preventDefault(); e.clipboardData?.clearData(); }, true);
            document.addEventListener('paste', e => { e.preventDefault(); }, true);
        `).catch(() => {});
    });

    mainWindow.on('close', (event) => {
        if (mainWindow && !mainWindow._allowClose) {
            event.preventDefault(); 
            console.log('[Security] Window close attempt intercepted and blocked.');
        }
    });

    mainWindow.on('closed', () => { mainWindow = null; });
}

app.whenReady().then(() => {
    if (!verifyApplicationIntegrity()) return;
    createWindow();
    startLowLevelKeyboardHook(mainWindow);

    screen.on('display-added', () => {
        if (mainWindow) {
            mainWindow.webContents.send('system:display-changed', {
                displays: screen.getAllDisplays().length
            });
        }
    });

    screen.on('display-removed', () => {
        if (mainWindow) {
            mainWindow.webContents.send('system:display-changed', {
                displays: screen.getAllDisplays().length
            });
        }
    });

    globalShortcut.register('CommandOrControl+Alt+Shift+Q', () => {
        console.log('[Admin Override] Emergency application termination.');
        if (mainWindow) mainWindow._allowClose = true;
        app.quit();
    });

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
});

app.on('window-all-closed', () => {
    if (mainWindow && mainWindow._allowClose) {
        if (process.platform !== 'darwin') app.quit();
    }
});

function getSelfAndChildPids() {
    const pids = new Set([process.pid]);
    try {
        if (mainWindow && !mainWindow.isDestroyed() && mainWindow.webContents) {
            const rPid = mainWindow.webContents.getOSProcessId();
            if (rPid) pids.add(rPid);
        }
        if (app.getAppMetrics) {
            app.getAppMetrics().forEach(m => {
                if (m && m.pid) pids.add(m.pid);
            });
        }
    } catch (_) {}
    return pids;
}

ipcMain.handle('system:get-diagnostics', async () => {
    const displays = screen.getAllDisplays();
    const primary = screen.getPrimaryDisplay();
    const totalMemGB = (os.totalmem() / (1024 ** 3)).toFixed(1);
    const freeMemGB = (os.freemem() / (1024 ** 3)).toFixed(1);

    return {
        os: `${os.type()} ${os.release()} (${os.arch()})`,
        platform: os.platform(),
        hostname: os.hostname(),
        cpus: os.cpus().length,
        cpuModel: os.cpus()[0]?.model || 'Generic Processor',
        totalMemoryGB: totalMemGB,
        freeMemoryGB: freeMemGB,
        displayCount: displays.length,
        displays: displays.map((d) => ({
            id: d.id,
            isPrimary: d.id === primary.id,
            resolution: `${d.bounds.width}x${d.bounds.height}`,
            scaleFactor: d.scaleFactor
        })),
        contentProtectionActive: true
    };
});

ipcMain.handle('system:detect-screenshare', async () => {
    return new Promise((resolve) => {
        const threats = [];
        let pending = 3;
        const done = () => { if (--pending === 0) resolve(threats); };

        const displays = screen.getAllDisplays();
        if (displays.length > 1) {
            threats.push({
                type: 'VIRTUAL_DISPLAY',
                severity: 'HIGH',
                detail: `${displays.length} displays detected — possible virtual/mirror display active (SpaceDesk, Miracast, etc.)`,
                action: 'Disconnect all external/virtual displays and close screen sharing apps'
            });
        }

        const screensharePorts = {
            28080: 'SpaceDesk Virtual Display Server',
            28081: 'SpaceDesk Discovery Service',
            7070:  'AnyDesk Remote Desktop',
            5938:  'TeamViewer Screen Share',
            5900:  'VNC Remote Desktop',
            5901:  'VNC Remote Desktop (Session 2)',
            5910:  'VNC Remote Desktop (Session 10+)',
            5931:  'UltraVNC Screen Share',
            4899:  'Radmin Remote Admin',
            3389:  'Windows RDP Screen Share',
            8801:  'Zoom Screen Share',
            5960:  'NDI Screen Capture Bridge',
        };

        exec('netstat -ano -p TCP 2>nul', { timeout: 3000 }, (err, stdout) => {
            if (!err && stdout) {
                const lines = stdout.split('\n');
                for (const [portStr, name] of Object.entries(screensharePorts)) {
                    const port = parseInt(portStr);
                    const isListening = lines.some(l =>
                        (l.includes(':' + port + ' ') || l.includes(':' + port + '\r')) &&
                        (l.includes('LISTENING') || l.includes('ESTABLISHED'))
                    );
                    if (isListening) {
                        threats.push({
                            type: 'ACTIVE_SCREENSHARE_PORT',
                            severity: 'HIGH',
                            detail: `Port ${port} (${name}) is actively LISTENING — screen sharing may be in progress`,
                            action: `Close ${name} completely`
                        });
                    }
                }
            }
            done();
        });

        const shareServices = [
            'spacedeskDRIVERSERVICE', 'spacedesk', 'NdisMiniportDriver',
            'miracastMiniport', 'MiracastVirtualDisplay', 'AnyDesk',
            'tvnserver', 'uvnc_service', 'TeamViewer'
        ];
        const scQuery = `sc query type= all state= active 2>nul`;
        exec(scQuery, { timeout: 3000 }, (err, stdout) => {
            if (!err && stdout) {
                for (const svc of shareServices) {
                    if (stdout.toLowerCase().includes(svc.toLowerCase())) {
                        threats.push({
                            type: 'SCREENSHARE_SERVICE',
                            severity: 'HIGH',
                            detail: `Screen sharing service "${svc}" is actively running`,
                            action: `Stop the "${svc}" service and disable it`
                        });
                    }
                }
            }
            done();
        });

        exec(`wmic path Win32_VideoController get Name,AdapterCompatibility /format:csv 2>nul`, { timeout: 3000 }, (err, stdout) => {
            if (!err && stdout) {
                const virtualAdapters = [
                    'spacedesk', 'innosilicon', 'iddsample', 'indirect display',
                    'miracast', 'virtual display', 'ndi', 'parsec display'
                ];
                const lower = stdout.toLowerCase();
                for (const adapter of virtualAdapters) {
                    if (lower.includes(adapter)) {
                        threats.push({
                            type: 'VIRTUAL_DISPLAY_ADAPTER',
                            severity: 'HIGH',
                            detail: `Virtual display adapter "${adapter}" detected — screenshare driver is installed and active`,
                            action: 'Uninstall or disable the virtual display adapter driver'
                        });
                        break;
                    }
                }
            }
            done();
        });
    });
});

const SAFE_OS_AND_DEV_SET = new Set([
    'system', 'registry', 'secure system', 'system idle process', 'smss.exe', 'csrss.exe', 'wininit.exe', 'services.exe', 'lsass.exe',
    'svchost.exe', 'fontdrvhost.exe', 'dwm.exe', 'spoolsv.exe', 'explorer.exe', 'taskhostw.exe',
    'sihost.exe', 'ctfmon.exe', 'shellexperiencehost.exe', 'startmenuexperiencehost.exe',
    'searchhost.exe', 'searchapp.exe', 'runtimebroker.exe', 'settingsynchost.exe', 'audiodg.exe',
    'systemsettings.exe', 'textinputhost.exe', 'applicationframehost.exe', 'electron.exe', 'node.exe', 'examfort.exe',
    'tasklist.exe', 'taskkill.exe', 'find.exe', 'wudfhost.exe', 'searchindexer.exe', 'searchprotocolhost.exe', 'searchfilterhost.exe', 'securityhealthservice.exe',
    'securityhealthsystray.exe', 'memory compression', 'smartscreen.exe', 'dashost.exe', 'dllhost.exe',
    'msdtc.exe', 'hidsvc.exe', 'deviceassociationbroker.exe', 'winlogon.exe', 'lsaiso.exe', 'wmiprvse.exe',
    'shellhost.exe', 'msmpeng.exe', 'mpdefendercorereservice.exe', 'mpdefendercoreservice.exe', 'mpdefenderservice.exe', 'nissrv.exe', 'sqlwriter.exe', 'aggregatorhost.exe',
    'git.exe', 'language_server_windows_x64.exe', 'crossdeviceservice.exe', 'crossdeviceresume.exe',
    'widgetservice.exe', 'msedgewebview2.exe', 'phoneexperiencehost.exe', 'xampp-control.exe', 'httpd.exe',
    'mysqld.exe', 'wampmanager.exe', 'cmd.exe', 'powershell.exe', 'pwsh.exe', 'conhost.exe', 'windowsterminal.exe',
    'wt.exe', 'openconsole.exe', 'antigravity.exe', 'code.exe', 'taskmgr.exe',
    'backgroundtaskhost.exe', 'useroobebroker.exe', 'widgetboard.exe', 'lockapp.exe', 'sdxhelper.exe',
    'appactions.exe', 'rvcontrolsvc.exe', 'rstmwservice.exe', 'armsvc.exe', 'officeclicktorun.exe',
    'esif_uf.exe', 'jhi_service.exe', 'oneapp.igcc.winservice.exe', 'igcc.exe', 'unsecapp.exe',
    'ngciso.exe', 'wmiregistrationservice.exe', 'microsoftstartfeedprovider.exe'
]);

let lastKnownViolations = [];

ipcMain.handle('system:scan-processes', async () => {
    return new Promise((resolve) => {
        if (os.platform() !== 'win32') return resolve([]);

        exec('tasklist /FO CSV /NH', { maxBuffer: 1024 * 1024 * 6, timeout: 3500 }, (err, stdout) => {
            if (err || !stdout) {
                return resolve(lastKnownViolations);
            }

            const violations = [];
            const lines = stdout.split('\r\n');
            const selfPids = getSelfAndChildPids();

            const behavioralMap = {
                
                'chatgpt classic.exe': 'AI Assistant / Solver Tool',
                'chatgpt.exe': 'AI Assistant / Solver Tool',
                'openai.exe': 'AI Assistant / Solver Tool',
                'copilot.exe': 'AI Assistant / Solver Tool',
                'claude.exe': 'AI Assistant / Solver Tool',
                'gemini.exe': 'AI Assistant / Solver Tool',

                'chrome.exe': 'External Web Browser (Chrome)',
                'msedge.exe': 'External Web Browser (Edge)',
                'edge.exe': 'External Web Browser (Edge)',
                'firefox.exe': 'External Web Browser (Firefox)',
                'brave.exe': 'External Web Browser (Brave)',
                'opera.exe': 'External Web Browser (Opera)',
                'opera_gx.exe': 'External Web Browser (Opera GX)',
                'vivaldi.exe': 'External Web Browser (Vivaldi)',
                'arc.exe': 'External Web Browser (Arc)',
                'tor.exe': 'External Web Browser (Tor)',
                'waterfox.exe': 'External Web Browser (Waterfox)',
                'librewolf.exe': 'External Web Browser (LibreWolf)',
                'chromium.exe': 'External Web Browser (Chromium)',
                'safari.exe': 'External Web Browser (Safari)',
                'maxthon.exe': 'External Web Browser (Maxthon)',
                'ucbrowser.exe': 'External Web Browser (UC Browser)',
                'yandex.exe': 'External Web Browser (Yandex)',
                'whale.exe': 'External Web Browser (Whale)',
                'duckduckgo.exe': 'External Web Browser (DuckDuckGo)',

                'tampermonkey.exe': 'Browser Extension / UserScript Automation',
                'violentmonkey.exe': 'Browser Extension / UserScript Automation',
                'greasemonkey.exe': 'Browser Extension / UserScript Automation',
                'native-messaging-host.exe': 'Browser Extension Helper Host',

                'anydesk.exe': 'Screen Sharing / Remote Desktop',
                'teamviewer.exe': 'Screen Sharing / Remote Desktop',
                'teamviewer_service.exe': 'Screen Sharing / Remote Desktop',
                'rustdesk.exe': 'Screen Sharing / Remote Desktop',
                'vncviewer.exe': 'Screen Sharing / Remote Desktop',
                'vnc.exe': 'Screen Sharing / Remote Desktop',
                'winvnc.exe': 'Screen Sharing / Remote Desktop',
                'tightvnc.exe': 'Screen Sharing / Remote Desktop',
                'realvnc.exe': 'Screen Sharing / Remote Desktop',
                'ultravnc.exe': 'Screen Sharing / Remote Desktop',
                'ultraviewer.exe': 'Screen Sharing / Remote Desktop',
                'parsec.exe': 'Screen Sharing / Remote Desktop',
                'nomachine.exe': 'Screen Sharing / Remote Desktop',
                'splashtop.exe': 'Screen Sharing / Remote Desktop',
                'aeroadmin.exe': 'Screen Sharing / Remote Desktop',
                'supremo.exe': 'Screen Sharing / Remote Desktop',
                'dwservice.exe': 'Screen Sharing / Remote Desktop',
                'screenleap.exe': 'Screen Sharing / Remote Desktop',
                'zohoassist.exe': 'Screen Sharing / Remote Desktop',
                'remotepc.exe': 'Screen Sharing / Remote Desktop',
                'logmein.exe': 'Screen Sharing / Remote Desktop',
                'gotomypc.exe': 'Screen Sharing / Remote Desktop',
                'quickassist.exe': 'Screen Sharing / Remote Desktop',
                'mstsc.exe': 'Screen Sharing / Windows RDP',
                'msra.exe': 'Screen Sharing / Remote Assistance',
                'rdpclip.exe': 'Screen Sharing / RDP Clipboard Bridge',
                'discord.exe': 'Screen Sharing / Remote Desktop',
                'zoom.exe': 'Screen Sharing / Remote Desktop',
                'skype.exe': 'Screen Sharing / Remote Desktop',
                'slack.exe': 'Screen Sharing / Remote Desktop',
                'teams.exe': 'Screen Sharing / Meeting Application',
                'webex.exe': 'Screen Sharing / Meeting Application',
                'bluejeans.exe': 'Screen Sharing / Meeting Application',
                'spacedesk.exe': 'Virtual Display / Screen Share',
                'spacedeskservice.exe': 'Virtual Display / Screen Share',
                'spacedeskdriverservice.exe': 'Virtual Display / Screen Share',
                'miracastview.exe': 'Virtual Display / Screen Share',
                'airserver.exe': 'Virtual Display / Screen Mirror',
                'reflector.exe': 'Virtual Display / Screen Mirror',
                'letsview.exe': 'Virtual Display / Screen Share',
                'apowermirror.exe': 'Virtual Display / Screen Share',
                'scrcpy.exe': 'Screen Mirroring Tool',
                'duet.exe': 'Virtual Display / Screen Share',
                'twomon.exe': 'Virtual Display / Screen Share',
                'moonlight.exe': 'Remote Game / Screen Streamer',
                'sunshine.exe': 'Remote Game / Screen Streamer',

                'obs64.exe': 'Screen Recording / Capture Tool',
                'obs32.exe': 'Screen Recording / Capture Tool',
                'streamlabs obs.exe': 'Screen Recording / Capture Tool',
                'camtasia.exe': 'Screen Recording / Capture Tool',
                'bandicam.exe': 'Screen Recording / Capture Tool',
                'sharex.exe': 'Screen Snipping / Text Extractor / Capture Tool',
                'lightshot.exe': 'Screen Snipping / Text Extractor / Capture Tool',
                'snippingtool.exe': 'Screen Snipping / Text Extractor / OCR Tool',
                'screenclippinghost.exe': 'Screen Snipping / Text Extractor Host',
                'snipaste.exe': 'Screen Snipping / Text Extractor Tool',
                'snipast.exe': 'Screen Snipping / Text Extractor Tool',
                'picpick.exe': 'Screen Snipping / OCR Tool',
                'flameshot.exe': 'Screen Snipping / Text Extractor Tool',
                'gyazo.exe': 'Screen Snipping / Capture Tool',
                'screentogif.exe': 'Screen Recording / Capture Tool',
                'snagit32.exe': 'Screen Snipping / OCR Tool',
                'snagit64.exe': 'Screen Snipping / OCR Tool',
                'greenshot.exe': 'Screen Snipping / OCR Tool',
                'gamebar.exe': 'Game Bar Screen Recorder',
                'avicacapturer.exe': 'Screen Recording / Capture Tool',
                'avica.exe': 'Remote Desktop / Screen Capture',
                'xsplit.exe': 'Screen Recording / Streaming Tool',
                'prismlive.exe': 'Screen Recording / Streaming Tool',

                'powertoys.textextractor.exe': 'PowerToys Screen Text Extractor / OCR Tool',
                'powertoys.powerlauncher.exe': 'PowerToys OCR / Launcher Tool',
                'powertoys.alwaysontop.exe': 'PowerToys Always-On-Top Overlay Injector',
                'powertoys.cropandlock.exe': 'PowerToys Window Overlay / Screen Cropper',
                'powertoys.exe': 'PowerToys Overlay Suite',
                'capture2text.exe': 'Capture2Text Screen OCR / Extractor Tool',
                'capture2text_cli.exe': 'Capture2Text Screen OCR / Extractor CLI',
                'textshot.exe': 'TextShot Screen OCR Text Extractor',
                'easyscreenocr.exe': 'Easy Screen OCR Text Grabber Tool',
                'easyscreenocrservice.exe': 'Easy Screen OCR Background Service',
                'tesseract.exe': 'Tesseract OCR Text Extraction Engine',
                'paddleocr.exe': 'PaddleOCR AI Text Extraction Engine',
                'easyocr.exe': 'EasyOCR Text Extraction Engine',
                'blackbox.exe': 'Blackbox AI Screen Text Extractor',
                'screentotext.exe': 'Screen To Text OCR Extractor',
                'clipgrab.exe': 'ClipGrab Screen Text/Media Harvester',

                'python.exe': 'Script Automation / Python Runtime',
                'pythonw.exe': 'Script Automation / Python Runtime',
                'py.exe': 'Script Automation / Python Launcher',
                'autohotkey.exe': 'Macro / Auto-Clicker / Hook Tool',
                'ahk.exe': 'Macro / Auto-Clicker / Hook Tool',
                'autoit3.exe': 'Macro / Automation Script',
                'autoit.exe': 'Macro / Automation Script',
                'tinytask.exe': 'Macro / Auto-Clicker / Hook Tool',
                'jitbit.exe': 'Macro / Automation Script',
                'pulover.exe': 'Macro / Automation Script',
                'autoclicker.exe': 'Macro / Auto-Clicker',
                'speedautoclicker.exe': 'Macro / Auto-Clicker',
                'opautoclicker.exe': 'Macro / Auto-Clicker',
                'gsautoclicker.exe': 'Macro / Auto-Clicker',
                'speedclicker.exe': 'Macro / Auto-Clicker',
                'clicker.exe': 'Macro / Auto-Clicker',
                'sss.exe': 'Unauthorized Scripting Tool',
                'coreservices.exe': 'Unauthorized Fake Component',

                'cheatengine-x86_64.exe': 'Cheat Engine / Memory Modifier',
                'cheatengine-i386.exe': 'Cheat Engine / Memory Modifier',
                'cheatengine.exe': 'Cheat Engine / Memory Modifier',
                'processhacker.exe': 'Process Hijacker / Debugger',
                'procexp.exe': 'Process Inspector / Debugger',
                'procmon.exe': 'System Monitor / Debugger',
                'x64dbg.exe': 'Reverse Engineering Debugger',
                'x32dbg.exe': 'Reverse Engineering Debugger',
                'ida64.exe': 'Disassembler / Reverse Tool',
                'ida.exe': 'Disassembler / Reverse Tool',
                'ghidra.exe': 'Disassembler / Reverse Tool',
                'dnspy.exe': 'Decompiler / Reverse Tool',
                'ilspy.exe': 'Decompiler / Reverse Tool',
                'ollydbg.exe': 'Debugger / Reverse Tool',
                'artmoney.exe': 'Memory Modifier / Cheat',
                'wemod.exe': 'Trainer / Cheat Engine',
                'injector.exe': 'DLL / Memory Injector',
                'xenos.exe': 'DLL / Memory Injector',
                'extremeinjector.exe': 'DLL / Memory Injector',
                'wireshark.exe': 'Network Packet Sniffer',
                'fiddler.exe': 'Network Traffic Interceptor',
                'charles.exe': 'Network Traffic Interceptor',
                'burp.exe': 'Network Traffic Interceptor',
                'burpsuite.exe': 'Network Traffic Interceptor',
                'mitmproxy.exe': 'Network Traffic Interceptor'
            };

            if (Array.isArray(PROHIBITED_PROCESSES)) {
                PROHIBITED_PROCESSES.forEach(p => {
                    if (p && p.name) {
                        behavioralMap[p.name.toLowerCase()] = p.category || p.label || 'Prohibited Process';
                    }
                });
            }

            const uniqueMap = new Map();

            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];
                if (!line || !line.startsWith('"')) continue;

                const parts = line.split('","').map(p => p.replace(/^"|"$/g, ''));
                if (parts.length >= 2) {
                    const rawName = parts[0];
                    const procName = rawName.toLowerCase();
                    const pid = parseInt(parts[1], 10);
                    const windowTitle = parts.length >= 9 ? parts[8] : '';
                    const hasActiveWindow = windowTitle && windowTitle !== 'N/A' && windowTitle.trim() !== '';

                    if (selfPids.has(pid) || pid <= 4) continue;
                    
                    const isSafeSystem = SAFE_OS_AND_DEV_SET.has(procName) || /^(asus|intel|igfx|nv|rtk|rav|waves|dts|elan|synaptics|dell|hp|lenovo|acer|msi)/i.test(procName);

                    const isCheatTitle = (hasActiveWindow && /(chatgpt|openai|copilot|claude|gemini|assistant|answer|solver|proctor|cheat|hack|inject|aimbot|hook|overlay|debugger|wireshark|fiddler|obs\s*studio|camtasia|bandicam|sharex|lightshot|greenshot|snipping|snip\s*&\s*sketch|screen\s*snippet|screen\s*capture|screen\s*record|screen\s*mirror|screen\s*share|screenshare|sharing\s*your\s*screen|presenting|anydesk|teamviewer|rustdesk|ultraviewer|parsec|vnc|zoom|discord|skype|slack|teams|webex|spacedesk|miracast|letsview|apower|scrcpy|bluestacks|nox|virtualbox|vmware|gateclass|overlayclass|securepop|tampermonkey|violentmonkey|greasemonkey|quizlet|studyx|chegg|parakeet|finalround|final\s*round|interviewcoder|interview\s*coder|sensei|ghost|teleprompter|cheatingdaddy|ultracoder)/i.test(windowTitle));

                    let threatCategory = behavioralMap[procName] || null;
                    if (!threatCategory) {
                        if (/(chatgpt|openai|copilot|claude|gemini|assistant|quillbot|grammarly|studyx|chegg|coursehero|quizlet|gauthmath|solver|parakeet|finalround|interviewcoder|sensei|ghost|teleprompter|cheatingdaddy|ultracoder|claudecopilot)/i.test(procName)) {
                            threatCategory = 'AI Assistant / Solver Tool / Overlay';
                        } else if (/(chrome|msedge|edge|firefox|brave|opera|vivaldi|arc|tor|waterfox|librewolf|chromium|safari|seb|safeexam|browser|ucbrowser|yandex|whale|duckduckgo)/i.test(procName)) {
                            threatCategory = 'External Web Browser (Prohibited during exam)';
                        } else if (/(tampermonkey|violentmonkey|greasemonkey|native-messaging-host|chrome-extension|extension-host)/i.test(procName)) {
                            threatCategory = 'Browser Extension / UserScript Automation';
                        } else if (/(anydesk|teamviewer|rustdesk|ultraviewer|parsec|vnc|nomachine|splashtop|aeroadmin|supremo|dwservice|screenleap|zohoassist|remotepc|logmein|gotomypc|quickassist|mstsc|msra|rdpclip|spacedesk|miracast|letsview|apower|scrcpy|airserver|reflector|duet|twomon|moonlight|sunshine|remote|rdp|screenshare|screensharing|desktopshare|webrtc|screenstream)/i.test(procName)) {
                            threatCategory = 'Screen Sharing / Remote Desktop';
                        } else if (/(obs|camtasia|bandicam|sharex|lightshot|snip|capture|record|screenclip|screenshot|greenshot|streamlabs|screentogif|snagit|avica|xsplit|prismlive)/i.test(procName)) {
                            threatCategory = 'Screen Recording / Capture Tool';
                        } else if (/(python|pythonw|py\.exe|ahk|autohotkey|autoit|clicker|macro|keylog|sss\.exe)/i.test(procName)) {
                            threatCategory = 'Script Automation / Macro Engine';
                        } else if (/(cheat|hack|inject|aimbot|debugger|wireshark|fiddler|charles|burp|mitmproxy|artmoney|wemod|trainer|x64dbg|x32dbg|ida64|ida\.exe|ghidra|dnspy|ilspy|ollydbg|processhacker|procmon|procexp|tesseract|autoclicker|tinytask|jitbit|pulover|speedclicker|speedautoclicker|opautoclicker|gsautoclicker|answer|gate|examcheat|proctorbypass)/i.test(procName)) {
                            threatCategory = 'Cheat Tool / Memory Modifier / Debugger';
                        } else if (/(virtualbox|vmware|vbox|bluestacks|nox|ldplayer|memu|mumu|genymotion|emulator)/i.test(procName)) {
                            threatCategory = 'Virtual Machine / Emulator';
                        } else if (/(zoom|discord|skype|slack|teams|webex|bluejeans)/i.test(procName)) {
                            threatCategory = 'Conferencing / Screen Sharing Application';
                        } else if (/(vpn|proxy|tunnel|service_manager)/i.test(procName)) {
                            threatCategory = 'VPN Tunnel / Unauthorized Background Service';
                        }

                    }

                    if (isSafeSystem && !isCheatTitle) {
                        continue;
                    }

                    if (threatCategory || isCheatTitle) {
                        const displayCategory = isCheatTitle ? 'Prohibited Cheat / Overlay Window' : threatCategory;
                        const displayLabel = hasActiveWindow && windowTitle !== 'N/A' ? `${rawName} ("${windowTitle}")` : rawName;

                        if (!uniqueMap.has(procName)) {
                            uniqueMap.set(procName, {
                                name: rawName,
                                label: displayLabel,
                                category: displayCategory,
                                classification: 'PROHIBITED',
                                confidence: 'HIGH',
                                pid: pid,
                                pids: [pid],
                                windowTitle: windowTitle,
                                instanceCount: 1,
                                detectedAt: new Date().toLocaleTimeString()
                            });
                        } else {
                            const item = uniqueMap.get(procName);
                            item.pids.push(pid);
                            item.instanceCount += 1;
                        }
                    }
                }
            }

            const results = Array.from(uniqueMap.values());
            lastKnownViolations = results;
            resolve(results);
        });
    });
});

const NEVER_KILL_SET = new Set([
    'antigravity.exe', 'antigravity', 'code.exe', 'code', 'electron.exe', 'electron',
    'node.exe', 'node', 'explorer.exe', 'dwm.exe', 'taskmgr.exe', 'taskmgr', 'system', 'registry',
    'cmd.exe', 'cmd', 'powershell.exe', 'powershell', 'pwsh.exe', 'pwsh', 'conhost.exe', 'conhost',
    'windowsterminal.exe', 'windowsterminal', 'wt.exe', 'wt', 'openconsole.exe', 'openconsole',
    'examfort.exe', 'examfort', 'wscript.exe', 'msiexec.exe', 'git.exe', 'npm.cmd', 'npx.cmd',
    'svchost.exe', 'services.exe', 'csrss.exe', 'lsass.exe'
]);

async function batchForceKillProcesses(processes) {
    if (!processes || !Array.isArray(processes) || processes.length === 0)
        return { killed: [], surviving: [] };

    const selfPids = getSelfAndChildPids();
    const targetPids = [];
    const targetNames = [];
    const inputMap = {}; 

    processes.forEach(p => {
        if (!p) return;
        const rawName = (p.name || '').toLowerCase().trim();
        if (rawName && !NEVER_KILL_SET.has(rawName)) {
            targetNames.push(rawName);
            inputMap[rawName] = p;
        }
        const allIds = [p.pid, ...(p.pids || [])].filter(
            id => id && typeof id === 'number' && id > 4 && !selfPids.has(id)
        );
        allIds.forEach(id => { targetPids.push(id); inputMap[id] = p; });
    });

    const uniquePids = [...new Set(targetPids)];
    const uniqueNames = [...new Set(targetNames)];
    if (uniquePids.length === 0 && uniqueNames.length === 0)
        return { killed: [], surviving: [] };

    console.log(`[Kill] PIDs:[${uniquePids.join(',')}] Names:[${uniqueNames.join(',')}]`);

    uniquePids.forEach(pid => { try { process.kill(pid, 'SIGKILL'); } catch (_) {} });

    const tkCommands = [];
    uniquePids.forEach(p => tkCommands.push(`taskkill /F /T /PID ${p}`));
    uniqueNames.forEach(n => {
        const cleanName = n.endsWith('.exe') ? n : `${n}.exe`;
        tkCommands.push(`taskkill /F /T /IM "${cleanName}"`);
    });
    if (tkCommands.length > 0) {
        try { execSync(tkCommands.join(' & ') + ' 2>nul', { timeout: 4000 }); } catch (_) {}
    }

    try {
        const wmicCmds = [];
        uniquePids.forEach(p => wmicCmds.push(`wmic process where "processid=${p}" call terminate 2>nul`));
        uniqueNames.forEach(n => {
            const cleanName = n.endsWith('.exe') ? n : `${n}.exe`;
            wmicCmds.push(`wmic process where "name='${cleanName}'" delete 2>nul`);
        });
        if (wmicCmds.length > 0) {
            execSync(wmicCmds.join(' & '), { timeout: 3500 });
        }
    } catch (_) {}

    try {
        const tmpPs1 = path.join(os.tmpdir(), `examfort_kill_${Date.now()}.ps1`);
        const pidArr = uniquePids.length > 0 ? uniquePids.join(',') : '0';
        const nameList = uniqueNames.map(n => `'${n.replace(/\.exe$/i, '').replace(/'/g, "''")}'`).join(',');

        const ps1 = [
            "Add-Type @'",
            "using System;",
            "using System.Runtime.InteropServices;",
            "public class NativeKiller {",
            "  [DllImport(\"kernel32.dll\", SetLastError=true)]",
            "  public static extern IntPtr OpenProcess(uint dwDesiredAccess, bool bInheritHandle, int dwProcessId);",
            "  [DllImport(\"kernel32.dll\", SetLastError=true)]",
            "  public static extern bool TerminateProcess(IntPtr hProcess, uint uExitCode);",
            "  [DllImport(\"kernel32.dll\")]",
            "  public static extern bool CloseHandle(IntPtr hObject);",
            "  public static void Kill(int pid) {",
            "    try {",
            "      IntPtr h = OpenProcess(1, false, pid);",
            "      if (h != IntPtr.Zero) {",
            "        TerminateProcess(h, 1);",
            "        CloseHandle(h);",
            "      }",
            "    } catch {}",
            "  }",
            "}",
            "'@ -ErrorAction SilentlyContinue;",
            `$pids = @(${pidArr});`,
            "foreach($p in $pids) {",
            "  if ($p -gt 4) {",
            "    [NativeKiller]::Kill($p);",
            "    Stop-Process -Id $p -Force -ErrorAction SilentlyContinue;",
            "  }",
            "}",
            nameList.length > 0 ? `$names = @(${nameList}); foreach($n in $names) { Get-Process -Name $n -ErrorAction SilentlyContinue | ForEach-Object { [NativeKiller]::Kill($_.Id); Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue; } }` : ''
        ].join("\r\n");

        fs.writeFileSync(tmpPs1, ps1, 'utf8');
        execSync(
            `powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File "${tmpPs1}"`,
            { timeout: 5000 }
        );
        try { fs.unlinkSync(tmpPs1); } catch (_) {}
    } catch (_) {}

    uniqueNames.forEach(n => {
        const baseName = n.replace(/\.exe$/i, '');
        try { execSync(`sc stop "${baseName}" 2>nul`, { timeout: 1000 }); } catch (_) {}
        try { execSync(`net stop "${baseName}" /y 2>nul`, { timeout: 1000 }); } catch (_) {}
    });

    await new Promise(r => setTimeout(r, 600));
    const surviving = [];
    const killed = [];

    for (const pid of uniquePids) {
        let alive = false;
        try {
            process.kill(pid, 0);
            alive = true;
        } catch (_) {
            alive = false;
        }
        if (alive) surviving.push(inputMap[pid] || { pid, name: `PID:${pid}` });
        else killed.push(pid);
    }

    for (const name of uniqueNames) {
        try {
            const cleanName = name.endsWith('.exe') ? name : `${name}.exe`;
            const out = execSync(`tasklist /FI "IMAGENAME eq ${cleanName}" /NH 2>nul`, { timeout: 1500 }).toString();
            const alive = out.toLowerCase().includes(cleanName.toLowerCase());
            if (alive) {
                if (!surviving.some(s => s.name?.toLowerCase() === name.toLowerCase())) {
                    surviving.push(inputMap[name] || { name });
                }
            } else {
                killed.push(name);
            }
        } catch (_) {
            killed.push(name);
        }
    }

    return { killed, surviving };
}

function forceKillProcess(pid, name, pids = []) {
    return batchForceKillProcesses([{ pid, name, pids }]);
}

ipcMain.handle('system:kill-process', async (event, payload) => {
    if (!payload || typeof payload !== 'object') {
        return { success: false, message: 'Invalid payload' };
    }
    const { pid, name, processes, pids } = payload;
    let targets = [];
    if (processes && Array.isArray(processes) && processes.length > 0) {
        targets = processes;
    } else if (pid || name) {
        targets = [{ pid, name, pids: pids || [] }];
    }
    if (targets.length === 0) return { success: false, message: 'No targets' };

    const SAFE_PROCESS_NAME_REGEX = /^[a-zA-Z0-9_\-\. ]+$/;
    const validatedTargets = [];

    for (const item of targets) {
        if (!item || typeof item !== 'object') continue;
        const validItem = {};

        if (typeof item.name === 'string' && item.name.trim().length > 0) {
            const trimmed = item.name.trim();
            if (SAFE_PROCESS_NAME_REGEX.test(trimmed)) {
                validItem.name = trimmed;
            }
        }

        if (typeof item.pid === 'number' && Number.isInteger(item.pid) && item.pid > 4) {
            validItem.pid = item.pid;
        }

        if (Array.isArray(item.pids)) {
            validItem.pids = item.pids.filter(p => typeof p === 'number' && Number.isInteger(p) && p > 4);
        }

        if (validItem.name || validItem.pid || (validItem.pids && validItem.pids.length > 0)) {
            validatedTargets.push(validItem);
        }
    }

    if (validatedTargets.length === 0) {
        return { success: false, message: 'No valid sanitized process targets' };
    }

    const result = await batchForceKillProcesses(validatedTargets);
    console.log(`[Kill Result] killed:${result.killed.length} surviving:${result.surviving.length}`);
    return {
        success: true,
        killed: result.killed,
        surviving: result.surviving
    };
});

ipcMain.handle('window:minimize', async () => {
    if (mainWindow) mainWindow.minimize();
    return { success: true };
});

ipcMain.handle('window:maximize', async () => {
    if (mainWindow) {
        if (mainWindow.isMaximized()) {
            mainWindow.unmaximize();
        } else {
            mainWindow.maximize();
        }
    }
    return { success: true };
});

ipcMain.handle('window:enter-lockdown', async () => {
    if (mainWindow) {
        mainWindow.setKiosk(true);
        mainWindow.setAlwaysOnTop(true, 'screen-saver');
        mainWindow.setContentProtection(true);
        return { success: true, mode: 'KIOSK_LOCKED' };
    }
    return { success: false };
});

ipcMain.handle('window:exit-app', async () => {
    console.log('[App] Exit authorized by user.');
    toggleWindowsTaskbar(true);
    toggleTouchpadGestures(true);
    clipCursorToSafeBounds(false);
    stopLowLevelKeyboardHook();
    if (mainWindow) mainWindow._allowClose = true;
    try {
        if (mainWindow && !mainWindow.isDestroyed()) {
            mainWindow.destroy();
        }
    } catch (_) {}
    app.exit(0);
});

app.on('will-quit', () => {
    toggleWindowsTaskbar(true);
    toggleTouchpadGestures(true);
    clipCursorToSafeBounds(false);
    stopLowLevelKeyboardHook();
});
