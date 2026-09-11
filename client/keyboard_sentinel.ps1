# ==============================================================================
# ExamFort Aegis Sentinel (Keyboard, Mouse & Anti-Cheat Overlay Neutralizer)
# Installs Win32 WH_KEYBOARD_LL and WH_MOUSE_LL low-level hooks.
# Features:
#   - 100% Topmost Superiority: No foreign overlay or small window can ever overlap.
#   - Name-Independent Generic Detection: Strips WS_EX_TOPMOST & kills any process
#     creating layered, transparent, toolwindow, or topmost overlays regardless of EXE name.
#   - Blocks synthetic/injected keystrokes & mouse clicks (bots, auto-typers, auto-clickers).
#   - Intercepts Win, Win+Tab, Alt+Tab, Alt+Esc, Ctrl+Esc, Alt+F4.
# ==============================================================================

Add-Type -TypeDefinition @"
using System;
using System.Diagnostics;
using System.Runtime.InteropServices;
using System.Text;
using System.Threading;
using System.Windows.Forms;
using System.Collections.Generic;

public class AegisKeyboardSentinel {
    private const int WH_KEYBOARD_LL = 13;
    private const int WH_MOUSE_LL = 14;

    private const int WM_KEYDOWN = 0x0100;
    private const int WM_KEYUP = 0x0101;
    private const int WM_SYSKEYDOWN = 0x0104;
    private const int WM_SYSKEYUP = 0x0105;

    private const int VK_TAB = 0x09;
    private const int VK_ESCAPE = 0x1B;
    private const int VK_LWIN = 0x5B;
    private const int VK_RWIN = 0x5C;
    private const int VK_CONTROL = 0x11;
    private const int VK_MENU = 0x12; // Alt key
    private const int VK_F4 = 0x73;

    // Win32 Extended Window Styles
    private const int GWL_EXSTYLE = -20;
    private const int WS_EX_TOPMOST = 0x00000008;
    private const int WS_EX_TRANSPARENT = 0x00000020;
    private const int WS_EX_TOOLWINDOW = 0x00000080;
    private const int WS_EX_LAYERED = 0x00080000;
    private const int WS_EX_NOACTIVATE = 0x08000000;

    private const uint SWP_NOSIZE = 0x0001;
    private const uint SWP_NOMOVE = 0x0002;
    private const uint SWP_NOACTIVATE = 0x0010;
    private const uint SWP_SHOWWINDOW = 0x0040;
    private const uint SWP_ASYNCWINDOWPOS = 0x4000;

    [StructLayout(LayoutKind.Sequential)]
    private struct KBDLLHOOKSTRUCT {
        public uint vkCode;
        public uint scanCode;
        public uint flags;
        public uint time;
        public IntPtr dwExtraInfo;
    }

    [StructLayout(LayoutKind.Sequential)]
    public struct POINT {
        public int X;
        public int Y;
    }

    [StructLayout(LayoutKind.Sequential)]
    private struct MSLLHOOKSTRUCT {
        public POINT pt;
        public uint mouseData;
        public uint flags;
        public uint time;
        public IntPtr dwExtraInfo;
    }

    [StructLayout(LayoutKind.Sequential)]
    public struct RECT {
        public int Left;
        public int Top;
        public int Right;
        public int Bottom;
    }

    private delegate IntPtr LowLevelKeyboardProc(int nCode, IntPtr wParam, IntPtr lParam);
    private delegate IntPtr LowLevelMouseProc(int nCode, IntPtr wParam, IntPtr lParam);

    [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
    private static extern IntPtr SetWindowsHookEx(int idHook, LowLevelKeyboardProc lpfn, IntPtr hMod, uint dwThreadId);

    [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
    private static extern IntPtr SetWindowsHookEx(int idHook, LowLevelMouseProc lpfn, IntPtr hMod, uint dwThreadId);

    [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
    [return: MarshalAs(UnmanagedType.Bool)]
    private static extern bool UnhookWindowsHookEx(IntPtr hhk);

    [DllImport("user32.dll", CharSet = CharSet.Auto, SetLastError = true)]
    private static extern IntPtr CallNextHookEx(IntPtr hhk, int nCode, IntPtr wParam, IntPtr lParam);

    [DllImport("kernel32.dll", CharSet = CharSet.Auto, SetLastError = true)]
    private static extern IntPtr GetModuleHandle(string lpModuleName);

    [DllImport("user32.dll")]
    private static extern short GetAsyncKeyState(int vKey);

    [DllImport("user32.dll")]
    private static extern bool EnumWindows(EnumWindowsProc lpEnumFunc, IntPtr lParam);
    public delegate bool EnumWindowsProc(IntPtr hWnd, IntPtr lParam);

    [DllImport("user32.dll", SetLastError = true, CharSet = CharSet.Auto)]
    private static extern int GetWindowText(IntPtr hWnd, StringBuilder lpString, int nMaxCount);

    [DllImport("user32.dll", SetLastError = true, CharSet = CharSet.Auto)]
    private static extern int GetClassName(IntPtr hWnd, StringBuilder lpClassName, int nMaxCount);

    [DllImport("user32.dll")]
    private static extern uint GetWindowThreadProcessId(IntPtr hWnd, out uint lpdwProcessId);

    [DllImport("user32.dll")]
    private static extern bool ShowWindow(IntPtr hWnd, int nCmdShow);

    [DllImport("user32.dll")]
    private static extern bool PostMessage(IntPtr hWnd, uint Msg, IntPtr wParam, IntPtr lParam);

    [DllImport("user32.dll")]
    private static extern void keybd_event(byte bVk, byte bScan, uint dwFlags, UIntPtr dwExtraInfo);

    [DllImport("user32.dll", EntryPoint = "GetWindowLong")]
    private static extern int GetWindowLong(IntPtr hWnd, int nIndex);

    [DllImport("user32.dll", EntryPoint = "SetWindowLong")]
    private static extern int SetWindowLong(IntPtr hWnd, int nIndex, int dwNewLong);

    [DllImport("user32.dll", SetLastError = true)]
    private static extern bool SetWindowPos(IntPtr hWnd, IntPtr hWndInsertAfter, int X, int Y, int cx, int cy, uint uFlags);

    [DllImport("user32.dll")]
    private static extern bool IsWindowVisible(IntPtr hWnd);

    [DllImport("user32.dll")]
    private static extern IntPtr GetForegroundWindow();

    [DllImport("user32.dll")]
    private static extern bool SetForegroundWindow(IntPtr hWnd);

    [DllImport("user32.dll")]
    private static extern bool BringWindowToTop(IntPtr hWnd);

    [DllImport("user32.dll")]
    private static extern bool GetWindowRect(IntPtr hWnd, out RECT lpRect);

    private static IntPtr _hookId = IntPtr.Zero;
    private static IntPtr _mouseHookId = IntPtr.Zero;
    private static LowLevelKeyboardProc _proc = HookCallback;
    private static LowLevelMouseProc _mouseProc = MouseHookCallback;
    private static int _parentPid = 0;
    private static HashSet<uint> _examPids = new HashSet<uint>();
    private static IntPtr _examHwnd = IntPtr.Zero;

    private static readonly HashSet<string> SafeSystemProcesses = new HashSet<string>(StringComparer.OrdinalIgnoreCase) {
        "explorer", "dwm", "system", "lsass", "csrss", "services", "svchost", "powershell", "pwsh", "cmd", "conhost", "taskmgr", "antigravity", "code"
    };

    public static void Start(int parentPid) {
        _parentPid = parentPid;
        _examPids.Add((uint)parentPid);
        _examPids.Add((uint)Process.GetCurrentProcess().Id);

        // Populate Exam child process PIDs
        try {
            foreach (Process p in Process.GetProcesses()) {
                try {
                    string pName = p.ProcessName.ToLower();
                    if (pName.Contains("examfort") || pName.Contains("electron")) {
                        _examPids.Add((uint)p.Id);
                    }
                } catch {}
            }
        } catch {}

        using (Process curProcess = Process.GetCurrentProcess())
        using (ProcessModule curModule = curProcess.MainModule) {
            _hookId = SetWindowsHookEx(WH_KEYBOARD_LL, _proc, GetModuleHandle(curModule.ModuleName), 0);
            _mouseHookId = SetWindowsHookEx(WH_MOUSE_LL, _mouseProc, GetModuleHandle(curModule.ModuleName), 0);
        }

        // Start aggressive background watcher thread for Topmost Dominance & Overlay Neutralization
        Thread watcher = new Thread(WatcherLoop);
        watcher.IsBackground = true;
        watcher.Start();

        // Start watchdog for parent process (if Electron dies, sentinel dies automatically)
        Thread parentWatchdog = new Thread(ParentWatchdogLoop);
        parentWatchdog.IsBackground = true;
        parentWatchdog.Start();

        // Run message pump (needed for WH_KEYBOARD_LL and WH_MOUSE_LL)
        Application.Run();

        if (_hookId != IntPtr.Zero) {
            UnhookWindowsHookEx(_hookId);
            _hookId = IntPtr.Zero;
        }
        if (_mouseHookId != IntPtr.Zero) {
            UnhookWindowsHookEx(_mouseHookId);
            _mouseHookId = IntPtr.Zero;
        }
    }

    public static void Stop() {
        if (_hookId != IntPtr.Zero) {
            UnhookWindowsHookEx(_hookId);
            _hookId = IntPtr.Zero;
        }
        if (_mouseHookId != IntPtr.Zero) {
            UnhookWindowsHookEx(_mouseHookId);
            _mouseHookId = IntPtr.Zero;
        }
        Application.Exit();
    }

    private static IntPtr HookCallback(int nCode, IntPtr wParam, IntPtr lParam) {
        if (nCode >= 0) {
            KBDLLHOOKSTRUCT hookStruct = (KBDLLHOOKSTRUCT)Marshal.PtrToStructure(lParam, typeof(KBDLLHOOKSTRUCT));
            uint vk = hookStruct.vkCode;

            // 0. BLOCK ALL SYNTHETIC / SIMULATED INJECTED KEYSTROKES (Auto-typers, SendInput, pyautogui, keybd_event)
            // LLKHF_INJECTED = 0x00000010 (16). Hardware keyboards will never have this flag set!
            if ((hookStruct.flags & 0x10) != 0) {
                return (IntPtr)1; // Drop injected keystroke immediately
            }

            // 1. Block PrintScreen (0x2C)
            if (vk == 0x2C) {
                return (IntPtr)1;
            }

            // 2. Block Function keys (F1 to F12: 0x70 to 0x7B)
            if (vk >= 0x70 && vk <= 0x7B) {
                return (IntPtr)1;
            }

            bool altDown = (hookStruct.flags & 0x20) != 0 || (GetAsyncKeyState(VK_MENU) & 0x8000) != 0;
            bool winDown = (GetAsyncKeyState(VK_LWIN) & 0x8000) != 0 || (GetAsyncKeyState(VK_RWIN) & 0x8000) != 0;
            bool ctrlDown = (GetAsyncKeyState(VK_CONTROL) & 0x8000) != 0;

            // 3. Block all Windows keys (VK_LWIN, VK_RWIN) and all Win combinations
            if (vk == VK_LWIN || vk == VK_RWIN || winDown) {
                return (IntPtr)1;
            }

            // 4. Block all Alt keys and Alt combinations (Alt+Tab, Alt+F4, Alt+Esc, Alt+Space, Alt+*)
            if (vk == VK_MENU || vk == 0xA4 || vk == 0xA5 || altDown) {
                return (IntPtr)1;
            }

            // 5. Block ALL Ctrl shortcuts EXCEPT Ctrl+Z (VK_Z = 0x5A)
            if (ctrlDown) {
                // If pressing Control key itself or 'Z' key with Ctrl -> ALLOW
                if (vk == VK_CONTROL || vk == 0xA2 || vk == 0xA3 || vk == 0x5A) {
                    return CallNextHookEx(_hookId, nCode, wParam, lParam);
                }
                // Drop all other Ctrl combinations (Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+A, Ctrl+P, etc.)
                return (IntPtr)1;
            }
        }
        return CallNextHookEx(_hookId, nCode, wParam, lParam);
    }

    private static IntPtr MouseHookCallback(int nCode, IntPtr wParam, IntPtr lParam) {
        if (nCode >= 0) {
            MSLLHOOKSTRUCT hookStruct = (MSLLHOOKSTRUCT)Marshal.PtrToStructure(lParam, typeof(MSLLHOOKSTRUCT));

            // BLOCK ALL SYNTHETIC / PROGRAMMATIC MOUSE TAMPERING (Auto-clickers, bots, pyautogui, AutoIt, SendInput, mouse_event)
            if ((hookStruct.flags & 0x01) != 0 || (hookStruct.flags & 0x02) != 0) {
                return (IntPtr)1; // Drop injected mouse move/click immediately!
            }
        }
        return CallNextHookEx(_mouseHookId, nCode, wParam, lParam);
    }

    private static void WatcherLoop() {
        while (true) {
            try {
                // 1. Locate ExamFort Main Window Handle if not cached
                if (_examHwnd == IntPtr.Zero || !IsWindowVisible(_examHwnd)) {
                    EnumWindows((hWnd, lParam) => {
                        uint pid = 0;
                        GetWindowThreadProcessId(hWnd, out pid);
                        if (_examPids.Contains(pid) && IsWindowVisible(hWnd)) {
                            StringBuilder cls = new StringBuilder(256);
                            GetClassName(hWnd, cls, 256);
                            if (cls.ToString().Contains("Chrome_WidgetWin")) {
                                _examHwnd = hWnd;
                                return false; // Found
                            }
                        }
                        return true;
                    }, IntPtr.Zero);
                }

                // 2. Continually enforce ExamFort to Absolute Topmost Z-Order (-1)
                if (_examHwnd != IntPtr.Zero) {
                    SetWindowPos(_examHwnd, (IntPtr)(-1), 0, 0, 0, 0, SWP_NOMOVE | SWP_NOSIZE | SWP_SHOWWINDOW | SWP_ASYNCWINDOWPOS);
                }

                // 3. Scan all windows for any Foreign Overlays, Cheats, or Topmost Invaders
                EnumWindows((hWnd, lParam) => {
                    uint pid = 0;
                    GetWindowThreadProcessId(hWnd, out pid);

                    // Skip ExamFort's own processes and Sentinel
                    if (_examPids.Contains(pid) || pid == 0) {
                        return true;
                    }

                    StringBuilder cls = new StringBuilder(256);
                    GetClassName(hWnd, cls, 256);
                    string clsStr = cls.ToString().ToLower();

                    // Skip Windows Desktop Root / Shell elements
                    if (clsStr == "progman" || clsStr == "workerw" || clsStr == "shell_traywnd" || clsStr == "shell_secondarytraywnd") {
                        return true;
                    }

                    StringBuilder title = new StringBuilder(256);
                    GetWindowText(hWnd, title, 256);
                    string titleStr = title.ToString().ToLower();

                    bool isVisible = IsWindowVisible(hWnd);
                    int exStyle = GetWindowLong(hWnd, GWL_EXSTYLE);

                    // A. Task View / Multitasking View Dismissal
                    if (clsStr.Contains("multitaskingview") || clsStr.Contains("xawlexplorerhost") ||
                        titleStr.Contains("task view") || titleStr.Contains("task switching")) {
                        ShowWindow(hWnd, 0); // SW_HIDE
                        PostMessage(hWnd, 0x0010, IntPtr.Zero, IntPtr.Zero); // WM_CLOSE
                        keybd_event(0x1B, 0, 0, UIntPtr.Zero);
                        keybd_event(0x1B, 0, 2, UIntPtr.Zero);
                        return true;
                    }

                    // B. Signature Detection (Titles / Classes of known AI Copilots, Cheats, OCR, Overlays)
                    bool isKnownCheatOrOverlay = 
                        clsStr.Contains("securepopclass") || clsStr.Contains("overlayclass") || clsStr.Contains("tktop") ||
                        clsStr.Contains("autohotkey") || clsStr.Contains("cheatengine") || clsStr.Contains("x64dbg") ||
                        clsStr.Contains("ida") || clsStr.Contains("processhacker") || clsStr.Contains("sunawtframe") ||
                        clsStr.Contains("parakeet") || clsStr.Contains("interview") ||
                        titleStr.Contains("parakeet") || titleStr.Contains("interview") || titleStr.Contains("copilot") ||
                        titleStr.Contains("final round") || titleStr.Contains("finalround") || titleStr.Contains("sensei") ||
                        titleStr.Contains("ghost") || titleStr.Contains("teleprompter") || titleStr.Contains("cheatingdaddy") ||
                        titleStr.Contains("cheat engine") || titleStr.Contains("ai answer") || titleStr.Contains("secure answer box") ||
                        titleStr.Contains("tampermonkey") || titleStr.Contains("violentmonkey") || titleStr.Contains("greasemonkey") ||
                        titleStr.Contains("quizlet") || titleStr.Contains("studyx") || titleStr.Contains("chegg") ||
                        titleStr.Contains("screenshare") || titleStr.Contains("sharing your screen") || titleStr.Contains("remote desktop") ||
                        titleStr.Contains("anydesk") || titleStr.Contains("teamviewer") || titleStr.Contains("rustdesk") ||
                        titleStr.Contains("ultraviewer") || titleStr.Contains("parsec") || titleStr.Contains("obs studio") ||
                        titleStr.Contains("streamlabs") || titleStr.Contains("discord") || titleStr.Contains("zoom");

                    // C. GENERIC BEHAVIORAL OVERLAY DETECTION (Independent of EXE name):
                    // Any visible foreign window with Topmost, Layered/Transparent overlay, Toolwindow, or NoActivate
                    bool isForeignTopmost = (exStyle & WS_EX_TOPMOST) != 0;
                    bool isStealthOverlay = isVisible && ((exStyle & WS_EX_LAYERED) != 0 || (exStyle & WS_EX_TRANSPARENT) != 0 || (exStyle & WS_EX_TOOLWINDOW) != 0 || (exStyle & WS_EX_NOACTIVATE) != 0);

                    string violationReason = "";
                    if (isKnownCheatOrOverlay) {
                        violationReason = "Blacklisted Cheat / Assistant Overlay Signature (" + titleStr + ")";
                    } else if (isForeignTopmost && isStealthOverlay) {
                        violationReason = "Topmost Layered Transparent Overlay (HWND_TOPMOST + WS_EX_LAYERED)";
                    } else if (isForeignTopmost) {
                        violationReason = "Topmost Window Property Detected (HWND_TOPMOST / WS_EX_TOPMOST)";
                    } else if (isStealthOverlay) {
                        violationReason = "Stealth Layered / Click-through Overlay (WS_EX_LAYERED / WS_EX_TRANSPARENT)";
                    }

                    if (!string.IsNullOrEmpty(violationReason)) {
                        // 1. Strip Topmost & Layered attributes immediately
                        SetWindowLong(hWnd, GWL_EXSTYLE, exStyle & ~WS_EX_TOPMOST & ~WS_EX_LAYERED & ~WS_EX_TRANSPARENT);

                        // 2. Push to Bottom Z-Order (HWND_BOTTOM = 1)
                        SetWindowPos(hWnd, (IntPtr)1, 0, 0, 0, 0, SWP_NOMOVE | SWP_NOSIZE | SWP_NOACTIVATE | SWP_ASYNCWINDOWPOS);

                        // 3. Hide & Force Close Window
                        ShowWindow(hWnd, 0); // SW_HIDE
                        PostMessage(hWnd, 0x0010, IntPtr.Zero, IntPtr.Zero); // WM_CLOSE

                        string procName = "Unknown";
                        try {
                            if (pid > 4) {
                                Process p = Process.GetProcessById((int)pid);
                                procName = p.ProcessName;
                                if (!SafeSystemProcesses.Contains(procName)) {
                                    p.Kill();
                                    try {
                                        Process.Start(new ProcessStartInfo("taskkill.exe", "/F /PID " + pid) {
                                            CreateNoWindow = true,
                                            UseShellExecute = false
                                        });
                                    } catch {}
                                }
                            }
                        } catch {}

                        // Print structured event to stdout for Electron Main process
                        Console.WriteLine("OVERLAY_VIOLATION|pid=" + pid + "|process=" + procName + "|title=" + title.ToString().Replace("|", "_") + "|reason=" + violationReason);

                        // 5. Instantly pull ExamFort back to absolute foreground
                        if (_examHwnd != IntPtr.Zero) {
                            BringWindowToTop(_examHwnd);
                            SetForegroundWindow(_examHwnd);
                        }
                    }

                    return true;
                }, IntPtr.Zero);

                // 4. Check Foreground Window: If a foreign app grabbed focus, restore ExamFort immediately
                IntPtr fgHwnd = GetForegroundWindow();
                if (fgHwnd != IntPtr.Zero && fgHwnd != _examHwnd) {
                    uint fgPid = 0;
                    GetWindowThreadProcessId(fgHwnd, out fgPid);
                    if (!_examPids.Contains(fgPid) && fgPid > 4) {
                        try {
                            Process p = Process.GetProcessById((int)fgPid);
                            string pName = p.ProcessName.ToLower();
                            if (!SafeSystemProcesses.Contains(pName)) {
                                ShowWindow(fgHwnd, 0);
                                PostMessage(fgHwnd, 0x0010, IntPtr.Zero, IntPtr.Zero);
                                p.Kill();
                            }
                        } catch {}

                        if (_examHwnd != IntPtr.Zero) {
                            SetForegroundWindow(_examHwnd);
                            BringWindowToTop(_examHwnd);
                        }
                    }
                }
            } catch {}

            Thread.Sleep(80); // Fast 80ms loop ensures zero visible flicker from intruders
        }
    }

    private static void ParentWatchdogLoop() {
        if (_parentPid <= 0) return;
        try {
            Process parent = Process.GetProcessById(_parentPid);
            parent.WaitForExit();
        } catch {}
        Stop();
        Environment.Exit(0);
    }
}
"@ -ReferencedAssemblies "System.Windows.Forms.dll", "System.Drawing.dll" -ErrorAction Stop

$parentPid = 0
if ($args.Count -gt 0) {
    [int]::TryParse($args[0], [ref]$parentPid) | Out-Null
}

Write-Output "KEYBOARD_SENTINEL_ACTIVE"
[AegisKeyboardSentinel]::Start($parentPid)
