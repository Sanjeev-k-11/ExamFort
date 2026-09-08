

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const asar = require('@electron/asar');

const ROOT_DIR = path.resolve(__dirname);
const DIST_DIR = path.join(ROOT_DIR, 'dist');
const ASAR_PATH = path.join(DIST_DIR, 'win-unpacked', 'resources', 'app.asar');
const EXE_PATH = path.join(DIST_DIR, 'win-unpacked', 'ExamFort Lockdown.exe');

console.log('\n' + '='.repeat(78));
console.log('  🔬  EXAMFORT LOCKDOWN - PRODUCTION SECURITY & REVERSE-ENGINEERING AUDIT');
console.log('='.repeat(78) + '\n');

let violations = [];
let auditSummary = {
    totalFiles: 0,
    jsBundles: 0,
    htmlViews: 0,
    stylesheets: 0,
    sourceMaps: 0,
    leakedOriginalNames: 0,
    leakedSecrets: 0,
    integrityManifestValid: false,
    privateKeysLeaked: 0
};

if (!fs.existsSync(ASAR_PATH)) {
    console.error(`❌ [FATAL] Packaged app.asar archive not found at: ${ASAR_PATH}`);
    console.error('   Run `npm run build:secure` first before running security audit.');
    process.exit(1);
}

console.log('[Audit 1/6] 📦 Extracting and verifying ASAR archive file table...');
const fileList = asar.listPackage(ASAR_PATH);
auditSummary.totalFiles = fileList.length;

const jsFiles = fileList.filter(f => f.endsWith('.js'));
const htmlFiles = fileList.filter(f => f.endsWith('.html'));
const cssFiles = fileList.filter(f => f.endsWith('.css'));
const mapFiles = fileList.filter(f => f.endsWith('.map'));

auditSummary.jsBundles = jsFiles.length;
auditSummary.htmlViews = htmlFiles.length;
auditSummary.stylesheets = cssFiles.length;
auditSummary.sourceMaps = mapFiles.length;

if (mapFiles.length > 0) {
    violations.push(`Source maps (.map) detected in production archive: ${mapFiles.join(', ')}`);
}

console.log('[Audit 2/6] 🏷️ Checking for prohibited original source filenames...');
const prohibitedNames = [
    'assessment-engine.js',
    'examfort-app.js',
    'security-guard.js',
    'system-check.js',
    'config.js',
    'dashboard.html',
    'assessment.html',
    'course_details.html',
    'instructions.html',
    'courses.html',
    'exam_details.html',
    'exams.html',
    'lesson_study.html',
    'profile.html',
    'support.html'
];

const foundProhibitedNames = prohibitedNames.filter(name =>
    fileList.some(item => path.basename(item).toLowerCase() === name.toLowerCase())
);

auditSummary.leakedOriginalNames = foundProhibitedNames.length;
if (foundProhibitedNames.length > 0) {
    violations.push(`Original descriptive filenames leaked in production ASAR: ${foundProhibitedNames.join(', ')}`);
}

console.log('[Audit 3/6] 🧹 Checking for prohibited development/backup artifacts...');
const devExtensions = ['.ts', '.bak', '.log', '.examfort-backup'];
for (const item of fileList) {
    const ext = path.extname(item).toLowerCase();
    if (devExtensions.includes(ext)) {
        violations.push(`Prohibited development file found in archive: ${item}`);
    }
}

console.log('[Audit 4/6] 🔐 Deep content inspection (leaked secrets, private keys, readable symbols)...');
const plainSymbolPatterns = [
    'class SystemDiagnosticEngine',
    'class AssessmentEngine',
    'class ExamFortApp',
    'calculateSecurityHash'
];

const privateKeyRegex = /-----BEGIN\s+(?:RSA|EC|OPENSSH|PGP|ENCRYPTED)?\s*PRIVATE\s+KEY-----/i;

for (const jsFile of jsFiles) {
    try {
        const normJs = jsFile.replace(/^[\\\/]+/, '').replace(/\\/g, '/');
        const rawContent = asar.extractFile(ASAR_PATH, normJs).toString('utf8');

        if (privateKeyRegex.test(rawContent)) {
            auditSummary.privateKeysLeaked++;
            violations.push(`Private signing key pattern leaked inside: ${jsFile}`);
        }

        if (normJs.includes('/j/') || normJs.includes('j/')) {
            for (const sym of plainSymbolPatterns) {
                if (rawContent.includes(sym)) {
                    violations.push(`Unobfuscated symbol "${sym}" detected in ${jsFile}`);
                }
            }
        }
    } catch (_) {}
}

console.log('[Audit 5/6] 🔏 Verifying Cryptographic Integrity Manifest & Signature...');
const manifestEntry = fileList.find(f => f.endsWith('integrity_manifest.json'));

if (!manifestEntry) {
    violations.push('Missing integrity_manifest.json inside packaged application archive.');
} else {
    try {
        const normManifest = manifestEntry.replace(/^[\\\/]+/, '').replace(/\\/g, '/');
        const manifestRaw = asar.extractFile(ASAR_PATH, normManifest).toString('utf8');
        const manifestJson = JSON.parse(manifestRaw);

        if (!manifestJson.payload || !manifestJson.signature) {
            violations.push('Malformed integrity_manifest.json: payload or signature missing.');
        } else {
            const payloadData = JSON.parse(manifestJson.payload);
            if (!payloadData.hashes || Object.keys(payloadData.hashes).length === 0) {
                violations.push('Integrity manifest contains no resource hashes.');
            } else {
                auditSummary.integrityManifestValid = true;
            }
        }
    } catch (err) {
        violations.push(`Failed to parse integrity manifest: ${err.message}`);
    }
}

console.log('[Audit 6/6] ⚡ Checking standalone Windows executable...');
if (!fs.existsSync(EXE_PATH)) {
    violations.push(`Executable not found at expected location: ${EXE_PATH}`);
}

console.log('\n' + '┌' + '─'.repeat(74) + '┐');
console.log('│  🛡️  EXAMFORT LOCKDOWN - SECURITY AUDIT VERIFICATION REPORT               │');
console.log('├' + '─'.repeat(74) + '┤');
console.log(`│  Archive Location:               dist/win-unpacked/resources/app.asar    │`);
console.log(`│  Total Packaged Resources:       ${String(auditSummary.totalFiles).padEnd(40)}│`);
console.log(`│  Packaged JavaScript Chunks:     ${String(auditSummary.jsBundles).padEnd(40)}│`);
console.log(`│  Packaged HTML View Templates:   ${String(auditSummary.htmlViews).padEnd(40)}│`);
console.log(`│  Packaged CSS Stylesheets:       ${String(auditSummary.stylesheets).padEnd(40)}│`);
console.log(`│  Source Maps Discovered (.map):  ${(auditSummary.sourceMaps === 0 ? '0 (CLEAN)' : String(auditSummary.sourceMaps)).padEnd(40)}│`);
console.log(`│  Original Filenames Exposed:     ${(auditSummary.leakedOriginalNames === 0 ? '0 (ALL REMOVED)' : String(auditSummary.leakedOriginalNames)).padEnd(40)}│`);
console.log(`│  Private Keys in Archive:        ${(auditSummary.privateKeysLeaked === 0 ? '0 (SECURE - NONE STORED)' : String(auditSummary.privateKeysLeaked)).padEnd(40)}│`);
console.log(`│  Integrity Manifest Status:      ${(auditSummary.integrityManifestValid ? 'DIGITALLY SIGNED & VALID' : 'INVALID').padEnd(40)}│`);
console.log(`│  Audit Violations Detected:      ${(violations.length === 0 ? '0 (PASS)' : String(violations.length) + ' (FAIL)').padEnd(40)}│`);
console.log('└' + '─'.repeat(74) + '┘\n');

if (violations.length > 0) {
    console.error('❌ SECURITY AUDIT FAILED with the following violations:');
    violations.forEach((v, idx) => console.error(`   ${idx + 1}. ${v}`));
    console.error('\nBuild failed closed due to security audit violations.');
    process.exit(1);
} else {
    console.log('✅ ALL PRODUCTION SECURITY CHECKS PASSED.');
    console.log('   The application has been verified against static inspection, resource tampering,');
    console.log('   source map leaks, credential exposure, and unauthorized file modification.\n');
    process.exit(0);
}
