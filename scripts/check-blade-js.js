/**
 * Extracts inline <script> blocks from mobileshop blade views and
 * syntax-checks them with node --check (via new Function / vm).
 */
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const base = path.join(__dirname, '..', 'resources', 'views', 'mobileshop');
const files = ['purchase.blade.php', 'sales.blade.php', 'stock.blade.php'];

let failures = 0;

for (const file of files) {
    const full = path.join(base, file);
    const content = fs.readFileSync(full, 'utf8');
    const re = /<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/gi;
    let m, i = 0;
    while ((m = re.exec(content)) !== null) {
        i++;
        // Neutralize Blade constructs so vm only validates the raw JS
        let js = m[1]
            .replace(/{!![\s\S]*?!!}/g, 'null')      // {!! !!} raw echo
            .replace(/{{[\s\S]*?}}/g, 'null')        // {{ }} escaped echo
            .replace(/@json\([^)]*\)/g, 'null')      // @json(...)
            .replace(/^\s*@(if|endif|else|elseif|foreach|endforeach|for|endfor|while|endwhile|unless|endunless|isset|endisset|php|endphp|json|comment|endcomment)[^\n]*$/gm, '');

        const line = content.slice(0, m.index).split('\n').length;
        try {
            new vm.Script(js, { filename: `${file}#block${i}` });
            console.log(`OK    ${file} block ${i} (line ${line}, ${js.length} chars)`);
        } catch (e) {
            failures++;
            console.log(`FAIL  ${file} block ${i} (line ${line}) -> ${e.message}`);
            const stack = e.stack || '';
            console.log(stack.split('\n').slice(0, 8).join('\n'));
        }
    }
}

console.log(failures === 0 ? '\nAll script blocks parse OK' : `\n${failures} script block(s) FAILED`);
process.exit(failures === 0 ? 0 : 1);
