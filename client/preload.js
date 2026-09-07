const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    // System Diagnostics
    getDiagnostics: () => ipcRenderer.invoke('system:get-diagnostics'),
    
    // Process Sentinel & 1-Click Terminator
    scanProcesses: () => ipcRenderer.invoke('system:scan-processes'),
    killProcess: (data) => ipcRenderer.invoke('system:kill-process', data),

    // Behavioral Screenshare Detection (port + driver + virtual display)
    detectScreenShare: () => ipcRenderer.invoke('system:detect-screenshare'),
    
    // Window Lockdown & Controls
    minimizeWindow: () => ipcRenderer.invoke('window:minimize'),
    maximizeWindow: () => ipcRenderer.invoke('window:maximize'),
    enterLockdown: () => ipcRenderer.invoke('window:enter-lockdown'),
    exitApp: () => ipcRenderer.invoke('window:exit-app'),
    
    // Realtime Security Event Listeners
    onDisplayChanged: (callback) => {
        ipcRenderer.on('system:display-changed', (event, data) => callback(data));
    },
    onSecurityViolation: (callback) => {
        ipcRenderer.on('security:violation', (event, data) => callback(data));
    }
});
