import fs from "node:fs";
import path from "node:path";
import {fileURLToPath} from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const args = new Set(process.argv.slice(2));
const shouldSeed = args.has("--seed");

const bundles = [
    {
        name: "br-branding",
        sourceDir: "resources/css/System/br-branding",
        output: "public/System/assets/css/br-branding.css",
        description: "tokens y componentes visuales transversales",
        split: [
            [1, "00-tokens.css"],
            [197, "10-tables-breadcrumb-filters.css"],
            [755, "20-sales-tables-status.css"],
            [1234, "30-modals-fields-forms.css"],
            [1877, "40-buttons-quick-create.css"],
            [2846, "50-layout-navbar-sidebar.css"],
            [4278, "60-dashboard-sales.css"],
        ],
    },
    {
        name: "custom",
        sourceDir: "resources/css/System/custom",
        output: "public/System/assets/css/custom.css",
        description: "pantallas y ajustes System/Guest heredados",
        split: [
            [1, "00-base-guest.css"],
            [707, "10-roles-core-legacy.css"],
            [3057, "20-inventory.css"],
            [3626, "30-purchases-suppliers.css"],
            [4287, "40-cash.css"],
            [4544, "50-pos-base.css"],
            [4961, "60-service-operations.css"],
            [5724, "70-pos-extensions.css"],
            [6991, "80-dashboard-actions-customers.css"],
        ],
    },
    {
        name: "br-login",
        sourceDir: "resources/css/System/br-login",
        output: "public/System/assets/css/br-login.css",
        description: "login y accesos invitados",
        split: [
            [1, "00-login.css"],
        ],
    },
    {
        name: "demo",
        sourceDir: "resources/css/System/demo",
        output: "public/System/assets/css/demo.css",
        description: "ajustes mínimos de plantilla",
        split: [
            [1, "00-demo.css"],
        ],
    },
];

function absolute(relativePath) {
    return path.join(root, relativePath);
}

function ensureDir(directory) {
    fs.mkdirSync(directory, {recursive: true});
}

function readUtf8(file) {
    return fs.readFileSync(file, "utf8").replace(/^\uFEFF/, "");
}

function writeUtf8(file, content) {
    ensureDir(path.dirname(file));
    fs.writeFileSync(file, content, "utf8");
}

function normalizeCss(content) {
    return content
        .replace(/^\s*@charset\s+["']UTF-8["'];\s*/i, "")
        .replace(/\r\n/g, "\n")
        .trim();
}

function splitByLineNumbers(content, splitPlan) {
    const lines = content.replace(/\r\n/g, "\n").split("\n");
    return splitPlan.map(([startLine, fileName], index) => {
        const next = splitPlan[index + 1]?.[0] ?? (lines.length + 1);
        const chunk = lines.slice(startLine - 1, next - 1).join("\n");

        return [fileName, normalizeCss(chunk)];
    });
}

function seedBundle(bundle) {
    const sourceFile = absolute(bundle.output);
    const sourceDir = absolute(bundle.sourceDir);

    if (!fs.existsSync(sourceFile)) {
        throw new Error(`No existe ${bundle.output}; no se puede crear semilla.`);
    }

    ensureDir(sourceDir);

    const chunks = splitByLineNumbers(readUtf8(sourceFile), bundle.split);

    for (const [fileName, chunk] of chunks) {
        writeUtf8(path.join(sourceDir, fileName), `${chunk}\n`);
    }
}

function cssFiles(sourceDir) {
    if (!fs.existsSync(sourceDir)) {
        return [];
    }

    return fs.readdirSync(sourceDir)
        .filter((file) => file.endsWith(".css"))
        .sort((a, b) => a.localeCompare(b, "en"))
        .map((file) => path.join(sourceDir, file));
}

function buildBundle(bundle) {
    const sourceDir = absolute(bundle.sourceDir);
    const files = cssFiles(sourceDir);

    if (!files.length) {
        throw new Error(`No hay parciales CSS en ${bundle.sourceDir}. Ejecuta npm run build:css:system:seed.`);
    }

    const parts = files.map((file) => {
        const relativeFile = path.relative(root, file).replaceAll("\\", "/");
        return `/* Source: ${relativeFile} */\n${normalizeCss(readUtf8(file))}`;
    });

    const output = [
        `@charset "UTF-8";`,
        `/*`,
        ` * Archivo generado: ${bundle.output}`,
        ` * Fuente: ${bundle.sourceDir} (${bundle.description}).`,
        ` * No editar este archivo directamente; modifica los parciales y ejecuta npm run build:css:system.`,
        ` */`,
        "",
        parts.join("\n\n"),
        "",
    ].join("\n");

    writeUtf8(absolute(bundle.output), output);
}

function writeViteEntrypoint() {
    const imports = bundles.flatMap((bundle) => {
        const sourceDir = absolute(bundle.sourceDir);
        return cssFiles(sourceDir).map((file) => {
            const relativeFile = path.relative(absolute("resources/css/System"), file).replaceAll("\\", "/");
            return `@import "./${relativeFile}";`;
        });
    });

    const content = [
        "/*",
        " * Entry CSS preparado para Vite.",
        " * Hoy los layouts siguen usando los archivos generados en public/System/assets/css.",
        " * Cuando se migre el layout a @vite, este archivo ya concentra el mismo orden de parciales.",
        " */",
        ...imports,
        "",
    ].join("\n");

    writeUtf8(absolute("resources/css/System/platform.css"), content);
}

if (shouldSeed) {
    for (const bundle of bundles) {
        seedBundle(bundle);
    }
}

for (const bundle of bundles) {
    buildBundle(bundle);
}

writeViteEntrypoint();

console.log(`CSS System ${shouldSeed ? "sembrado y " : ""}generado correctamente.`);
