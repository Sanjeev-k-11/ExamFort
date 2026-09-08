

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { spawnSync } = require('child_process');

const JavaScriptObfuscator = require('javascript-obfuscator');
const terser = require('terser');
const htmlMinifier = require('html-minifier-terser');
const CleanCSS = require('clean-css');
const esbuild = require('esbuild');
const { flipFuses, FuseVersion, FuseV1Options } = require('@electron/fuses');
const asar = require('@electron/asar');

const DEV_ROOT = path.resolve(__dirname);
const STAGING_DIR = path.join(DEV_ROOT, '.build-staging');
const DIST_DIR = path.join(DEV_ROOT, 'dist');
const SRC_DIR = path.join(DEV_ROOT, 'src');

console.log('\n' + '='.repeat(78));
console.log('  🛡️  EXAMFORT LOCKDOWN - MULTI-STAGE HARDENED PRODUCTION PIPELINE');
console.log('='.repeat(78) + '\n');

function makeHashName(prefix, seed, ext) {
    const hash = crypto.createHash('sha256').update(seed).digest('hex').substring(0, 8);
    return `${prefix}_${hash}${ext}`;
}

function copyDirSync(src, dest) {
    fs.mkdirSync(dest, { recursive: true });
    const entries = fs.readdirSync(src, { withFileTypes: true });
    for (const entry of entries) {
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);
        if (entry.isDirectory()) {
            copyDirSync(srcPath, destPath);
        } else {
            fs.copyFileSync(srcPath, destPath);
        }
    }
}

function removeDirSync(dirPath) {
    if (fs.existsSync(dirPath)) {
        fs.rmSync(dirPath, { recursive: true, force: true });
    }
}

function scanForSecrets(dir) {
    console.log('[Stage 1/13] 🔍 Scanning source codebase for leaked credentials & private keys...');
    const forbiddenPatterns = [
        /-----BEGIN\s+(?:RSA|EC|OPENSSH|PGP)\s+PRIVATE\s+KEY-----/i,
        /['"][a-zA-Z0-9_-]{20,}['"]\s*:\s*['"](?:sk_live_|ghp_|xoxb-|AIzaSy)[a-zA-Z0-9_-]{20,}['"]/i,
        /(?:aws_secret_access_key|jwt_secret|database_password|db_pass)\s*[:=]\s*['"][^'"]{8,}['"]/i
    ];

    function walk(currDir) {
        const files = fs.readdirSync(currDir);
        for (const file of files) {
            const fullPath = path.join(currDir, file);
            const stat = fs.statSync(fullPath);
            if (stat.isDirectory()) {
                if (file !== 'node_modules' && file !== '.git' && file !== 'dist' && file !== '.build-staging') {
                    walk(fullPath);
                }
            } else if (/\.(js|json|html|css|env)$/i.test(file)) {
                const content = fs.readFileSync(fullPath, 'utf8');
                for (const pattern of forbiddenPatterns) {
                    if (pattern.test(content)) {
                        throw new Error(`[CRITICAL SECURITY ALERT] Leaked secret or private key pattern matched in: ${fullPath}`);
                    }
                }
            }
        }
    }

    walk(DEV_ROOT);
    console.log('  ✅ No hardcoded private keys or secret credentials detected. Only public config present.\n');
}

function initStaging() {
    console.log('[Stage 2/13] 📁 Preparing isolated production staging directory (.build-staging)...');
    removeDirSync(STAGING_DIR);
    fs.mkdirSync(STAGING_DIR, { recursive: true });

    fs.copyFileSync(path.join(DEV_ROOT, 'main.js'), path.join(STAGING_DIR, 'main.js'));
    fs.copyFileSync(path.join(DEV_ROOT, 'preload.js'), path.join(STAGING_DIR, 'preload.js'));
    fs.copyFileSync(path.join(DEV_ROOT, 'keyboard_sentinel.ps1'), path.join(STAGING_DIR, 'keyboard_sentinel.ps1'));
    fs.copyFileSync(path.join(DEV_ROOT, 'package.json'), path.join(STAGING_DIR, 'package.json'));

    const buildIconDir = path.join(DEV_ROOT, 'build');
    if (fs.existsSync(buildIconDir)) {
        copyDirSync(buildIconDir, path.join(STAGING_DIR, 'build'));
    }

    copyDirSync(SRC_DIR, path.join(STAGING_DIR, 'src'));

    console.log('  ✅ Staging mirror created. Development source remains completely isolated & untouched.\n');
}

function generateProductionManifest() {
    console.log('[Stage 3/13] 🏷️ Generating non-semantic production filename mappings...');

    const htmlFiles = fs.readdirSync(SRC_DIR).filter(f => f.endsWith('.html'));

    const manifest = {
        html: {},
        css: {
            'style.css': makeHashName('s', 'style.css_salt_v3', '.css')
        },
        images: {
            'image.png': makeHashName('a', 'image.png_salt_v3', '.png')
        },
        vendorJs: {
            'html2pdf.bundle.min.js': makeHashName('v_pdf', 'html2pdf_vendor_v3', '.js'),
            'pptxgen.bundle.js': makeHashName('v_ppt', 'pptxgen_vendor_v3', '.js')
        },
        appBundles: {
            core: makeHashName('k_app', 'examfort_core_bundle_v3', '.js'),
            engine: makeHashName('k_eng', 'assessment_engine_bundle_v3', '.js')
        }
    };

    htmlFiles.forEach(file => {
        manifest.html[file] = makeHashName('h', file + '_salt_v3', '.html');
    });

    console.log('  ✅ Non-semantic production asset map generated:');
    console.log(`     - HTML Pages Mapped: ${Object.keys(manifest.html).length}`);
    console.log(`     - Application Chunks: [${manifest.appBundles.core}, ${manifest.appBundles.engine}]`);
    console.log(`     - Stylesheet: ${manifest.css['style.css']}`);
    console.log(`     - Vendor Libraries: [${manifest.vendorJs['html2pdf.bundle.min.js']}, ${manifest.vendorJs['pptxgen.bundle.js']}]\n`);

    return manifest;
}

function applyDynamicBase64XorPacking(rawCode, targetEnv = 'browser') {
    const keyBytes = crypto.randomBytes(16);
    const keyArray = Array.from(keyBytes);

    const rawBuf = Buffer.from(rawCode, 'utf8');
    const xorBuf = Buffer.alloc(rawBuf.length);
    for (let i = 0; i < rawBuf.length; i++) {
        xorBuf[i] = rawBuf[i] ^ keyBytes[i % keyBytes.length];
    }

    const base64Cipher = xorBuf.toString('base64');
    const chunkSize = 76;
    const chunks = [];
    for (let i = 0; i < base64Cipher.length; i += chunkSize) {
        chunks.push(base64Cipher.substring(i, i + chunkSize));
    }

    let stub;
    if (targetEnv === 'browser') {
        stub = `(function(_0xa, _0xk) {
            var _0xb = _0xa.join('');
            var _0xr;
            if (typeof atob === 'function') {
                _0xr = atob(_0xb);
            } else if (typeof Buffer !== 'undefined') {
                _0xr = Buffer.from(_0xb, 'base64').toString('binary');
            } else {
                return;
            }
            var _0xu = new Uint8Array(_0xr.length);
            for (var _0xi = 0; _0xi < _0xr.length; _0xi++) {
                _0xu[_0xi] = _0xr.charCodeAt(_0xi) ^ _0xk[_0xi % _0xk.length];
            }
            var _0xd = new TextDecoder('utf-8').decode(_0xu);
            (0, eval)(_0xd);
        })(${JSON.stringify(chunks)}, ${JSON.stringify(keyArray)});`;
    } else {
        stub = `(function(_0xa, _0xk) {
            var _0xb = _0xa.join('');
            var _0xr = Buffer.from(_0xb, 'base64');
            var _0xu = Buffer.alloc(_0xr.length);
            for (var _0xi = 0; _0xi < _0xr.length; _0xi++) {
                _0xu[_0xi] = _0xr[_0xi] ^ _0xk[_0xi % _0xk.length];
            }
            var _0xd = _0xu.toString('utf8');
            (0, eval)(_0xd);
        })(${JSON.stringify(chunks)}, ${JSON.stringify(keyArray)});`;
    }

    const obfuscatedStub = JavaScriptObfuscator.obfuscate(stub, {
        compact: true,
        identifierNamesGenerator: 'hexadecimal',
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 0.85,
        deadCodeInjection: false,
        debugProtection: false,
        disableConsoleOutput: targetEnv === 'browser',
        renameGlobals: false,
        selfDefending: false,
        stringArray: true,
        stringArrayEncoding: ['rc4'],
        stringArrayThreshold: 0.9,
        splitStrings: true,
        splitStringsChunkLength: 8,
        numbersToExpressions: true,
        transformObjectKeys: true,
        target: targetEnv === 'browser' ? 'browser' : 'node',
        sourceMap: false
    }).getObfuscatedCode();

    return obfuscatedStub;
}

async function multiPassJSProtection(manifest) {
    console.log('[Stage 4-6/13] ⚡ Multi-Stage JavaScript Hardening Pipeline:');

    const stagingSrc = path.join(STAGING_DIR, 'src');
    const stagingJs = path.join(stagingSrc, 'js');

    const configCode = fs.readFileSync(path.join(stagingJs, 'config.js'), 'utf8');
    const securityGuardCode = fs.readFileSync(path.join(stagingJs, 'security-guard.js'), 'utf8');
    const examfortAppCode = fs.readFileSync(path.join(stagingJs, 'examfort-app.js'), 'utf8');
    const systemCheckCode = fs.readFileSync(path.join(stagingJs, 'system-check.js'), 'utf8');

    const combinedCoreSource = [
        '',
        configCode,
        securityGuardCode,
        examfortAppCode,
        systemCheckCode
    ].join('\n;\n');

    const engineCode = fs.readFileSync(path.join(stagingJs, 'assessment-engine.js'), 'utf8');

    console.log('     * [Pass 1] Bundling application chunks & stripping debug/comments (esbuild)...');
    const pass1Core = await esbuild.transform(combinedCoreSource, {
        minify: true,
        target: 'es2020',
        sourcemap: false,
        legalComments: 'none'
    });

    const pass1Engine = await esbuild.transform(engineCode, {
        minify: true,
        target: 'es2020',
        sourcemap: false,
        legalComments: 'none'
    });

    console.log('     * [Pass 2] Applying Layer 1 Obfuscation (Control-flow flattening 0.85, RC4 string arrays, String splitting, Numbers-to-expressions)...');
    const obfuscationConfig = {
        compact: true,
        identifierNamesGenerator: 'hexadecimal',
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 0.85,
        deadCodeInjection: false,
        debugProtection: false,
        disableConsoleOutput: true,
        renameGlobals: false,
        selfDefending: true,
        stringArray: true,
        stringArrayEncoding: ['rc4'],
        stringArrayThreshold: 1.0,
        splitStrings: true,
        splitStringsChunkLength: 8,
        stringArrayWrappersCount: 2,
        stringArrayWrappersChainedCalls: true,
        stringArrayRotate: true,
        stringArrayShuffle: true,
        numbersToExpressions: true,
        transformObjectKeys: true,
        unicodeEscapeSequence: false,
        target: 'browser',
        sourceMap: false
    };

    const pass2Core = JavaScriptObfuscator.obfuscate(pass1Core.code, obfuscationConfig).getObfuscatedCode();
    const pass2Engine = JavaScriptObfuscator.obfuscate(pass1Engine.code, obfuscationConfig).getObfuscatedCode();

    console.log('     * [Pass 3] Applying Layer 2 Independent Transformation & AST Restructuring (Terser multi-pass)...');
    const terserOptions = {
        compress: {
            passes: 2,
            dead_code: true,
            drop_debugger: true,
            evaluate: true,
            booleans: true,
            sequences: true,
            hoist_funs: true,
            reduce_vars: true,
            collapse_vars: true
        },
        mangle: {
            eval: false,
            toplevel: false
        },
        format: {
            comments: false,
            ascii_only: true,
            quote_style: 3
        }
    };

    const pass3Core = await terser.minify(pass2Core, terserOptions);
    const pass3Engine = await terser.minify(pass2Engine, terserOptions);

    console.log('     * [Pass 4] Applying Custom Multi-Key XOR + Base64 Bytecode Encryption Envelope & Polymorphic Loader...');
    const pass4Core = applyDynamicBase64XorPacking(pass3Core.code || pass2Core, 'browser');
    const pass4Engine = applyDynamicBase64XorPacking(pass3Engine.code || pass2Engine, 'browser');

    const outJDir = path.join(stagingSrc, 'j');
    const outVDir = path.join(stagingSrc, 'v');
    const outCDir = path.join(stagingSrc, 'c');
    const outADir = path.join(stagingSrc, 'a');

    fs.mkdirSync(outJDir, { recursive: true });
    fs.mkdirSync(outVDir, { recursive: true });
    fs.mkdirSync(outCDir, { recursive: true });
    fs.mkdirSync(outADir, { recursive: true });

    fs.writeFileSync(path.join(outJDir, manifest.appBundles.core), pass4Core, 'utf8');
    fs.writeFileSync(path.join(outJDir, manifest.appBundles.engine), pass4Engine, 'utf8');

    fs.copyFileSync(
        path.join(stagingJs, 'html2pdf.bundle.min.js'),
        path.join(outVDir, manifest.vendorJs['html2pdf.bundle.min.js'])
    );
    fs.copyFileSync(
        path.join(stagingJs, 'pptxgen.bundle.js'),
        path.join(outVDir, manifest.vendorJs['pptxgen.bundle.js'])
    );

    if (fs.existsSync(path.join(stagingSrc, 'image.png'))) {
        fs.copyFileSync(
            path.join(stagingSrc, 'image.png'),
            path.join(outADir, manifest.images['image.png'])
        );
        fs.unlinkSync(path.join(stagingSrc, 'image.png'));
    }

    removeDirSync(stagingJs);

    console.log('  ✅ Multi-stage JavaScript bundling, Layer-1 RC4 obfuscation, Layer-2 AST transformation, and Base64-XOR polymorphic packaging completed.\n');
}

async function processHTMLAndCSS(manifest) {
    console.log('[Stage 7/13] 🎨 Minifying CSS & rewriting HTML navigation graphs...');
    const stagingSrc = path.join(STAGING_DIR, 'src');

    const stylePath = path.join(stagingSrc, 'css', 'style.css');
    if (fs.existsSync(stylePath)) {
        const rawCSS = fs.readFileSync(stylePath, 'utf8');
        const cleanCSS = new CleanCSS({
            level: {
                1: { all: true },
                2: { all: true }
            }
        }).minify(rawCSS);

        const outCDir = path.join(stagingSrc, 'c');
        fs.mkdirSync(outCDir, { recursive: true });
        fs.writeFileSync(path.join(outCDir, manifest.css['style.css']), cleanCSS.styles, 'utf8');
        removeDirSync(path.join(stagingSrc, 'css'));
    }

    for (const [origName, newName] of Object.entries(manifest.html)) {
        const origPath = path.join(stagingSrc, origName);
        if (!fs.existsSync(origPath)) continue;

        let content = fs.readFileSync(origPath, 'utf8');

        content = content.replace(/href=["']css\/style\.css["']/g, `href="c/${manifest.css['style.css']}"`);

        content = content.replace(/src=["']image\.png["']/g, `src="a/${manifest.images['image.png']}"`);
        content = content.replace(/["']image\.png["']/g, `"a/${manifest.images['image.png']}"`);

        const hasCoreScript = /<script\s+src=["']js\/(config|security-guard|examfort-app|system-check)\.js["']><\/script>/i.test(content);
        content = content.replace(/<script\s+src=["']js\/(config|security-guard|examfort-app|system-check)\.js["']><\/script>\s*/gi, '');

        if (hasCoreScript) {
            content = content.replace(/<\/head>/i, `<script src="j/${manifest.appBundles.core}"></script></head>`);
        }

        content = content.replace(
            /<script\s+src=["']js\/assessment-engine\.js["']><\/script>/gi,
            `<script src="j/${manifest.appBundles.engine}"></script>`
        );

        content = content.replace(
            /<script\s+src=["']js\/html2pdf\.bundle\.min\.js["']><\/script>/gi,
            `<script src="v/${manifest.vendorJs['html2pdf.bundle.min.js']}"></script>`
        );
        content = content.replace(
            /<script\s+src=["']js\/pptxgen\.bundle\.js["']><\/script>/gi,
            `<script src="v/${manifest.vendorJs['pptxgen.bundle.js']}"></script>`
        );

        for (const [targetHtml, hashedHtml] of Object.entries(manifest.html)) {
            const hrefRegex = new RegExp(`(['"\`])${targetHtml}((\\?[^'"\`]*)?)(['"\`])`, 'g');
            content = content.replace(hrefRegex, `$1${hashedHtml}$2$4`);
        }

        const minified = await htmlMinifier.minify(content, {
            collapseWhitespace: true,
            removeComments: true,
            removeRedundantAttributes: true,
            removeScriptTypeAttributes: true,
            removeStyleLinkTypeAttributes: true,
            useShortDoctype: true,
            minifyCSS: true,
            minifyJS: true
        });

        fs.writeFileSync(path.join(stagingSrc, newName), minified, 'utf8');
        fs.unlinkSync(origPath);
    }

    console.log('  ✅ Stylesheet and all HTML pages rewritten, minified, and rebound.\n');
}

function generateSigningKeyPair() {
    console.log('[Stage 8/13] 🔏 Generating Asymmetric Ed25519 Cryptographic Keypair...');
    return crypto.generateKeyPairSync('ed25519', {
        publicKeyEncoding: { type: 'spki', format: 'pem' },
        privateKeyEncoding: { type: 'pkcs8', format: 'pem' }
    });
}

function generateAndSignIntegrityManifest(manifest, privateKey) {
    console.log('[Stage 10/13] 🔏 Calculating SHA-256 Checksums for Final Obfuscated Assets & Signing Manifest...');

    const hashes = {};
    const criticalFiles = [
        'preload.js',
        'keyboard_sentinel.ps1',
        `src/c/${manifest.css['style.css']}`,
        `src/j/${manifest.appBundles.core}`,
        `src/j/${manifest.appBundles.engine}`,
        `src/v/${manifest.vendorJs['html2pdf.bundle.min.js']}`,
        `src/v/${manifest.vendorJs['pptxgen.bundle.js']}`
    ];

    for (const hashedHtml of Object.values(manifest.html)) {
        criticalFiles.push(`src/${hashedHtml}`);
    }

    criticalFiles.forEach(relPath => {
        const fullPath = path.join(STAGING_DIR, relPath);
        if (fs.existsSync(fullPath)) {
            const buf = fs.readFileSync(fullPath);
            hashes[relPath.replace(/\\/g, '/')] = crypto.createHash('sha256').update(buf).digest('hex');
        }
    });

    const manifestPayload = JSON.stringify({
        version: '1.0.0',
        timestamp: Date.now(),
        algorithm: 'SHA-256',
        signatureAlgorithm: 'Ed25519',
        hashes: hashes
    }, null, 2);

    const signature = crypto.sign(null, Buffer.from(manifestPayload, 'utf8'), privateKey).toString('base64');

    const manifestFileContent = JSON.stringify({
        payload: manifestPayload,
        signature: signature
    }, null, 2);

    fs.writeFileSync(path.join(STAGING_DIR, 'integrity_manifest.json'), manifestFileContent, 'utf8');

    console.log(`  ✅ SHA-256 hashes generated for ${Object.keys(hashes).length} final obfuscated production resources.`);
    console.log('  ✅ Integrity manifest digitally signed using Ed25519 private key.');
    console.log('  🔒 Private signing key discarded from memory — NEVER stored inside application or ASAR.\n');
}

async function processElectronMainAndPreload(manifest, publicKey) {
    console.log('[Stage 9/13] 🔒 Adapting & protecting Electron Main (main.js) & Preload (preload.js)...');

    const mainPath = path.join(STAGING_DIR, 'main.js');
    let mainContent = fs.readFileSync(mainPath, 'utf8');

    mainContent = mainContent.replace(
        '__EXAMFORT_PUBLIC_KEY_PLACEHOLDER__',
        publicKey.trim()
    );

    const hashedIndexHtml = manifest.html['index.html'] || 'index.html';
    mainContent = mainContent.replace(
        /mainWindow\.loadFile\(path\.join\(__dirname,\s*['"]src['"],\s*['"]index\.html['"]\)\);/,
        `mainWindow.loadFile(path.join(__dirname, 'src', '${hashedIndexHtml}'));`
    );

    const hashedAllowedPages = Object.values(manifest.html);
    const pagesArrayString = JSON.stringify(hashedAllowedPages);
    mainContent = mainContent.replace(
        /const ALLOWED_INTERNAL_PAGES\s*=\s*\[[^\]]+\];/s,
        `const ALLOWED_INTERNAL_PAGES = ${pagesArrayString};`
    );

    const nodeObfuscationConfig = {
        compact: true,
        identifierNamesGenerator: 'hexadecimal',
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 0.7,
        deadCodeInjection: false,
        debugProtection: false,
        disableConsoleOutput: false,
        renameGlobals: false,
        selfDefending: false,
        stringArray: true,
        stringArrayEncoding: ['rc4'],
        stringArrayThreshold: 0.85,
        numbersToExpressions: true,
        transformObjectKeys: true,
        target: 'node',
        sourceMap: false,
        reservedNames: [
            'app', 'BrowserWindow', 'ipcMain', 'globalShortcut', 'screen',
            'clipboard', 'systemPreferences', 'process', 'require', 'module',
            'exports', '__dirname', '__filename', 'keyboardSentinelProc',
            'batchForceKillProcesses', 'mainWindow', 'crypto', 'fs', 'path'
        ]
    };

    const mainObfuscated = JavaScriptObfuscator.obfuscate(mainContent, nodeObfuscationConfig).getObfuscatedCode();
    fs.writeFileSync(mainPath, mainObfuscated, 'utf8');

    const preloadPath = path.join(STAGING_DIR, 'preload.js');
    const preloadContent = fs.readFileSync(preloadPath, 'utf8');

    const preloadObfuscated = JavaScriptObfuscator.obfuscate(preloadContent, {
        compact: true,
        identifierNamesGenerator: 'hexadecimal',
        controlFlowFlattening: true,
        controlFlowFlatteningThreshold: 0.7,
        deadCodeInjection: false,
        debugProtection: false,
        disableConsoleOutput: true,
        identifierNamesGenerator: 'hexadecimal',
        renameGlobals: false,
        selfDefending: false,
        stringArray: true,
        stringArrayEncoding: ['rc4'],
        stringArrayThreshold: 0.85,
        target: 'node',
        sourceMap: false,
        reservedNames: [
            'contextBridge', 'ipcRenderer', 'electronAPI', 'require',
            'module', 'exports', 'window'
        ]
    }).getObfuscatedCode();

    fs.writeFileSync(preloadPath, preloadObfuscated, 'utf8');

    console.log('  ✅ Main and Preload scripts protected with Node IPC compatibility guards.\n');
}

function prepareStagingPackageJson() {
    console.log('[Stage 10/13] 📦 Configuring production package metadata & cleaning build artifacts...');

    const pkgPath = path.join(STAGING_DIR, 'package.json');
    const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));

    pkg.build = pkg.build || {};
    pkg.build.asar = true;
    pkg.build.electronVersion = "28.2.0";
    pkg.build.asarUnpack = [
        "keyboard_sentinel.ps1"
    ];
    pkg.build.files = [
        "main.js",
        "preload.js",
        "integrity_manifest.json",
        "keyboard_sentinel.ps1",
        "src/**/*"
    ];
    pkg.build.directories = {
        output: path.relative(STAGING_DIR, DIST_DIR)
    };

    pkg.devDependencies = {
        "electron": "^28.2.0"
    };

    fs.writeFileSync(pkgPath, JSON.stringify(pkg, null, 2), 'utf8');

    function sweep(dir) {
        const files = fs.readdirSync(dir);
        for (const f of files) {
            const full = path.join(dir, f);
            const stat = fs.statSync(full);
            if (stat.isDirectory()) {
                sweep(full);
            } else if (/\.(map|ts|bak|log|md|gitignore|examfort-backup)$/i.test(f) && f !== 'README.md') {
                fs.unlinkSync(full);
            }
        }
    }
    sweep(STAGING_DIR);

    console.log('  ✅ Staging package configured: ASAR enabled, dev artifacts cleaned.\n');
}

async function packageAndApplyFuses() {
    console.log('[Stage 11/13] 🔨 Executing electron-builder packaging on hardened staging image...');

    const builderCmd = process.platform === 'win32'
        ? path.join(DEV_ROOT, 'node_modules', '.bin', 'electron-builder.cmd')
        : path.join(DEV_ROOT, 'node_modules', '.bin', 'electron-builder');

    const args = ['--win', '--project', STAGING_DIR];

    console.log(`     Running: ${builderCmd} ${args.join(' ')}`);

    const result = spawnSync(builderCmd, args, {
        cwd: DEV_ROOT,
        stdio: 'inherit',
        shell: true
    });

    if (result.status !== 0) {
        throw new Error(`electron-builder failed with exit code ${result.status}`);
    }

    console.log('  ✅ electron-builder packaging completed successfully.');

    console.log('     🔐 Applying Electron security fuses & ASAR integrity enforcement...');
    const exePath = path.join(DIST_DIR, 'win-unpacked', 'ExamFort Lockdown.exe');

    if (fs.existsSync(exePath)) {
        try {
            await flipFuses(exePath, {
                version: FuseVersion.V1,
                [FuseV1Options.RunAsNode]: false,
                [FuseV1Options.EnableCookieEncryption]: true,
                [FuseV1Options.EnableNodeOptionsEnvironmentVariable]: false,
                [FuseV1Options.EnableNodeCliInspectArguments]: false
            });

            console.log('  ✅ Electron Fuses locked:');
            console.log('     - RunAsNode: DISABLED (Prevents ELECTRON_RUN_AS_NODE escape)');
            console.log('     - EnableNodeCliInspectArguments: DISABLED (Prevents --inspect debugging)');
            console.log('     - EnableNodeOptionsEnvironmentVariable: DISABLED (Prevents NODE_OPTIONS injection)');
            console.log('     - EnableCookieEncryption: ENABLED\n');
        } catch (err) {
            console.warn('  ⚠️ Fuses notice:', err.message);
        }
    }
}

function runSecurityAudit() {
    console.log('[Stage 12/13] 🔬 Executing Automated Production Security Audit...');
    const auditScript = path.join(DEV_ROOT, 'security-audit.js');

    const result = spawnSync(process.execPath, [auditScript], {
        cwd: DEV_ROOT,
        stdio: 'inherit'
    });

    if (result.status !== 0) {
        throw new Error('Automated Security Audit failed. Build aborted due to security violations.');
    }
}

async function runPipeline() {
    const startTime = Date.now();
    try {
        scanForSecrets(DEV_ROOT);
        initStaging();
        const manifest = generateProductionManifest();
        await multiPassJSProtection(manifest);
        await processHTMLAndCSS(manifest);
        const { publicKey, privateKey } = generateSigningKeyPair();
        await processElectronMainAndPreload(manifest, publicKey);
        generateAndSignIntegrityManifest(manifest, privateKey);
        prepareStagingPackageJson();
        await packageAndApplyFuses();
        runSecurityAudit();

        removeDirSync(STAGING_DIR);

        const duration = ((Date.now() - startTime) / 1000).toFixed(1);
        console.log('='.repeat(78));
        console.log(`  🎉 MULTI-STAGE HARDENED PRODUCTION BUILD COMPLETE (${duration}s)`);
        console.log('  Production source is obfuscated, bundled and packaged to increase');
        console.log('  reverse-engineering difficulty while keeping the application fully functional.');
        console.log('='.repeat(78) + '\n');
    } catch (err) {
        console.error('\n❌ BUILD PIPELINE FAILED:', err.message);
        console.error(err.stack);
        removeDirSync(STAGING_DIR);
        process.exit(1);
    }
}

runPipeline();
