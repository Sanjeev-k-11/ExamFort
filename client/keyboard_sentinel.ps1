# ==============================================================================
# ExamFort Aegis Sentinel (Keyboard, Mouse & Anti-Cheat Overlay Neutralizer)
# Installs Win32 WH_KEYBOARD_LL and WH_MOUSE_LL low-level hooks.
# Keyboard Drops:
#   - Windows Key (VK_LWIN, VK_RWIN)
#   - Win+Tab (VK_TAB when Win is pressed)
#   - Alt+Tab (VK_TAB when Alt is pressed)
#   - Alt+Esc / Ctrl+Esc
#   - Alt+F4
#   - Synthetic / injected keystrokes (LLKHF_INJECTED)
# Allows:
#   - Standard Tab without Alt/Win (so code editor indentation works normally!)
# Mouse Drops:
#   - Synthetic / injected mouse moves, clicks, scrolls (LLMHF_INJECTED)
#   - Protects cursor from programmatic spoofing, bots, auto-clickers, and scripts
# Allows:
#   - 100% natural, smooth hardware mouse/touchpad movement across all corners & edges
#   - No cursor jumping, bouncing, or throwing!
# Overlay Neutralizer:
#   - Automatically monitors and dismisses Task View / Multitasking View overlays
#   - Terminates known cheat/overlay windows and strips WS_EX_TOPMOST
# ==============================================================================

Add-Type -TypeDefinition @"
using System;
using System.Diagnostics;
using System.Runtime.InteropServices;
using System.Text;
using System.Threading;
using System.Windows.Forms;

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

    [DllImport("user32.dll", SetLastError = true)]
    private static extern bool SetWindowPos(IntPtr hWnd, IntPtr hWndInsertAfter, int X, int Y, int cx, int cy, uint uFlags);

    private static IntPtr _hookId = IntPtr.Zero;
    private static IntPtr _mouseHookId = IntPtr.Zero;
    private static LowLevelKeyboardProc _proc = HookCallback;
    private static LowLevelMouseProc _mouseProc = MouseHookCallback;
    private static int _parentPid = 0;

    public static void Start(int parentPid) {
        _parentPid = parentPid;

        using (Process curProcess = Process.GetCurrentProcess())
        using (ProcessModule curModule = curProcess.MainModule) {
            _hookId = SetWindowsHookEx(WH_KEYBOARD_LL, _proc, GetModuleHandle(curModule.ModuleName), 0);
            _mouseHookId = SetWindowsHookEx(WH_MOUSE_LL, _mouseProc, GetModuleHandle(curModule.ModuleName), 0);
        }

        // Start background watcher thread for Task View / Multitasking overlay and cheat windows
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

            bool altDown = (hookStruct.flags & 0x20) != 0 || (GetAsyncKeyState(VK_MENU) & 0x8000) != 0;
            bool winDown = (GetAsyncKeyState(VK_LWIN) & 0x8000) != 0 || (GetAsyncKeyState(VK_RWIN) & 0x8000) != 0;
            bool ctrlDown = (GetAsyncKeyState(VK_CONTROL) & 0x8000) != 0;

            // 1. Block Win keys completely (VK_LWIN, VK_RWIN)
            if (vk == VK_LWIN || vk == VK_RWIN) {
                return (IntPtr)1;
            }

            // 2. Block Tab switching: Alt+Tab and Win+Tab (and Ctrl+Tab if pressed)
            // Note: Standard Tab alone is allowed for code indentation!
            if (vk == VK_TAB && (altDown || winDown)) {
                return (IntPtr)1;
            }

            // 3. Block Alt+Escape & Ctrl+Escape
            if (vk == VK_ESCAPE && (altDown || ctrlDown)) {
                return (IntPtr)1;
            }

            // 4. Block Alt+F4
            if (vk == VK_F4 && altDown) {
                return (IntPtr)1;
            }
        }
        return CallNextHookEx(_hookId, nCode, wParam, lParam);
    }

    private static IntPtr MouseHookCallback(int nCode, IntPtr wParam, IntPtr lParam) {
        if (nCode >= 0) {
            MSLLHOOKSTRUCT hookStruct = (MSLLHOOKSTRUCT)Marshal.PtrToStructure(lParam, typeof(MSLLHOOKSTRUCT));

            // 🛡️ BLOCK ALL SYNTHETIC / PROGRAMMATIC MOUSE TAMPERING (Auto-clickers, bots, pyautogui, AutoIt, SendInput, mouse_event)
            // LLMHF_INJECTED = 0x00000001 (1), LLMHF_LOWER_IL_INJECTED = 0x00000002 (2)
            // Legitimate physical hardware mouse & trackpad events always have flags == 0.
            if ((hookStruct.flags & 0x01) != 0 || (hookStruct.flags & 0x02) != 0) {
                return (IntPtr)1; // Drop injected mouse move/click immediately!
            }
        }
        return CallNextHookEx(_mouseHookId, nCode, wParam, lParam);
    }

    private static void WatcherLoop() {
        while (true) {
            try {
                EnumWindows((hWnd, lParam) => {
                    uint pid = 0;
                    GetWindowThreadProcessId(hWnd, out pid);
                    if (pid == (uint)_parentPid || pid == (uint)Process.GetCurrentProcess().Id) {
                        return true;
                    }

                    StringBuilder cls = new StringBuilder(256);
                    GetClassName(hWnd, cls, 256);
                    string clsStr = cls.ToString().ToLower();

                    StringBuilder title = new StringBuilder(256);
                    GetWindowText(hWnd, title, 256);
                    string titleStr = title.ToString().ToLower();

                    // 1. Detect Task View / Multitasking View and dismiss immediately
                    if (clsStr.Contains("multitaskingview") || clsStr.Contains("xawlexplorerhost") ||
                        titleStr.Contains("task view") || titleStr.Contains("task switching")) {
                        ShowWindow(hWnd, 0); // SW_HIDE
                        PostMessage(hWnd, 0x0010, IntPtr.Zero, IntPtr.Zero); // WM_CLOSE
                        keybd_event(0x1B, 0, 0, UIntPtr.Zero);
                        keybd_event(0x1B, 0, 2, UIntPtr.Zero);
                    }

                    // 2. Terminate Known Cheat Windows, Browser Extensions, Screen Sharing & Unauthorized Overlays
                    bool isCheatWindow = clsStr.Contains("securepopclass") || clsStr.Contains("overlayclass") || 
                        clsStr.Contains("tktop") || clsStr.Contains("autohotkey") || clsStr.Contains("cheatengine") ||
                        clsStr.Contains("x64dbg") || clsStr.Contains("ida") || clsStr.Contains("processhacker") ||
                        titleStr.Contains("cheat engine") || titleStr.Contains("ai answer") || titleStr.Contains("secure answer box") || 
                        titleStr.Contains("secure code payload") || titleStr.Contains("verification gate") ||
                        titleStr.Contains("universal assistant") || titleStr.Contains("aimbot") || titleStr.Contains("memory editor") ||
                        titleStr.Contains("tampermonkey") || titleStr.Contains("violentmonkey") || titleStr.Contains("greasemonkey") ||
                        titleStr.Contains("quizlet") || titleStr.Contains("studyx") || titleStr.Contains("chegg") ||
                        titleStr.Contains("wireshark") || titleStr.Contains("fiddler") || titleStr.Contains("x64dbg");

                    bool isScreenShareWindow = titleStr.Contains("screenshare") || titleStr.Contains("screen share") ||
                        titleStr.Contains("sharing your screen") || titleStr.Contains("remote desktop") ||
                        titleStr.Contains("remote session") || titleStr.Contains("incoming connection") ||
                        titleStr.Contains("anydesk") || titleStr.Contains("teamviewer") || titleStr.Contains("rustdesk") ||
                        titleStr.Contains("ultraviewer") || titleStr.Contains("parsec") || titleStr.Contains("vnc viewer") ||
                        titleStr.Contains("obs studio") || titleStr.Contains("streamlabs") || titleStr.Contains("discord stream") ||
                        clsStr.Contains("anydesk") || clsStr.Contains("teamviewer") || clsStr.Contains("tv_wndclass");

                    bool isBrowserWindow = (clsStr.Contains("chrome_widgetwin") || clsStr.Contains("mozillawindowclass")) &&
                        (titleStr.Contains("google chrome") || titleStr.Contains("mozilla firefox") || 
                         titleStr.Contains("microsoft edge") || titleStr.Contains("brave") || titleStr.Contains("opera"));

                    if (isCheatWindow || isScreenShareWindow || isBrowserWindow) {
                        ShowWindow(hWnd, 0);
                        PostMessage(hWnd, 0x0010, IntPtr.Zero, IntPtr.Zero);
                        try {
                            if (pid > 0 && isCheatWindow) {
                                Process p = Process.GetProcessById((int)pid);
                                p.Kill();
                            }
                        } catch {}
                    }

                    // 3. Strip WS_EX_TOPMOST from non-ExamFort foreign windows so they cannot overlay
                    int exStyle = GetWindowLong(hWnd, -20);
                    if ((exStyle & 0x00000008) != 0) {
                        // Strip WS_EX_TOPMOST (HWND_NOTOPMOST = -2)
                        SetWindowPos(hWnd, (IntPtr)(-2), 0, 0, 0, 0, 0x0001 | 0x0002 | 0x0010);
                    }

                    return true;
                }, IntPtr.Zero);
            } catch {}

            Thread.Sleep(150);
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
