const { execSync } = require('child_process');
const path = require('path');
const fs = require('fs');

const localAppData = process.env.LOCALAPPDATA || path.join(process.env.USERPROFILE, 'AppData', 'Local');
const cacheDir = path.join(localAppData, 'electron-builder', 'Cache', 'winCodeSign');
const sevenZip = path.join(__dirname, 'node_modules', '7zip-bin', 'win', 'x64', '7za.exe');

console.log('Fixing electron-builder toolchain cache...');

if (fs.existsSync(cacheDir) && fs.existsSync(sevenZip)) {
    const files = fs.readdirSync(cacheDir);
    for (const file of files) {
        if (file.endsWith('.7z')) {
            const archivePath = path.join(cacheDir, file);
            const targetDir = path.join(cacheDir, 'winCodeSign-2.6.0');
            console.log(`Extracting ${file} to ${targetDir} (excluding darwin symlinks)...`);
            try {
                execSync(`"${sevenZip}" x "${archivePath}" "-o${targetDir}" "-x!*darwin*" -aoa -bd`, { stdio: 'inherit' });
                console.log('winCodeSign extracted successfully without symlink errors.');
            } catch (err) {
                console.warn('Extraction notice:', err.message);
            }
        }
    }
}
console.log('Cache preparation done.');
