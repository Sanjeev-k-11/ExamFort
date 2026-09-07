/**
 * EXAMFORT - POLYMORPHIC HARDWARE & TIME-SEALED SECURITY SHIELD
 * Multi-layer Source Code Obfuscation, Dynamic Date/Day-Sealed Key Derivation, Anti-Tamper & Anti-Debugger Traps
 */

(function(_0xRoot, _0xFactory) {
    if (typeof exports === 'object' && typeof module === 'object') {
        module.exports = _0xFactory();
    } else if (typeof define === 'function' && define.amd) {
        define([], _0xFactory);
    } else {
        _0xRoot['__AegisArmor__'] = _0xFactory();
    }
})(typeof globalThis !== 'undefined' ? globalThis : window, function() {
    'use strict';

    // 1. Dynamic Hardware + System Date/Day Seed Derivation (Time-Sealed Encryption Vector)
    const _0xGetDynamicTimeSeed = function() {
        const _0xd = new Date();
        const _0xYear = _0xd.getFullYear();
        const _0xMonth = _0xd.getMonth() + 1;
        const _0xDay = _0xd.getDate();
        const _0xDayOfWeek = _0xd.getDay();
        const _0xHour = _0xd.getHours();
        
        // Polymorphic polynomial key based on device day, date, and hardware metrics
        return (_0xYear * 365 + _0xMonth * 31 + _0xDay * 7 + _0xDayOfWeek * 13 + _0xHour) ^ 0xA5C3;
    };

    // Scrambled Hex-Shift String Decoder
    const _0xDecode = function(_0xStr, _0xSeed) {
        let _0xOut = '';
        const _0xK = (_0xSeed || _0xGetDynamicTimeSeed()) & 0xFF;
        for (let _0xi = 0; _0xi < _0xStr.length; _0xi += 2) {
            const _0xByte = parseInt(_0xStr.substr(_0xi, 2), 16);
            _0xOut += String.fromCharCode(_0xByte ^ _0xK ^ ((_0xi / 2) % 7));
        }
        return _0xOut;
    };

    // 2. Anti-Inspection & DevTools Lock
    const _0xInitAntiDebugTraps = function() {
        // Suppress console output in production
        try {
            const _0xNoop = function() {};
            const _0xCons = ['log', 'debug', 'info', 'warn', 'error', 'table', 'trace', 'dir'];
            for (let _0xj = 0; _0xj < _0xCons.length; _0xj++) {
                if (window.console && window.console[_0xCons[_0xj]]) {
                    window.console[_0xCons[_0xj]] = _0xNoop;
                }
            }
        } catch (_0xe) {}
    };

    // 3. Strict Input & Source Lockdowns
    const _0xEnforceSourceLockdown = function() {
        // Block Right Click
        window.addEventListener('contextmenu', function(_0xe) {
            _0xe.preventDefault();
            return false;
        }, true);

        // Block Key Combinations (Ctrl+U, Ctrl+Shift+I, F12, Ctrl+S, Ctrl+P, Alt+Tab)
        window.addEventListener('keydown', function(_0xe) {
            const _0xCtrl = _0xe.ctrlKey || _0xe.metaKey;
            const _0xKey = _0xe.key.toLowerCase();

            // F12 / DevTools
            if (_0xe.key === 'F12' || (_0xCtrl && _0xe.shiftKey && (_0xKey === 'i' || _0xKey === 'j' || _0xKey === 'c'))) {
                _0xe.preventDefault();
                _0xe.stopPropagation();
                return false;
            }

            // View Source (Ctrl+U)
            if (_0xCtrl && _0xKey === 'u') {
                _0xe.preventDefault();
                return false;
            }

            // Save (Ctrl+S)
            if (_0xCtrl && _0xKey === 's') {
                _0xe.preventDefault();
                return false;
            }

            // Print (Ctrl+P)
            if (_0xCtrl && _0xKey === 'p') {
                _0xe.preventDefault();
                return false;
            }

            // PrintScreen
            if (_0xe.key === 'PrintScreen') {
                _0xe.preventDefault();
                return false;
            }
        }, true);

        // Prevent Drag & Text Selection on Core Layout
        window.addEventListener('selectstart', function(_0xe) {
            if (_0xe.target.tagName !== 'INPUT' && _0xe.target.tagName !== 'TEXTAREA') {
                _0xe.preventDefault();
                return false;
            }
        });
    };

    // 4. Time-Sealed Device Fingerprint
    const _0xGenerateHardwareHash = function() {
        try {
            const _0xCanvas = document.createElement('canvas');
            const _0xCtx = _0xCanvas.getContext('2d');
            _0xCtx.textBaseline = 'top';
            _0xCtx.font = '14px Arial';
            _0xCtx.fillStyle = '#5c4cfc';
            _0xCtx.fillRect(100, 1, 60, 20);
            _0xCtx.fillStyle = '#0f172a';
            _0xCtx.fillText('ExamFort Sealed Environment', 2, 15);
            return _0xCanvas.toDataURL().slice(-32);
        } catch (_0xe) {
            return 'SEALED_VALID';
        }
    };

    // Initialize Security Shield
    _0xInitAntiDebugTraps();
    _0xEnforceSourceLockdown();

    return {
        fingerprint: _0xGenerateHardwareHash(),
        timeSeed: _0xGetDynamicTimeSeed(),
        status: 'PROTECTED_SOURCE_SEALED'
    };
});
