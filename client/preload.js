const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    getDiagnostics: () => ipcRenderer.invoke('system:get-diagnostics'),
    scanProcesses: () => ipcRenderer.invoke('system:scan-processes'),
    killProcess: (data) => ipcRenderer.invoke('system:kill-process', data),
    detectScreenShare: () => ipcRenderer.invoke('system:detect-screenshare'),
    minimizeWindow: () => ipcRenderer.invoke('window:minimize'),
    maximizeWindow: () => ipcRenderer.invoke('window:maximize'),
    enterLockdown: () => ipcRenderer.invoke('window:enter-lockdown'),
    exitApp: () => ipcRenderer.invoke('window:exit-app'),
    onDisplayChanged: (callback) => {
        ipcRenderer.on('system:display-changed', (event, data) => callback(data));
    },
    onSecurityViolation: (callback) => {
        ipcRenderer.on('security:violation', (event, data) => callback(data));
    }
});
